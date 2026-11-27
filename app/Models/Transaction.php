<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    protected $table = 'transaction';
    
    protected $fillable = [
        'description',
        'out_date',
        'in_date',
        'staff_id',
        'warehouse_id',
        'major_id',
        'transaction_number',
        'parent_borrow_tx_id',
    ];

    public function borrower(): HasOne
    { 
        return $this->hasOne(Borrower::class);
    }

    public function itemsOut(): HasMany
    {
        return $this->hasMany(ItemsOut::class);
    }
}