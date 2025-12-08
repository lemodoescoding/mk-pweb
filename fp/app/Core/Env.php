<?php

declare(strict_types=1);

namespace App\Core;

use \Dotenv\Dotenv;

class Env
{
  public static function load(string $envPath)
  {
    if (!file_exists($envPath . '/.env')) {
      throw new \Exception('.env file is not found in : ' . $envPath);
    }

    $dotenv = Dotenv::createImmutable($envPath);
    $dotenv->load();
  }

  public static function get(string $key, $default = null): mixed
  {
    return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
  }
}
