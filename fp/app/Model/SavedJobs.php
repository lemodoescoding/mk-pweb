<?php

declare(strict_types=1);

namespace App\Model;

use \PDO;

class SavedJobs extends Model
{
  public function save(int $userId, int $jobId): bool
  {
    $stmt = $this->db->prepare("
            INSERT IGNORE INTO saved_jobs (user_id, job_id)
            VALUES (:uid, :jid)
        ");
    $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':jid', $jobId, PDO::PARAM_INT);
    return $stmt->execute();
  }
}
