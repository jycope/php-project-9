<?php

namespace App;

class UrlValidator
{
  public function validate(array $url): array
  {
    $errors = [];
    if (empty($url['name'])) {
      $errors['name'] = "Имя не может быть пустым";
    }

    // if (empty($url['date'])) {
    //   $errors['date'] = "Дата не может быть пустым";
    // }

    return $errors;
  }
}
