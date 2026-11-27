<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RequestItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [];

        for ($i = 1; $i <= 20; $i++) {
            $data[] = [
                'brand_name'      => Str::upper(fake()->words(2, true)), // contoh: "SONY HEADSET"
                'quantity'        => fake()->numberBetween(1, 40),
                'link_shop'       => fake()->url(),
                'reason_request'  => fake()->sentence(),
                'image_item'      => null, // tidak diisi
                'requestor_id'    => 4, // sesuaikan dengan ID user yang ada di database
                'request_status'  => 'PENDING',
                'major_id'        => 5, // sesuaikan dengan ID major yang ada
                'created_at'      => now(),
                'updated_at'      => now(),
            ];
        }

        DB::table('request_item')->insert($data);
    }
}
