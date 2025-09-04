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
      url_checks.title,
      url_checks.description,
      urls.name AS name,
      url_checks.created_at
    FROM url_checks
    INNER JOIN urls
        ON url_checks.url_id = urls.id
    WHERE urls.id = :id";

    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    while ($row = $stmt->fetch()) {
      $urlCheck = UrlChecks::fromArray([
        $row['id'],
        $row['created_at'],
        $row['name'],
        $row['status_code'],
        $row['title'],
        $row['h1'],
        $row['description']
      ]);
      $urlChecks[] = $urlCheck;
    }

    return $urlChecks;
  }


  public function getLastCheckForUrl(int $urlId): ?array
  {
    $sql = "
    SELECT 
      url_checks.status_code,
      url_checks.created_at
    FROM url_checks
    WHERE url_checks.url_id = :url_id
    ORDER BY url_checks.created_at DESC
    LIMIT 1";
    
    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':url_id', $urlId);
    $stmt->execute();
    
    $row = $stmt->fetch();
    return $row ? $row : null;
  }

  public function getUrlsWithLastChecks(array $urls): array
  {
    $urlsWithChecks = [];
    foreach ($urls as $url) {
      $lastCheck = $this->getLastCheckForUrl($url->getId());
      $urlsWithChecks[] = [
        'url' => $url,
        'lastCheck' => $lastCheck
      ];
    }
    
    return $urlsWithChecks;
  }

  public function save(UrlChecks $urlChecks): void
  {
    $this->create($urlChecks);
  }

  private function create(UrlChecks $urlChecks): void
  {
    $sql = "
    INSERT INTO 
      url_checks (url_id, h1, title, description, created_at, status_code)
     VALUES (:url_id, :h1, :title, :description, :created_at, :status_code)";
    $stmt = $this->conn->prepare($sql);
    $created_at = $urlChecks->getDate();
    $urlId = $urlChecks->getId();
    $h1 = $urlChecks->getH1();
    $title = $urlChecks->getTitle();
    $description = $urlChecks->getMeta();
    $status = $urlChecks->getStatus();

    $stmt->bindParam(':url_id', $urlId);
    $stmt->bindParam(':created_at', $created_at);
    $stmt->bindParam(':h1', $h1);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':status_code', $status);
    $stmt->execute();
  }
}