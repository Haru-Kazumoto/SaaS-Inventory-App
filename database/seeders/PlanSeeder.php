<?php

namespace Database\Seeders;

use App\Domain\Platform\Models\Plan;
use Illuminate\Database\Seeder;

/** Paket langganan default (bisa diubah di Platform → Paket). */
class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            ['code' => 'free', 'name' => 'Gratis', 'description' => 'Untuk organisasi kecil yang baru mulai.',
                'price_monthly' => 0, 'price_yearly' => 0, 'max_units' => 1, 'max_users' => 3, 'max_products' => 100, 'sort_order' => 1],
            ['code' => 'basic', 'name' => 'Basic', 'description' => 'Untuk organisasi dengan beberapa unit.',
                'price_monthly' => 99000, 'price_yearly' => 990000, 'max_units' => 3, 'max_users' => 10, 'max_products' => 1000, 'sort_order' => 2],
            ['code' => 'school', 'name' => 'Sekolah', 'description' => 'Untuk sekolah/kampus dengan banyak jurusan.',
                'price_monthly' => 249000, 'price_yearly' => 2490000, 'max_units' => null, 'max_users' => 50, 'max_products' => null, 'sort_order' => 3],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['code' => $plan['code']], [...$plan, 'is_active' => true]);
        }
    }
}
