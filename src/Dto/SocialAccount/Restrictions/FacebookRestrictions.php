<?php

declare(strict_types=1);

namespace App\Dto\SocialAccount\Restrictions;

use App\Entity\SocialAccount\FacebookSocialAccount;

final class FacebookRestrictions implements \JsonSerializable, RestrictionInterface
{
    private int $textMaxCharacters = 63206;

    private int $videoMaxFile = 1;
    private int $videoMaxDurationSeconds = 7200;
    private int $videoMaxDurationSecondsVerified = 14400;
    private int $videoMaxFileSizeBytes = 10737418240;
    private int $videoMaxFileSizeBytesVerified = 21474836480;
    private string $videoMaxFileSizeFormatted = '10 GB';
    private string $videoMaxFileSizeFormattedVerified = '20 GB';

    private int $imageMaxFile = 10;
    private int $imageMaxFileSizeBytes = 26214400;
    private int $imageMaxFileSizeBytesVerified = 52428800;
    private string $imageMaxFileSizeFormatted = '25 MB';
    private string $imageMaxFileSizeFormattedVerified = '50 MB';

    private int $gifMaxFile = 1;
    private int $gifMaxFileSizeBytes = 26214400;
    private int $gifMaxFileSizeBytesVerified = 52428800;
    private string $gifMaxFileSizeFormatted = '25 MB';
    private string $gifMaxFileSizeFormattedVerified = '50 MB';

    public function __construct(
        private FacebookSocialAccount $facebookSocialAccount,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'text' => [
                'max_characters' => $this->textMaxCharacters,
            ],
            'video' => [
                'max_file' => $this->videoMaxFile,
                'max_duration_seconds' => $this->facebookSocialAccount->isVerified() ? $this->videoMaxDurationSecondsVerified : $this->videoMaxDurationSeconds,
                'max_file_size_bytes' => $this->facebookSocialAccount->isVerified() ? $this->videoMaxFileSizeBytesVerified : $this->videoMaxFileSizeBytes,
                'max_file_size_formatted' => $this->facebookSocialAccount->isVerified() ? $this->videoMaxFileSizeFormattedVerified : $this->videoMaxFileSizeFormatted,
            ],
            'image' => [
                'max_file' => $this->imageMaxFile,
                'max_file_size_bytes' => $this->facebookSocialAccount->isVerified() ? $this->imageMaxFileSizeBytesVerified : $this->imageMaxFileSizeBytes,
                'max_file_size_formatted' => $this->facebookSocialAccount->isVerified() ? $this->imageMaxFileSizeFormattedVerified : $this->imageMaxFileSizeFormatted,
            ],
            'gif' => [
                'max_file' => $this->gifMaxFile,
                'max_file_size_bytes' => $this->facebookSocialAccount->isVerified() ? $this->gifMaxFileSizeBytesVerified : $this->gifMaxFileSizeBytes,
                'max_file_size_formatted' => $this->facebookSocialAccount->isVerified() ? $this->gifMaxFileSizeFormattedVerified : $this->gifMaxFileSizeFormatted,
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
        return $this->facebookSocialAccount->isVerified() ? $this->videoMaxDurationSecondsVerified : $this->videoMaxDurationSeconds;
    }

    public function getVideoMaxFileSizeBytes(): int
    {
        return $this->facebookSocialAccount->isVerified() ? $this->videoMaxFileSizeBytesVerified : $this->videoMaxFileSizeBytes;
    }

    public function getVideoMaxFileSizeFormatted(): string
    {
        return $this->facebookSocialAccount->isVerified() ? $this->videoMaxFileSizeFormattedVerified : $this->videoMaxFileSizeFormatted;
    }

    public function getImageMaxFile(): int
    {
        return $this->imageMaxFile;
    }

    public function getImageMaxFileSizeBytes(): int
    {
        return $this->facebookSocialAccount->isVerified() ? $this->imageMaxFileSizeBytesVerified : $this->imageMaxFileSizeBytes;
    }

    public function getImageMaxFileSizeFormatted(): string
    {
        return $this->facebookSocialAccount->isVerified() ? $this->imageMaxFileSizeFormattedVerified : $this->imageMaxFileSizeFormatted;
    }

    public function getGifMaxFile(): int
    {
        return $this->gifMaxFile;
    }

    public function getGifMaxFileSizeBytes(): int
    {
        return $this->facebookSocialAccount->isVerified() ? $this->gifMaxFileSizeBytesVerified : $this->gifMaxFileSizeBytes;
    }

    public function getGifMaxFileSizeFormatted(): string
    {
        return $this->facebookSocialAccount->isVerified() ? $this->gifMaxFileSizeFormattedVerified : $this->gifMaxFileSizeFormatted;
    }
}
