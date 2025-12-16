<?php

declare(strict_types=1);

namespace App\Utils;

use App\Core\Env;

class JobsFetcher
{
  private static function parseLocationString(?string $location, array $orig): array
  {
    $result = [
      'city' => null,
      'state' => null,
      'country' => null
    ];

    if (!$location || trim($location) === '') {
      return $result;
    }

    $location = trim($location);
    $parts = array_map('trim', explode(',', $location));
    $count = count($parts);

    // List of all 50 US state codes
    $usStates = [
      "AL",
      "AK",
      "AZ",
      "AR",
      "CA",
      "CO",
      "CT",
      "DE",
      "FL",
      "GA",
      "HI",
      "ID",
      "IL",
      "IN",
      "IA",
      "KS",
      "KY",
      "LA",
      "ME",
      "MD",
      "MA",
      "MI",
      "MN",
      "MS",
      "MO",
      "MT",
      "NE",
      "NV",
      "NH",
      "NJ",
      "NM",
      "NY",
      "NC",
      "ND",
      "OH",
      "OK",
      "OR",
      "PA",
      "RI",
      "SC",
      "SD",
      "TN",
      "TX",
      "UT",
      "VT",
      "VA",
      "WA",
      "WV",
      "WI",
      "WY"
    ];

    if ($count === 1 && !empty($orig['job_state'])) {
      // Example: "Berlin"
      $result['city'] = $parts[0];
      return $result;
    }

    if ($count === 1 && empty($orig['job_state'])) {
      $result['city'] = $parts[0];
      $result['state'] = $parts[0];

      return $result;
    }

    if ($count === 2) {
      // Examples:
      // "Chicago, IL"
      // "Paris, France"
      $result['city'] = $parts[0];
      $region = $parts[1];

      if (in_array($region, $usStates, true)) {
        $result['state'] = $region;
        $result['country'] = "US";
      } else {
        // Not a US state, treat as country
        $result['country'] = $region;
      }

      return $result;
    }

    if ($count >= 3) {
      // Example: "New York, NY, USA"
      $result['city'] = $parts[0];
      $result['state'] = $parts[1];

      if (in_array($parts[1], $usStates, true)) {
        $result['country'] = "US";
      } else {
        $result['country'] = $parts[$count - 1];
      }

      return $result;
    }

    return $result;
  }
  public static function mapJob(array $job): array
  {
    $orig = [
      'job_city' => $job['job_city'],
      'job_state' => $job['job_state'],
      'job_country' => $job['job_country']
    ];

    $parsed = self::parseLocationString($job['job_location'] ?? null, $orig);

    return [
      'external_id' => $job['job_id'],
      'title' => $job['job_title'],
      'employer_name' => $job['employer_name'],
      'employer_logo' => $job['employer_logo'],
      'employer_website' => $job['employer_website'],
      'publisher' => $job['job_publisher'],
      'employment_type' => $job['job_employment_type'],
      'employment_types' => $job['job_employment_types'][0] ?? null,
      'apply_options' => $job['apply_options'],
      'description' => $job['job_description'],
      'is_remote' => $job['job_is_remote'],
      'posted_at_timestamp' => $job['job_posted_at_timestamp'],
      'posted_at_utc' => $job['job_posted_at_datetime_utc'],
      'location' => $job['job_location'],
      'city' => $job['job_city'] ?? $parsed['city'],
      'state' => $job['job_state'] ?? $parsed['state'],
      'country' => $job['job_country'] ?? $parsed['country'],
      'lat' => $job['job_latitude'],
      'lon' => $job['job_longitude'],
      'min_salary' => $job['job_min_salary'] ?? 0,
      'max_salary' => $job['job_max_salary'] ?? 0,
      'salary_period' => $job['job_salary_period'] ?? "MONTHLY",
      'quals' => $job['job_highlights']['Qualitifactions'] ?? [],
      'responsibilities' => $job['job_highlights']['Responsibilities'] ?? [],
    ];
  }

private static function multiFetch(array $requests): array
{
    $cmh = curl_multi_init();
    $handles = [];
    $results = [];

    foreach ($requests as $key => $req) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $req['url'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,         // Increased to 60 seconds
            CURLOPT_CONNECTTIMEOUT => 10,  // Added connection timeout
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "x-rapidapi-key: " . Env::get('RAPID_API_KEY')
            ],
        ]);

        curl_multi_add_handle($cmh, $ch);
        $handles[$key] = $ch;
    }

    do {
        $status = curl_multi_exec($cmh, $active);
        curl_multi_select($cmh);
    } while ($active && $status == CURLM_OK);

    $results = [];

    foreach ($handles as $key => $ch) {
        $response = curl_multi_getcontent($ch);

        // Get detailed error information
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        $errno = curl_errno($ch); // Get error number for better diagnosis

        curl_multi_remove_handle($cmh, $ch);
        curl_close($ch);

        if ($err) {
            // This captures timeouts (code 28), connection refused, etc.
            $results[$key] = [
                [
                    "_error" => "curl_error",
                    "message" => "cURL Error (" . $errno . "): " . $err, // Detailed message
                    "http_code" => $http,
                    "body" => $response
                ]
            ];
            continue;
        }

        if ($http !== 200) {
            $results[$key] = [
                [
                    "_error" => "http_error",
                    "http_code" => $http,
                    "body" => $response
                ]
            ];
            continue;
        }
        
        // ... (Rest of the JSON decoding logic remains the same) ...
        
        $json = json_decode($response, true);

        if (!isset($json["data"])) {
            $results[$key] = [
                [
                    "_error" => "no_data",
                    "body" => $json
                ]
            ];
            continue;
        }

        $results[$key] = $json["data"] ?? [];
    }

    curl_multi_close($cmh);
    return $results;
  }

  public static function buildFetchUrls(array $categories, array $countries, int $pages = 1, int $start_page = 1): array
  {
    $requests = [];

    foreach ($categories as $category => $cities) {

      $jobs[$category] = [];

      foreach ($cities as $city) {

        for ($page = $start_page; $page <= ($pages + $start_page - 1); $page++) {

          $url = "https://jsearch.p.rapidapi.com/search?query={$category}%20jobs%20in%20{$city}"
            . "&page={$page}&num_pages=1&country={$countries[$city]}&date_posted=all";

          $requests[] = [
            'category' => $category,
            'url' => $url
          ];
        }
      }
    }

    return $requests;
  }

  public static function fetchJSearchJobs(array $categories, array $countries, int $pages = 1): array
  {
    $jobs = [];

    $requests = self::buildFetchUrls($categories, $countries, $pages);

    $responses = self::multiFetch($requests);


    foreach ($responses as $key => $responseJobs) {
      $category = $requests[$key]['category'];



      foreach ($responseJobs as $job) {

        if (isset($job['_error'])) {
          $jobs[$category][] = $job;
          continue;
        }
        $jobs[$category][] = self::mapJob($job);
      }
    }

    return $jobs;
  }
}
