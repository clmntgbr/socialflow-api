<?php

namespace App\Service\Publish;

interface PublishServiceInterface
{
    public function post();

    public function delete();

    public function uploadMedia();
}
