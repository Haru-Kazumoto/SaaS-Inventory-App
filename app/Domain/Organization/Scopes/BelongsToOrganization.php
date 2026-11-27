<?php

namespace App\Domain\Organization\Scopes;

use App\Domain\Organization\Models\Organization;
use App\Domain\Organization\Support\OrganizationContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pasang di setiap model bisnis milik tenant:
 * - global scope `organization_id = organisasi aktif`
 * - organization_id terisi otomatis saat membuat data.
 */
trait BelongsToOrganization
{
    public static function bootBelongsToOrganization(): void
    {
        static::addGlobalScope(new OrganizationScope());

        static::creating(function ($model) {
            $context = app(OrganizationContext::class);

            if (empty($model->organization_id) && $context->has()) {
                $model->organization_id = $context->id();
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
