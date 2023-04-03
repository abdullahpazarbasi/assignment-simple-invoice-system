<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\UserServer;
use App\Models\DataTransferModels\UserDetails;
use App\Models\User;

class UserService implements UserServer
{
    public function getById(string $userId): UserDetails
    {
        $user = User::query()->findOrFail($userId);

        return new UserDetails(
            (string)$user->id,
            $user->name,
            $user->email,
        );
    }

    /**
     * @return UserDetails[]
     */
    public function list(): array
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
}
