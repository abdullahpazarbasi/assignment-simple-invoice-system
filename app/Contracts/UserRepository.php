<?php

namespace App\Contracts;

use App\Models\DataTransferModels\UserDetails;

interface UserRepository
{
    /**
     * @return UserDetails[]
     */
    public function findAll(): array;

    /**
     * @param string $id
     * @return UserDetails
     */
    public function getSingleById(string $id): UserDetails;
}
