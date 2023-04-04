<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\UserServer;
use App\Models\ViewModels\UserDetails as UserDetailsView;
use Illuminate\Http\Request;

class UserWebAppFrontController extends Controller
{
    protected UserServer $userService;

    /**
     * @param UserServer $userService
     */
    public function __construct(UserServer $userService)
    {
        $this->userService = $userService;
    }

    public function list(Request $request)
    {
        $userCollection = $this->userService->list();
        $userCollectionView = [];
        foreach ($userCollection as $user) {
            $userCollectionView[] = new UserDetailsView(
                $user->getId(),
                $user->getName(),
                $user->getEmail(),
            );
        }

        return view('index')->with('users', $userCollectionView);
    }
}
