<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = [
            "first_name" => "Super",
            "last_name" => "Admin",
            "email" => env('ADMIN_EMAIL'),
            "password" => Hash::make(env('ADMIN_PASSWORD')),
            "is_email_verified" => true,
            "type" => 'admin'
        ];

        if (!User::where('email', $admin['email'])->first()) {
            User::create($admin);
        }
    }
}
