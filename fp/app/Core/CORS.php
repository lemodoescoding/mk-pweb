<?php

declare(strict_types=1);

namespace App\Core;

use App\Enums\StatusCodes;

class CORS
{
  public static function handleCORS(): void
  {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE, PUT");

    // Preflight request handling
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
      http_response_code(StatusCodes::NO_CONTENT->value);
      exit();
    }
  }
}
