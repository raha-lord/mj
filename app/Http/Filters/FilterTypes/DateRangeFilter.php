<?php


namespace App\Http\Filters\FilterTypes;

use App\Http\Filters\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class DateRangeFilter implements FilterInterface
{
    public function apply(Builder $builder, $value): Builder
    {
        // Ожидаем массив ['from' => '2023-01-01', 'to' => '2023-12-31', 'field' => 'created_at']
        if (!is_array($value) || !isset($value['field'])) {
            return $builder;
        }

        $field = $value['field'];

        if (isset($value['from']) && $value['from']) {
            $builder->whereDate($field, '>=', $value['from']);
        }

        if (isset($value['to']) && $value['to']) {
            $builder->whereDate($field, '<=', $value['to']);
        }

        return $builder;
    }
}