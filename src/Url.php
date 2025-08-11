<?php

namespace App;

use Carbon\Carbon;

class Url
{
  private ?int $id = null;
  private ?string $name = null;
  private ?string $date = null;

  public static function fromArray(array $urlData): Url
  {
    [$name] = $urlData;
    $url = new Url();
    $url->setName($name);
    $url->setDate();
    return $url;
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getName(): ?string
  {
    return $this->name;
  }

  public function getDate(): ?string
  {
    return $this->date;
  }

  public function setId(int $id): void
  {
    $this->id = $id;
  }

  public function setName(string $name): void
  {
    $this->name = $name;
  }

  public function setDate(): void
  {
    $this->date = Carbon::now();
  }

  public function exists(): bool
  {
    return !is_null($this->getId());
  }
}