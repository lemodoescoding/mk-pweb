<?php

declare(strict_types=1);

namespace App\Model;

use App\Core\Env;
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

  public function delete(int $userId, int $jobId): bool
  {
    $stmt = $this->db->prepare(
      "DELETE FROM saved_jobs
        WHERE user_id = :uid 
        AND job_id = :jid;"
    );
    $stmt->bindValue(':jid', $jobId, PDO::PARAM_INT);
    $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);

    return (bool) $stmt->execute();
  }

  public function getAllSaved(int $userId): array
  {
    $stmt = $this->db->prepare("
      SELECT 
        j.id,
        j.title,
        j.apply_link,
        j.is_remote,
        j.min_salary,
        j.max_salary,
        j.salary_period,
        j.date_posted,
        c.name AS company,
        l.city,
        l.state,
        l.country,
        sj.saved_at
      FROM saved_jobs sj
      JOIN jobs j ON j.id = sj.job_id
      JOIN companies c ON c.id = j.company_id
      JOIN locations l ON l.id = j.location_id
      WHERE sj.user_id = :uid AND j.deleted_at IS NULL
      ORDER BY sj.saved_at DESC;
    ");
    $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
    $stmt->execute();

    return (array) $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getPaginated(int $userId, int $page): array
  {
    $page = max(1, intval($page));
    $limit = intval(Env::get('LIMIT_FETCH_SQL')) ?? 20;
    $offset = ($page - 1) * $limit;

    $stmt = $this->db->prepare("
      SELECT
        j.id,
        j.title,
        c.name AS company,
        l.city,
        l.state,
        l.country,
        sj.saved_at
      FROM saved_jobs sj
      JOIN jobs j ON j.id = sj.job_id
      JOIN companies c ON c.id = j.company_id
      JOIN locations l ON l.id = j.location_id
      WHERE sj.user_id = :uid AND j.deleted_at IS NULL
      ORDER BY sj.saved_at DESC
      LIMIT :lim OFFSET :off;");

    $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':off', $offset, PDO::PARAM_INT);

    $stmt->execute();

    return (array) $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getCountSaved(int $userId): int
  {
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM saved_jobs WHERE user_id = :uid");
    $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);

    $stmt->execute();
    return (int) $stmt->fetchColumn();
  }
}
