<?php

declare(strict_types=1);

namespace App\Model;

use \PDO;

class Company extends Model
{
  private function getLastInsertedId(): int
  {
    $stmt = $this->db->prepare("SELECT id FROM companies ORDER BY id DESC");
    $stmt->execute();

    return (int) $stmt->fetchColumn();
  }
  public function firstOrCreate(string $name, string $logo = "", string $website = ""): int
  {
    $stmt = $this->db->prepare("SELECT id FROM companies WHERE name = :name");
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) return (int) $row['id'];

    $stmt = $this->db->prepare("
            INSERT INTO companies (name, logo, website)
            VALUES (:name, :logo, :website)
        ");
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':logo', $logo, PDO::PARAM_STR);
    $stmt->bindValue(':website', $website, PDO::PARAM_STR);
    $stmt->execute();

    return (int) $this->getLastInsertedId();
  }

  public function updateCompany(
    int $companyId,
    string $name,
    ?string $logo,
    ?string $website
  ): bool {
    $stmt = $this->db->prepare("
        UPDATE companies
        SET name = :name,
            logo = :logo,
            website = :website
        WHERE id = :id
    ");

    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':logo', $logo);
    $stmt->bindValue(':website', $website);
    $stmt->bindValue(':id', $companyId, PDO::PARAM_INT);

    return $stmt->execute();
  }
}
