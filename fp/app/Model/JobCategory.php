<?php

declare(strict_types=1);

namespace App\Model;

use \PDO;

class JobCategory extends Model
{
  public function attach(int $jobId, int $categoryId): void
  {
    try {
      $this->db->beginTransaction();
      $stmt = $this->db->prepare("
            INSERT IGNORE INTO job_categories (job_id, category_id)
            VALUES (:jid, :cid)
        ");

      $stmt->bindValue(':jid', $jobId, PDO::PARAM_INT);
      $stmt->bindValue(':cid', $categoryId, PDO::PARAM_INT);
      $stmt->execute();

      $this->db->commit();
    } catch (\Throwable $e) {
      echo $e->getMessage();
      $this->db->rollBack();
    }
  }

  public function detach(int $jobId): int
    {
        try {
            $stmt = $this->db->prepare(
                "DELETE FROM job_categories WHERE job_id = :job_id"
            );
            $stmt->bindValue(':job_id', $jobId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->rowCount();
        } catch (\Throwable $e) {
            return -1; // Return -1 on failure
        }
    }

  public function getCountCategory(): array
  {
    $stmt = $this->db->prepare("SELECT COUNT(jc.category_id) AS category_count, c.name AS category_name FROM job_categories jc 
      INNER JOIN jobs j ON jc.job_id = j.id 
      INNER JOIN categories c ON jc.category_id = c.id 
      WHERE j.deleted_at IS NULL
      GROUP BY jc.category_id");

    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
  }
}
