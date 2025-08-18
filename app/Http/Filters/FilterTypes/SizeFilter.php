<?php

namespace App\Http\Filters\FilterTypes;

use App\Http\Filters\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class SizeFilter implements FilterInterface
{
    public function apply(Builder $builder, $value): Builder
    {
        return $builder->bySize($value);
    }
}