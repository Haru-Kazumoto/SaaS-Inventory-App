<?php

namespace App\Domain\Borrowing\Models;

use App\Domain\Borrowing\Enums\TxType;
use App\Domain\Organization\Models\Unit;
use App\Domain\Organization\Scopes\BelongsToOrganization;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id', 'unit_id', 'tx_number', 'tx_type', 'status', 'borrower_id', 'parent_borrow_tx_id',
        'occurred_at', 'due_at', 'returned_at', 'description', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tx_type' => TxType::class,
            'occurred_at' => 'datetime',
            'due_at' => 'date',
            'returned_at' => 'datetime',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function borrower(): BelongsTo
    {
        return $this->belongsTo(Borrower::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(TransactionLine::class)->orderBy('line_no');
    }

    /** Transaksi pengembalian dari pinjaman ini. */
    public function returns(): HasMany
    {
        return $this->hasMany(self::class, 'parent_borrow_tx_id');
    }
}
