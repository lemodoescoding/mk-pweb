<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\JobSource;
use App\Model\Company;
use App\Model\Category;
use App\Model\Location;
use App\Model\JobCategory;

use App\Core\DB;
use App\Utils\GeoFetch;
use \PDO;

class Job extends Model
{
  private JobSource $jbs;
  private Company $cmy;
  private Location $lct;
  private JobCategory $jbc;
  private Category $ctg;

  public function __construct()
  {
    parent::__construct(DB::getInstance());

    $this->jbs = new JobSource(DB::getInstance());
    $this->cmy = new Company(DB::getInstance());
    $this->lct = new Location(DB::getInstance());
    $this->jbc = new JobCategory(DB::getInstance());
    $this->ctg = new Category(DB::getInstance());
  }
  public function all(): array
  {
    $stmt = $this->db->query("
            SELECT j.*, c.name AS company, l.city, l.state, l.country 
            FROM jobs j
            JOIN companies c ON j.company_id = c.id
            JOIN locations l ON j.location_id = l.id
            WHERE j.deleted_at IS NULL
            ORDER BY j.date_posted DESC
        ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function countAll(): int
  {
    $stmt = $this->db->query("
            SELECT COUNT(*) 
            FROM jobs j
            JOIN companies c ON j.company_id = c.id
            JOIN locations l ON j.location_id = l.id
            WHERE j.deleted_at IS NULL
            ORDER BY j.date_posted DESC
        ");

    return (int) ($stmt->fetchColumn() ?: 0);
  }

  public function find(int $id): ?array
  {
    $stmt = $this->db->prepare("
            SELECT j.*, ct.name as category_name, c.name AS company, l.city, l.state, l.country
            FROM jobs j
            JOIN companies c ON j.company_id = c.id
            JOIN locations l ON j.location_id = l.id
            JOIN job_categories jc ON jc.job_id = j.id
            JOIN categories ct ON ct.id = jc.category_id
            WHERE j.id = :id AND j.deleted_at IS NULL
        ");

    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $job = $stmt->fetch(PDO::FETCH_ASSOC);

    return $job ?: null;
  }

  public function search(string $term, int $limit = 1000, int $offset = 0): array
  {
    $stmt = $this->db->prepare("
            SELECT j.*, c.name AS company_name, l.city, l.state, l.country
            FROM jobs j
            JOIN companies c ON j.company_id = c.id
            JOIN locations l ON j.location_id = l.id
            WHERE MATCH(j.title, j.description) AGAINST(:term IN NATURAL LANGUAGE MODE)
            AND j.deleted_at IS NULL
            LIMIT :lim OFFSET :of
        ");

    $stmt->bindValue(':term', $term, PDO::PARAM_STR);
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':of', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  private function getLastInsertedRow(): int
  {
    $stmt = $this->db->prepare("SELECT id FROM jobs ORDER BY id DESC LIMIT 1");
    $stmt->execute();

    return (int) $stmt->fetchColumn();
  }

  public function create(array $data): int
  {
    try {
      $this->db->beginTransaction();
      $stmt = $this->db->prepare("
            INSERT INTO jobs (
                external_id, title, description, apply_link,
                is_remote, company_id, location_id, min_salary, max_salary, source_id,
                date_posted, raw_json, salary_period
            ) VALUES (:eid, :title, :desc, :applink, :remote, :cid, :lid, :mis, :mas, :soid, :dpost, :rj, :sp)
            ON DUPLICATE KEY UPDATE external_id = external_id
        ");

      $stmt->bindValue(':eid', $data['external_id'], PDO::PARAM_STR);
      $stmt->bindValue(':title', htmlspecialchars($data['title']), PDO::PARAM_STR);
      $stmt->bindValue(':desc', htmlspecialchars($data['description']), PDO::PARAM_STR);
      $stmt->bindValue(':applink', $data['apply_link'], PDO::PARAM_STR);
      $stmt->bindValue(':remote', $data['is_remote'], PDO::PARAM_STR);
      $stmt->bindValue(':cid', intval($data['company_id']), PDO::PARAM_INT);
      $stmt->bindValue(':lid', intval($data['location_id']), PDO::PARAM_INT);
      $stmt->bindValue(':mis', intval($data['min_salary']), PDO::PARAM_INT);
      $stmt->bindValue(':mas', intval($data['max_salary']), PDO::PARAM_INT);
      $stmt->bindValue(':soid', intval($data['source_id']), PDO::PARAM_INT);
      $stmt->bindValue(':dpost', \date("Y-m-d H:i:s", strtotime($data['date_posted'])), PDO::PARAM_STR);
      $stmt->bindValue(':rj', json_encode($data['raw_json'], JSON_UNESCAPED_SLASHES));
      $stmt->bindValue(':sp', isset($data['salary_period']) ? $data['salary_period'] : 'MONTHLY', PDO::PARAM_STR);

      $stmt->execute();
      $this->db->commit();

      return (int) $this->getLastInsertedRow();
    } catch (\Throwable $e) {

      echo $e->getMessage();
      $this->db->rollBack();
      return -1;
    }
  }

  public function getPaginated(int $limit, int $offset): array
  {
    $stmt = $this->db->prepare("
        SELECT j.*,
               c.name AS company,
               l.city, l.state, l.country
        FROM jobs j
        JOIN companies c ON j.company_id = c.id
        JOIN locations l ON j.location_id = l.id
        ORDER BY j.date_posted DESC
        LIMIT :limit OFFSET :offset
    ");

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function searchCount(string $term): int
  {
    $stmt = $this->db->prepare("
            SELECT COUNT(*) AS job_count
            FROM jobs j
            JOIN companies c ON j.company_id = c.id
            WHERE MATCH(j.title, j.description) AGAINST(:term IN NATURAL LANGUAGE MODE)
            AND j.deleted_at IS NULL
        ");

    $stmt->bindValue(':term', $term, PDO::PARAM_STR);
    $stmt->execute();
    return (int) ($stmt->fetchColumn() ?: 0);
  }

  // 'external_id' => $job['job_id'],
  // 'title' => $job['job_title'],
  // 'employer_name' => $job['employer_name'],
  // 'employer_logo' => $job['employer_logo'],
  // 'employer_website' => $job['employer_website'],
  // 'publisher' => $job['job_publisher'],
  // 'employment_type' => $job['job_employment_type'],
  // 'employment_types' => $job['job_employment_types'][0] ?? null,
  // 'apply_options' => $job['apply_options'],
  // 'description' => $job['job_description'],
  // 'is_remote' => $job['job_is_remote'],
  // 'posted_at_timestamp' => $job['job_posted_at_timestamp'],
  // 'posted_at_utc' => $job['job_posted_at_datetime_utc'],
  // 'location' => $job['job_location'],
  // 'city' => $job['job_city'] ?? $parsed['city'],
  // 'state' => $job['job_state'] ?? $parsed['state'],
  // 'country' => $job['job_country'] ?? $parsed['country'],
  // 'lat' => $job['job_latitude'],
  // 'lon' => $job['job_longitude'],
  // 'min_salary' => $job['job_min_salary'] ?? 0,
  // 'max_salary' => $job['job_max_salary'] ?? 0,
  // 'salary_period' => $job['job_salary_period'] ?? "MONTHLY",
  // 'quals' => $job['job_highlights']['Qualitifactions'] ?? [],
  // 'responsibilities' => $job['job_highlights']['Responsibilities'] ?? [],
  public function inputJob(array $job): int
  {
    $categoryId = $this->ctg->firstOrCreate($job['category_name']);
    $sourceId   = $this->jbs->insertSource('jsearch');
    $companyId  = $this->cmy->firstOrCreate($job['employer_name'], $job['employer_logo'], $job['employer_website']);


    $job['external_id'] = 'test-job' . uniqid();
    $job['date_posted'] = date('Y-m-d H:i:s');
    if ((!isset($job['lat']) || !isset($job['lon']))) {
      $resp = GeoFetch::getLatLon($job['city'] . ' ' . $job['state'] . ' ' . $job['country']);

      $job['lat'] = $resp[0]['lat'];
      $job['lon'] = $resp[0]['lon'];
    }

    $locationId = $this->lct->insertLocation($job);

    $job['company_id'] = $companyId;
    $job['source_id'] = $sourceId;
    $job['location_id'] = $locationId;
    $job['raw_json'] = json_encode([]);

    $jobId = $this->create($job);

    if ($jobId > 0) {
      $this->jbc->attach($jobId, $categoryId);

      return $jobId;
    }

    return -1;
  }

  public function softDeleteJob(int $jobId): bool
  {
    try {
      $stmt = $this->db->prepare("
            UPDATE jobs
            SET deleted_at = NOW()
            WHERE id = :job_id
              AND deleted_at IS NULL
        ");

      $stmt->bindValue(':job_id', $jobId, PDO::PARAM_INT);
      $stmt->execute();

      return $stmt->rowCount() > 0;
    } catch (\Throwable $e) {
      return false;
    }
  }

  public function deleteJob(int $jobId): bool
  {
    // better to use transaction, in case something happen in the middle of deleting process.
    try {
      $this->db->beginTransaction();

      $stmt = $this->db->prepare("DELETE FROM job_categories WHERE job_id = :job_id");
      $stmt->bindValue(':job_id', $jobId, PDO::PARAM_INT);
      $stmt->execute();

      $stmt = $this->db->prepare("DELETE FROM saved_jobs WHERE job_id = :job_id");
      $stmt->bindValue(':job_id', $jobId, PDO::PARAM_INT);
      $stmt->execute();

      $stmt = $this->db->prepare("DELETE FROM applications WHERE job_id = :job_id");
      $stmt->bindValue(':job_id', $jobId, PDO::PARAM_INT);
      $stmt->execute();

      $stmt = $this->db->prepare("DELETE FROM jobs WHERE id = :job_id");
      $stmt->bindValue(':job_id', $jobId, PDO::PARAM_INT);
      $stmt->execute();

      $this->db->commit();
      return true;
    } catch (\Throwable $e) {
      $this->db->rollBack();
      return false;
    }
  }

  public function updateJobCategory(string $categoryName, int $jobId): int
  {
    try {
      $newCategoryId = $this->ctg->firstOrCreate($categoryName);

      // 1. Delete existing category link (if any)
      $this->jbc->detach($jobId);

      // 2. Attach new category link
      $this->jbc->attach($jobId, $newCategoryId);

      return $newCategoryId;
    } catch (\Throwable $th) {
      // Log error
      return -1;
    }
  }

  public function updateJobData(array $data, array $user, int $jobId): int
  {
    try {
      $this->db->beginTransaction();

      $jobData = $this->find($jobId);
      if (!$jobData) {
        $this->db->rollBack();
        return 0;
      }

      if (isset($data['employer_name'])) {
        $companyId = $this->cmy->firstOrCreate(
          $data['employer_name'],
          $data['employer_logo'] ?? null,
          $data['employer_website'] ?? null
        );

        $stmt = $this->db->prepare(
          "UPDATE jobs SET company_id = :cid WHERE id = :job_id"
        );
        $stmt->bindValue(':cid', $companyId, PDO::PARAM_INT);
        $stmt->bindValue(':job_id', $jobId, PDO::PARAM_INT);
        $stmt->execute();

        unset($data['employer_name'], $data['employer_logo'], $data['employer_website']);
      }


      if (isset($data['city']) || isset($data['state']) || isset($data['country'])) {
        $locationData = [
          'city' => $data['city'] ?? $jobData['city'],
          'state' => $data['state'] ?? $jobData['state'],
          'country' => $data['country'] ?? $jobData['country'],
          'lat' => $data['lat'] ?? null, // Assuming lat/lon might be passed or should be calculated
          'lon' => $data['lon'] ?? null,
        ];

        if ((!$locationData['lat'] || !$locationData['lon']) && ($locationData['city'] || $locationData['state'])) {
          $resp = GeoFetch::getLatLon($locationData['city'] . ' ' . $locationData['state'] . ' ' . $locationData['country']);
          $locationData['lat'] = $resp[0]['lat'] ?? null;
          $locationData['lon'] = $resp[0]['lon'] ?? null;
        }

        $this->updateJobLocation($locationData, $jobId);

        unset($data['city'], $data['state'], $data['country'], $data['lat'], $data['lon']);
      }

      if (isset($data['category_name'])) {
        $this->updateJobCategory($data['category_name'], $jobId);
        unset($data['category_name']);
      }


      $allowedFields = [
        'description',
        'apply_link',
        'is_remote',
        'min_salary',
        'max_salary',
        'salary_period',
        'raw_json',
      ];

      $fields = [];
      $bindings = [];
      $updatedData = [];

      foreach ($data as $key => $value) {
        if (!in_array($key, $allowedFields, true)) {
          continue;
        }

        // If value is null, treat it as null in the DB
        if ($value === null) {
          $fields[] = "{$key} = NULL";
        } else {
          $fields[] = "{$key} = :{$key}";
          $bindings[$key] = $value;
          $updatedData[$key] = $value;
        }
      }

      if (empty($fields)) {
        $this->db->commit();
        return 1;
      }

      $sql = "
                UPDATE jobs
                SET " . implode(', ', $fields) . "
                WHERE id = :job_id
            ";

      $stmt = $this->db->prepare($sql);

      foreach ($bindings as $key => $value) {
        $paramType = match ($key) {
          'is_remote' => PDO::PARAM_BOOL,
          'min_salary',
          'max_salary' => PDO::PARAM_INT,
          default => PDO::PARAM_STR,
        };

        // HTML-encode title and description for safety
        $finalValue = ($key === 'title' || $key === 'description') ? htmlspecialchars($value) : $value;

        $stmt->bindValue(":{$key}", $finalValue, $paramType);
      }

      $stmt->bindValue(':job_id', $jobId, PDO::PARAM_INT);

      $stmt->execute();
      $this->db->commit();
      return $stmt->rowCount();
    } catch (\Throwable $e) {
      $this->db->rollBack();
      // Log the error: $e->getMessage()
      return -1;
    }
  }

  // public function updateJobData(array $data, int $jobId): int
  // {
  //   // 'title' => 'Backend Engineer',
  //   // 'location' => [
  //   //     'city' => 'Chicago',
  //   //     'state' => 'IL',
  //   //     'country' => 'US',
  //   //     'lat' => 41.8781,
  //   //     'lon' => -87.6298
  //   //
  //   if (isset($data['location'])) {
  //     $this->updateJobLocation($data['location'], $jobId);
  //     unset($data['location']);
  //   }
  //
  //
  //   // 'company' => [
  //   //     'name' => 'Chicago',
  //   //     'logo' => 'IL',
  //   //     'website' => 'US',
  //   if (isset($data['company'])) {
  //     $companyId = $this->cmy->firstOrCreate(
  //       $data['company']['name'],
  //       $data['company']['logo'],
  //       $data['company']['website']
  //     );
  //
  //     $stmt = $this->db->prepare(
  //       "UPDATE jobs SET company_id = :cid WHERE id = :job_id"
  //     );
  //
  //     $stmt->bindValue(':cid', $companyId, PDO::PARAM_INT);
  //     $stmt->bindValue(':job_id', $jobId, PDO::PARAM_INT);
  //     $stmt->execute();
  //
  //     unset($data['company']);
  //   }
  //
  //   try {
  //     if (empty($data)) return -1;
  //
  //     $allowedFields = [
  //       'title',
  //       'description',
  //       'apply_link',
  //       'is_remote',
  //       'min_salary',
  //       'max_salary',
  //       'salary_period',
  //       'raw_json',
  //       'date_posted'
  //     ];
  //
  //
  //     $fields = [];
  //     $bindings = [];
  //
  //     foreach ($data as $key => $value) {
  //       if (!in_array($key, $allowedFields, true)) {
  //         continue;
  //       }
  //
  //       $fields[] = "{$key} = :{$key}";
  //       $bindings[$key] = $value;
  //     }
  //
  //     if (empty($fields)) {
  //       return -1;
  //     }
  //
  //     $sql = "
  //       UPDATE jobs
  //       SET " . implode(', ', $fields) . "
  //       WHERE id = :job_id
  //   ";
  //     $this->db->beginTransaction();
  //
  //     $stmt = $this->db->prepare($sql);
  //
  //     foreach ($bindings as $key => $value) {
  //       $paramType = match ($key) {
  //         'is_remote' => PDO::PARAM_BOOL,
  //         'min_salary',
  //         'max_salary' => PDO::PARAM_INT,
  //         default => PDO::PARAM_STR,
  //       };
  //
  //       $stmt->bindValue(":{$key}", $value, $paramType);
  //     }
  //
  //     $stmt->bindValue(':job_id', $jobId, PDO::PARAM_INT);
  //
  //     $stmt->execute();
  //     $this->db->commit();
  //     return $stmt->rowCount();
  //   } catch (\Throwable $e) {
  //     $this->db->rollBack();
  //     return -1;
  //   }
  // }

  public function updateJobLocation(array $data, int $jobId): int
  {
    try {
      $locationId = $this->lct->firstOrCreate($data);

      $stmt = $this->db->prepare("UPDATE jobs SET location_id = :location_id WHERE id = :job_id");

      $stmt->bindValue(':location_id', $locationId, PDO::PARAM_INT);

      $stmt->bindValue(':job_id', $jobId, PDO::PARAM_INT);
      $stmt->execute();

      return $stmt->rowCount();
    } catch (\Throwable $th) {
      return -1;
    }
  }
}
