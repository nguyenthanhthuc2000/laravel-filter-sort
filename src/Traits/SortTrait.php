<?php

namespace LaravelWakeUp\FilterSort\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait SortTrait
{
    use ModelHelperTrait;

    public const SORT_DEFAULT_FIELD = 'id';
    public const SORT_DEFAULT_ORDER = 'asc';
    public const SORT_ALLOWED_ORDERS = ['asc', 'desc'];
    public const SORT_FIELD_SUFFIX = '_sort';

    /**
     * Scope Sort
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Http\Request $request
     * @return Builder
     */
    public function scopeSort(Builder $query, Request $request): Builder
    {
        $sorts = $this->parseSortParameters($request);
        return $this->applySort($query, $sorts);
    }

    /**
     * Parse Sort Parameters
     * 
     * @param Request $request
     * @return array<array{field: string, order: string}>
     */
    protected function parseSortParameters(Request $request): array
    {
        $allowedSorts = $this->getAllowedSorts() ?: $this->getTableColumns();
        $sorts = [];
        $suffix = $this->getSortFieldSuffix();

        // Find all valid sort fields from request
        foreach ($allowedSorts as $field) {
            $key = "{$field}{$suffix}";
            if ($request->has($key)) {
                $sorts[] = [
                    'field' => $field,
                    'order' => $this->validateSortOrder($request->query($key))
                ];
            }
        }

        return $sorts;
    }

    /**
     * Get Sort Field Suffix
     * 
     * @return string
     */
    protected function getSortFieldSuffix(): string
    {
        return config('laravel-filter-sort.sort_field_suffix', self::SORT_FIELD_SUFFIX);
    }

    /**
     * Validate Sort Order
     * 
     * @param mixed $value
     * @return string
     */
    protected function validateSortOrder($value): string
    {
        $value = strtolower((string)$value);
        return in_array($value, self::SORT_ALLOWED_ORDERS) ? $value : self::SORT_DEFAULT_ORDER;
    }

    /**
     * Apply Sort
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array<array{field: string, order: string}> $sorts
     * @return Builder
     */
    protected function applySort(Builder $query, array $sorts): Builder
    {
        foreach ($sorts as $sort) {
            $query->orderBy($sort['field'], $sort['order']);
        }

        return $query;
    }

    /**
     * Get Allowed Sorts
     * 
     * @return array<string>
     */
    protected function getAllowedSorts(): array
    {
        return property_exists($this, 'allowedSorts') 
            ? $this->allowedSorts 
            : [];
    }
}
