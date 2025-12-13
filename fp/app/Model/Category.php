<?php

declare(strict_types=1);

namespace App\Model;

use \PDO;

class Category extends Model
{
  public function firstOrCreate(string $name): int
  {
    $stmt = $this->db->prepare("SELECT id FROM categories WHERE name = :name");
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) return (int) $row['id'];

    $stmt = $this->db->prepare("INSERT INTO categories (name) VALUES (:name)");
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->execute();

    return (int) $this->db->lastInsertId();
  }

  public function getAll(): array
  {
    $stmt = $this->db->prepare("SELECT name FROM categories");
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
  }
}
