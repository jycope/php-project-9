<?php

namespace App;

use Illuminate\Support\Str;

class UrlValidator
{
  public function validate(array $url): array
  {
    $errors = [];
    if (empty($url['name'])) {
      $errors['name'] = "Имя не может быть пустым";
    }

    if (Str::of($url['name'])->length() > 255) {
      $errors['name'] = "Имя не может быть более 255 символов";
    }

    return $errors;
  }
}