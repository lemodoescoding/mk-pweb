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
    // 1) Check session
    if (isset($_SESSION['user_id'])) {
      $user = $this->auth->findById(intval($_SESSION['user_id']));
      if ($user) return $user;
    }

    // 2) Check Authorization header Bearer <token>
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

  // Helper: admin required
  private function requireAdmin()
  {
    $user = $this->getAuthenticatedUser();

    if (!$user || ($user['role'] ?? 'user') !== 'admin') {
      Response::error("Admin only", StatusCodes::FORBIDDEN);
    }

    return $user;
  }

  // === Admin Dashboard ===

  // GET /api/admin/stats
  public function stats()
  {
    $this->requireAdmin();

    return Response::success([
      'total_users' => $this->auth->countUsers(),
      'total_admins' => $this->auth->countAdmins(),
    ], StatusCodes::OK, "Admin stats");
  }

  // GET /api/admin/users
  public function listUsers()
  {
    $this->requireAdmin();

    return Response::success([
      'users' => $this->auth->getAllUsers()
    ], StatusCodes::OK, "User list");
  }

  // GET /api/admin/user/{id}
  public function viewUser(int $id)
  {
    $this->requireAdmin();

    $user = $this->auth->getUserById($id);

    if (!$user) {
      return Response::error("User not found", StatusCodes::NOT_FOUND);
    }

    return Response::success(['user' => $user]);
  }

  // POST /api/admin/user/{id}/role
  public function updateRole(int $id)
  {
    $admin = $this->requireAdmin();

    $body = json_decode(file_get_contents("php://input"), true);
    if (!isset($body['role']) || !in_array($body['role'], ['user', 'admin'])) {
      return Response::error("Invalid role", StatusCodes::BAD_REQUEST);
    }

    if ($admin['id'] == $id) {
      return Response::error("You cannot change your own role", StatusCodes::FORBIDDEN);
    }

    $success = $this->auth->updateUserRole($id, $body['role']);

    if (!$success) {
      return Response::error("Failed to update role", StatusCodes::INTERNAL_SERVER_ERROR);
    }

    return Response::success(null, StatusCodes::OK, "Role updated");
  }

  // DELETE /api/admin/user/{id}
  public function deleteUser(int $id)
  {
    $admin = $this->requireAdmin();

    if ($admin['id'] == $id) {
      return Response::error("You cannot delete yourself", StatusCodes::FORBIDDEN);
    }

    if (!$this->auth->getUserById($id)) {
      return Response::error("User not found", StatusCodes::NOT_FOUND);
    }

    $this->auth->deleteUser($id);

    return Response::success(null, StatusCodes::OK, "User deleted");
  }
}
