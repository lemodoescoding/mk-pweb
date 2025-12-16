<?php

declare(strict_types=1);

namespace App\Model;

use App\Core\DB;
use PDO;

class UserJobHistory
{
  private PDO $db;

  public function __construct()
  {
    $this->db = DB::getInstance();
  }

  public function getByUserId(int $userId): array
  {
    $stmt = $this->db->prepare("
            SELECT id, job_title, company_name, status, applied_at
            FROM user_job_history
            WHERE user_id = :uid
            ORDER BY applied_at DESC
        ");

    $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function create(array $data): bool
  {
    $sql = "
            INSERT INTO user_job_history
            (user_id, job_title, company_name, status, applied_at)
            VALUES (:uid, :title, :company, :status, :applied_at)
        ";

    $stmt = $this->db->prepare($sql);

    $stmt->bindValue(':uid', $data['user_id'], PDO::PARAM_INT);
    $stmt->bindValue(':title', $data['job_title'], PDO::PARAM_STR);
    $stmt->bindValue(':company', $data['company_name'], PDO::PARAM_STR);
    $stmt->bindValue(':status', $data['status'] ?? 'applied', PDO::PARAM_STR);
    $stmt->bindValue(':applied_at', $data['applied_at'] ?? date('Y-m-d'), PDO::PARAM_STR);

    return $stmt->execute();
  }

  public function delete(int $id, int $userId): bool
  {
    $stmt = $this->db->prepare("
            DELETE FROM user_job_history
            WHERE id = :id AND user_id = :uid
        ");

    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);

    return $stmt->execute();
  }
}
