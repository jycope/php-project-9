<?php

namespace App;

class UrlChecks
{
  private ?int $id = null;
  private ?string $date = null;
  private ?string $name = null;
  private ?string $status = null;

  public static function fromArray(array $urlData): UrlChecks
  {
    [$id, $date, $name, $status] = $urlData;
    $urlChecks = new UrlChecks();
    $urlChecks->setId($id);
    $urlChecks->setDate($date);
    $urlChecks->setName($name);
    $urlChecks->setStatus($status);
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

  public function setName($name): void
  {
    $this->name = $name;
  }

  public function getName(): ?string
  {
    return $this->name;
  }

  public function setStatus($status): void
  {
    $this->status = $status;
  }

  public function getStatus(): ?string
  {
    return $this->status;
  }

  public function exists(): bool
  {
    return !is_null($this->getId());
  }
}
