<?php

declare(strict_types=1);

namespace App\Controller;

abstract class BaseAuth
{
  abstract function login();
  abstract function register();
  abstract function logout();
  abstract function me();
}
