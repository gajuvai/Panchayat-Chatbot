<?php

namespace Database\Seeders;

use App\Models\ComplaintCategory;
use App\Models\Role;
use Illuminate\Database\Seeder;

class ComplaintCategorySeeder extends Seeder
{
    public function run(): void
    {
        $securityRole = Role::where('name', 'security_head')->first();
        $adminRole    = Role::where('name', 'admin')->first();

        $categories = [
            ['name' => 'Security',      'icon' => 'shield',    'description' => 'Security concerns and incidents',     'assigned_role_id' => $securityRole?->id],
            ['name' => 'Maintenance',   'icon' => 'wrench',    'description' => 'Building and facility maintenance',   'assigned_role_id' => $adminRole?->id],
            ['name' => 'Noise',         'icon' => 'speaker',   'description' => 'Noise disturbance complaints',        'assigned_role_id' => $adminRole?->id],
            ['name' => 'Parking',       'icon' => 'car',       'description' => 'Parking violations and issues',       'assigned_role_id' => $adminRole?->id],
            ['name' => 'Cleanliness',   'icon' => 'trash',     'description' => 'Cleanliness and hygiene issues',      'assigned_role_id' => $adminRole?->id],
            ['name' => 'Water Supply',  'icon' => 'droplet',   'description' => 'Water supply and plumbing issues',    'assigned_role_id' => $adminRole?->id],
            ['name' => 'Electricity',   'icon' => 'zap',       'description' => 'Electrical and power issues',         'assigned_role_id' => $adminRole?->id],
            ['name' => 'Other',         'icon' => 'help-circle','description' => 'Other general complaints',           'assigned_role_id' => $adminRole?->id],
        ];

        foreach ($categories as $cat) {
            ComplaintCategory::firstOrCreate(['name' => $cat['name']], $cat);
        }
    }
}
