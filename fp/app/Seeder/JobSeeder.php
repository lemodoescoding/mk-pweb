<?php

declare(strict_types=1);

namespace App\Seeder;

// use App\Core\DB;

use App\Utils\GeoFetch;
use App\Utils\JobsFetcher;

use \PDO;

class JobSeeder
{
  private PDO $db;

  public function __construct(PDO $db)
  {
    $this->db = $db;
  }

  public function seed(array $categories, array $countries, int $pages = 1, int $start_page = 1): array
  {
    $fetched = JobsFetcher::fetchJSearchJobs($categories, $countries, $pages, $start_page);

    foreach ($fetched as $categoryName => $jobs) {
      // echo "Processing category: {$categoryName}\n";

      $categoryId = $this->insertCategory($categoryName);

      foreach ($jobs as $job) {
        $this->storeJob($job, $categoryId);
      }
    }

    return $fetched;
  }

  private function storeJob(array $job, int $categoryId): void
  {
    if (empty($job['employer_name'])) {
      return; // Skip job
    }

    $sourceId   = $this->insertSource('jsearch');
    $companyId  = $this->insertCompany($job);
    $locationId = $this->insertLocation($job);

    if($sourceId == 0 || $companyId == 0 || $locationId == 0){
      return;
    }

    $jobId = $this->insertJob($job, $companyId, $locationId, $sourceId);

    if ($jobId) {
      $this->insertJobCategory($jobId, $categoryId);
    }
  }

