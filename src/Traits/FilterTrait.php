<?php

namespace LaravelWakeUp\FilterSort\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait FilterTrait
{
    use ModelHelperTrait;

    public const FILTER_TYPE_SUFFIX = '_op';
    public const FILTER_DEFAULT_OPERATOR = 'like';
    public const FILTER_START_RANGE_SUFFIX = '_start_range';
    public const FILTER_END_RANGE_SUFFIX = '_end_range';

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
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilter(Builder $query, Request $request): Builder
    {
        $prefix = config('laravel-filter-sort.filter_type_suffix', self::FILTER_TYPE_SUFFIX);
        $allowedFilters = $this->getAllowedFilters() ?: $this->getTableColumns();
        $filters = $request->query();

        $this->processMultiColumnFilters($query, $filters);
        $this->processRangeFilters($query, $filters, $allowedFilters);

        // Process value filters
        $validFilters = array_filter(
            $filters,
            fn($value, $field) =>
            !str_ends_with($field, $prefix)
            && in_array($field, $allowedFilters),
            ARRAY_FILTER_USE_BOTH
        );

        // Process other filters
        foreach ($validFilters as $field => $value) {
            if (str_ends_with($field, self::FILTER_START_RANGE_SUFFIX) || str_ends_with($field, self::FILTER_END_RANGE_SUFFIX)) {
                continue;
            }

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
     * Process multi-column search.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * 
     * @return void
     */
    protected function processMultiColumnFilters(Builder $query, array $filters): void
    {
        $multiColumnSearch = $this->getMultiColumnSearch();

        if (
            !empty($multiColumnSearch) 
            && isset($multiColumnSearch['search_field']) 
            && isset($multiColumnSearch['fields']) 
            && is_array($multiColumnSearch['fields'])
        ) {
            $searchField = $multiColumnSearch['search_field'];
        
            if (isset($filters[$searchField]) && trim($filters[$searchField]) !== '') {
                $this->applyMultiColumnSearch($query, $filters[$searchField], $multiColumnSearch['fields']);
            }
        }
        
    }

    /**
     * Apply filter
     * 
     * @param Builder $query
     * @param string $field
     * @param string $operator
     * @param mixed $value
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
     * 
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
     * 
     * @return void
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
     * 
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
     * 
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
     * 
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
     * 
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
     * 
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
     * 
     * @return void
     */
    protected function applyBetweenFilter(Builder $query, string $field, string|array $value): void
    {
        $values = is_string($value) ? explode(',', $value) : $value;

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
     * 
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
     * 
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

    /**
     * Apply multi-column search to the query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $searchTerm
     * @param $fields
     * 
     * @return void
     */
    protected function applyMultiColumnSearch(Builder $query, string $searchTerm, array $fields): void
    {
        $tableColumns = $this->getTableColumns();

        $query->where(function ($query) use ($fields, $searchTerm, $tableColumns) {
            foreach ($fields as $field => $operator) {
                if (in_array($field, $tableColumns)) {
                    if ($operator === self::FILTER_LIKE) {
                        $query->orWhere($field, 'like', "%{$searchTerm}%");
                    } elseif ($operator === self::FILTER_EQUAL) {
                        $query->orWhere($field, '=', $searchTerm);
                    }
                }
            }
        });
    }

    /**
     * Process Range Filters
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @param array $allowedFilters
     * 
     * @return void
     */
    protected function processRangeFilters(Builder $query, array $filters, array $allowedFilters): void
    {
        $rangeFilters = [];
        foreach ($filters as $field => $value) {
            if (str_ends_with($field, self::FILTER_START_RANGE_SUFFIX) || str_ends_with($field, self::FILTER_END_RANGE_SUFFIX)) {
                $baseField = str_replace([self::FILTER_START_RANGE_SUFFIX, self::FILTER_END_RANGE_SUFFIX], '', $field);
                if (in_array($baseField, $allowedFilters) && trim($value) !== '') {
                    $rangeFilters[$baseField][str_ends_with($field, self::FILTER_START_RANGE_SUFFIX) ? 'start' : 'end'] = $value;
                }
            }
        }

        if (count($rangeFilters) > 0) {
            foreach ($rangeFilters as $field => $range) {
                if (isset($range['start']) && isset($range['end'])) {
                    $this->applyFilter($query, $field, self::FILTER_BETWEEN, [$range['start'], $range['end']]);
                }
                if (isset($range['start']) && !isset($range['end'])) {
                    $this->applyFilter($query, $field, self::FILTER_GREATER_THAN_OR_EQUAL, $range['start']);
                }
                if (!isset($range['start']) && isset($range['end'])) {
                    $this->applyFilter($query, $field, self::FILTER_LESS_THAN_OR_EQUAL, $range['end']);
                }
            }
        }
    }

    /**
     * Get Multi Column Search
     * 
     * @return array
     */
    protected function getMultiColumnSearch(): array
    {
        return property_exists($this, 'multiColumnSearch')
            ? $this->multiColumnSearch
            : [];
    }
}
