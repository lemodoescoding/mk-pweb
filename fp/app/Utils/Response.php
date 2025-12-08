<?php

declare(strict_types=1);

namespace App\Utils;

use App\Enums\StatusCodes;

class Response
{
  public static function json(bool $status, $data = null, ?string $message = null, StatusCodes $httpCode = StatusCodes::OK)
  {
    http_response_code($httpCode->value);

    header('Content-Type: application/json');

    echo json_encode([
      'status' => $status,
      'data' => $data,
      'message' => $message,
    ]);

    exit;
  }

  public static function success($data = null, StatusCodes $httpCode = StatusCodes::OK, string $message = "success")
  {
    self::json(true, $data, $message, $httpCode);
  }

  public static function error($data = null, StatusCodes $httpCode = StatusCodes::BAD_REQUEST, string $message = "error")
  {
    self::json(false, $data, $message, $httpCode);
  }
}
