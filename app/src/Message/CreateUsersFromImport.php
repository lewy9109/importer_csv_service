<?php

namespace App\Message;

use App\Service\User\UserDto;

class CreateUsersFromImport
{
    public function __construct(private readonly array $user, private readonly string $businessId)
    {
    }

    public function getUsers(): array
    {
        return $this->user;
    }

    public function getReportBusinessId(): string
    {
        return $this->businessId;
    }
}