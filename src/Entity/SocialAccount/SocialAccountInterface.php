<?php

namespace App\Entity\SocialAccount;

interface SocialAccountInterface
{
    public function getType(): string;
    public function getRestrictions(): array;
}
