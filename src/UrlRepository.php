<?php

namespace App;

use App\Url;

class UrlRepository
{
  private \PDO $conn;

  public function __construct(\PDO $conn)
  {
    $this->conn = $conn;
  }

  public function getEntities(): array
  {
    $urls = [];
    $sql = "SELECT * FROM urls";
    $stmt = $this->conn->query($sql);

    while ($row = $stmt->fetch()) {
      $url = Url::fromArray([$row['name'], $row['created_at']]);
      $url->setId($row['id']);
      $urls[] = $url;
    }

    return $urls;
  }

  public function find(int $id): ?Url
  {
    $sql = "SELECT * FROM urls WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([$id]);
    if ($row = $stmt->fetch()) {
      $car = Url::fromArray([$row['name']]);
      $car->setId($row['id']);
      return $car;
    }

    return null;
  }

  public function save(Url $url): void
  {
    if ($url->exists()) {
      $this->update($url);
    } else {
      $this->create($url);
    }
  }

  private function update(Url $url): void
  {
    $sql = "UPDATE cars SET name = :name, date = :date WHERE id = :id";
    $stmt = $this->conn->prepare($sql);
    $id = $url->getId();
    $name = $url->getName();
    $date = $url->getDate();
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':date', $date);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
  }

  private function create(Url $url): void
  {
    $sql = "INSERT INTO urls (name, created_at) VALUES (:name, :created_at)";
    $stmt = $this->conn->prepare($sql);
    $name = $url->getName();
    $created_at = $url->getDate();
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':created_at', $created_at);
    $stmt->execute();
    $id = (int) $this->conn->lastInsertId();
    $url->setId($id);
  }
}