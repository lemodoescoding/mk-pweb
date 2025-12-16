<?php

declare(strict_types=1);

namespace App\Model;

use \PDO;

class Applications extends Model
{
  /**
   * Apply to a job (prevent duplicate)
   */
  public function apply(int $userId, int $jobId): bool
  {
    $stmt = $this->db->prepare("
            INSERT IGNORE INTO applications (user_id, job_id)
            VALUES (:uid, :jid)
        ");

    $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':jid', $jobId, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->rowCount() > 0;
  }

  /**
   * Count applications
   * - by user
   * - by job
   * - total
   */
  public function count(?int $userId = null, ?int $jobId = null): int
  {
    $sql = "SELECT COUNT(*) FROM applications WHERE 1=1";
    $params = [];

    if ($userId !== null) {
      $sql .= " AND user_id = :uid";
      $params[':uid'] = $userId;
    }

    if ($jobId !== null) {
      $sql .= " AND job_id = :jid";
      $params[':jid'] = $jobId;
    }

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);

    return (int) $stmt->fetchColumn();
  }

  /**
   * Get latest applications
   * (Admin dashboard / activity feed)
   */
  public function latest(int $limit = 10): array
  {
    $stmt = $this->db->prepare("
            SELECT 
                a.id,
                a.status,
                a.applied_at,
                u.id AS user_id,
                u.username,
                j.id AS job_id,
                j.title AS job_title,
                c.name AS company
            FROM applications a
            JOIN users u ON u.id = a.user_id
            JOIN jobs j ON j.id = a.job_id
            JOIN companies c ON c.id = j.company_id
            ORDER BY a.applied_at DESC
            LIMIT :limit
        ");

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Show a single application
   * (User view or admin view)
   */
  public function show(int $applicationId): ?array
  {
    $stmt = $this->db->prepare("
            SELECT 
                a.id,
                a.status,
                a.applied_at,
                u.id AS user_id,
                u.username,
                u.email,
                j.id AS job_id,
                j.title,
                j.salary_period,
                j.min_salary,
                j.max_salary,
                c.name AS company,
                l.city,
                l.state,
                l.country
            FROM applications a
            JOIN users u ON u.id = a.user_id
            JOIN jobs j ON j.id = a.job_id
            JOIN companies c ON c.id = j.company_id
            JOIN locations l ON l.id = j.location_id
            WHERE a.id = :id
            LIMIT 1
        ");

    $stmt->bindValue(':id', $applicationId, PDO::PARAM_INT);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result ?: null;
  }

  /**
   * Show all applications
   * - Admin: all users
   * - User: only their applications
   */
  public function showAll(?int $userId = null, int $limit = 20, int $offset = 0): array
  {
    $sql = "
            SELECT 
                a.id,
                a.status,
                a.applied_at,
                u.username,
                j.title AS job_title,
                j.id AS job_id,
                c.name AS company
            FROM applications a
            JOIN users u ON u.id = a.user_id
            JOIN jobs j ON j.id = a.job_id
            JOIN companies c ON c.id = j.company_id
            WHERE 1=1
        ";

    $params = [];

    if ($userId !== null) {
      $sql .= " AND a.user_id = :uid";
      $params[':uid'] = $userId;
    }

    $sql .= " ORDER BY a.applied_at DESC LIMIT :limit OFFSET :offset";

    $stmt = $this->db->prepare($sql);

    foreach ($params as $key => $value) {
      $stmt->bindValue($key, $value, PDO::PARAM_INT);
    }

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
