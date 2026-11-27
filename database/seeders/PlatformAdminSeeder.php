<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/** Akun pengelola SaaS (area /platform). Ganti password di produksi lewat env. */
class PlatformAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'internal_developer'],
            [
                'fullname' => 'Pengelola Platform',
                'email' => env('PLATFORM_ADMIN_EMAIL', 'developer@platform.local'),
                'password' => env('PLATFORM_ADMIN_PASSWORD', '040406_050106'),
                'is_platform_admin' => true,
                'organization_id' => null,
                'organization_role' => null,
            ],
        );
    }
}
