<?php

namespace App;

class UrlChecks
{
  private ?int $id = null;
  private ?string $date = null;
  private ?string $name = null;
  private ?string $status = null;
  private ?string $h1 = null;
  private ?string $title = null;
  private ?string $meta = null;

  public static function fromArray(array $urlData): UrlChecks
  {
    $urlChecks = new UrlChecks();

    [$id, $date, $name, $status, $title, $h1, $meta] = $urlData;
    $urlChecks->setId($id);
    $urlChecks->setName($name);
    $urlChecks->setDate($date);
    $urlChecks->setStatus($status);
    $urlChecks->setH1($h1);
    $urlChecks->setTitle($title);
    $urlChecks->setMeta($meta);

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

  public function getH1(): ?string
  {
    return $this->h1;
  }

  public function setH1(?string $h1): void
  {
    $this->h1 = $h1;
  }

  public function getTitle(): ?string
  {
    return $this->title;
  }

  public function setTitle(?string $title): void
  {
    $this->title = $title;
  }

  public function getMeta(): ?string
  {
    return $this->meta;
  }

  public function setMeta(?string $meta): void
  {
    $this->meta = $meta;
  }
}
