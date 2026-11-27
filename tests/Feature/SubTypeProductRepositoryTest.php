<?php

namespace Tests\Feature;

use App\Dto\SubTypeProduct\CreateSubTypeProductDto;
use App\Dto\SubTypeProduct\UpdateSubTypeProductDto;
use App\Models\Major;
use App\Repositories\Interfaces\SubTypeProductRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

class SubTypeProductRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private SubTypeProductRepositoryInterface $repository;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = app(SubTypeProductRepositoryInterface::class);
    }

    public function test_should_storing_data()
    {
        Major::create(['name' => 'TEKNIK OTOMOTIF', 'code' => 'TO']);

        $data = [
            'name' => 'TANG',
            'code' => 'TANG',
            'description' => 'Sub tipe tang',
            'major_id' => 1,
        ];

        $dto = new CreateSubTypeProductDto(...$data);

        $sub_type_product = $this->repository->store($dto);

        $this->assertDatabaseHas('sub_type_products', [
            'id' => $sub_type_product->id,
            'name' => 'TANG',
            'code' => 'TANG',
            'description' => 'Sub tipe tang',
            'major_id' => 1,
        ]);
    }

    public function test_can_retrieve_all_data()
    {
        $major = Major::create(['name' => 'TEKNIK ELEKTRO', 'code' => 'TE']);

        $rows = [
            [
                'name' => 'TANG',
                'code' => 'TANG',
                'description' => 'Sub tipe tang',
                'major_id' => $major->id,
            ],
            [
                'name' => 'BAUT',
                'code' => 'BAUT',
                'description' => 'Sub tipe baut',
                'major_id' => $major->id,
            ],
        ];

        collect($rows)->each(function ($row) {
            $this->repository->store(new CreateSubTypeProductDto(...$row));
        });

        $all_sub_type_products = $this->repository->getAll();

        $this->assertNotNull($all_sub_type_products);
        $this->assertCount(2, $all_sub_type_products);
        $this->assertEquals('TANG', $all_sub_type_products[0]->name);
    }

    public function test_can_retrieve_data_by_id()
    {
        $major = Major::create(['name' => 'TEKNIK ELEKTRO', 'code' => 'TE']);

        $data = [
            'name' => 'TANG',
            'code' => 'TANG',
            'description' => 'Sub tipe tang',
            'major_id' => $major->id,
        ];

        $sub_type_product = $this->repository->store(new CreateSubTypeProductDto(...$data));

        $fetched_sub_type_product = $this->repository->getById($sub_type_product->id);

        $this->assertNotNull($fetched_sub_type_product);
        $this->assertEquals('TANG', $fetched_sub_type_product->name);
        $this->assertEquals('TANG', $fetched_sub_type_product->code);
    }

    public function test_should_update_data()
    {
        $major = Major::create(['name' => 'TEKNIK OTOMOTIF', 'code' => 'TO']);

        $data = [
            'name' => 'TANG',
            'code' => 'TANG',
            'description' => 'Sub tipe tang',
            'major_id' => $major->id,
        ];

        $dto = new CreateSubTypeProductDto(...$data);

        $sub_type_product = $this->repository->store($dto);

        $updateData = [
            'name' => 'TANG UPDATE',
            'code' => 'TANG-UPDATE',
            'description' => 'Sub tipe tang updated',
        ];

        $updateDto = new UpdateSubTypeProductDto(...$updateData);

        $this->repository->update($updateDto, $sub_type_product->id);

        $this->assertDatabaseHas('sub_type_products', [
            'id' => $sub_type_product->id,
            'name' => 'TANG UPDATE',
            'code' => 'TANG-UPDATE',
            'description' => 'Sub tipe tang updated',
            'major_id' => $sub_type_product->major_id,
        ]);
    }

    public function test_should_delete_data()
    {
        $major = Major::create(['name' => 'TEKNIK OTOMOTIF', 'code' => 'TO']);

        $data = [
            'name' => 'TANG',
            'code' => 'TANG',
            'description' => 'Sub tipe tang',
            'major_id' => $major->id,
        ];

        $dto = new CreateSubTypeProductDto(...$data);

        $sub_type_product = $this->repository->store($dto);

        $this->repository->delete($sub_type_product->id);

        $this->assertDatabaseMissing('sub_type_products', [
            'id' => $sub_type_product->id,
            'name' => 'TANG',
            'code' => 'TANG',
            'description' => 'Sub tipe tang',
            'major_id' => 1,
        ]);
    }
}
