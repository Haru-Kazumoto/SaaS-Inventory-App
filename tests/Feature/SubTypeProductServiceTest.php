<?php

namespace Tests\Feature;

use App\Dto\SubTypeProduct\CreateSubTypeProductDto;
use App\Dto\SubTypeProduct\UpdateSubTypeProductDto;
use App\Models\Major;
use App\Repositories\Interfaces\SubTypeProductRepositoryInterface;
use App\Repositories\SubTypeProductRepository;
use App\Services\SubTypeProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

class SubTypeProductServiceTest extends TestCase
{
    use RefreshDatabase;

    private SubTypeProductService $service;
    private $major = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = app(SubTypeProductService::class);
        $this->major = Major::create(['name' => 'TEKNIK ELEKTRO', 'code' => 'TE']);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_uses_injected_repository()
    {
        $repo = Mockery::mock(SubTypeProductRepositoryInterface::class);
        $repo->shouldReceive('getAll')->once()->andReturn(collect([]));

        $service = new SubTypeProductService($repo);

        $sub_types = $service->getAll();

        $this->assertIsIterable($sub_types);
    }

    public function test_it_should_storing_data()
    {
        $data = [
            'name' => 'TANG',
            'code' => 'TANG',
            'description' => 'Sub tipe tang',
            'major_id' => $this->major->id,
        ];

        $res = $this->service->store(new CreateSubTypeProductDto(...$data));

        $this->assertDatabaseHas('sub_type_products', [
            'name' => $data['name'],
            'code' => $data['code'],
            'description' => $data['description'],
            'major_id' => $data['major_id'],
        ]);
        $this->assertSame('TANG', $res->name);
    }

    // #[Group('ignore')]
    public function test_it_should_retrieve_data()
    {
        $data = [
            [
                'name' => 'TANG',
                'code' => 'TANG',
                'description' => 'Sub tipe tang',
                'major_id' => $this->major->id,
            ],
            [
                'name' => 'KABEL',
                'code' => 'KABEL',
                'description' => 'Sub tipe kabel',
                'major_id' => $this->major->id,
            ],
        ];

        collect($data)->each(function ($d) {
            $this->service->store(new CreateSubTypeProductDto(...$d));
        });

        $res = $this->service->getAll();

        $this->assertNotNull($res);
        $this->assertCount(2, $res);
        $this->assertEquals('KABEL', $res[1]['code']);
    }

    public function test_it_should_update_data()
    {
        $data = [
            'name' => 'TANG',
            'code' => 'TANG',
            'description' => 'Sub tipe tang',
            'major_id' => $this->major->id,
        ];

        $created_obj = $this->service->store(new CreateSubTypeProductDto(...$data));
        $update_data = [
            'name' => 'TANG_UPDT',
            'code' => 'TANG_UPDT',
            'description' => 'Sub tipe tang',
        ];

        $dto = new UpdateSubTypeProductDto(...$update_data);
        $this->service->update($dto, $created_obj->id);

        $this->assertDatabaseHas('sub_type_products', [
            'name' => 'TANG_UPDT',
            'code' => 'TANG_UPDT',
        ]);
    }

    public function test_it_should_delete_data()
    {
        $data = [
            'name' => 'TANG',
            'code' => 'TANG',
            'description' => 'Sub tipe tang',
            'major_id' => $this->major->id,
        ];

        $res = $this->service->store(new CreateSubTypeProductDto(...$data));

        $this->service->delete($res->id);
        $this->assertDatabaseMissing('sub_type_products', [
            'name' => 'TANG'
        ]);
    }
}
