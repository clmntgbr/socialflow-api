<?php

namespace App\Service;

use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\MessageBusInterface;

class S3Service
{
    public function __construct(
        private FilesystemOperator $awsStorage,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function upload(UploadedFile $file)
    {
    }
}
