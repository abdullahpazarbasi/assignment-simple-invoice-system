<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\UserServer;
use App\Models\ViewModels\UserDetails as UserDetailsView;
use Illuminate\Http\Request;

class UserWebAppFrontController extends Controller
{
    public function list(Request $request, UserServer $userService)
    {
        $userCollection = $userService->list();
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
