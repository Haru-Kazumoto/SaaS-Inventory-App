<?php

namespace App\Domain\Organization\Scopes;

use App\Domain\Organization\Support\OrganizationContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope: batasi query Eloquent ke organisasi aktif.
 * Tanpa konteks (console, area platform) scope tidak diterapkan.
 */
class OrganizationScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $context = app(OrganizationContext::class);

        if ($context->has()) {
            $builder->where($model->qualifyColumn('organization_id'), $context->id());
        }
    }
}
