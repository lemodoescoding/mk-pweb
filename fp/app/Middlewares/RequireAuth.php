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
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
      Response::error(null, StatusCodes::UNAUTHORIZED, "Unauthorized");
      return false; // stop further execution
    }

    $token = $matches[1];
    $authModel = new Auth(DB::getInstance());
    $user = $authModel->findByRememberToken(hash('sha256', $token));

    if($user) 
      return $user;
    else
      return false;
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

  public static function validateAdmin() : array | bool 
  {
    $user = self::validate();

    if (!$user || ($user['role'] ?? 'user') !== 'admin') {
      Response::error(null, StatusCodes::UNAUTHORIZED, "Unauthorized");
      return false;
    }

    return $user;
  }
}
