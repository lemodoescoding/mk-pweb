<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\View;

class Page
{
  public function home(array $user): void
  {
    View::render('index_page', $user);
  }

  public function login(): void
  {
    View::render('html/login');
  }

  public function register(): void
  {
    View::render('html/register');
  }

  public function dashboardUser(array $user): void
  {
    View::render('indexDashboardUser', $user);
  }

  public function dashboardAdmin(array $user): void
  {
    View::render('indexDashboardAdmin', $user);
  }

  public function forbidden(): void
  {
    View::error(403);
  }

  public function categories(array $user): void
  {
    View::render('html/indexCategories', $user);
  }

  public function updateProfile(array $user): void
  {
    View::render('html/editProfile', $user);
  }

  public function jobSeed(array $user): void
  {
    View::render('html/jobseed', $user);
  }
}
