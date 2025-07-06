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
        private string $projectDir,
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

    public function download(AbstractMedia $media): string
    {
        if (null === $media->getName()) {
            throw new \RuntimeException('File name is null');
        }

        if (!$this->awsStorage->fileExists($media->getName())) {
            throw new \RuntimeException('Remote file not found on S3.');
        }

        $localPath = $this->projectDir.'/public/media/'.$media->getName();

        $stream = $this->awsStorage->readStream($media->getName());
        if (false === $stream) {
            throw new \RuntimeException('An error occurred during the download from S3.');
        }

        $localStream = fopen($localPath, 'w');
        if (false === $localStream) {
            if (is_resource($stream)) {
                fclose($stream);
            }
            throw new \RuntimeException('An error occurred during local file creation.');
        }

        try {
            if (false === stream_copy_to_stream($stream, $localStream)) {
                throw new \RuntimeException('Failed to copy stream from S3 to local file.');
            }
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
            if (is_resource($localStream)) {
                fclose($localStream);
            }
        }

        return $localPath;
    }

    public function delete(AbstractMedia $media): void
    {
        if (null === $media->getName()) {
            return;
        }

        $this->awsStorage->delete($media->getName());
    }
}
