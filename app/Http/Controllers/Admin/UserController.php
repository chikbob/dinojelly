<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;

class UserController extends     Controller
{
    public function index()
    {
        $users = User::paginate(15);
        return Inertia::render('admin/Users/Index', ['users' => $users]);
    }

    public function show(User $user)
    {
        return Inertia::render('admin/Users/Show', ['user' => $user]);
    }
}
