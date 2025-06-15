<?php

namespace App\Http\Filters\FilterTypes;

use App\Http\Filters\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class SearchFilter implements FilterInterface
{
    public function apply(Builder $builder, $value): Builder
    {
        return $builder->where(function($q) use ($value) {
            $q->where('name', 'LIKE', "%{$value}%")
                ->orWhere('description', 'LIKE', "%{$value}%")
                ->orWhereHas('status', fn($sq) => $sq->where('name', 'LIKE', "%{$value}%"))
                ->orWhereHas('project', fn($pq) => $pq->where('name', 'LIKE', "%{$value}%"));
        });
    }
}