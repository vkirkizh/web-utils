# Russian Pluralization

A small PHP helper for selecting the correct Russian word form based on a number.

In English, displaying a count often requires little more than choosing between singular and plural forms. Russian commonly requires three different forms, so output such as `2 комментариев` or `3 товаров` is grammatically incorrect.

This utility encapsulates the numeric rule in a single function.

## Usage

```php
sklonyalka($n, $v1, $v2, $v5)
```

Parameters:

- `$n` — the number
- `$v1` — word form used with 1
- `$v2` — word form used with 2
- `$v5` — word form used with 5

For example:

```php
echo $itemsNumber . ' ' . sklonyalka(
    $itemsNumber,
    'товар',
    'товара',
    'товаров'
);
```

Produces:

```text
1 товар
2 товара
5 товаров
21 товар
24 товара
100 товаров
```

Another example:

```php
echo $n . ' ' . sklonyalka(
    $n,
    'комментарий',
    'комментария',
    'комментариев'
);
```

```text
1 комментарий
21 комментарий
142 комментария
18535 комментариев
```

## How it works

The function selects one of three supplied word forms based on the last one or two digits of the number.

Numbers ending in `1` normally use the first form, numbers ending in `2`–`4` use the second form and the remaining numbers use the third form.

The `11`–`20` range is handled as an exception and always uses the third form.

The complete implementation is intentionally compact:

```php
function sklonyalka($n, $v1, $v2, $v5)
{
    return $n % 100 < 10 || $n % 100 > 20
        ? ($n % 10 == 1
            ? $v1
            : ($n % 10 >= 2 && $n % 10 <= 4 ? $v2 : $v5))
        : $v5;
}
```

## JavaScript version

The same rule can be implemented directly in JavaScript:

```javascript
function sklonyalka(n, v1, v2, v5)
{
    return n % 100 < 10 || n % 100 > 20
        ? (n % 10 == 1
            ? v1
            : (n % 10 >= 2 && n % 10 <= 4 ? v2 : v5))
        : v5;
}
```

## Technology

- PHP
- JavaScript

## Project status

Historical utility originally used in web projects during the 2010s.

It is no longer maintained; the repository preserves the original implementation.

## Author

Valery Kirkizh

[valery@kirkizh.com](mailto:valery@kirkizh.com)
