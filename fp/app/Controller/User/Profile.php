<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Core\DB;
use App\Utils\Response;
use App\Model\User;
use App\Model\UserProfile;
use App\Model\UserJobHistory;
use App\Enums\StatusCodes;

class Profile
{
  private User $user;
  private UserProfile $profile;
  private UserJobHistory $history;

  public function __construct()
  {
    $this->user    = new User(DB::getInstance());
    $this->profile = new UserProfile(DB::getInstance());
    $this->history = new UserJobHistory(DB::getInstance());
  }

  public function create(array $user): void {}

  /**
   * GET /api/user/profile
   */
  public function show(array $user): void
  {
    $userId = $user['id'];

    $userthis = $this->user->getById($userId);
    $profile = $this->profile->getByUserId($userId);
    $jobs = $this->history->getByUserId($userId);

    Response::success([
      'username' => $userthis['username'],
      'placeholder' => $userthis['placeholder'],
      'role' => $user['role'],
      'profile' => $profile,
      'jobs' => $jobs
    ], StatusCodes::OK, "Authenticated");
    exit;
  }

  /**
   * POST /api/profile/avatar
   * Uses $_FILES['avatar']
   */
  public function updateAvatar(array $user): void
  {
    $userId = $user['id'];

    if (!isset($_FILES['photo'])) {
      Response::error(null, StatusCodes::BAD_REQUEST, 'Photo file not provided');
      exit;
    }

    $file = $_FILES['photo'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
      Response::error(null, StatusCodes::BAD_REQUEST, 'Error when trying to upload the photo, please retry');
      exit;
    }

    $allowedMime = [
      'image/jpeg',
      'image/png',
      'image/webp'
    ];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowedMime, true)) {
      Response::error(null, StatusCodes::BAD_REQUEST, 'Invalid image type');
    }

    $extension = match ($mime) {
      'image/jpeg' => 'jpg',
      'image/png'  => 'png',
      'image/webp' => 'webp',
    };

    $filename = sprintf(
      'avatar_%d_%s%s.%s',
      $userId,
      uniqid(),
      date('YmdHis'),
      $extension
    );

    $uploadDir = __DIR__ . '/../../../storage/photo_profiles/';
    if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0755, true);
    }

    $destination = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
      Response::error(null, StatusCodes::INTERNAL_SERVER_ERROR, 'Failed to save image');
    }

    $avatarPath = '/storage/photo_profiles/' . $filename;

    $updated = $this->profile->updatePhoto($userId, $avatarPath);

    if ($updated) {
      Response::success([
        'avatar' => $avatarPath
      ], StatusCodes::OK, 'Photo profile updated');
      exit;
    }

    Response::error(null, StatusCodes::BAD_REQUEST, 'Failed to update profile photo');
    exit;
  }

  public function updatePlaceholder(array $data, array $user): void
  {
    $userId = (int) $user['id'];

    if (!isset($data['placeholder'])) {
      Response::error(null, StatusCodes::BAD_REQUEST, 'Data profile is missing');
      exit;
    }

    $resp = $this->user->updatePlaceholder($userId, $data['placeholder']);

    if ($resp) {
      Response::success(['test' => $resp], StatusCodes::OK, 'Profile updated');
    } else {
      Response::error(['test' => $resp], StatusCodes::BAD_REQUEST, 'Profile failed to update');
    }

    exit;
  }

  /**
   * PUT /api/user/profile
   * Update bio & education
   */
  public function updateBio(array $data, array $user): void
  {
    $userId = (int) $user['id'];

    if (!isset($data['bio']) || !isset($data['last_education'])) {
      Response::error(null, StatusCodes::BAD_REQUEST, 'Data profile is missing');
      exit;
    }

    if ($this->profile->updateDetails($userId, $data['bio'], $data['last_education'])) {
      Response::success(null, StatusCodes::OK, 'Profile updated');
    } else {
      Response::error(null, StatusCodes::BAD_REQUEST, 'Profile failed to update');
    }

    exit;
  }

  /**
   * POST /api/user/profile/job-history
   */
  public function addJobHistory(array $data, array $user): void
  {
    $userId = $user['id'];

    if (!isset($data['job_title']) || !isset($data['company_name']) || !isset($data['status']) || !$data['applied_at']) {
      Response::error(null, StatusCodes::BAD_REQUEST, 'Data for job history is missing');
      exit;
    }

    $success = $this->history->create([
      'user_id'      => $userId,
      'job_title'    => $data['job_title'],
      'company_name' => $data['company_name'],
      'status'       => $data['status'],
      'applied_at'   => $data['applied_at'],
    ]);

    if ($success) {
      Response::success(null, StatusCodes::OK, 'Job History updated');
    } else {
      Response::error(null, StatusCodes::BAD_REQUEST, 'Job history update failed');
    }

    exit;
  }

  public function delete(array $user, int $userId): void
  {
    $userModel = new \App\Model\User(DB::getInstance());

    if ($userModel->deleteUser($userId)) {
      Response::success(null, StatusCodes::OK, "User and all associated data deleted successfully.");
    } else {
      // User not found or deletion failed
      Response::error(null, StatusCodes::INTERNAL_SERVER_ERROR, "Failed to delete user or user not found.");
    }
    exit;
  }

  public function promote(array $user, int $userId): void
  {
    $userModel = new \App\Model\User(DB::getInstance());

    if ($userModel->promoteUser($userId)) {
      Response::success(null, StatusCodes::OK, "User role updated successfully.");
    } else {
      // User not found or deletion failed
      Response::error(null, StatusCodes::INTERNAL_SERVER_ERROR, "Failed to unpromote user or user not found.");
    }
    exit;
  }

  public function unpromote(array $user, int $userId): void
  {
    $userModel = new \App\Model\User(DB::getInstance());

    if ($userModel->unpromoteUser($userId)) {
      Response::success(null, StatusCodes::OK, "User role updated successfully.");
    } else {
      // User not found or deletion failed
      Response::error(null, StatusCodes::INTERNAL_SERVER_ERROR, "Failed to unpromote user or user not found.");
    }
    exit;
  }
}
