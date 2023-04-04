<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\UserRepository;
use App\Contracts\UserServer;
use App\Models\DataTransferModels\UserDetails;

class UserService implements UserServer
{
    protected UserRepository $userRepository;

    /**
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function get(string $userId): UserDetails
    {
        return $this->userRepository->getSingleById($userId);
    }

    /**
     * @return UserDetails[]
     */
    public function list(): array
    {
        return $this->userRepository->findAll();
    }
}
