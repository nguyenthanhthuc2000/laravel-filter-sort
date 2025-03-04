# Filter Sort Scope for Laravel

## Giới thiệu
Package này cung cấp một trait `FilterTrait` và `SortTrait` để giúp lọc và sort dữ liệu linh động với nhiều toán tử khác nhau trong Laravel Eloquent.

## Cách sử dụng
### Cài đặt

```bash
composer require laravelwakeup/filter-sort:@dev
```

1. Thêm trait `FilterTrait` và `SortTrait` vào Model của bạn:
```php
use LaravelWakeUp\FilterSort\Traits\FilterTrait;
use LaravelWakeUp\FilterSort\Traits\SortTrait;

class Post extends Model
{
    use FilterTrait, SortTrait;
    
    protected array $allowedFilters = ['title', 'created_at', 'status'];

    protected array $allowedSorts = ['id'];
}
```
Mặc định nếu không thêm hoặc thêm `$allowedFilters` và `$allowedSorts` với mảng rỗng sẽ cho phép filter và sort tất cả các trường trong bảng của bạn. Ngược lại sẽ giới hạn các trường hợp lệ.

2. Sử dụng `scopeFilter` và `scopeSort` trong Query Builder:
```php
$posts = Post::query()->filter(request())->sort(request())->get();
```

## Cấu trúc Query String
Bạn có thể truyền các tham số vào query string để lọc dữ liệu linh hoạt:

### Toán tử hỗ trợ:
| Toán tử | Query String | Mô tả |
|---------|-------------|--------|
| `like` (mặc định) | `title=Laravel` | Lọc dữ liệu với LIKE "%Laravel%" |
| `eq`  | `status=published&status_op=eq` | Lọc dữ liệu với status = 'published' |
| `gt`  | `created_at=2023-01-01&created_at_op=gt` | Lọc dữ liệu với created_at > '2023-01-01' |
| `gte` | `created_at=2023-01-01&created_at_op=gte` | Lọc dữ liệu với created_at >= '2023-01-01' |
| `lt`  | `created_at=2023-01-01&created_at_op=lt` | Lọc dữ liệu với created_at < '2023-01-01' |
| `lte` | `created_at=2023-01-01&created_at_op=lte` | Lọc dữ liệu với created_at <= '2023-01-01' |
| `between` | `created_at=2023-01-01,2023-12-31&created_at_op=between` | Lọc dữ liệu trong khoảng |
| `notin` | `status=draft,pending&status_op=notin` | Loại bỏ các giá trị trong danh sách |

### Ví dụ sử dụng
#### 1. Tìm kiếm gần đúng (LIKE)
```sh
/posts?title=Laravel
```
#### 2. Tìm kiếm chính xác (Equal)
```sh
/posts?status=published&status_op=eq
```
#### 3. Tìm kiếm lớn hơn / nhỏ hơn
```sh
/posts?created_at=2023-01-01&created_at_op=gt
```
#### 4. Tìm kiếm theo khoảng (Between)
```sh
/posts?created_at=2023-01-01,2023-12-31&created_at_op=between
```
#### 5. Lọc dữ liệu không nằm trong danh sách (Not In)
```sh
/posts?status=draft,pending&status_op=notin
```
#### 6. Sort
```sh
/posts?sort=id&order=asc
```
| Tham số | Giá trị mẩu    | Mô tả  |
| :-------- | :------- | :-------------------------------- |
| **sort**      | **id** | Tên cột trong bảng |
| **order**      | **asc** or **desc** | Điều kiện sort |




## License

[MIT](https://choosealicense.com/licenses/mit/)

