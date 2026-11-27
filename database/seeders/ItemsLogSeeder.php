<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ItemsLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua ID dari tabel relasi
        $itemIds = DB::table('items')->pluck('id')->toArray();
        $userIds = DB::table('users')->pluck('id')->toArray();
        $partialItemIds = DB::table('partial_item')->pluck('id')->toArray();

        // Jika tabel kosong, jangan seed untuk menghindari error foreign key
        if (empty($itemIds)) {
            $this->command->warn('Tabel items kosong, seeder ItemsLogSeeder dilewati.');
            return;
        }

        // Tentukan berapa banyak data dummy yang ingin dibuat
        $total = 50;

        for ($i = 0; $i < $total; $i++) {
            DB::table('items_log')->insert([
                'process_date' => Carbon::now()->subDays(rand(0, 30))->subMinutes(rand(0, 1440)),
                'action' => Arr::random(['IN', 'OUT']),
                'item_id' => Arr::random($itemIds),
                'quantity' => rand(1, 100),
                'created_by' => !empty($userIds) ? Arr::random($userIds) : null,
                'partial_item_id' => !empty($partialItemIds) ? Arr::random($partialItemIds) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info("Seeder ItemsLogSeeder berhasil menambahkan {$total} data.");
    }
}
