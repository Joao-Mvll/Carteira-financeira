<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegistrationService
{
    /**
     * Cria um usuário já com uma carteira zerada.
     *
     * @param array{name: string, email: string, password: string} $data
     */
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            Wallet::create(['user_id' => $user->id, 'balance' => 0]);

            return $user;
        });
    }
}
