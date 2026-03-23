<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return Inertia::render('Auth/Login');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            return redirect()->intended(
                $user->isAdmin()
                    ? route('admin.dashboard')
                    : route('products.index')
            );
        }

        return back()->withErrors([
            'email' => __('auth.failed'),
        ]);
    }


    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
        ]);

        Auth::login($user);

        return redirect()->route('products.index')
            ->with('success', __('auth.register_success')); // <-- локализация
    }

    public function showRegisterForm()
    {
        return Inertia::render('Auth/Register');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('products.index')
            ->with('success', __('auth.logged_out')); // <-- локализация
    }
}
