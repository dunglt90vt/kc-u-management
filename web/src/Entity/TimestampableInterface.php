<?php

declare(strict_types=1);

namespace App\Entity;

interface TimestampableInterface
{
    public function setCreatedAt(): void;

    public function getCreatedAt(): \DateTimeInterface;

    public function setUpdatedAt(): void;

    public function getUpdatedAt(): \DateTimeInterface;
}
