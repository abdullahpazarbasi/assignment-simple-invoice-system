<?php

namespace App\Contracts;

use App\Models\DataTransferModels\UserDetails;

interface UserServer
{
    public function getById(string $userId): UserDetails;

    /**
     * @return UserDetails[]
     */
    public function list(): array;
}
