<?php

declare(strict_types=1);

namespace App\Controller\Job;

use App\Model\User as UserModel;
use App\Model\Applications as ApplicationModel;
use App\Core\DB;
use App\Utils\Response;
use App\Enums\StatusCodes;

use \PDO;

class Application
{
  private UserModel $usm;
  private ApplicationModel $am;

  public function __construct()
  {
    $db = DB::getInstance();
    $this->usm = new UserModel($db);
    $this->am  = new ApplicationModel($db);
  }

  /**
   * POST /api/jobs/apply/{id}
   */
  public function apply(array $userData, int $jobId): void
  {
    $applied = $this->am->apply((int)$userData['id'], $jobId);

    if ($applied) {
      Response::success(null, StatusCodes::OK, "Application submitted.");
    } else {
      Response::error(null, StatusCodes::OK, "Already applied.");
    }
  }

  /**
   * GET /api/applications/count
   * GET /api/applications/count/{jobId}
   * (Admin only or user-specific)
   */
  public function count(array $userData, ?int $jobId = null): void
  {
    if ($userData['role'] === 'admin') {
      $count = $this->am->count(null, $jobId);
    } else {
      $count = $this->am->count((int)$userData['id'], $jobId);
    }

    Response::success([
      'count' => $count
    ]);
  }

  /**
   * GET /api/applications/latest
   * (Admin dashboard)
   */
  public function latest(array $userData): void
  {
    if ($userData['role'] !== 'admin') {
      Response::error(null, StatusCodes::FORBIDDEN, "Access denied.");
    }

    $data = $this->am->latest(10);

    Response::success($data);
  }

  /**
   * GET /api/applications/{id}
   * (User can only view their own)
   */
  public function show(array $userData, int $applicationId): void
  {
    $application = $this->am->show($applicationId);

    if (!$application) {
      Response::error(null, StatusCodes::NOT_FOUND, "Application not found.");
    }

    if (
      $userData['role'] !== 'admin' &&
      (int)$application['user_id'] !== (int)$userData['id']
    ) {
      Response::error(null, StatusCodes::FORBIDDEN, "Access denied.");
    }

    Response::success($application);
  }

  /**
   * GET /api/applications
   * GET /api/applications?page=1
   * (Admin = all, User = own)
   */
  public function index(array $userData, int $page = 1): void
  {
    $limit  = 20;
    $offset = ($page - 1) * $limit;

    if ($userData['role'] === 'admin') {
      $data = $this->am->showAll(null, $limit, $offset);
    } else {
      $data = $this->am->showAll((int)$userData['id'], $limit, $offset);
    }

    Response::success([
      'page' => $page,
      'data' => $data
    ]);
  }
}
