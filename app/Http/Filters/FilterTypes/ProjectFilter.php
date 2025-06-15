<?php

namespace App\Http\Filters\FilterTypes;

namespace App\Http\Filters\FilterTypes;

use App\Http\Filters\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class ProjectFilter implements FilterInterface
{
    public function apply(Builder $builder, $value): Builder
    {
        return $builder->where('project_id', $value);
    }
}