<?php

declare(strict_types=1);

namespace App\Controller;

use \PDO;
use App\Enums\StatusCodes;
use App\Core\DB;
use App\Utils\Response;
use App\Model\Auth as AuthModel;

class Admin
{
  private AuthModel $auth;

  public function __construct()
  {
    $this->auth = new AuthModel(DB::getInstance());
  }

  private function getAuthenticatedUser(): ?array
  {
    $headers = \getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
    if ($authHeader && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
      $token = $matches[1];

      // token stored as SHA256 in DB
      $tokenHash = hash('sha256', $token);

      return $this->auth->findByToken($tokenHash);
    }

    return null;
  }

  // === Admin Dashboard ===

  // GET /api/admin/stats
  public function stats(array $dummy)
  {
    return Response::success([
      'total_users' => $this->auth->countUsers(),
      'total_admins' => $this->auth->countAdmins(),
    ], StatusCodes::OK, "Admin stats");
  }

  // GET /api/admin/users
  public function listUsers(array $dummy)
  {
    return Response::success([
      'users' => $this->auth->getAllUsers()
    ], StatusCodes::OK, "User list");
  }

  // GET /api/admin/user/{id}
  public function viewUser(array $dummy, int $id)
  {
    $user = $this->auth->getUserById(intval($id));

    if (!$user) {
      return Response::error(null, StatusCodes::NOT_FOUND, "User not found");
    }

    return Response::success(['user' => $user], StatusCodes::OK);
  }

  // PUT /api/admin/user/{id}/role
  public function updateRole(array $admin, int $id)

  {
    $body = json_decode(file_get_contents("php://input"), true);
    if (!isset($body['role']) || !in_array($body['role'], ['user', 'admin'])) {
      return Response::error(null, StatusCodes::BAD_REQUEST, "Invalid role");
    }

    if ($admin['id'] == $id) {
      return Response::error(null, StatusCodes::FORBIDDEN, "You cannot change your own role");
    }

    $success = $this->auth->updateUserRole($id, $body['role']);

    if (!$success) {
      return Response::error(null, StatusCodes::INTERNAL_SERVER_ERROR, "Failed to update role");
    }

    return Response::success(null, StatusCodes::OK, "Role updated");
  }

  // DELETE /api/admin/user/{id}
  public function deleteUser(array $admin, int $id)
  {
    if ($admin['id'] == $id) {
      return Response::error(null, StatusCodes::FORBIDDEN, "You cannot delete yourself");
    }

    if (!$this->auth->getUserById($id)) {
      return Response::error(null, StatusCodes::NOT_FOUND, "User not found");
    }

    $this->auth->deleteUser($id);

    return Response::success(null, StatusCodes::OK, "User deleted");
  }
}
