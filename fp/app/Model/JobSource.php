<?php

declare(strict_types=1);

namespace App\Model;

use \PDO;

class JobSource extends Model
{
  public function getId(string $name): int
  {
    $stmt = $this->db->prepare("SELECT id FROM job_sources WHERE name = :name");
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->execute();
    $r = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($r) return (int) $r['id'];

    $stmt = $this->db->prepare("INSERT INTO job_sources (name) VALUES (:name)");
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->execute();
    return (int) $this->db->lastInsertId();
  }

  public function insertSource(string $name): int
  {
    $stmt = $this->db->prepare("
            INSERT IGNORE INTO job_sources (name) VALUES (:name)
        ");
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->execute();

    return (int) $this->db->lastInsertId() ?: $this->getIdByField('job_sources', 'name', $name);
  }
}
