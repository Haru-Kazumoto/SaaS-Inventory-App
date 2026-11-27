<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $datas = [
            ['name' => 'SUPERADMIN','code' => 'SUPER'],
            ['name' => 'ADMIN_TJKT','code' => 'ADMIN'],
            ['name' => 'ADMIN_TE','code' => 'ADMIN'],
            ['name' => 'ADMIN_AK','code' => 'ADMIN'],
            ['name' => 'ADMIN_TO','code' => 'ADMIN'],
            ['name' => 'ADMIN_TU','code' => 'ADMIN'],
            ['name' => 'DEVELOPER','code' => 'DEVELOPER'],
            ['name' => 'ADMIN_KKPI','code' => 'ADMIN'],
        ];

        foreach($datas as $data)
        {
            Role::create($data);
        }
    }
}
