<?php

namespace Tests\Unit;

use App\Dto\SubTypeProduct\UpdateSubTypeProductDto;
use PHPUnit\Framework\TestCase;

class UpdateSubTypeProductDtoTest extends TestCase
{
    public function test_should_wrap_to_object(): void
    {
        $data = [
            'name' => 'TANG',
            'code' => 'TANG',
            'description' => 'Sub tipe tang',
        ];

        $dto = new UpdateSubTypeProductDto(...$data);

        $this->assertEquals('TANG', $dto->name);
        $this->assertEquals('TANG', $dto->code);
        $this->assertEquals('Sub tipe tang', $dto->description);
    }

    public function test_should_return_to_array()
    {
        $data = [
            'name' => 'TANG',
            'code' => 'TANG',
            'description' => 'Sub tipe tang',
        ];

        $dto = new UpdateSubTypeProductDto(...$data);
        $array = $dto->toArray();

        $this->assertIsArray($array);
        $this->assertEquals($data, $array);
    }
}
