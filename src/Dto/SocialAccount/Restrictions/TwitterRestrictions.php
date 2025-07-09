<?php

declare(strict_types=1);

namespace App\Dto\SocialAccount\Restrictions;

use App\Entity\SocialAccount\TwitterSocialAccount;

final class TwitterRestrictions implements \JsonSerializable, RestrictionInterface
{
    private int $textMaxCharacters = 280;
    private int $textMaxCharactersVerified = 25000;

    private int $videoMaxFile = 1;
    private int $videoMaxDurationSeconds = 140;
    private int $videoMaxDurationSecondsVerified = 300;
    private int $videoMaxFileSizeBytes = 536870912;
    private int $videoMaxFileSizeBytesVerified = 1073741824;
    private string $videoMaxFileSizeFormatted = '512 MB';
    private string $videoMaxFileSizeFormattedVerified = '1 GB';

    private int $imageMaxFile = 4;
    private int $imageMaxFileSizeBytes = 5242880;
    private string $imageMaxFileSizeFormatted = '5 MB';

    private int $gifMaxFile = 1;
    private int $gifMaxFileSizeBytes = 15242880;
    private string $gifMaxFileSizeFormatted = '15 MB';

    public function __construct(
        private TwitterSocialAccount $twitterSocialAccount
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'text' => [
                'max_characters' => $this->twitterSocialAccount->isVerified() ? $this->textMaxCharactersVerified : $this->textMaxCharacters,
            ],
            'video' => [
                'max_file' => $this->videoMaxFile,
                'max_duration_seconds' => $this->twitterSocialAccount->isVerified() ? $this->videoMaxDurationSecondsVerified : $this->videoMaxDurationSeconds,
                'max_file_size_bytes' => $this->twitterSocialAccount->isVerified() ? $this->videoMaxFileSizeBytesVerified : $this->videoMaxFileSizeBytes,
                'max_file_size_formatted' => $this->twitterSocialAccount->isVerified() ? $this->videoMaxFileSizeFormattedVerified : $this->videoMaxFileSizeFormatted,
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
        return $this->twitterSocialAccount->isVerified() ? $this->textMaxCharactersVerified : $this->textMaxCharacters;
    }

    public function getVideoMaxFile(): int
    {
        return $this->videoMaxFile;
    }

    public function getVideoMaxDurationSeconds(): int
    {
        return $this->twitterSocialAccount->isVerified() ? $this->videoMaxDurationSecondsVerified : $this->videoMaxDurationSeconds;
    }

    public function getVideoMaxFileSizeBytes(): int
    {
        return $this->twitterSocialAccount->isVerified() ? $this->videoMaxFileSizeBytesVerified : $this->videoMaxFileSizeBytes;
    }

    public function getVideoMaxFileSizeFormatted(): string
    {
        return $this->twitterSocialAccount->isVerified() ? $this->videoMaxFileSizeFormattedVerified : $this->videoMaxFileSizeFormatted;
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
