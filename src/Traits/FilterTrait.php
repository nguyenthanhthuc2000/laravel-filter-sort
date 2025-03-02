<?php

namespace LaravelWakeUp\FilterSort\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait FilterTrait
{
    use ModelHelperTrait;

    /**
     * Scope Filter
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Http\Request $request
     * @return Builder
     */
    public function scopeFilter(Builder $query, Request $request): Builder
    {
        $allowedFilters = $this->getAllowedFilters() ?: $this->getTableColumns();
        $filters = array_filter($request->query(), fn($value) => trim($value) !== '');

        foreach ($filters as $field => $value) {
            if (in_array($field, $allowedFilters)) {
                $query->where($field, 'LIKE', "%$value%");
            }
        }

        return $query;
    }

    /**
     * Get Allowed Filters
     * 
     * @return array
     */
    protected function getAllowedFilters(): array
    {
        return property_exists($this, 'allowedFilters') 
            ? $this->allowedFilters 
            : [];
    }
}
