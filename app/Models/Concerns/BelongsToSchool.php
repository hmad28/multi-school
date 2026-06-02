<?php

namespace App\Models\Concerns;

use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToSchool
{
    protected static function bootBelongsToSchool(): void
    {
        static::addGlobalScope('school', function (Builder $builder): void {
            if ($schoolId = TenantContext::id()) {
                $builder->where(
                    $builder->getModel()->getTable().'.school_id',
                    $schoolId,
                );
            }
        });

        static::creating(function (Model $model): void {
            if (empty($model->school_id) && TenantContext::id()) {
                $model->school_id = TenantContext::id();
            }
        });
    }
}
