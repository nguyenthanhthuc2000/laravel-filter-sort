<?php

namespace LaravelWakeUp\FilterSort\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait FilterTrait
{
    use ModelHelperTrait;

    public const FILTER_TYPE_SUFFIX = '_op';
    public const FILTER_DEFAULT_OPERATOR = 'like';

    /**
     * Filter operators
     */
    public const FILTER_NULL = 'null';
    public const FILTER_NOT_NULL = 'notNull';
    public const FILTER_EQUAL = 'eq';
    public const FILTER_NOT_EQUAL = 'ne';
    public const FILTER_GREATER_THAN = 'gt';
    public const FILTER_LESS_THAN = 'lt';
    public const FILTER_GREATER_THAN_OR_EQUAL = 'gte';
    public const FILTER_LESS_THAN_OR_EQUAL = 'lte';
    public const FILTER_BETWEEN = 'between';
    public const FILTER_NOT_IN = 'notIn';
    public const FILTER_IN = 'in';
    public const FILTER_LIKE = 'like';

    /**
     * Get valid operators
     * 
     * @return array
     */
    protected function getValidOperators(): array
    {
        return [
            self::FILTER_NULL,
            self::FILTER_NOT_NULL,
            self::FILTER_EQUAL,
            self::FILTER_NOT_EQUAL,
            self::FILTER_GREATER_THAN,
            self::FILTER_LESS_THAN,
            self::FILTER_GREATER_THAN_OR_EQUAL,
            self::FILTER_LESS_THAN_OR_EQUAL,
            self::FILTER_BETWEEN,
            self::FILTER_NOT_IN,
            self::FILTER_IN,
            self::FILTER_LIKE,
        ];
    }

    /**
     * Scope Filter
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilter(Builder $query, Request $request): Builder
    {
        $prefix = config('laravel-filter-sort.filter_type_suffix', self::FILTER_TYPE_SUFFIX);
        $allowedFilters = $this->getAllowedFilters() ?: $this->getTableColumns();
        $filters = $request->query();

        // Process value filters
        $validFilters = array_filter(
            $filters,
            fn($value, $field) =>
            !str_ends_with($field, $prefix)
            && in_array($field, $allowedFilters),
            ARRAY_FILTER_USE_BOTH
        );

        foreach ($validFilters as $field => $value) {
            $operator = $filters["{$field}{$prefix}"] ?? self::FILTER_DEFAULT_OPERATOR;

            // Skip if operator is not valid
            if (!in_array($operator, $this->getValidOperators())) {
                continue;
            }
            // Handle all filters
            if ($value !== null && trim($value) !== '') {
                $this->applyFilter($query, $field, $operator, $value);
            }
        }

        return $query;
    }

    /**
     * Apply filter
     * 
     * @param Builder $query
     * @param string $field Field to filter on
     * @param string $operator Operator to use (must be one of the FILTER_* constants)
     * @param mixed $value Value to filter by
     */
    protected function applyFilter(Builder $query, string $field, string $operator, mixed $value): void
    {
        match ($operator) {
            self::FILTER_EQUAL => $this->applyEqualFilter($query, $field, $value),
            self::FILTER_NOT_EQUAL => $this->applyNotEqualFilter($query, $field, $value),
            self::FILTER_GREATER_THAN => $this->applyGreaterThanFilter($query, $field, $value),
            self::FILTER_LESS_THAN => $this->applyLessThanFilter($query, $field, $value),
            self::FILTER_GREATER_THAN_OR_EQUAL => $this->applyGreaterThanOrEqualFilter($query, $field, $value),
            self::FILTER_LESS_THAN_OR_EQUAL => $this->applyLessThanOrEqualFilter($query, $field, $value),
            self::FILTER_BETWEEN => $this->applyBetweenFilter($query, $field, $value),
            self::FILTER_NOT_IN => $this->applyNotInFilter($query, $field, $value),
            self::FILTER_IN => $this->applyInFilter($query, $field, $value),
            self::FILTER_NULL => $this->applyNullFilter($query, $field),
            self::FILTER_NOT_NULL => $this->applyNotNullFilter($query, $field),
            default => $this->applyLikeFilter($query, $field, $value),
        };
    }

    /**
     * Apply Null Filter
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $field
     * @return void
     */
    protected function applyNullFilter(Builder $query, string $field): void
    {
        $query->whereNull($field);
    }

    /**
     * Apply Not Null Filter
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $field
     * @return void
     */
    protected function applyNotNullFilter(Builder $query, string $field): void
    {
        $query->whereNotNull($field);
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
     * Apply In Filter
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $field
     * @param $value
     * @return void
     */
    protected function applyInFilter(Builder $query, string $field, string|array $values): void
    {
        if (is_string($values)) {
            $values = explode(',', $values);
        }

        if (is_array($values)) {
            $query->whereIn($field, $values);
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
