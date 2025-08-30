<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function attemptLogin(string $email, string $password): bool
    {
        return Auth::attempt([
            'email' => $email,
            'password' => $password,
            'ativo' => true
        ]);
    }

    public function logout(): void
    {
        Auth::logout();
    }
} 