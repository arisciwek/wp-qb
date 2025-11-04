# WP Query Builder

Eloquent-like Query Builder untuk WordPress. Library ini menyediakan interface yang fluent dan ekspresif untuk membangun dan menjalankan database queries pada custom tables WordPress.

## Features

- ✅ Fluent method chaining interface
- ✅ SELECT dengan columns, distinct, aggregate functions
- ✅ WHERE clauses (where, orWhere, whereIn, whereBetween, whereNull, dll)
- ✅ JOIN support (inner, left, right, cross join)
- ✅ GROUP BY, HAVING
- ✅ ORDER BY, LIMIT, OFFSET
- ✅ INSERT, UPDATE, DELETE operations
- ✅ Raw expressions
- ✅ Collection helper untuk hasil query
- ✅ SQL injection safe (prepared statements)
- ✅ Debug helpers (toSql(), dump(), dd())

## Installation

1. Clone atau copy plugin ke `wp-content/plugins/wp-qb`
2. Run composer install:

```bash
cd wp-content/plugins/wp-qb
composer install
```

3. Aktifkan plugin dari WordPress admin

## Usage

### Basic Select

```php
use WPQB\QueryBuilder;

// Get all records
$customers = QueryBuilder::table('wp_customers')->get();

// Select specific columns
$customers = QueryBuilder::table('wp_customers')
    ->select('id', 'name', 'email')
    ->get();

// Get first record
$customer = QueryBuilder::table('wp_customers')
    ->where('id', 1)
    ->first();

// Find by ID
$customer = QueryBuilder::table('wp_customers')->find(1);
```

### Where Clauses

```php
// Basic where
QueryBuilder::table('customers')
    ->where('status', 'active')
    ->get();

// Where with operator
QueryBuilder::table('customers')
    ->where('age', '>', 18)
    ->get();

// Multiple where
QueryBuilder::table('customers')
    ->where('status', 'active')
    ->where('age', '>', 18)
    ->get();

// Or where
QueryBuilder::table('customers')
    ->where('status', 'active')
    ->orWhere('type', 'premium')
    ->get();

// Where In
QueryBuilder::table('customers')
    ->whereIn('type', ['premium', 'gold', 'platinum'])
    ->get();

// Where Not In
QueryBuilder::table('customers')
    ->whereNotIn('status', ['banned', 'suspended'])
    ->get();

// Where Between
QueryBuilder::table('customers')
    ->whereBetween('age', [18, 65])
    ->get();

// Where Null
QueryBuilder::table('customers')
    ->whereNull('deleted_at')
    ->get();

// Where Not Null
QueryBuilder::table('customers')
    ->whereNotNull('email_verified_at')
    ->get();

// Raw where
QueryBuilder::table('customers')
    ->whereRaw('YEAR(created_at) = %d', [2024])
    ->get();
```

### Joins

```php
// Inner Join
QueryBuilder::table('customers')
    ->join('orders', 'customers.id', '=', 'orders.customer_id')
    ->select('customers.*', 'orders.total')
    ->get();

// Left Join
QueryBuilder::table('customers')
    ->leftJoin('orders', 'customers.id', '=', 'orders.customer_id')
    ->get();

// Multiple Joins
QueryBuilder::table('orders')
    ->join('customers', 'orders.customer_id', '=', 'customers.id')
    ->join('products', 'orders.product_id', '=', 'products.id')
    ->get();
```

### Ordering, Grouping, Limit

```php
// Order By
QueryBuilder::table('customers')
    ->orderBy('created_at', 'DESC')
    ->get();

// Multiple Order By
QueryBuilder::table('customers')
    ->orderBy('status')
    ->orderBy('created_at', 'DESC')
    ->get();

// Group By
QueryBuilder::table('orders')
    ->select('customer_id', 'COUNT(*) as total_orders')
    ->groupBy('customer_id')
    ->get();

// Having
QueryBuilder::table('orders')
    ->select('customer_id', 'COUNT(*) as total_orders')
    ->groupBy('customer_id')
    ->having('COUNT(*)', '>', 5)
    ->get();

// Limit & Offset
QueryBuilder::table('customers')
    ->limit(10)
    ->offset(20)
    ->get();

// Alias: take & skip
QueryBuilder::table('customers')
    ->take(10)
    ->skip(20)
    ->get();
```

### Aggregate Functions

