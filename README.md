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
- [Testing](#testing)
- [Contributing](#contributing)
- [Security](#security)
- [Credits](#credits)
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

## Testing
```bash
composer test
```

## Contributing
Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security
If you discover any security related issues, please email nguyenthanhthuc.2k@gmail.com instead of using the issue tracker.

## Credits
- [LaravelWakeUp](https://github.com/nguyenthanhthuc20000)
- [All Contributors](../../contributors)

## License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

