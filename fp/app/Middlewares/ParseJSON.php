<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Utils\Response;
use App\Model\Auth;
use App\Enums\StatusCodes;
use App\Core\DB;

class ParseJSON
{
  public static function parse(): array
  {
    $body = file_get_contents('php://input');
    if (empty($body)) {
      return [];
    }

    $data = json_decode($body, true);
    return is_array($data) ? $data : [];
  }
}