  private function insertSource(string $name): int
  {
    $stmt = $this->db->prepare("
            INSERT IGNORE INTO job_sources (name) VALUES (:name)
        ");
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->execute();

    return (int) $this->db->lastInsertId() ?: $this->getIdByField('job_sources', 'name', $name);
  }

  private function insertCompany(array $job): int
  {
    $stmt = $this->db->prepare("
            INSERT INTO companies (name, logo, website)
            VALUES (:name, :logo, :website)
            ON DUPLICATE KEY UPDATE logo = VALUES(logo), website = VALUES(website)
        ");

    $companyName = $job['employer_name'] ?? 'Unknown Company';
    $stmt->bindValue(':name', $companyName, PDO::PARAM_STR);
    $stmt->bindValue(':logo', $job['employer_logo'], PDO::PARAM_STR);
    $stmt->bindValue(':website', $job['employer_website'], PDO::PARAM_STR);

    $stmt->execute();

    return (int) $this->db->lastInsertId() ??
      $this->getIdByField('companies', 'name', $job['employer_name']);
  }

  private function insertLocation(array $job): int
  {
    $stmt = $this->db->prepare("
            INSERT INTO locations (city, state, country, lat, lon)
            VALUES (:city, :state, :country, :lat, :lon)
            ON DUPLICATE KEY UPDATE id = id
        ");

    $stmt->bindValue(':city', $job['city'], PDO::PARAM_STR);
    $stmt->bindValue(':state', $job['state'], PDO::PARAM_STR);
    $stmt->bindValue(':country', $job['country'], PDO::PARAM_STR);
    $stmt->bindValue(':lat', $job['lat']);
    $stmt->bindValue(':lon', $job['lon']);

    $stmt->execute();

    return (int) $this->db->lastInsertId() ?:
      $this->getLocationId($job);
  }

  private function insertJob(array $job, int $companyId, int $locationId, int $sourceId): ?int
  {
    $stmt = $this->db->prepare("
            INSERT INTO jobs (
                external_id, title, description, apply_link, is_remote,
                company_id, location_id, min_salary, max_salary, salary_period,
                source_id, date_posted, raw_json
            ) VALUES (
                :external_id, :title, :description, :apply_link, :is_remote,
                :company_id, :location_id, :salary_min, :salary_max, :salary_period,
                :source_id, :date_posted, :raw_json
            )
            ON DUPLICATE KEY UPDATE external_id = external_id
        ");

    $stmt->bindValue(':external_id', $job['external_id'], PDO::PARAM_STR);
    $stmt->bindValue(':title', htmlspecialchars($job['title']), PDO::PARAM_STR);
    $stmt->bindValue(':description', htmlspecialchars($job['description']), PDO::PARAM_STR);
    $stmt->bindValue(':apply_link', json_encode($job['apply_options']));
    $stmt->bindValue(':is_remote', $job['is_remote'] ? 1 : 0);

    $stmt->bindValue(':company_id', $companyId, PDO::PARAM_INT);
    $stmt->bindValue(':location_id', $locationId, PDO::PARAM_INT);

    $stmt->bindValue(':salary_min', $job['min_salary'] ?? null);
    $stmt->bindValue(':salary_max', $job['max_salary'] ?? null);
    $stmt->bindValue(':salary_period', $job['salary_period'] ?? null);

    $stmt->bindValue(':source_id', $sourceId);
    $stmt->bindValue(':date_posted', date('Y-m-d H:i:s', $job['posted_at_timestamp']));
    $stmt->bindValue(':raw_json', json_encode($job, JSON_UNESCAPED_SLASHES));

    $stmt->execute();

    return (int) $this->db->lastInsertId() ?:
      $this->getIdByField('jobs', 'external_id', $job['external_id']);
  }

  private function insertCategory(string $name): int
  {
    $stmt = $this->db->prepare("
            INSERT IGNORE INTO categories (name) VALUES (:name)
        ");
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->execute();

    return (int) $this->db->lastInsertId() ?:
      $this->getIdByField('categories', 'name', $name);
  }

  private function insertJobCategory(int $jobId, int $categoryId): void
  {
    $stmt = $this->db->prepare("
            INSERT IGNORE INTO job_categories (job_id, category_id)
            VALUES (:job, :cat)
        ");

    $stmt->bindValue(':job', $jobId, PDO::PARAM_INT);
    $stmt->bindValue(':cat', $categoryId, PDO::PARAM_INT);

    $stmt->execute();
  }

  private function getIdByField(string $table, string $field, string $value): int
  {
    $stmt = $this->db->prepare("SELECT id FROM {$table} WHERE {$field} = :v LIMIT 1");
    $stmt->bindValue(':v', $value);
    $stmt->execute();

    return (int) ($stmt->fetchColumn() ?: 0);
  }

  private function getLocationId(array $job): int
  {
    $stmt = $this->db->prepare("
            SELECT id FROM locations
            WHERE city = :city AND state = :state AND country = :country
            LIMIT 1
        ");

    $stmt->bindValue(':city', $job['city'] ?? "N/A", PDO::PARAM_STR);
    $stmt->bindValue(':state', $job['state'] ?? "N/A", PDO::PARAM_STR);
    $stmt->bindValue(':country', $job['country'] ?? "N/A", PDO::PARAM_STR);

    $stmt->execute();

    return (int) ($stmt->fetchColumn() ?: 0);
  }

  private static function enrichWithCoordinates(array $job): array
    {
        // 1. Check if coordinates are already present and valid
        if (isset($job['lat']) && $job['lat'] !== null && $job['lat'] != 0) {
            return $job; // Coordinates are good, no need to fetch
        }

        // 2. Build the query string for GeoFetch
        $locationQuery = '';
        if (!empty($job['city']) && !empty($job['country'])) {
            $locationQuery = "{$job['city']}, {$job['country']}";
        } elseif (!empty($job['location'])) {
            $locationQuery = $job['location']; // Fallback to the raw location string
        }

        if (empty($locationQuery)) {
            return $job; // Cannot geocode without location data
        }

        // 3. Call the GeoFetch utility
        $geoData = GeoFetch::getLatLon($locationQuery);

        // 4. Process the response
        if (!empty($geoData) && is_array($geoData)) {
            // geocode.maps.co returns an array of matches, we take the first one
            $firstMatch = $geoData[0] ?? null; 
            
            if ($firstMatch && isset($firstMatch['lat']) && isset($firstMatch['lon'])) {
                $job['lat'] = floatval($firstMatch['lat']);
                $job['lon'] = floatval($firstMatch['lon']);
            }
        }

        return $job;
    }
}
