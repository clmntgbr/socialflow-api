<?php

declare(strict_types=1);

namespace App\Dto\SocialAccount\Restrictions;

final class TwitterRestrictionNotVerified implements \JsonSerializable, RestrictionInterface
{
    private int $textMaxCharacters = 280;
    private int $videoMaxFile = 1;
    private int $videoMaxDurationSeconds = 140;
    private int $videoMaxFileSizeBytes = 536870912;
    private string $videoMaxFileSizeFormatted = '512 MB';
    private int $imageMaxFile = 4;
    private int $imageMaxFileSizeBytes = 5242880;
    private string $imageMaxFileSizeFormatted = '5 MB';
    private int $gifMaxFile = 1;
    private int $gifMaxFileSizeBytes = 15242880;
    private string $gifMaxFileSizeFormatted = '15 MB';

    public function jsonSerialize(): array
    {
        return [
            'text' => [
                'max_characters' => $this->textMaxCharacters,
            ],
            'video' => [
                'max_file' => $this->videoMaxFile,
                'max_duration_seconds' => $this->videoMaxDurationSeconds,
                'max_file_size_bytes' => $this->videoMaxFileSizeBytes,
                'max_file_size_formatted' => $this->videoMaxFileSizeFormatted,
            ],
            'image' => [
                'max_file' => $this->imageMaxFile,
                'max_file_size_bytes' => $this->imageMaxFileSizeBytes,
                'max_file_size_formatted' => $this->imageMaxFileSizeFormatted,
            ],
            'gif' => [
                'max_file' => $this->gifMaxFile,
                'max_file_size_bytes' => $this->gifMaxFileSizeBytes,
                'max_file_size_formatted' => $this->gifMaxFileSizeFormatted,
            ],
        ];
    }

    public function getTextMaxCharacters(): int
    {
        return $this->textMaxCharacters;
    }

    public function getVideoMaxFile(): int
    {
        return $this->videoMaxFile;
    }

    public function getVideoMaxDurationSeconds(): int
    {
        return $this->videoMaxDurationSeconds;
    }

    public function getVideoMaxFileSizeBytes(): int
    {
        return $this->videoMaxFileSizeBytes;
    }

    public function getVideoMaxFileSizeFormatted(): string
    {
        return $this->videoMaxFileSizeFormatted;
    }

    public function getImageMaxFile(): int
    {
        return $this->imageMaxFile;
    }

    public function getImageMaxFileSizeBytes(): int
    {
        return $this->imageMaxFileSizeBytes;
    }

    public function getImageMaxFileSizeFormatted(): string
    {
        return $this->imageMaxFileSizeFormatted;
    }

    public function getGifMaxFile(): int
    {
        return $this->gifMaxFile;
    }

    public function getGifMaxFileSizeBytes(): int
    {
        return $this->gifMaxFileSizeBytes;
    }

    public function getGifMaxFileSizeFormatted(): string
    {
        return $this->gifMaxFileSizeFormatted;
    }
}
