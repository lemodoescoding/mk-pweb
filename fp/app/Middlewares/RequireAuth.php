<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Utils\Response;
use App\Model\Auth;
use App\Enums\StatusCodes;
use App\Core\DB;

class RequireAuth
{

  public static function getUser(): array | bool
  {
    if (function_exists('getallheaders')) {
      $headers = getallheaders();
      $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? $headers['X-Authorization'] ?? null;
    } else {
      $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null;
    }

    // Check if the header was successfully retrieved
    if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
      Response::error([
        // 'headers' => $headers ?? [],
        // 'authHeader' => $authHeader ?? '',
      ], StatusCodes::UNAUTHORIZED, "Unauthorized, missing API Token");
      return false;
    }
    // Check if the header was successfully retrieved
    if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
      Response::error([
        'headers' => $headers ?? [],
        'authHeader' => $authHeader ?? '',
      ], StatusCodes::UNAUTHORIZED, "Unauthorized, missing API Token");
      return false;
    }

    $token = $matches[1];
    $tokenHash = hash('sha256', $token);
    $authModel = new Auth(DB::getInstance());
    $user = $authModel->findByRememberToken($tokenHash);

    // If token found in DB, return user
    if ($user)
      return $user;
    else {
      // If token is present but invalid/expired, give a more specific error
      Response::error(null, StatusCodes::UNAUTHORIZED, "Unauthorized: Invalid or expired token.");
      return false;
    }
  }

  public static function validate(): array | bool
  {
    $user = self::getUser();

    if (!$user) {
      Response::error(null, StatusCodes::UNAUTHORIZED, "Unauthorized");
      return false;
    }


    return $user;
  }

  public static function validateAdmin(): array | bool
  {
    $user = self::validate();

    if (!$user || ($user['role'] ?? 'user') !== 'admin') {
      Response::error(null, StatusCodes::UNAUTHORIZED, "test");
      return false;
    }

    return $user;
  }
}
