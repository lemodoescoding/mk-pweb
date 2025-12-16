<?php

declare(strict_types=1);

namespace App\Controller\Job;

use App\Core\DB;
use App\Core\Env;
use App\Enums\StatusCodes;
use App\Utils\Response;
use App\Model\Job as JobModel;
use App\Model\SavedJobs as SavedJobModel;
use App\Model\Applications as ApplicationModel;
use App\Model\User as UserModel;
use App\Seeder\JobSeeder;
use \PDO;

class Job
{
  private JobModel $jb;
  private SavedJobModel $sjb;
  private ApplicationModel $am;
  private JobSeeder $jsd;
  private UserModel $usm;
  private PDO $db;


  public function __construct()
  {
    $this->jb = new JobModel(DB::getInstance());
    $this->sjb = new SavedJobModel(DB::getInstance());
    $this->am = new ApplicationModel(DB::getInstance());

    $this->db = DB::getInstance();

    $this->jsd = new JobSeeder(DB::getInstance());

    $this->usm = new UserModel(DB::getInstance());
  }

  public function run(): void
  {
    // $stmt = $this->db->query("SELECT jobs_seeded FROM system_state WHERE id = 1");
    // $state = $stmt->fetchColumn();
    //
    // if ($state == 1) {
    //   return; // do not run again
    // }

    $countries = [
      "chicago" => "us",
      "washington" => "us",
      "illinois" => "us",
      "london" => "uk",
      "cardiff" => "uk",
      "surabaya" => "id",
      "jakarta" => "id",
      "moskow" => "ru",
      "copenhagen" => "dk"
    ];

    // seed categories
    $categories = [
      "developer" => ["chicago", "illinois"],
      "designer" => ["cardiff"],
      // "marketing" => ["illinois", "copenhagen"],
      // "finance" => ["jakarta"]
    ];

    $pages = 1;

    $resp = $this->jsd->seed($categories, $countries, $pages, 2);

    Response::success($resp, StatusCodes::OK, "anjai");
  }

  public function seedJobs(array $data, array $user): void
  {
    if (empty($data['categories']) || empty($data['countries']) || !isset($data['pages'])) {
      Response::error(null, StatusCodes::BAD_REQUEST, "Missing required job seeding parameters (categories, countries, pages, or limit_per_category)");
      exit;
    }

    $offset_page = $data['start_page'] ?? 1;
    $categories = $data['categories'];
    $countries = $data['countries'];
    $pages = intval($data['pages']);

    if (!is_array($categories) || !is_array($countries)) {
      Response::error(null, StatusCodes::BAD_REQUEST, "Categories and countries must be arrays.");
      exit;
    }

    try {
      $resp = $this->jsd->seed($categories, $countries, $pages, $offset_page);

      Response::success($resp, StatusCodes::OK, "Job seeding successful.");
    } catch (\Throwable $e) {
      Response::error($e->getMessage(), StatusCodes::INTERNAL_SERVER_ERROR, "Job seeding failed.");
    }

    exit;
  }

  public function indexAll(): void
  {
    $jobs = $this->jb->all();

    Response::success(['jobs' => $jobs], StatusCodes::OK, "Return all data");
    exit;
  }

  public function index(int $page = 1): void
  {
    $page = max(1, intval($page));
    $limit = intval(Env::get('LIMIT_FETCH_SQL')) ?? 20;
    $offset = ($page - 1) * $limit;

    $jobs = $this->jb->getPaginated($limit, $offset);
    $count = $this->jb->countAll();

    if (!empty($jobs)) {

      Response::success(['jobs' => $jobs, 'countAll' => $count, 'currStart' => ($page - 1) * $limit + 1], StatusCodes::OK, "Return paginated data");
    } else {

      Response::error(null, StatusCodes::NOT_FOUND, "Pagination doesnt go that far");
    }
    exit;
  }

  public function show(int $id): void
  {
    $job = $this->jb->find($id);

    if (!$job) {
      Response::error(null, StatusCodes::NOT_FOUND, "JobModel not found");
      exit;
    }

    if (isset($job['external_id'])) unset($job['external_id']);

    Response::success(['job' => $job], StatusCodes::OK, "Return single data");
    exit;
  }

  public function search(array $query): void
  {
    $term = $query['q'] ?? '';
    $jobs = $this->jb->search($term);

    Response::success(['jobs' => $jobs], StatusCodes::OK, "Return filtered job");
    exit;
  }

  public function searchPaginated($term, $page)
  {
    $page = max(1, intval($page));
    $limit = intval(Env::get('LIMIT_FETCH_SQL')) ?? 20;
    $offset = ($page - 1) * $limit;

    $jobs = $this->jb->search($term, $limit, $offset);
    $count = $this->jb->searchCount($term);

    if ($count < 1 && $page > 0) {
      Response::success(null, StatusCodes::OK, 'No data matched from search');
      exit;
    }

    if ((!empty($jobs))) {
      Response::success(['jobs' => $jobs, 'countAll' => $count, 'currStart' => ($page - 1) * $limit + 1], StatusCodes::OK, "Return paginated filtered data");
    } else {
      Response::error(null, StatusCodes::NOT_FOUND, "Pagination filtered doesnt go that far");
    }
    exit;
  }

  public function saveJobModel(int $userId, int $jobId): void
  {
    $saved = $this->sjb->save($userId, $jobId);

    if ($saved) {
      Response::success(null, StatusCodes::OK, "JobModel saved.");
    } else {
      Response::error(null, StatusCodes::NOT_MODIFIED, "Already saved");
    }

    exit;
  }

  public function addJobManual(array $user, array $data): void
  {
    $jobId = $this->jb->inputJob($data);

    if ($jobId != -1) {
      Response::success(['job_id' => $jobId], StatusCodes::OK, "Job Created");
      exit;
    } else {
      Response::error(null, StatusCodes::INTERNAL_SERVER_ERROR, "Something went wrong when creating job entry");
      exit;
    }
  }

  public function deleteJob(array $user, int $job_id): void
  {
    $successDel = $this->jb->deleteJob($job_id);

    if ($successDel) {
      Response::success(['deleted_id' => $job_id], StatusCodes::OK, "Job Deleted");
      exit;
    } else {
      Response::error(null, StatusCodes::INTERNAL_SERVER_ERROR, "Something went wrong when deleting job entry");
      exit;
    }
  }

  public function softDeleteJob(int $job_id): void
  {
    $successSoftDel = $this->jb->deleteJob($job_id);

    if ($successSoftDel) {
      Response::success(['deleted_id' => $job_id], StatusCodes::OK, "Job Soft Deleted");
      exit;
    } else {
      Response::error(null, StatusCodes::INTERNAL_SERVER_ERROR, "Something went wrong when soft deleting job entry");
      exit;
    }
  }

  public function editJob(array $data, array $user, int $job_id)
  {
    $successEdit = $this->jb->updateJobData($data, $user,  $job_id);

    if ($successEdit) {
      Response::success(['updated_id' => $job_id], StatusCodes::OK, "Job Info Updated!");
      exit;
    } else {
      Response::error(null, StatusCodes::INTERNAL_SERVER_ERROR, "Something went wrong when updating job entry");
      exit;
    }
  }
}
