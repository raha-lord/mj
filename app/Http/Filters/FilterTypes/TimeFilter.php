<?php

namespace App\Http\Filters\FilterTypes;

use App\Http\Filters\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class TimeFilter implements FilterInterface
{
    public function apply(Builder $builder, $value): Builder
    {
        switch ($value) {
            case 'overdue':
                return $builder->overdue();

            case 'over_budget':
                return $builder->whereRaw('actual_hours > estimated_hours');

            case 'no_estimate':
                return $builder->whereNull('estimated_hours');

            case 'completed_today':
                return $builder->whereDate('completed_date', today());

            case 'due_this_week':
                return $builder->whereBetween('due_date', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])->whereNull('completed_date');

            case 'long_running':
                return $builder->where('actual_hours', '>', 40)
                    ->whereNull('completed_date');

            default:
                return $builder;
        }
    }
}