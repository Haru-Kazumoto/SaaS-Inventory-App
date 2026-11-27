<?php

namespace Database\Seeders;

use App\Models\Major;
use App\Models\Warehouse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $id_major_tjkt = $this->getIdMajorFromCode('TJKT');
        $id_major_ak = $this->getIdMajorFromCode('AK');
        $id_major_te = $this->getIdMajorFromCode('TE');
        $id_major_to = $this->getIdMajorFromCode('TO');

        $datas = [
            // TJKT
            ['name' => 'LAB 1 TJKT', 'code' => 'LAB_1', 'major_id' => $id_major_tjkt],
            ['name' => 'LAB 2 TJKT', 'code' => 'LAB_2', 'major_id' => $id_major_tjkt],
            ['name' => 'LAB 3 TJKT', 'code' => 'LAB_3', 'major_id' => $id_major_tjkt],
            ['name' => 'LAB 4 TJKT', 'code' => 'LAB_4', 'major_id' => $id_major_tjkt],

            // TE
            ['name' => 'LAB 1 TE', 'code' => 'LAB_1', 'major_id' => $id_major_te],
            ['name' => 'LAB 2 TE', 'code' => 'LAB_2', 'major_id' => $id_major_te],
            ['name' => 'LAB 3 TE', 'code' => 'LAB_3', 'major_id' => $id_major_te],
            ['name' => 'LAB 4 TE', 'code' => 'LAB_4', 'major_id' => $id_major_te],

            // TO
            ['name' => 'LAB 1 TO', 'code' => 'LAB_1', 'major_id' => $id_major_to],
            ['name' => 'LAB 2 TO', 'code' => 'LAB_2', 'major_id' => $id_major_to],
            ['name' => 'LAB 3 TO', 'code' => 'LAB_3', 'major_id' => $id_major_to],
            ['name' => 'LAB 4 TO', 'code' => 'LAB_4', 'major_id' => $id_major_to],

            // AK
            ['name' => 'LAB 1 AK', 'code' => 'LAB_1', 'major_id' => $id_major_ak],
            ['name' => 'LAB 2 AK', 'code' => 'LAB_2', 'major_id' => $id_major_ak],
            ['name' => 'LAB 3 AK', 'code' => 'LAB_3', 'major_id' => $id_major_ak],
            ['name' => 'LAB 4 AK', 'code' => 'LAB_4', 'major_id' => $id_major_ak],
        ];

        foreach($datas as $data) {
            Warehouse::create($data);
        }
    }

    private function getIdMajorFromCode(string $code) {
        $major = Major::where('code', $code)->first(['id']);

        return $major->id;
    }
}

