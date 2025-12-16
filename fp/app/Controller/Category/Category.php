<?php

declare(strict_types=1);

namespace App\Controller\Category;

use App\Model\Category as CategoryModel;
use App\Core\DB;
use App\Core\Env;
use App\Enums\StatusCodes;
use App\Utils\Response;
use \PDO;

class Category
{
  private CategoryModel $ctm;

  public function __construct()
  {
    $this->ctm = new CategoryModel(DB::getInstance());
  }
  public function getAll(): void
  {
    $data = $this->ctm->getAll();

    if (empty($data)) {
      Response::success(null, StatusCodes::OK, "No Category Data Stored");
      exit;
    }

    Response::success(['categories' => $data], StatusCodes::OK, "Category data returned");
  }

  public function searchPaginated(string $term, int $page)
  {
    $page = max(1, intval($page));
    $limit = intval(Env::get('LIMIT_FETCH_SQL')) ?? 20;
    $offset = ($page - 1) * $limit;

    $jobs = $this->ctm->getPaginated($term, $page);
    $count = $this->ctm->searchCount($term);

    if ($count < 1 && $page > 0) {
      Response::success(null, StatusCodes::OK, 'No data matched from search');
      exit;
    }

    if ((!empty($jobs))) {
      Response::success(['jobs' => $jobs, 'countAll' => $count, 'currStart' => ($page - 1) * $limit + 1], StatusCodes::OK, "Return paginated filtered data");
    } else {
      Response::error(null, StatusCodes::NOT_FOUND, "Pagination filtered doesnt go that far");
    }
    exit;
  }
}
