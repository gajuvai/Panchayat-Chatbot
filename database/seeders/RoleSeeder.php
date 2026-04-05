<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'admin',         'display_name' => 'Administrator',  'description' => 'Society administrator with full access'],
            ['name' => 'security_head', 'display_name' => 'Security Head',  'description' => 'Manages security incidents and patrols'],
            ['name' => 'resident',      'display_name' => 'Resident',       'description' => 'Society resident/complainant'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
