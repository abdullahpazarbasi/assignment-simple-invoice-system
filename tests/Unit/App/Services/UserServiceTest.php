<?php

namespace Tests\Unit\App\Services;

use App\Contracts\UserRepository;
use App\Models\DataTransferModels\UserDetails;
use App\Services\UserService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use RuntimeException;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    public function testGetAgainstUserId()
    {
        // given
        $userRepositoryMock = $this->createMock(UserRepository::class);

        $userRepositoryMock
            ->expects($this->once())
            ->method('getSingleById')
            ->with('1')
            ->willReturn(
                new UserDetails('1', 'Abc', 'abc@gmail.com')
            );

        $userService = new UserService($userRepositoryMock);
        $givenUserId = '1';

        // when
        $userDetails = $userService->get($givenUserId);

        // then
        $this->assertEquals(
            new UserDetails('1', 'Abc', 'abc@gmail.com'),
            $userDetails
        );
    }

    public function testGetAgainstEmptyUserId()
    {
        // given
        $userRepositoryMock = $this->createMock(UserRepository::class);

        $userRepositoryMock
            ->expects($this->once())
            ->method('getSingleById')
            ->with('')
            ->willThrowException(new ModelNotFoundException());

        $userService = new UserService($userRepositoryMock);
        $givenUserId = '';
        $this->expectException(ModelNotFoundException::class);

        // when
        $userService->get($givenUserId);

        // then
    }
}
