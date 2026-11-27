<?php

namespace Database\Seeders;

use App\Models\Lookup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LookupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $datas = [
            ['name' => 'HABIS PAKAI','code' => 'ITEM_TYPE','value' => 'CONSUMABLE'],
            ['name' => 'TIDAK HABIS PAKAI','code' => 'ITEM_TYPE','value' => 'DURABLE']
        ];

        foreach($datas as $data) {
            Lookup::create($data);
        }
    }
}
