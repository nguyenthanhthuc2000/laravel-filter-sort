
# Filter and sort package for Laravel

Filter Sort is a powerful Laravel package that supports searching and filtering saving your development time.


## Installation

Install this package with composer

```bash
    composer require laravelwakeup/filter-sort:@dev
```

Configure your model

```bash
    use LaravelWakeUp\FilterSort\Traits\FilterTrait;
    use LaravelWakeUp\FilterSort\Traits\SortTrait;

    class Category extends Model
    {
        use FilterTrait, SortTrait;
    }
```

Publish configuration (optional)

```bash
    php artisan vendor:publish --tag=laravel-filter-sort-config
```

If you want to constrain fields that can be Filtered, in your model...

```bash
    protected $allowedFilters = ['name'];
```

If you want to limit the fields that can be Sorted, in your model...

```bash
    protected $allowedSorts = ['name'];
```

Get started

```bash
   YourModel::filter($request)->sort($request)->get();
```
## Using 

#### Filter (only LIKE)

```http
  YourModel::filter($request)->get();
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| **{field}** | **value** | Filter by value of column {field} |

#### Sort, default parameter is `id` and `asc`

```http
  YourModel::sort($request)->get();
```

| Parameter | Sample value     | Description                       |
| :-------- | :------- | :-------------------------------- |
| **sort**      | **name** | Column to sort |
| **order**      | **asc** or **desc** | Sort order |


## License

[MIT](https://choosealicense.com/licenses/mit/)

