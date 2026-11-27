<?php

namespace Database\Seeders;

use App\Models\Major;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $data = [
            [
                'username' => 'admin_tjkt',
                'fullname' => 'Admin TJKT Management',
                'phone' => '123456789',
                'password' => 'inventory_yatindo',
                'role_id' => 2,
                'major_id' => 1
            ],
            [
                'username' => 'admin_ak',
                'fullname' => 'Admin AK Management',
                'phone' => '123456789',
                'password' => 'inventory_yatindo',
                'role_id' => 4,
                'major_id' => 2
            ],
            [
                'username' => 'admin_to',
                'fullname' => 'Admin TO Management',
                'phone' => '123456789',
                'password' => 'inventory_yatindo',
                'role_id' => 5,
                'major_id' => 4
            ],
            [
                'username' => 'admin_te',
                'fullname' => 'Admin TE Management',
                'phone' => '123456789',
                'password' => 'inventory_yatindo',
                'role_id' => 3,
                'major_id' => 3
            ],
            [
                'username' => 'admin_tu',
                'fullname' => 'Admin TU Management',
                'phone' => '123456789',
                'password' => 'inventory_yatindo',
                'role_id' => 6,
                'major_id' => 5
            ],
            [
                'username' => 'superadmin',
                'fullname' => 'Superadmin Management',
                'phone' => '088976972688',
                'password' => 'super_admin_inventory',
                'role_id' => 1,
            ],
            [
                'username' => 'internal_developer',
                'fullname' => 'Haru Kazumoto',
                'phone' => '0000000000',
                'password' => '040406_050106',
                'role_id' => 7
            ],
            [
                'username' => 'admin_kkpi',
                'fullname' => 'Admin KKPI Management',
                'phone' => '123456789',
                'password' => 'inventory_yatindo',
                'role_id' => 8,
                'major_id' => 6
            ],
        ];

        foreach ($data as $d) {
            User::create($d);
        }
    }
}
