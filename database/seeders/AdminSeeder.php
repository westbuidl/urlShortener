<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::create([
            'name' => 'West',
            'email' => 'hyacinth@agroease.ng',
            'password' => Hash::make('123456'), // Change to a secure password
        ]);

        Admin::create([
            'name' => 'Joshua',
            'email' => 'joshua.iyoha@agroease.ng',
            'password' => Hash::make('123456'), // Change to a secure password
        ]);

        Admin::create([
            'name' => 'Joseph',
            'email' => 'nyamah.joseph@agroease.ng',
            'password' => Hash::make('123456'), // Change to a secure password
        ]);

        Admin::create([
            'name' => 'Star',
            'email' => 'kpakpando@agroease.ng',
            'password' => Hash::make('123456'), // Change to a secure password
        ]);

    }
}
//php artisan db:seed --class=AdminSeeder