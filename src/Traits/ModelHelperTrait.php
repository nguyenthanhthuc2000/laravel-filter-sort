<?php

namespace LaravelWakeUp\FilterSort\Traits;

trait ModelHelperTrait
{
    /**
     * Get Table Columns
     * 
     * @return array
     */
    protected function getTableColumns(): array
    {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }
}
