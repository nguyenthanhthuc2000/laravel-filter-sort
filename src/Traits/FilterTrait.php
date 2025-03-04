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
        $filters = array_filter($request->query(), fn($value) => !is_null($value) && trim($value) !== '');

        foreach ($filters as $field => $value) {
            if (str_ends_with($field, '_op')) {
                continue;
            }

            if (in_array($field, $allowedFilters)) {
                $operator = $request->query("{$field}_op", 'like');
                $this->applyFilter($query, $field, $operator, $value);
            }
        }

        return $query;
    }

    /**
     * Áp dụng filter theo toán tử
     * 
     * @param Builder $query
     * @param $field
     * @param $operator
     * @param $value
     */
    protected function applyFilter(Builder $query, string $field, string $operator, mixed $value): void
    {
        $operator = strtolower($operator);

        match ($operator) {
            'eq' => $this->applyEqualFilter($query, $field, $value),
            'ne' => $this->applyNotEqualFilter($query, $field, $value),
            'gt' => $this->applyGreaterThanFilter($query, $field, $value),
            'lt' => $this->applyLessThanFilter($query, $field, $value),
            'gte' => $this->applyGreaterThanOrEqualFilter($query, $field, $value),
            'lte' => $this->applyLessThanOrEqualFilter($query, $field, $value),
            'between' => $this->applyBetweenFilter($query, $field, $value),
            'notin' => $this->applyNotInFilter($query, $field, $value),
            default => $this->applyLikeFilter($query, $field, $value),
        };
    }

    /**
     * Apply Not Equal Filter
     *
     * @param Builder $query
     * @param $field
     * @param $value
     */
    protected function applyNotEqualFilter(Builder $query, string $field, mixed $value): void
    {
        $query->where($field, '!=', $value);
    }

    /**
     * Apply Greater Than Or Equal Filter
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $field
     * @param $value
     * @return void
     */
    protected function applyGreaterThanOrEqualFilter(Builder $query, string $field, mixed $value): void
    {
        $query->where($field, '>=', $value);
    }

    /**
     * Apply Less Than Or Equal Filter
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $field
     * @param $value
     * @return void
     */
    protected function applyLessThanOrEqualFilter(Builder $query, string $field, mixed $value): void
    {
        $query->where($field, '<=', $value);
    }

    /**
     * Apply Equal Filter
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $field
     * @param $value
     * @return void
     */
    protected function applyEqualFilter(Builder $query, string $field, mixed $value): void
    {
        $query->where($field, '=', $value);
    }

    /**
     * Apply Greater Than Filter
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $field
     * @param $value
     * @return void
     */
    protected function applyGreaterThanFilter(Builder $query, string $field, mixed $value): void
    {
        $query->where($field, '>', $value);
    }

    /**
     * Apply Less Than Filter
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $field
     * @param $value
     * @return void
     */
    protected function applyLessThanFilter(Builder $query, string $field, mixed $value): void
    {
        $query->where($field, '<', $value);
    }

    /**
     * Apply Between Filter
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $field
     * @param $value
     * @return void
     */
    protected function applyBetweenFilter(Builder $query, string $field, string|array $value): void
    {
        if (is_string($value)) {
            $values = explode(',', $value);
        }

        if (count($values) === 2) {
            $query->whereBetween($field, [$values[0], $values[1]]);
        }
    }

    /**
     * Apply Like Filter
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $field
     * @param $value
     * @return void
     */
    protected function applyLikeFilter(Builder $query, string $field, string $value): void
    {
        $query->where($field, 'LIKE', "%$value%");
    }

    /**
     * Apply Not In Filter
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $field
     * @param $value
     * @return void
     */
    protected function applyNotInFilter(Builder $query, string $field, string|array $values): void
    {
        if (is_string($values)) {
            $values = explode(',', $values);
        }
    
        if (is_array($values)) {
            $query->whereNotIn($field, $values);
        }
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
