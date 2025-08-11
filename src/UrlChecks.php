<?php

namespace App;

class UrlChecks
{
  private ?int $id = null;
  private ?string $date = null;

  public static function fromArray(array $urlData): UrlChecks
  {
    [$id, $date] = $urlData;
    $urlChecks = new UrlChecks();
    $urlChecks->setId($id);
    $urlChecks->setDate($date);
    return $urlChecks;
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getDate(): ?string
  {
    return $this->date;
  }

  public function setId(int $id): void
  {
    $this->id = $id;
  }

  public function setDate($date): void
  {
    $this->date = $date;
  }

  public function exists(): bool
  {
    return !is_null($this->getId());
  }
}
