# Database Helper

A compact PHP helper for working with MySQL through `mysqli`.

I originally built and used this utility across PHP web projects between 2011 and 2021. It was inspired by DbSimple library, but kept as a small single-file implementation with a simplified interface and support for the PHP 5 and PHP 7.

## Features

- Single-file `mysqli` wrapper
- Convenient methods for selecting rows, single rows and scalar values
- Typed SQL placeholders for values, identifiers, arrays and raw fragments
- Conditional query fragments using `{ ... }` blocks and `DB::SKIP`
- Query result transformations into associative arrays and trees
- Pagination helper using `SQL_CALC_FOUND_ROWS` and `FOUND_ROWS()`
- Transaction helpers
- Query execution timing
- Debug output for generated SQL
- Exceptions that include the SQL query and original call site

## Basic usage

```php
$db = new DB([
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'name' => 'example',
]);

$users = $db->select(
    'SELECT * FROM users WHERE status = ?s AND id IN (?a)',
    'active',
    [10, 20, 30]
);

$user = $db->selectRow(
    'SELECT * FROM users WHERE id = ?d',
    10
);

$count = $db->selectCell(
    'SELECT COUNT(*) FROM users'
);
```

## Query methods

### `select()`

Returns the complete result as an array of rows.

```php
$rows = $db->select(
    'SELECT id, email FROM users WHERE status = ?s',
    'active'
);
```

### `selectRow()`

Returns the first result row.

```php
$user = $db->selectRow(
    'SELECT * FROM users WHERE id = ?d',
    42
);
```

### `selectCell()`

Returns the first column of the first result row.

```php
$total = $db->selectCell(
    'SELECT COUNT(*) FROM users'
);
```

### `selectPage()`

Runs a paginated query and also returns the total number of matching rows.

```php
$total = 0;

$rows = $db->selectPage(
    $total,
    'SELECT * FROM users ORDER BY id DESC LIMIT ?d, ?d',
    0,
    20
);
```

Internally, the helper adds `SQL_CALC_FOUND_ROWS` to the `SELECT` query and then executes `SELECT FOUND_ROWS()`.

### `query()`

Executes non-`SELECT` queries.

```php
$id = $db->query(
    'INSERT INTO users SET ?a',
    [
        'email' => 'user@example.com',
        'status' => 'active',
    ]
);
```

For inserts, the method returns the generated insert ID when available. Otherwise it returns the number of affected rows.

## Placeholders

The helper supports several placeholder types.

| Placeholder | Purpose |
| --- | --- |
| `?` | Escaped scalar value |
| `?s` | Escaped string without surrounding quotes |
| `?d` | Integer |
| `?f` | Floating-point value |
| `?b` | Boolean |
| `?n` | Nullable integer-like value |
| `?l` | Escaped value for `LIKE` expressions |
| `?a` | Array of values or associative `field = value` pairs |
| `?#` | SQL identifier or list of identifiers |
| `?_` | Configured table prefix |
| `?x` | Raw SQL fragment |

Example:

```php
$db->query(
    'UPDATE users SET ?a WHERE id = ?d',
    [
        'email' => 'new@example.com',
        'status' => 'active',
    ],
    42
);
```

## Conditional query fragments

Blocks inside `{ ... }` can be conditionally removed by passing `DB::SKIP` to one of their placeholders.

```php
$status = $filterByStatus ? 'active' : DB::SKIP;

$rows = $db->select(
    'SELECT * FROM users WHERE 1 { AND status = ? }',
    $status
);
```

If a placeholder inside the block receives `DB::SKIP`, the whole block is omitted from the generated SQL.

This was useful for building dynamic queries without concatenating many SQL fragments manually.

## Result transformations

Special aliases in a `SELECT` result can change the shape of the returned PHP array.

### Associative arrays

Columns beginning with `ARRAY_KEY` are used as array keys.

```sql
SELECT
    id AS ARRAY_KEY,
    email,
    status
FROM users
```

The result becomes an associative array indexed by user ID.

Multiple `ARRAY_KEY...` columns can be used to create nested arrays.

### Trees

A result containing both an `ARRAY_KEY...` column and a `PARENT_KEY...` column is transformed into a tree.

Child rows are added to a `childNodes` array.

This was useful for hierarchical data such as categories and nested structures.

## Transactions

```php
$db->transaction();

try {
    $db->query(...);
    $db->query(...);

    $db->commit();
} catch (Exception $e) {
    $db->rollback();
    throw $e;
}
```

## Debugging and errors

Calling:

```php
$db->debug();
```

prints the next generated SQL query before execution.

Database and validation errors are reported through `DB_Exception`. The exception includes the generated SQL and attempts to point to the original caller rather than only the internal database helper.

## Configuration

The constructor accepts:

```php
[
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'name' => 'database',
    'pref' => '',
]
```

`pref` is available through the `?_` placeholder and can be used for table prefixes.

## Technology

- PHP 5.x-7.x
- MySQL / mysqli

## Background

This helper was based conceptually on the older DbSimple library by Dmitry Koterov.

I kept the ideas I found useful: typed placeholders, conditional query fragments and flexible result transformations, but maintained a compact single-file implementation for my own web projects.

## Project status

Historical utility used across PHP web projects between 2011 and 2021.

It is no longer maintained; the repository preserves the original implementation.

## Author

Valery Kirkizh

[valery@kirkizh.com](mailto:valery@kirkizh.com)
