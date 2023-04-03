<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserWebAppController extends Controller
{
    public function list(Request $request)
    {
        $users = User::all();

        return view('index')->with('users', $users);
    }
}
