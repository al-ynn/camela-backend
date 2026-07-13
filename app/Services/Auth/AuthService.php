<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function login(array $credentials): array
    {
        if (!Auth::attempt($credentials)) {

            throw new \Exception("Invalid email or password.");

        }

        $user = User::where('email', $credentials['email'])->first();

        $token = $user->createToken('camela-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function register(array $data): array
    {
        $user = User::create([
            'role_id' => 2, // CUSTOMER
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $token = $user->createToken('camela-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}