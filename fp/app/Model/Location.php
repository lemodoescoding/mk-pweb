<?php

declare(strict_types=1);

namespace App\Model;

use \PDO;

class Location extends Model
{
  public function firstOrCreate(array $loc): int
  {
    $stmt = $this->db->prepare("
            SELECT id FROM locations 
            WHERE city = :c AND state = :s AND country = :y
        ");
    $stmt->bindValue(':c', $loc['city'], PDO::PARAM_STR);
    $stmt->bindValue(':s', $loc['state'], PDO::PARAM_STR);
    $stmt->bindValue(':y', $loc['country'], PDO::PARAM_STR);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) return (int) $row['id'];

    $stmt = $this->db->prepare("
            INSERT INTO locations (city, state, country, lat, lon)
            VALUES (:c, :s, :y, :lt, :ln)
        ");

    $stmt->bindValue(':c', $loc['city'], PDO::PARAM_STR);
    $stmt->bindValue(':s', $loc['state'], PDO::PARAM_STR);
    $stmt->bindValue(':y', $loc['country'], PDO::PARAM_STR);
    $stmt->bindValue(':lt', $loc['lat']);
    $stmt->bindValue(':ln', $loc['lon']);
    $stmt->execute();

    return (int) $this->db->lastInsertId();
  }

  public function insertLocation(array $data): int
  {
    $stmt = $this->db->prepare("
            INSERT INTO locations (city, state, country, lat, lon)
            VALUES (:city, :state, :country, :lat, :lon)
            ON DUPLICATE KEY UPDATE id = id
        ");

    $stmt->bindValue(':city', $data['city'], PDO::PARAM_STR);
    $stmt->bindValue(':state', $data['state'], PDO::PARAM_STR);
    $stmt->bindValue(':country', $data['country'], PDO::PARAM_STR);
    $stmt->bindValue(':lat', $data['lat']);
    $stmt->bindValue(':lon', $data['lon']);

    $stmt->execute();

    return (int) $this->db->lastInsertId() ?:
      $this->getLocationId($data);
  }

  public function getLocationId(array $data): int
  {
    $stmt = $this->db->prepare("
            SELECT id FROM locations
            WHERE city = :city AND state = :state AND country = :country
            LIMIT 1
        ");

    $stmt->bindValue(':city', $data['city'], PDO::PARAM_STR);
    $stmt->bindValue(':state', $data['state'], PDO::PARAM_STR);
    $stmt->bindValue(':country', $data['country'], PDO::PARAM_STR);

    $stmt->execute();

    return (int) ($stmt->fetchColumn() ?: 0);
  }
}
