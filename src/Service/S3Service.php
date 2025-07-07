<?php

namespace App\Service;

use App\Entity\AbstractMedia;
use App\Exception\UploadMediaException;
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
            throw new UploadMediaException('Upload failed: local file not found for media.');
        }

        $stream = fopen($localPath, 'r');

        if (false === $stream) {
            throw new UploadMediaException('Upload failed: could not open local file for reading.');
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
            throw new UploadMediaException('Download failed: media file name is null.');
        }

        if (!$this->awsStorage->fileExists($media->getName())) {
            throw new UploadMediaException('Download failed: remote file not found on S3.');
        }

        $localPath = $this->projectDir.'/public/media/'.$media->getName();

        $stream = $this->awsStorage->readStream($media->getName());
        if (false === $stream) {
            throw new UploadMediaException('Download failed: could not open remote file stream from S3.');
        }

        $localStream = fopen($localPath, 'w');
        if (false === $localStream) {
            if (is_resource($stream)) {
                fclose($stream);
            }
            throw new UploadMediaException('Download failed: could not create local file for writing.');
        }

        try {
            if (false === stream_copy_to_stream($stream, $localStream)) {
                throw new UploadMediaException('Download failed: could not copy stream from S3 to local file.');
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
