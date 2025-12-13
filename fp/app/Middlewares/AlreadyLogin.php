<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Utils\Response;
use App\Model\Auth;
use App\Enums\StatusCodes;
use App\Core\DB;

class AlreadyLogin
{
  public static function validate()
  {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if ($authHeader && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
      $token = $matches[1];

      $authModel = new Auth(DB::getInstance());
      $user = $authModel->findByRememberToken(hash('sha256', $token));

      if ($user) {
        return Response::error(null, StatusCodes::BAD_REQUEST, "You are already logged in");
      }
    }
  }
}
