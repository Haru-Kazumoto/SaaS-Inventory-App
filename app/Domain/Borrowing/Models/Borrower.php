<?php

namespace App\Domain\Borrowing\Models;

use App\Domain\Organization\Scopes\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Kontak peminjam. */
class Borrower extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id', 'unit_id', 'fullname', 'identity_number', 'phone',
        'responsible_name', 'usage_location', 'notes',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
