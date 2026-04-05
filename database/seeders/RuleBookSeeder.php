<?php

namespace Database\Seeders;

use App\Models\RuleBookSection;
use App\Models\User;
use Illuminate\Database\Seeder;

class RuleBookSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::whereHas('role', fn($q) => $q->where('name', 'admin'))->first();
        if (!$admin) return;

        $sections = [
            [
                'title'         => 'General Community Rules',
                'section_order' => 1,
                'content'       => "<h3>1. Respect and Conduct</h3><p>All residents are expected to maintain a respectful and courteous demeanor towards neighbours, staff, and visitors at all times.</p><h3>2. Common Area Usage</h3><p>Common areas including the garden, parking lot, and lobby must be kept clean. No littering is permitted.</p><h3>3. Noise Policy</h3><p>Residents must maintain noise levels within acceptable limits, especially between 10 PM and 7 AM.</p>",
            ],
            [
                'title'         => 'Maintenance & Repairs',
                'section_order' => 2,
                'content'       => "<h3>Maintenance Hours</h3><p>Routine maintenance requests are handled Monday to Saturday, 8 AM to 6 PM. For emergency repairs (water leaks, electrical hazards), 24/7 support is available.</p><h3>How to Request</h3><p>Submit a complaint through the Panchayat app under the 'Maintenance' category. Include photos if possible.</p>",
            ],
            [
                'title'         => 'Security Guidelines',
                'section_order' => 3,
                'content'       => "<h3>Visitor Entry</h3><p>All visitors must be registered at the security desk. Residents must inform the security team about expected guests.</p><h3>Emergency Procedures</h3><p>In case of emergency, contact the security head immediately using the app's SOS feature or call 100 (Police), 101 (Fire), 108 (Ambulance).</p>",
            ],
        ];

        foreach ($sections as $section) {
            RuleBookSection::firstOrCreate(
                ['title' => $section['title']],
                array_merge($section, ['user_id' => $admin->id, 'is_published' => true])
            );
        }
    }
}
