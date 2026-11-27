<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubTypeProduct extends Model
{
    protected $table = 'sub_type_products';

    protected $fillable = [
        'name',
        'code',
        'description',
        'major_id',
    ];
}
