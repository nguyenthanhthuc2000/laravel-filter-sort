# Filter Sort Scope for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/laravelwakeup/filter-sort.svg)](https://packagist.org/packages/laravelwakeup/filter-sort)
[![License](https://img.shields.io/github/license/laravelwakeup/filter-sort.svg)](LICENSE.md)

## Table of Contents
- [Introduction](#introduction)
- [Requirements](#requirements)
- [Installation & Configuration](#installation--configuration)
- [Basic Usage](#basic-usage)
- [Available Operators](#available-operators)
- [Examples](#examples)
- [JavaScript Usage](#javascript-usage)
- [License](#license)

## Introduction
This package provides `FilterTrait` and `SortTrait` to help you filter and sort data dynamically with various operators in Laravel Eloquent.

## Requirements
- PHP >= 8.0
- Laravel >= 9.0

## Installation & Configuration
### 1. Install Package
```bash
composer require laravelwakeup/filter-sort:@dev
```

### 2. Publish Configuration
```bash
php artisan vendor:publish --tag=laravel-filter-sort-config
```

After running the command above, the `laravel-filter-sort.php` config file will be created in your `config/` directory. You can adjust the following settings:
```php
return [
    // Change the operator suffix (default is '_op')
    // Example: status_op=eq -> status$op=eq
    'prefix' => '_op',
];
```

## Basic Usage
### 1. Add Traits to Your Model
```php
use LaravelWakeUp\FilterSort\Traits\FilterTrait;
use LaravelWakeUp\FilterSort\Traits\SortTrait;

class Post extends Model
{
    use FilterTrait, SortTrait;
    
    // Optional: Restrict which fields can be filtered
    protected array $allowedFilters = ['title', 'created_at', 'status', 'deleted_at'];

    // Optional: Restrict which fields can be sorted
    protected array $allowedSorts = ['id', 'created_at'];
}
```
> **Note**: By default, if you don't define or set empty arrays for `$allowedFilters` and `$allowedSorts`, the package will allow filtering and sorting on all table fields.

### 2. Use in Controller
```php
$posts = Post::query()
    ->filter(request())
    ->sort(request())
    ->get();
```

### 4. Sorting
The package provides a simple and flexible way to sort your data. Sorting is applied only when sort parameters are present in the request.

```sh
# Sort by single field
/posts?id_sort=desc

# Sort by multiple fields (applies in order of appearance)
/posts?created_at_sort=desc&id_sort=asc

# Combine with filters
/posts?title=Laravel&status=published&status_op=eq&created_at_sort=desc&id_sort=asc
```

#### Sorting Parameters
For any field you want to sort by (e.g., `id`, `created_at`, `title`), append `_sort` to the field name:
- `{field}_sort`: Set the sort direction
  - `asc` for ascending order (default if invalid value provided)
  - `desc` for descending order

#### Multiple Sort Example
```php
// Sort by created_at DESC, then by id ASC
/posts?created_at_sort=desc&id_sort=asc

// Sort by status DESC, created_at DESC, and id ASC
/posts?status_sort=desc&created_at_sort=desc&id_sort=asc
```

#### Sorting Configuration
You can customize sorting behavior in your model:

1. **Restrict Sortable Fields**
```php
protected array $allowedSorts = ['id', 'created_at', 'title', 'status'];
```

2. **Customize Sort Field Suffix**
You can change the default `_sort` suffix by publishing the config file and modifying the `sort_field_suffix` value:
```php
// config/laravel-filter-sort.php
return [
    'sort_field_suffix' => '_sort'  // Change this to your preferred suffix
];
```

> **Note**: Sorting is only applied when sort parameters are provided in the request. The order of sorting follows the order of parameters in the URL.

## Available Operators
| Operator | Query String | Description |
|---------|-------------|--------|
| `like` (default) | `title=Laravel` | Filter data with LIKE "%Laravel%" |
| `eq`  | `status=published&status_op=eq` | Filter where status = 'published' |
| `gt`  | `created_at=2023-01-01&created_at_op=gt` | Filter where created_at > '2023-01-01' |
| `gte` | `created_at=2023-01-01&created_at_op=gte` | Filter where created_at >= '2023-01-01' |
| `lt`  | `created_at=2023-01-01&created_at_op=lt` | Filter where created_at < '2023-01-01' |
| `lte` | `created_at=2023-01-01&created_at_op=lte` | Filter where created_at <= '2023-01-01' |
| `between` | `created_at=2023-01-01,2023-12-31&created_at_op=between` | Filter data within range |
| `notIn` | `status=draft,pending&status_op=notIn` | Exclude values in the list |
| `in` | `status=draft,pending&status_op=in` | Filter values in the list |
| `null` | `deleted_at=1&deleted_at_op=null` | Filter where field is NULL |
| `notNull` | `deleted_at=1&deleted_at_op=notNull` | Filter where field is NOT NULL |

## Examples
### 1. Basic Search
```sh
# Fuzzy search (LIKE)
/posts?title=Laravel

# Exact match (Equal)
/posts?status=published&status_op=eq

# NULL check
/posts?deleted_at=1&deleted_at_op=null

# NOT NULL check
/posts?deleted_at=1&deleted_at_op=notNull
```

### 2. Range Search
```sh
# Greater than
/posts?created_at=2023-01-01&created_at_op=gt

# Between range
/posts?created_at=2023-01-01,2023-12-31&created_at_op=between
```

### 3. List Search
```sh
# Filter by list (IN)
/posts?status=draft,pending&status_op=in

# Exclude list (NOT IN)
/posts?status=draft,pending&status_op=notIn
```

### 4. Sorting
```sh
# Simple sort
/posts?sort=id&order=asc

# Combined with filters
/posts?title=Laravel&status=1&status_op=notNull&created_at=2023-01-01&created_at_op=gte&sort=created_at&order=desc
```

## JavaScript Usage

### Using with qs library

```javascript
// Installation
// npm install qs
// yarn add qs

// Import
import qs from 'qs';
// or
const qs = require('qs');

// Example filters object
const filters = {
    // Normal filter
    name: 'John',
    name_op: 'like',
    
    // Filter with IN operator
    status: ['active', 'pending'],
    status_op: 'in',
    
    // Filter with BETWEEN operator
    created_at: ['2023-01-01', '2023-12-31'],
    created_at_op: 'between',
    
    // Filter with NULL operator
    deleted_at: '1',
    deleted_at_op: 'null',
    
    // Multiple field sorting
    created_at_sort: 'desc',
    id_sort: 'asc'
};

// Convert object to query string
const queryString = qs.stringify(filters, {
    arrayFormat: 'comma',    // Convert arrays to comma-separated strings
    encode: false            // Don't encode special characters
});
// Result: name=John&name_op=like&status=active,pending&status_op=in&created_at=2023-01-01,2023-12-31&created_at_op=between&deleted_at=1&deleted_at_op=null&created_at_sort=desc&id_sort=asc

// API call with Axios
axios.get(`/api/posts?${queryString}`);

// API call with Fetch
fetch(`/api/posts?${queryString}`);

// API call with jQuery
$.get(`/api/posts?${queryString}`);

// Parse query string back to object
const url = window.location.search; // ?name=John&name_op=like...
const parsed = qs.parse(url, { 
    ignoreQueryPrefix: true,
    comma: true  // Parse comma-separated strings back to arrays
});
console.log(parsed);
// {
//     name: 'John',
//     name_op: 'like',
//     status: ['active', 'pending'],
//     status_op: 'in',
//     created_at: ['2023-01-01', '2023-12-31'],
//     created_at_op: 'between',
//     deleted_at: '1',
//     deleted_at_op: 'null',
//     created_at_sort: 'desc',
//     id_sort: 'asc'
// }
```

### Using URLSearchParams (Browser built-in)

```javascript
// Create a new URLSearchParams instance
const params = new URLSearchParams();

// Add normal filter
params.append('name', 'John');
params.append('name_op', 'like');

// Add filter with IN operator
params.append('status', 'active,pending');  // Use string directly instead of array.join()
params.append('status_op', 'in');

// Add filter with BETWEEN operator
params.append('created_at', '2023-01-01,2023-12-31');  // Use string directly
params.append('created_at_op', 'between');

// Add filter with NULL operator
params.append('deleted_at', '1');
params.append('deleted_at_op', 'null');

// Add sorting
params.append('created_at_sort', 'desc');
params.append('id_sort', 'asc');

// Convert to query string and decode it
const queryString = decodeURIComponent(params.toString());
// Result: name=John&name_op=like&status=active,pending&status_op=in&created_at=2023-01-01,2023-12-31&created_at_op=between&deleted_at=1&deleted_at_op=null&created_at_sort=desc&id_sort=asc

// API calls
// With Fetch
fetch(`/api/posts?${queryString}`);

// With Axios
axios.get(`/api/posts?${queryString}`);

// With jQuery
$.get(`/api/posts?${queryString}`);

```

## License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
