<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
class ActiveProviderScope implements Scope
{
    public function apply(Builder $builder, Model $model): Builder
    {
        return $builder->where('is_active', true);
    }
}
