<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole    = Role::where('name', 'admin')->first();
        $securityRole = Role::where('name', 'security_head')->first();
        $residentRole = Role::where('name', 'resident')->first();

        User::firstOrCreate(['email' => 'admin@panchayat.local'], [
            'name'     => 'Society Admin',
            'password' => Hash::make('password'),
            'role_id'  => $adminRole?->id,
            'phone'    => '9876543210',
        ]);

        User::firstOrCreate(['email' => 'security@panchayat.local'], [
            'name'     => 'Security Head',
            'password' => Hash::make('password'),
            'role_id'  => $securityRole?->id,
            'phone'    => '9876543211',
        ]);

        $residents = [
            ['name' => 'Ramesh Kumar',   'email' => 'ramesh@example.com',   'flat_number' => 'A-101', 'block' => 'A'],
            ['name' => 'Priya Sharma',   'email' => 'priya@example.com',    'flat_number' => 'B-202', 'block' => 'B'],
            ['name' => 'Suresh Patel',   'email' => 'suresh@example.com',   'flat_number' => 'A-305', 'block' => 'A'],
            ['name' => 'Anita Singh',    'email' => 'anita@example.com',    'flat_number' => 'C-104', 'block' => 'C'],
            ['name' => 'Vikram Mehta',   'email' => 'vikram@example.com',   'flat_number' => 'B-401', 'block' => 'B'],
        ];

        foreach ($residents as $resident) {
            User::firstOrCreate(['email' => $resident['email']], array_merge($resident, [
                'password' => Hash::make('password'),
                'role_id'  => $residentRole?->id,
                'phone'    => '98765' . rand(10000, 99999),
            ]));
        }
    }
}
