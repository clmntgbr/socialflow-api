<?php

namespace App\Service;

interface SocialAccountServiceInterface
{
    public function getConnectUrl();
    public function getScopes();
    public function create();
    public function delete();
}