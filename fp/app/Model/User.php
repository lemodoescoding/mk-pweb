<?php

declare(strict_types=1);

namespace App\Model;

use App\Core\DB;
use \PDO;

class User extends Model
{
  protected PDO $db;

  public function __construct()
  {
    $this->db = DB::getInstance();
  }

  public function getUserIdByToken(string $apiToken): int
  {
    $stmt = $this->db->prepare("
            SELECT id
            FROM users
            WHERE api_token = :token
            LIMIT 1
        ");

    $stmt->bindValue(':token', $apiToken, PDO::PARAM_STR);
    $stmt->execute();

    $id = $stmt->fetchColumn();
    return $id ? (int) $id : -1;
  }

  public function getById(int $userId): ?array
  {
    $stmt = $this->db->prepare("
            SELECT id, username, email, role, placeholder, created_at
            FROM users
            WHERE id = :id
            LIMIT 1
        ");

    $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
    $stmt->execute();

    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    return $data ?: null;
  }

  /**
   * Update avatar path
   */
  public function updateAvatar(int $userId, string $path): bool
  {
    $stmt = $this->db->prepare("
            UPDATE users
            SET avatar = :avatar, updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");

    $stmt->bindValue(':avatar', $path, PDO::PARAM_STR);
    $stmt->bindValue(':id', $userId, PDO::PARAM_INT);

    return $stmt->execute();
  }

  /**
   * Change API token (optional but useful)
   */
  public function updateApiToken(int $userId, string $token): bool
  {
    $stmt = $this->db->prepare("
            UPDATE users
            SET api_token = :token
            WHERE id = :id
        ");

    $stmt->bindValue(':token', $token, PDO::PARAM_STR);
    $stmt->bindValue(':id', $userId, PDO::PARAM_INT);

    return $stmt->execute();
  }

  public function updatePlaceholder(int $userId, string $placeholder): bool
  {
    $stmt = $this->db->prepare("
            UPDATE users
            SET placeholder = :pl
            WHERE id = :id
        ");

    $stmt->bindValue(':pl', htmlspecialchars(strtolower($placeholder)), PDO::PARAM_STR);
    $stmt->bindValue(':id', $userId, PDO::PARAM_INT);

    return (bool) $stmt->execute();
  }

  public function create(array $data): int
  {
    try {
      $stmt = $this->db->prepare("
            INSERT INTO users (username, email, password, placeholder)
            VALUES (:username, :email, :password, :placeholder)
        ");

      // Set default values if not provided, aligning with table defaults
      $username = $data['username'];
      $email = $data['email'] ?? null;
      $password = $data['password']; // Expected to be HASHED
      $placeholder = $data['placeholder'] ?? $username; // Use username as default placeholder

      $stmt->bindValue(':username', $username, PDO::PARAM_STR);
      $stmt->bindValue(':email', $email, $email === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
      $stmt->bindValue(':password', $password, PDO::PARAM_STR);
      $stmt->bindValue(':placeholder', $placeholder, PDO::PARAM_STR);

      $stmt->execute();

      // Return the ID of the new user
      return (int) $this->db->lastInsertId();
    } catch (\PDOException $e) {
      return 0;
    } catch (\Throwable $e) {
      // Log other errors
      return 0;
    }
  }

  public function getByUsername(string $username): ?array
  {
    $stmt = $this->db->prepare("
        SELECT id, username, email, password, role, placeholder, api_token
        FROM users
        WHERE username = :username
        LIMIT 1
    ");

    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    return $data ?: null;
  }

  public function updatePassword(int $userId, string $hashedPassword): bool
  {
    try {
      $stmt = $this->db->prepare("
            UPDATE users
            SET password = :password, updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");

      $stmt->bindValue(':password', $hashedPassword, PDO::PARAM_STR);
      $stmt->bindValue(':id', $userId, PDO::PARAM_INT);

      return $stmt->execute();
    } catch (\Throwable $th) {
      // Log error
      return false;
    }
  }

  public function deleteUser(int $userId): bool
  {
    try {
      $stmt = $this->db->prepare("
            DELETE FROM users
            WHERE id = :id
        ");

      $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
      $stmt->execute();

      // Check if exactly one row was affected (the user was deleted)
      return $stmt->rowCount() === 1;
    } catch (\PDOException $e) {
      return false;
    } catch (\Throwable $e) {
      return false;
    }
  }

  public function promoteUser(int $userId): bool
  {
    try {
      $stmt = $this->db->prepare("
            UPDATE users SET role = 'admin'
            WHERE id = :id
        ");

      $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
      $stmt->execute();

      // Check if exactly one row was affected (the user was deleted)
      return $stmt->rowCount() === 1;
    } catch (\PDOException $e) {
      return false;
    } catch (\Throwable $e) {
      return false;
    }
  }

  public function unpromoteUser(int $userId): bool
  {
    try {
      $stmt = $this->db->prepare("
            UPDATE users SET role = 'user'
            WHERE id = :id
        ");

      $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
      $stmt->execute();

      // Check if exactly one row was affected (the user was deleted)
      return $stmt->rowCount() === 1;
    } catch (\PDOException $e) {
      return false;
    } catch (\Throwable $e) {
      return false;
    }
  }
}
