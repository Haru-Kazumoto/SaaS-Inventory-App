<?php

namespace Tests\Feature;

use App\Dto\UnitProduct\CreateUnitProductDto;
use App\Dto\UnitProduct\UpdateUnitProductDto;
use App\Models\Major;
use App\Repositories\Interfaces\UnitProductRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UnitProductRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UnitProductRepositoryInterface $repository;
    private $major = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = app(UnitProductRepositoryInterface::class);
        $this->major = Major::create(['name' => 'TEKNIK ELEKTRO', 'code' => 'TE']);
    }

    public function test_it_should_storing_data()
    {

        $data = [
            'name' => 'PCS',
            'code' => 'PCS',
            'description' => 'Satuan pcs',
            'major_id' => $this->major->id
        ];

        $res = $this->repository->store(new CreateUnitProductDto(...$data));

        $this->assertEquals('PCS', $res->name);
        $this->assertDatabaseHas('unit_products', ['id' => 1]);
    }

    public function test_it_should_retrieve_data()
    {
        $data = [
            [
                'name' => 'PCS',
                'code' => 'PCS',
                'description' => 'Satuan pcs',
                'major_id' => $this->major->id
            ],
            [
                'name' => 'LT',
                'code' => 'LT',
                'description' => 'Satuan liter',
                'major_id' => $this->major->id
            ]
        ];

        collect($data)->each(function ($d) {
            $this->repository->store(new CreateUnitProductDto(...$d));
        });

        $res = $this->repository->getAll();

        $this->assertNotNull($res);
        $this->assertCount(2, $res);
        $this->assertEquals('LT', $res[1]['code']);
    }

    public function test_it_should_update_data()
    {
        $data = [
            'name' => 'PCS',
            'code' => 'PCS',
            'description' => 'Satuan pcs',
            'major_id' => $this->major->id
        ];

        $created_obj = $this->repository->store(new CreateUnitProductDto(...$data));

        $updated_obj = [
            'name' => 'PCS UPDATED',
            'code' => 'PCS_UPDATED',
            'description' => null
        ];

        $this->repository->update(new UpdateUnitProductDto(...$updated_obj), $created_obj->id);

        $this->assertDatabaseMissing('unit_products', ['code' => 'PCS']);
        $this->assertDatabaseHas('unit_products', ['code' => 'PCS_UPDATED']);
    }

    public function test_it_should_delete_data()
    {
        $data = [
            'name' => 'PCS',
            'code' => 'PCS',
            'description' => 'Satuan pcs',
            'major_id' => $this->major->id
        ];

        $created_obj = $this->repository->store(new CreateUnitProductDto(...$data));

        $this->repository->delete($created_obj->id);

        $this->assertDatabaseMissing('unit_products', ['id' => 1]);
    }
}
