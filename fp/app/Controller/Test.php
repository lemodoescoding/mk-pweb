<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enums\StatusCodes;
use App\Utils\Response;
use \Ramsey\Uuid\Rfc4122\UuidV4;

class Test
{
  private $uuid;

  public function __construct()
  {
    $this->uuid = UuidV4::uuid4();
  }

  public function getUUID()
  {
    Response::success(['uuid' => $this->uuid], StatusCodes::OK);
  }
}
