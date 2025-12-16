<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\UserProfile;
use App\Model\UserJobHistory;

use \PDO;

class Auth extends Model
{
  private UserProfile $upf;
  private UserJobHistory $ujh;

  // find data by id
  public function __construct(PDO $db)
  {
    parent::__construct($db);
    $this->ujh = new UserJobHistory($db);
    $this->upf = new UserProfile($db);
  }
  public function findById(int $id): ?array
  {
    $stmt = $this->db->prepare(
      "SELECT *
             FROM users 
             WHERE id = :id LIMIT 1"
    );
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch() ?: null;
  }

  // find user by username or email
  public function findUser(string $usernameOrEmail): ?array
  {
    $sql = "SELECT * FROM users WHERE username = :u OR email = :e LIMIT 1";
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(":e", $usernameOrEmail, PDO::PARAM_STR);
    $stmt->bindValue(":u", $usernameOrEmail, PDO::PARAM_STR);

    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result ?: null;
  }

  // check data by username/email exists
  public function usernameOrEmailExists(string $username, ?string $email): bool
  {
    $stmt = $this->db->prepare(
      "SELECT id 
             FROM users 
             WHERE username = :username 
                OR (email IS NOT NULL AND email = :email)
             LIMIT 1"
    );
    $stmt->bindValue(':username', $username);
    $stmt->bindValue(':email', $email);
    $stmt->execute();

    return (bool)$stmt->fetch();
  }

  private function getLastInsertedUserId(): int
  {
    $stmt = $this->db->prepare("SELECT id FROM users ORDER BY id DESC LIMIT 1");
    $stmt->execute();

    return (int) $stmt->fetchColumn() ?: 0;
  }

  // create user
  public function createUser(string $username, string $email, string $passwordHash, string $type = 'manual'): int
  {
    $sql = "INSERT INTO users (username, email, password, type, placeholder)
                VALUES (:u, :e, :p, :t, :hl)";

    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(":u", $username, PDO::PARAM_STR);
    $stmt->bindValue(":e", $email, PDO::PARAM_STR);
    $stmt->bindValue(":p", $passwordHash, PDO::PARAM_STR);
    $stmt->bindValue(":t", $type, PDO::PARAM_STR);
    $stmt->bindValue(":hl", strtolower(substr($username, 0, (strlen($username) < 8 ? strlen($username) : 8))), PDO::PARAM_STR);

    $stmt->execute();

    $userId = $this->getLastInsertedUserId();

    if ($userId > 0) {
      $this->upf->createDefault($userId);
    }

    return (int) $userId;
  }

  // update remember token (api_token)
  public function setToken(int $userId, string $tokenHash): bool
  {
    $sql = "UPDATE users SET api_token = :t WHERE id = :id";
    $stmt = $this->db->prepare($sql);

    $stmt->bindValue(":t", $tokenHash, PDO::PARAM_STR);
    $stmt->bindValue(":id", $userId, PDO::PARAM_INT);

    return $stmt->execute();
  }

  // find user by token
  public function findByRememberToken(string $tokenHash): ?array
  {
    $sql = "SELECT * FROM users WHERE api_token = :t LIMIT 1";
    $stmt = $this->db->prepare($sql);

    $stmt->bindValue(":t", $tokenHash, PDO::PARAM_STR);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return $user ?: null;
  }

  // verify credential by username/email and password
  public function verifyLogin(string $usernameOrEmail, string $password): ?array
  {
    $user = $this->findUser($usernameOrEmail);

    if (!$user) return null;

    if (!password_verify($password, $user["password"])) {
      return null;
    }

    return $user;
  }

  // find data by token
  public function findByToken(string $tokenHash): ?array
  {
    $stmt = $this->db->prepare(
      "SELECT id, username, email 
             FROM users 
             WHERE api_token = :token LIMIT 1"
    );
    $stmt->bindValue(':token', $tokenHash);
    $stmt->execute();

    return $stmt->fetch() ?: null;
  }

  // clear token by id
  public function clearToken(int $userId): void
  {
    $stmt = $this->db->prepare(
      "UPDATE users
             SET api_token = NULL
             WHERE id = :id"
    );
    $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
    $stmt->execute();
  }

  // Fetch all users (admin dashboard)
  public function getAllUsers(): array
  {
    $stmt = $this->db->query(
      "SELECT id, username, email, role, created_at 
             FROM users 
             ORDER BY id DESC"
    );
    return $stmt->fetchAll();
  }

  // Fetch single user by ID (for admin)
  public function getUserById(int $id): ?array
  {
    $stmt = $this->db->prepare(
      "SELECT id, username, email, role, created_at
             FROM users 
             WHERE id = :id LIMIT 1"
    );
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch() ?: null;
  }

  // Promote/demote user
  public function updateUserRole(int $id, string $role): bool
  {
    $stmt = $this->db->prepare(
      "UPDATE users SET role = :role WHERE id = :id"
    );
    $stmt->bindValue(':role', $role);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);

    return $stmt->execute();
  }

  // Delete user
  public function deleteUser(int $id): bool
  {
    $stmt = $this->db->prepare(
      "DELETE FROM users WHERE id = :id"
    );
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    return $stmt->execute();
  }

  // Count users
  public function countUsers(): int
  {
    $stmt = $this->db->query("SELECT COUNT(*) AS total FROM users");
    $res = $stmt->fetch();
    return (int)$res['total'];
  }

  // Count admins
  public function countAdmins(): int
  {
    $stmt = $this->db->query("SELECT COUNT(*) AS total FROM users WHERE role = 'admin'");
    $res = $stmt->fetch();
    return (int)$res['total'];
  }

  public function findOrCreateFromGoogle(array $googleUser): array
  {
    // Use email as primary lookup
    $email = $googleUser['email'] ?? null;

    if (!$email) {
      throw new \Exception("Google user email is missing");
    }

    // Try to find user by email
    $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
      return $user; // found existing
    }

    // Create new user if not found
    $username = $googleUser['name'] ?? explode('@', $email)[0];
    $passwordHash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT); // random password

    $stmt = $this->db->prepare(
      "INSERT INTO users (username, email, password, placeholder, type) VALUES (:username, :email, :password, :plc, :t)"
    );
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->bindValue(':password', $passwordHash, PDO::PARAM_STR);
    $stmt->bindValue(':plc', strtolower(substr($username, 0, (strlen($username) < 8 ? strlen($username) : 8))), PDO::PARAM_STR);
    $stmt->bindValue(':t', 'google', PDO::PARAM_STR);
    // $stmt->bindValue(':ava', $googleUser['picture'], PDO::PARAM_STR);
    $stmt->execute();

    $userId = $this->getLastInsertedUserId();

    if ($userId) {
      $this->upf->updateOrCreate($userId, [
        'bio' => 'My Bio',
        'last_education' => 'Your Education',
        'photo' => $googleUser['picture']
      ]);
    }

    return $this->findById($userId);
  }

  public function updateApiToken(int $userId, string $tokenHash): bool
  {
    return $this->setToken($userId, $tokenHash);
  }

  public function countUsersByType(string $type): int
  {
    $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM users WHERE type = :type");
    $stmt->bindValue(':type', $type, PDO::PARAM_STR);
    $stmt->execute();
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    return (int)$res['total'];
  }
}
