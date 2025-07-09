<?php

declare(strict_types=1);

namespace App\Dto\SocialAccount\Restrictions;

interface RestrictionInterface
{
    public function getTextMaxCharacters(): int;

    public function getVideoMaxFile(): int;

    public function getVideoMaxDurationSeconds(): int;

    public function getVideoMaxFileSizeBytes(): int;

    public function getVideoMaxFileSizeFormatted(): string;

    public function getImageMaxFile(): int;

    public function getImageMaxFileSizeBytes(): int;

    public function getImageMaxFileSizeFormatted(): string;

    public function getGifMaxFile(): int;

    public function getGifMaxFileSizeBytes(): int;

    public function getGifMaxFileSizeFormatted(): string;
}
