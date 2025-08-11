<?php

namespace App;

use App\UrlChecks;

class UrlChecksRepository
{
  private \PDO $conn;

  public function __construct(\PDO $conn)
  {
    $this->conn = $conn;
  }

  public function getChecks(int $id): array
  {
    $urlChecks = [];

    $sql = "
    SELECT 
      url_checks.id AS id,
      url_checks.status_code,
      url_checks.h1,
      url_checks.description,
      url_checks.created_at
    FROM url_checks
    INNER JOIN urls
        ON url_checks.url_id = urls.id
    WHERE urls.id = :id";

    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    while ($row = $stmt->fetch()) {
      $urlCheck = UrlChecks::fromArray([$row['id'], $row['created_at']]);
      $urlChecks[] = $urlCheck;
    }

    return $urlChecks;
  }

  public function save(UrlChecks $urlChecks): void
  {
    $this->create($urlChecks);
  }

  private function create(UrlChecks $urlChecks): void
  {
    $sql = "INSERT INTO url_checks (url_id, created_at) VALUES (:url_id, :created_at)";
    $stmt = $this->conn->prepare($sql);
    $created_at = $urlChecks->getDate();
    $urlId = $urlChecks->getId();
    $stmt->bindParam(':url_id', $urlId);
    $stmt->bindParam(':created_at', $created_at);
    $stmt->execute();
  }
}
