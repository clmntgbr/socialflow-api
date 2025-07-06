<?php

namespace App\Service;

use App\Entity\AbstractMedia;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Messenger\MessageBusInterface;
use Vich\UploaderBundle\Storage\StorageInterface;

class S3Service
{
    public function __construct(
        private FilesystemOperator $awsStorage,
        private MessageBusInterface $messageBus,
        private StorageInterface $vichStorage,
    ) {
    }

    public function upload(AbstractMedia $media): void
    {
        $localPath = $this->vichStorage->resolvePath($media, 'file');

        if (!$localPath || !file_exists($localPath)) {
            throw new \RuntimeException('Local file not found.');
        }

        $stream = fopen($localPath, 'r');

        if (false === $stream) {
            throw new \RuntimeException('An error occurred during the upload.');
        }

        $this->awsStorage->writeStream($media->getName(), $stream, [
            'visibility' => 'public',
        ]);

        if (is_resource($stream)) {
            fclose($stream);
        }
    }

    public function delete(AbstractMedia $media): void
    {
        $this->awsStorage->delete($media->getName());
    }
}
