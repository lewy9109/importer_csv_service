<?php

namespace App\Service\User;

class UserFactory
{
    /**
     * @param array<string, string> $data
     */
    public static function fromArray(array $data): UserDto
    {
        return new UserDto(
            id: isset($data['id']) ? (int)$data['id'] : null,
            fullName: isset($data['fullName']) ? (string)$data['fullName'] : null,
            email: isset($data['email']) ? (string)$data['email'] : null,
            city: isset($data['city']) ? (string)$data['city'] : null,
            reportId: isset($data['reportId']) ? (string)$data['reportId'] : null,
        );
    }
}