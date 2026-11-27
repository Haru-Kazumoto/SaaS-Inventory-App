<?php

namespace Tests\Unit;

use App\Dto\SubTypeProduct\CreateSubTypeProductDto;
use PHPUnit\Framework\TestCase;

class CreateSubTypeProductDtoTest extends TestCase
{
    public function test_should_wrap_to_object(): void
    {
        $data = [
            'name' => 'TANG',
            'code' => 'TANG',
            'description' => 'Sub tipe tang',
            'major_id' => 1,
        ];

        $dto = new CreateSubTypeProductDto(...$data);

        $this->assertEquals('TANG', $dto->name);
        $this->assertEquals('TANG', $dto->code);
        $this->assertEquals('Sub tipe tang', $dto->description);
        $this->assertEquals(1, $dto->major_id);
    }

    public function test_should_return_to_array()
    {
        $data = [
            'name' => 'TANG',
            'code' => 'TANG',
            'description' => 'Sub tipe tang',
            'major_id' => 1,
        ];

        $dto = new CreateSubTypeProductDto(...$data);
        $array = $dto->toArray();

        $this->assertIsArray($array);
        $this->assertEquals($data, $array);
    }
}
