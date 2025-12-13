<?php

declare(strict_types=1);

namespace App\Controller\Job;

use App\Core\DB;
use App\Enums\StatusCodes;
use App\Utils\Response;
use App\Model\Job as JobModel;
use App\Model\SavedJobs as SavedJobModel;
use App\Model\Applications as ApplicationModel;
use App\Utils\JobsFetcher;
use App\Seeder\JobSeeder;
use \PDO;

class Job
{
  private JobModel $jb;
  private SavedJobModel $sjb;
  private ApplicationModel $am;
  private JobSeeder $jsd;
  private PDO $db;


  public function __construct()
  {
    $this->jb = new JobModel(DB::getInstance());
    $this->sjb = new SavedJobModel(DB::getInstance());
    $this->am = new ApplicationModel(DB::getInstance());

    $this->db = DB::getInstance();

    $this->jsd = new JobSeeder($this->db);
  }

  public function run(): void
  {
    $stmt = $this->db->query("SELECT jobs_seeded FROM system_state WHERE id = 1");
    $state = $stmt->fetchColumn();

    if ($state == 1) {
      return; // do not run again
    }

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
      "developer" => ["chicago", "london", "washington"],
      "designer" => ["surabaya", "cardiff"],
      // "marketing" => ["illinois", "copenhagen"],
      // "finance" => ["jakarta"]
    ];

    $pages = 2;

    $resp = $this->jsd->seed($categories, $countries, $pages);

    Response::success($resp, StatusCodes::OK, "anjai");
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
    $limit = 20;
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

    if(isset($job['external_id'])) unset($job['external_id']);

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
    $limit = 20;
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

  public function apply(int $userId, int $jobId): void
  {
    $apply = $this->am->apply($userId, $jobId);

    if ($apply) {
      Response::success(null, StatusCodes::OK, "Application submitted.");
    } else {
      Response::error(null, StatusCodes::NOT_MODIFIED, "Already applied");
    }

    exit;
  }

  public function addJobManual(array $data): void {
    $jobId = $this->jb->inputJob($data);

    if($jobId) {
      Response::success(['job_id' => $jobId], StatusCodes::OK, "Job Created");
      exit;
    } else {
      Response::error(null, StatusCodes::INTERNAL_SERVER_ERROR, "Something went wrong when creating job entry");
      exit;
    }
  }

  public function deleteJob(int $job_id): void {
    $successDel = $this->jb->deleteJob($job_id);

    if($successDel) {
      Response::success(['deleted_id' => $job_id], StatusCodes::OK, "Job Deleted");
      exit;
    } else {
      Response::error(null, StatusCodes::INTERNAL_SERVER_ERROR, "Something went wrong when deleting job entry");
      exit;
    }
  }

  public function editJob(array $data, int $job_id) {
    $successEdit = $this->jb->updateJobData($data, $job_id);


  }

  public function getAllCategory(): void {}
}
