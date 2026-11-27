<?php

namespace App\Repositories;

use App\Dto\SubTypeProduct\CreateSubTypeProductDto;
use App\Dto\SubTypeProduct\UpdateSubTypeProductDto;
use App\Models\SubTypeProduct;
use App\Repositories\Interfaces\SubTypeProductRepositoryInterface;

class SubTypeProductRepository implements SubTypeProductRepositoryInterface
{
    public function getAll(int $major_id) 
    {
        return SubTypeProduct::where('major_id', $major_id)->get();
    }
    
    public function getById(int $id) 
    {
        return SubTypeProduct::find($id);
    }

    public function getByCode(string $code)
    {
        return SubTypeProduct::where('code',$code)->first();
    }
    
    public function store(CreateSubTypeProductDto $data) 
    {
        return SubTypeProduct::create($data->toArray());
    }
    
    public function update(UpdateSubTypeProductDto $data, int $id) 
    {
        $sub_type_product = $this->getById($id);

        return $sub_type_product->update($data->toArray());
    }
    
    public function delete(int $id) 
    {
        $sub_type_product = $this->getById($id);

        return $sub_type_product->delete();
    }
    
}
