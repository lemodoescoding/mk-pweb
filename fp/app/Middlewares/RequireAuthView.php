<?php

declare(strict_types=1);

namespace App\Middlewares;

// use App\Utils\Response;
use App\Model\Auth;
use App\Enums\StatusCodes;
use App\Core\View;
use App\Core\DB;

class RequireAuthView
{
  public static function getUser(): array | bool
  {
    $token = $_COOKIE['api_token'] ?? null;

    if (!$token) {
      return false;
    }

    $tokenHash = hash('sha256', $token);
    $authModel = new Auth(DB::getInstance());
    $user = $authModel->findByRememberToken($tokenHash);

    if ($user)
      return $user;
    else
      return false;
  }

  public static function validate(): array | bool
  {
    $user = self::getUser();

    if (!$user) {
      View::error((int) StatusCodes::FORBIDDEN->value);
      return false;
    }


    return $user;
  }

  public static function validateAdmin(): array | bool
  {
    $user = self::validate();

    if (!$user || ($user['role'] ?? 'user') !== 'admin') {
      View::error((int) StatusCodes::FORBIDDEN->value);
      return false;
    }

    return $user;
  }

  public static function validateHome(): array | bool
  {
    $user = self::getUser();

    if (!$user) {
      View::render('html/login');
      return false;
    }


    return $user;
  }
}
