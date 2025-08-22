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

    if (!preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url['name'])) {
      $errors['name'] = "Невалидное название сайта";
    }

    return $errors;
  }
}