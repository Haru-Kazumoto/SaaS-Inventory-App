<?php

namespace Database\Seeders;

use App\Models\Major;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MajorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $datas = [
            ['name' => 'TJKT','code' => 'TJKT'],
            ['name' => 'AK','code' => 'AK'],
            ['name' => 'TE','code' => 'TE'],
            ['name' => 'TO','code' => 'TO'],
            ['name' => 'TU','code' => 'TU'],
            ['name' => 'KKPI','code' => 'KKPI'],
        ];

        foreach($datas as $data)
        {
            Major::create($data);
        }
    }
}