```php
// Count
$total = QueryBuilder::table('customers')->count();

// Count with where
$active = QueryBuilder::table('customers')
    ->where('status', 'active')
    ->count();

// Max, Min, Sum, Avg
$maxAge = QueryBuilder::table('customers')->max('age');
$minAge = QueryBuilder::table('customers')->min('age');
$totalRevenue = QueryBuilder::table('orders')->sum('total');
$avgOrder = QueryBuilder::table('orders')->avg('total');
```

### Insert

```php
// Insert single record
$id = QueryBuilder::table('customers')->insert([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'status' => 'active'
]);

// Insert multiple records
QueryBuilder::table('customers')->insertMultiple([
    ['name' => 'John', 'email' => 'john@example.com'],
    ['name' => 'Jane', 'email' => 'jane@example.com']
]);
```

### Update

```php
// Update records
QueryBuilder::table('customers')
    ->where('id', 1)
    ->update([
        'status' => 'inactive',
        'updated_at' => current_time('mysql')
    ]);

// Update multiple records
QueryBuilder::table('customers')
    ->where('status', 'pending')
    ->update(['status' => 'active']);
```

### Delete

```php
// Delete records
QueryBuilder::table('customers')
    ->where('status', 'banned')
    ->delete();

// Delete by ID
QueryBuilder::table('customers')
    ->where('id', 1)
    ->delete();
```

### Exists

```php
// Check if records exist
if (QueryBuilder::table('customers')->where('email', 'john@example.com')->exists()) {
    // Email already exists
}

// Check if records don't exist
if (QueryBuilder::table('customers')->where('email', 'john@example.com')->doesntExist()) {
    // Email available
}
```

### Collection Methods

Results dikembalikan sebagai `Collection` object dengan helper methods:

```php
$customers = QueryBuilder::table('customers')->get();

// Get all as array
$array = $customers->all();

// Get first/last
$first = $customers->first();
$last = $customers->last();

// Count
$count = $customers->count();

// Check if empty
if ($customers->isEmpty()) {
    // No results
}

// Filter
$active = $customers->filter(function($customer) {
    return $customer->status === 'active';
});

// Map
$names = $customers->map(function($customer) {
    return $customer->name;
});

// Pluck
$emails = $customers->pluck('email');
$emailById = $customers->pluck('email', 'id');

// Convert to JSON
$json = $customers->toJson();
```

### Debugging

```php
// Get SQL query
$sql = QueryBuilder::table('customers')
    ->where('status', 'active')
    ->toSql();
// Result: "SELECT * FROM customers WHERE status = %s"

// Get bindings
$bindings = QueryBuilder::table('customers')
    ->where('status', 'active')
    ->getBindings();
// Result: ['active']

// Dump query (without dying)
QueryBuilder::table('customers')
    ->where('status', 'active')
    ->dump()
    ->get();

// Dump and die
QueryBuilder::table('customers')
    ->where('status', 'active')
    ->dd();
```

## Advanced Examples

### Complex Query Example

```php
$results = QueryBuilder::table('wp_customers')
    ->select('customers.id', 'customers.name', 'COUNT(orders.id) as total_orders', 'SUM(orders.total) as revenue')
    ->leftJoin('wp_orders', 'customers.id', '=', 'orders.customer_id')
    ->where('customers.status', 'active')
    ->whereNotNull('customers.email_verified_at')
    ->whereBetween('customers.created_at', ['2024-01-01', '2024-12-31'])
    ->groupBy('customers.id')
    ->having('COUNT(orders.id)', '>', 5)
    ->orderBy('revenue', 'DESC')
    ->limit(10)
    ->get();
```

### Pagination Example

```php
$page = 1;
$perPage = 20;

$customers = QueryBuilder::table('customers')
    ->where('status', 'active')
    ->orderBy('created_at', 'DESC')
    ->limit($perPage)
    ->offset(($page - 1) * $perPage)
    ->get();

$total = QueryBuilder::table('customers')
    ->where('status', 'active')
    ->count();

$totalPages = ceil($total / $perPage);
```

## Integration dengan Model

Query Builder ini dirancang untuk digunakan sebagai foundation library. Anda bisa extend untuk membuat Model class:

```php
// Example di plugin lain (wp-customer)
class Customer extends BaseModel {
    protected $table = 'wp_customers';

    public function orders() {
        return QueryBuilder::table('wp_orders')
            ->where('customer_id', $this->id)
            ->get();
    }
}
```

## Requirements

- PHP >= 7.4
- WordPress >= 5.0
- Composer (untuk autoloading)

## License

MIT License

## Author

Your Name

## Contributing

Contributions are welcome! Silakan buat PR atau issue di repository.
