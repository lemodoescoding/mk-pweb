<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Controller\BaseAuth;
use App\Utils\Response;
use App\Core\DB;
use App\Enums\StatusCodes;
use App\Model\Auth as AuthModel;

class Auth extends BaseAuth
{
  private AuthModel $auth;

  public function __construct()
  {
    $this->auth = new AuthModel(DB::getInstance());
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
      return Response::error(null, StatusCodes::BAD_REQUEST, "username and password are required");
    }

    if ($password !== $passwordConfirm) {
      return Response::error(null, StatusCodes::BAD_REQUEST, "passwords do not match");
    }

    if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return Response::error("invalid email", StatusCodes::BAD_REQUEST);
    }

    if ($this->auth->usernameOrEmailExists($username, $email)) {
      return Response::error(null, StatusCodes::CONFLICT, "username or email already taken");
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    try {

      $userId = $this->auth->createUser($username, $email, $passwordHash);
    } catch (\Throwable $e) {
      return Response::error($e->getMessage(), StatusCodes::INTERNAL_SERVER_ERROR, "failed to create user");
    }

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
      return Response::error(null, StatusCodes::BAD_REQUEST, "username/email and password required");
    }

    $user = $this->auth->findUser($usernameOrEmail);

    if (!$user || !password_verify($password, $user['password'])) {
      return Response::error(null, StatusCodes::UNAUTHORIZED, "invalid credentials");
    }

    $userId = (int)$user['id'];
    // generate new api token (rotate)
    $rawToken = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $rawToken);

    $this->auth->setToken($userId, $tokenHash);

    // return Response::success([
    //   'user' => [
    //     // 'id' => $userId,
    //     'username' => $user['username'],
    //     'email' => $user['email'],
    //     'placeholder' => $user['placeholder'],
    //     'role' => $user['role']
    //   ],
    //   'api_token' => $rawToken
    // ], StatusCodes::OK, "Login successful");

    setcookie('api_token', $rawToken, [
      'expires' => time() + 3600 * 24,
      'path' => '/',
      'httponly' => false,
      'samesite' => 'Lax',
    ]);

    setcookie('role', $user['role'], [
      'expires' => time() + 3600 * 24,
      'path' => '/',
      'httponly' => false,
      'samesite' => 'Lax',
    ]);

    $redirect = '/dashboard';

    if ($user['role'] == 'admin') {
      $redirect = '/admin';
    }

    header('Location: ' . $redirect);

    return;
  }

  // GET /api/auth/me
  public function me(?array $user = null)
  {
    return Response::success([
      'username' => $user['username'],
      'placeholder' => $user['placeholder'],
      'email' => $user['email'],
      'role' => $user['role']
    ], StatusCodes::OK, "Authenticated");
  }

  // POST /api/auth/logout
  public function logout(?array $user = null)
  {
    // $user = $this->getAuthenticatedUser();
    // if (!$user) {
    //   return Response::error("unauthorized", StatusCodes::UNAUTHORIZED);
    // }

    $this->auth->clearToken($user['id']);

    setcookie('api_token', '', time() - 3600, '/', '', false, true);
    setcookie('role', '', time() - 3600, '/', '', false, true);

    session_unset();
    session_destroy();

    return Response::success(null, StatusCodes::OK, "Logged out");
  }
}
