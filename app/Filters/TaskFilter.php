<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class TaskFilter
{
    public function apply(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('title', 'like', "%{$search}%"))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['priority'] ?? null, fn ($query, $category) => $query->where('priority', $category))
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('category_id', $category));
    }
}
