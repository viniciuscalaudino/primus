<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{

    public function run(): void
    {
        $users = [
            ['name' => 'Admin',     'email' => 'admin@primus.com',    'role' => 'admin'],
            ['name' => 'Cliente 1', 'email' => 'cliente1@primus.com', 'role' => 'customer'],
            ['name' => 'Cliente 2', 'email' => 'cliente2@primus.com', 'role' => 'customer'],
            ['name' => 'Cliente 3', 'email' => 'cliente3@primus.com', 'role' => 'customer'],
        ];
        
        foreach ($users as $data) {
            $user = new User([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => 'senha123',
            ]);
            $user->role = $data['role'];
            $user->save();
        }
    }
}
