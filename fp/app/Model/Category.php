<?php

declare(strict_types=1);

namespace App\Model;

use App\Core\Env;
use \PDO;

class Category extends Model
{
  public function firstOrCreate(string $name): int
  {
    $stmt = $this->db->prepare("SELECT id FROM categories WHERE name = :name");
    $stmt->bindValue(':name', strtolower($name), PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) return (int) $row['id'];

    $stmt = $this->db->prepare("INSERT INTO categories (name) VALUES (:name)");
    $stmt->bindValue(':name', strtolower($name), PDO::PARAM_STR);
    $stmt->execute();

    return (int) $this->db->lastInsertId();
  }

  public function getAll(): array
  {
    $stmt = $this->db->prepare("SELECT name FROM categories");
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getPaginated(string $category, int $page): array
  {
    $page = max(1, intval($page));
    $limit = intval(Env::get('LIMIT_FETCH_SQL')) ?? 20;
    $offset = ($page - 1) * $limit;

    $stmt = $this->db->prepare("
      SELECT 
        j.*,
        c.name AS company,
        l.city, l.state, l.country
      FROM jobs j
      JOIN job_categories jc ON jc.job_id = j.id
      JOIN categories cat ON cat.id = jc.category_id
      JOIN companies c ON c.id = j.company_id
      JOIN locations l ON l.id = j.location_id
      WHERE cat.name = :ct AND j.deleted_at IS NULL
      ORDER BY j.date_posted DESC
      LIMIT :limit OFFSET :offset;
    ");

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':ct', strtolower($category), PDO::PARAM_STR);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function searchCount(string $category): int
  {
    $stmt = $this->db->prepare("
      SELECT COUNT(*) FROM job_categories jc
      INNER JOIN jobs j ON j.id = jc.job_id
      INNER JOIN categories ct ON jc.category_id = ct.id
      WHERE ct.name = :ct AND j.deleted_at IS NULL
      ");

    $stmt->bindValue(':ct', strtolower($category), PDO::PARAM_STR);
    $stmt->execute();

    return (int) $stmt->fetchColumn();
  }
}
