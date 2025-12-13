<?php

declare(strict_types=1);

namespace App\Model;

use \PDO;

abstract class Model
{
  protected PDO $db;

  public function __construct(PDO $db)
  {
    $this->db = $db;
  }

  public function getIdByField(string $table, string $field, string $value): int
  {
    $stmt = $this->db->prepare("SELECT id FROM {$table} WHERE {$field} = :v LIMIT 1");
    $stmt->bindValue(':v', $value);
    $stmt->execute();

    return (int) ($stmt->fetchColumn() ?: 0);
  }
}
