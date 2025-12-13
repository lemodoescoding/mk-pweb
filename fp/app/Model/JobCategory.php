<?php

declare(strict_types=1);

namespace App\Model;

use \PDO;

class JobCategory extends Model
{
  public function attach(int $jobId, int $categoryId): void
  {
    $stmt = $this->db->prepare("
            INSERT IGNORE INTO job_categories (job_id, category_id)
            VALUES (:jid, :cid)
        ");

    $stmt->bindValue(':jid', $jobId, PDO::PARAM_INT);
    $stmt->bindValue(':cid', $categoryId, PDO::PARAM_INT);
    $stmt->execute();
  }

  public function getCountCategory(): array
  {
    $stmt = $this->db->prepare("SELECT COUNT(jc.category_id) AS category_count, c.name AS category_name FROM job_categories jc 
      INNER JOIN jobs j ON jc.job_id = j.id 
      INNER JOIN categories c ON jc.category_id = c.id 
      GROUP BY jc.category_id");

    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
  }
}
