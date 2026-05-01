<?php

namespace App\Dto;

final class UserNameDto
{
    public function __construct(
        public readonly int $id,
        public readonly string $firstName,
        public readonly string $lastName,
    ) {
    }
}
