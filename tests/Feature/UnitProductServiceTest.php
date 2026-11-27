<?php

namespace Tests\Feature;

use App\Dto\UnitProduct\CreateUnitProductDto;
use App\Models\Major;
use App\Repositories\Interfaces\UnitProductRepositoryInterface;
use App\Services\UnitProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class UnitProductServiceTest extends TestCase
{
    use RefreshDatabase;

    private UnitProductService $service;
    private $major = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = app(UnitProductService::class);
        $this->major = Major::create(['name' => 'TEKNIK ELEKTRO', 'code' => 'TE']);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function it_should_use_dependency_injection_functions()
    {
        $repo = Mockery::mock(UnitProductRepositoryInterface::class);
        $repo->shouldReceive('getAll')->once()->andReturn(collect([]));

        $service = new UnitProductService($repo);

        $unit_products = $service->getAll();

        $this->assertCount(0, $unit_products);
        $this->assertIsIterable($unit_products);
    }

    public function test_it_should_storing_data()
    {
        $data = [
            'name' => 'PCS',
            'code' => 'PCS',
            'description' => 'Satuan pcs',
            'major_id' => $this->major->id
        ];

        $this->service->store(new CreateUnitProductDto(...$data));

        $this->assertDatabaseHas('unit_products', ['name' => 'PCS']);
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
                'name' => 'UNIT',
                'code' => 'UNIT',
                'description' => 'Satuan unit',
                'major_id' => $this->major->id
            ]
        ];

        collect($data)->each(function($d) {
            $this->service->store(new CreateUnitProductDto(...$d));
        });

        $res = $this->service->getAll();

        $this->assertCount(2, $res);
        $this->assertEquals('UNIT', $res[1]['code']);
    }
}
