<?php

namespace LaravelWakeUp\FilterSort\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait SortTrait
{
    use ModelHelperTrait;

    /**
     * Scope Sort
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Http\Request $request
     * @return Builder
     */
    public function scopeSort(Builder $query, Request $request): Builder
    {
        $allowedSorts = $this->getAllowedSorts() ?: $this->getTableColumns();
        $sortField = $request->query('sort', 'id');

        if (!in_array($sortField, $allowedSorts)) {
            return $query;
        }

        $sortOrder = strtolower($request->query('order', 'asc'));
        $sortOrder = in_array($sortOrder, ['asc', 'desc']) ? $sortOrder : 'asc';

        return $query->orderBy($sortField, $sortOrder);
    }

    /**
     * Get Allowed Sorts
     * 
     * @return array
     */
    protected function getAllowedSorts(): array
    {
        return property_exists($this, 'allowedSorts') 
            ? $this->allowedSorts 
            : [];
    }
}
