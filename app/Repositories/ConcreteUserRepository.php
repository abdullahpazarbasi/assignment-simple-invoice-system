<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\UserRepository;
use App\Models\DataTransferModels\UserDetails;
use App\Models\User;

class ConcreteUserRepository implements UserRepository
{
    /**
     * @return UserDetails[]
     */
    public function findAll(): array
    {
        $users = User::all();
        $userDetailsCollection = [];
        foreach ($users as $user) {
            $userDetailsCollection[] = new UserDetails(
                (string)$user->id,
                $user->name,
                $user->email,
            );
        }

        return $userDetailsCollection;
    }

    /**
     * @param string $id
     * @return UserDetails
     */
    public function getSingleById(string $id): UserDetails
    {
        $user = User::query()->findOrFail($id);

        return new UserDetails(
            (string)$user->id,
            $user->name,
            $user->email,
        );
    }
}
