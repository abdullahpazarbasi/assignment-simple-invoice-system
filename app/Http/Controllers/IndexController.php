<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index(Request $request) {
        $users = User::all();

        return view('index')->with('users', $users);
    }
}
