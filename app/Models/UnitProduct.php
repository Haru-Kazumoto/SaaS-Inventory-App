<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitProduct extends Model
{
    protected $table = 'unit_products';

    protected $fillable = ['name','code','description','major_id'];
}
