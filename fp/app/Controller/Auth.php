<?php

declare(strict_types=1);

namespace App\Controller;

use App\Utils\Response;
use App\Core\DB;
use App\Enums\StatusCodes;
use App\Model\Auth as AuthModel;

use \PDO;

class Auth
{
  private AuthModel $auth;

  public function __construct()
  {
    $this->auth = new AuthModel(DB::getInstance());
  }

  // Helper: admin required
  private function requireAdmin()
  {
    $user = $this->getAuthenticatedUser();

    if (!$user || ($user['role'] ?? 'user') !== 'admin') {
      Response::error("Not Authorized!", StatusCodes::FORBIDDEN);
    }

    return $user;
  }


  private function parseJSON(): array
  {
    $body = file_get_contents('php://input');
    if (empty($body)) {
      return [];
    }

    $data = json_decode($body, true);
    return is_array($data) ? $data : [];
  }

  // helper: find user by token or session
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

  // POST /api/auth/register
  public function register()
  {
    $data = $this->parseJSON();

    $username = trim((string)($data['username'] ?? ''));
    $email = isset($data['email']) ? trim((string)$data['email']) : null;
    $password = (string)($data['password'] ?? '');
    $passwordConfirm = (string)($data['password_confirm'] ?? '');

    if (!preg_match('/^[A-Za-z0-9_]{3,32}$/', $username)) {
      return Response::error(null, StatusCodes::BAD_REQUEST, "Invalid username format");
    }

    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/', $password)) {
      return Response::error(null, StatusCodes::BAD_REQUEST, "Password not met criteria");
    }

    if ($username === '' || $password === '') {
      return Response::error("username and password are required", StatusCodes::BAD_REQUEST);
    }

    if ($password !== $passwordConfirm) {
      return Response::error("passwords do not match", StatusCodes::BAD_REQUEST);
    }

    if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return Response::error("invalid email", StatusCodes::BAD_REQUEST);
    }

    if ($this->auth->usernameOrEmailExists($username, $email)) {
      return Response::error("username or email already taken", StatusCodes::CONFLICT);
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    try {

      $userId = $this->auth->createUser($username, $email, $passwordHash);
    } catch (\Throwable $e) {
      return Response::error("failed to create user", StatusCodes::INTERNAL_SERVER_ERROR);
    }

    // Auto-login: create session + issue api token
    $_SESSION['user_id'] = $userId;

    // generate api token and store hashed
    // $rawToken = bin2hex(random_bytes(32));
    // $tokenHash = hash('sha256', $rawToken);
    //
    // $this->auth->setToken($userId, $tokenHash);

    return Response::success([
      'user' => [
        'id' => $userId,
        'username' => $username,
        'email' => $email,
      ],
      // 'api_token' => $rawToken
    ], StatusCodes::CREATED, "User registered");
  }

  // POST /api/auth/login
  public function login()
  {
    $data = $this->parseJSON();

    $usernameOrEmail = trim((string)($data['username'] ?? $data['email'] ?? ''));
    $password = (string)($data['password'] ?? '');

    if ($usernameOrEmail === '' || $password === '') {
      return Response::error("username/email and password required", StatusCodes::BAD_REQUEST);
    }

    $user = $this->auth->findUser($usernameOrEmail);

    if (!$user || !password_verify($password, $user['password'])) {
      return Response::error("invalid credentials", StatusCodes::UNAUTHORIZED);
    }

    $userId = (int)$user['id'];
    // set session
    $_SESSION['user_id'] = $userId;

    // generate new api token (rotate)
    $rawToken = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $rawToken);

    $this->auth->setToken($userId, $tokenHash);

    return Response::success([
      'user' => [
        'id' => $userId,
        'username' => $user['username'],
        'email' => $user['email'],
      ],
      'api_token' => $rawToken
    ], StatusCodes::OK, "Login successful");
  }

  // GET /api/auth/me
  public function me()
  {
    $user = $this->getAuthenticatedUser();

    if (!$user) {
      return Response::error("unauthorized", StatusCodes::UNAUTHORIZED);
    }

    return Response::success(['user' => $user], StatusCodes::OK, "Authenticated");
  }

  // POST /api/auth/logout
  public function logout()
  {
    $user = $this->getAuthenticatedUser();
    if ($user) {
      $this->auth->clearToken($user['id']);
    }

    unset($_SESSION['user_id']);
    session_unset();
    session_destroy();

    return Response::success(null, StatusCodes::OK, "Logged out");
  }
}
