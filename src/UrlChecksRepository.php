<?php

namespace App;

use App\UrlChecks;
use GuzzleHttp\Psr7\Response;

class UrlChecksRepository
{
  private \PDO $conn;

  public function __construct(\PDO $conn)
  {
    $this->conn = $conn;
  }

  public function getCheckedSite(string $url): Response
  {
    $client = new \GuzzleHttp\Client();
    $response = '';

    try {
      $response = $client->request('GET', $url);
    } catch (\GuzzleHttp\Exception\ConnectException $e) {
    }

    return $response;
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
      $urlCheck = UrlChecks::fromArray([$row['id'], $row['created_at'], $row['h1'], $row['status_code']]);
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
    $sql = "
    INSERT INTO 
      url_checks (url_id, h1, created_at, status_code)
     VALUES (:url_id, :h1, :created_at, :status_code)";
    $stmt = $this->conn->prepare($sql);
    $created_at = $urlChecks->getDate();
    $urlId = $urlChecks->getId();
    $name = $urlChecks->getName();
    $status = $urlChecks->getStatus();

    $stmt->bindParam(':url_id', $urlId);
    $stmt->bindParam(':created_at', $created_at);
    $stmt->bindParam(':h1', $name);
    $stmt->bindParam(':status_code', $status);
    $stmt->execute();
  }
}
