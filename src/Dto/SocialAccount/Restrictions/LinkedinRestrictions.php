<?php

declare(strict_types=1);

namespace App\Dto\SocialAccount\Restrictions;

use App\Entity\SocialAccount\LinkedinSocialAccount;

final class LinkedinRestrictions implements \JsonSerializable, RestrictionInterface
{
    private int $textMaxCharacters = 3000;

    private int $videoMaxFile = 1;
    private int $videoMaxDurationSeconds = 600;
    private int $videoMaxDurationSecondsVerified = 900;
    private int $videoMaxFileSizeBytes = 524288000;
    private int $videoMaxFileSizeBytesVerified = 1073741824;
    private string $videoMaxFileSizeFormatted = '500 MB';
    private string $videoMaxFileSizeFormattedVerified = '1 GB';

    private int $imageMaxFile = 9;
    private int $imageMaxFileSizeBytes = 5242880;
    private int $imageMaxFileSizeBytesVerified = 10485760;
    private string $imageMaxFileSizeFormatted = '5 MB';
    private string $imageMaxFileSizeFormattedVerified = '10 MB';

    private int $gifMaxFile = 1;
    private int $gifMaxFileSizeBytes = 8388608;
    private int $gifMaxFileSizeBytesVerified = 16777216;
    private string $gifMaxFileSizeFormatted = '8 MB';
    private string $gifMaxFileSizeFormattedVerified = '16 MB';

    public function __construct(
        private LinkedinSocialAccount $linkedinSocialAccount,
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
                'max_duration_seconds' => $this->linkedinSocialAccount->isVerified() ? $this->videoMaxDurationSecondsVerified : $this->videoMaxDurationSeconds,
                'max_file_size_bytes' => $this->linkedinSocialAccount->isVerified() ? $this->videoMaxFileSizeBytesVerified : $this->videoMaxFileSizeBytes,
                'max_file_size_formatted' => $this->linkedinSocialAccount->isVerified() ? $this->videoMaxFileSizeFormattedVerified : $this->videoMaxFileSizeFormatted,
            ],
            'image' => [
                'max_file' => $this->imageMaxFile,
                'max_file_size_bytes' => $this->linkedinSocialAccount->isVerified() ? $this->imageMaxFileSizeBytesVerified : $this->imageMaxFileSizeBytes,
                'max_file_size_formatted' => $this->linkedinSocialAccount->isVerified() ? $this->imageMaxFileSizeFormattedVerified : $this->imageMaxFileSizeBytes,
            ],
            'gif' => [
                'max_file' => $this->gifMaxFile,
                'max_file_size_bytes' => $this->linkedinSocialAccount->isVerified() ? $this->gifMaxFileSizeBytesVerified : $this->gifMaxFileSizeBytes,
                'max_file_size_formatted' => $this->linkedinSocialAccount->isVerified() ? $this->gifMaxFileSizeFormattedVerified : $this->gifMaxFileSizeFormatted,
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
        return $this->linkedinSocialAccount->isVerified() ? $this->videoMaxDurationSecondsVerified : $this->videoMaxDurationSeconds;
    }

    public function getVideoMaxFileSizeBytes(): int
    {
        return $this->linkedinSocialAccount->isVerified() ? $this->videoMaxFileSizeBytesVerified : $this->videoMaxFileSizeBytes;
    }

    public function getVideoMaxFileSizeFormatted(): string
    {
        return $this->linkedinSocialAccount->isVerified() ? $this->videoMaxFileSizeFormattedVerified : $this->videoMaxFileSizeFormatted;
    }

    public function getImageMaxFile(): int
    {
        return $this->imageMaxFile;
    }

    public function getImageMaxFileSizeBytes(): int
    {
        return $this->linkedinSocialAccount->isVerified() ? $this->imageMaxFileSizeBytesVerified : $this->imageMaxFileSizeBytes;
    }

    public function getImageMaxFileSizeFormatted(): string
    {
        return $this->linkedinSocialAccount->isVerified() ? $this->imageMaxFileSizeFormattedVerified : $this->imageMaxFileSizeFormatted;
    }

    public function getGifMaxFile(): int
    {
        return $this->gifMaxFile;
    }

    public function getGifMaxFileSizeBytes(): int
    {
        return $this->linkedinSocialAccount->isVerified() ? $this->gifMaxFileSizeBytesVerified : $this->gifMaxFileSizeBytes;
    }

    public function getGifMaxFileSizeFormatted(): string
    {
        return $this->linkedinSocialAccount->isVerified() ? $this->gifMaxFileSizeFormattedVerified : $this->gifMaxFileSizeFormatted;
    }
}
