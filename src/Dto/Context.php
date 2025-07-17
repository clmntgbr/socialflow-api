<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class Context
{
    public function __construct(
      private array $groups
    ) {}

    public function getGroups(): array
    {
      return $this->groups;
    }
}