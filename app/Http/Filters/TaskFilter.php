<?php
// app/Http/Filters/TaskFilter.php

namespace App\Http\Filters;

use App\Http\Filters\FilterTypes\SearchFilter;
use App\Http\Filters\FilterTypes\StatusFilter;
use App\Http\Filters\FilterTypes\PriorityFilter;
use App\Http\Filters\FilterTypes\SizeFilter;
use App\Http\Filters\FilterTypes\ProjectFilter;
use App\Http\Filters\FilterTypes\AssigneeFilter;
use App\Http\Filters\FilterTypes\TimeFilter;
use App\Http\Filters\FilterTypes\DateRangeFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TaskFilter
{
    protected $request;
    protected $builder;

    protected $filters = [
        'search' => SearchFilter::class,
        'status' => StatusFilter::class,
        'priority' => PriorityFilter::class,
        'size' => SizeFilter::class,
        'project' => ProjectFilter::class,
        'assignee' => AssigneeFilter::class,
        'time_filter' => TimeFilter::class,
        'date_range' => DateRangeFilter::class,
    ];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function apply(Builder $builder): Builder
    {
        $this->builder = $builder;

        foreach ($this->filters as $name => $filterClass) {
            if ($this->request->filled($name)) {
                $filter = new $filterClass();
                $filter->apply($this->builder, $this->request->get($name));
            }
        }

        return $this->builder;
    }

    /**
     * Получить активные фильтры
     */
    public function getActiveFilters(): array
    {
        $active = [];

        foreach ($this->filters as $name => $filterClass) {
            if ($this->request->filled($name)) {
                $active[$name] = $this->request->get($name);
            }
        }

        return $active;
    }

    /**
     * Проверить есть ли активные фильтры
     */
    public function hasActiveFilters(): bool
    {
        return !empty($this->getActiveFilters());
    }
}