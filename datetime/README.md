# Date and Time Helper

A small JavaScript utility for displaying the time of a server-side event in the visitor's local timezone.

I originally used this approach in web projects where event timestamps were stored in the server's timezone, while visitors needed to see those events according to their own local time.

## Problem

A server-rendered application may know when an event happened, but it does not automatically know the visitor's local timezone.

For example, suppose an event is stored in the database as:

```text
2014-09-10 03:17:00
```

If that value represents server's local time, a visitor in a different timezone should not necessarily see `03:17`.

The browser, however, does know the visitor's local time. The idea behind this helper is therefore to avoid sending the server timezone to the browser at all.

Instead, the server calculates how long ago the event happened and sends that difference to the client.

## How it works

The method has four steps:
1. The server converts the event time to a Unix timestamp.
2. It calculates the difference between the current server time and the event timestamp.
3. That difference, in seconds, is included in the HTML.
4. JavaScript subtracts the difference from the visitor's current local time and formats the result.

```text
event time in database
        │
        ▼
event Unix timestamp
        │
        ▼
current server time minus event time
        │
        ▼
difference in seconds
        │
        ▼
HTML data-time attribute
        │
        ▼
visitor's current local time minus difference
        │
        ▼
event time in the visitor's timezone
```

Because the calculation on the client starts from the browser's current local time, the resulting event time is displayed according to the visitor's timezone.

## Server-side example

The generated HTML can look like this:

```html
<span
    class="fg-time"
    data-time="xxxxx"
    data-format="j F Y, H:i">
    9 September 2014, 23:17 GMT
</span>
```

Here:
- `xxxxx` is the difference in seconds between the current server time and the event timestamp
- `j F Y, H:i` is the desired output format
- the text inside the element is a server-rendered GMT fallback, which can be generated with PHP's `gmdate()`

This means the page still contains a meaningful timestamp before JavaScript replaces it with the visitor-local representation.

## Client-side example

Using jQuery, all timestamps on the page can be converted at once:

```javascript
$('.fg-time').each(function () {
    var time = $(this).data('time'),
        format = $(this).data('format');

    $(this).text(fgDate(format, time));
});
```

`fgDate()` takes the desired format and the elapsed time in seconds:

```javascript
fgDate('j F Y, H:i', time);
```

Internally, it takes the browser's current time and subtracts the supplied difference:

```text
visitor's current time minus elapsed seconds = local event time
```

For example, the same event can therefore be displayed as `03:17` to a visitor in one timezone and `02:17` to a visitor whose local time is one hour behind.

## Date formatting

Besides the timezone conversion approach, `fgDate()` provides PHP `date()`-style formatting in JavaScript.

Supported format characters include:

| Character | Meaning |
| --- | --- |
| `Y` | Four-digit year |
| `y` | Two-digit year |
| `L` | Leap-year flag |
| `n` | Month number |
| `m` | Zero-padded month number |
| `F` | Full month name |
| `M` | Short month name |
| `t` | Number of days in the month |
| `j` | Day of the month |
| `d` | Zero-padded day of the month |
| `S` | Ordinal suffix used by the original implementation |
| `z` | Day of the year |
| `W` | Week number |
| `w` | Day of the week |
| `l` | Full weekday name |
| `D` | Short weekday name |
| `g` | 12-hour format |
| `G` | 24-hour format |
| `h` | Zero-padded 12-hour format |
| `H` | Zero-padded 24-hour format |
| `i` | Minutes |
| `s` | Seconds |
| `a` | `am` / `pm` |
| `A` | `AM` / `PM` |

The original implementation contains Russian month and weekday names.

## Why use a time difference?

The important part of the approach is that the client does not need to know the server's timezone.

The server and browser only exchange the elapsed time between "now" and the event. Both sides can therefore work with their own local clocks while the browser reconstructs the event time relative to the visitor's current time.

This made the approach convenient for server-rendered applications where the visitor's timezone was not otherwise known to the backend.

## Technology

- JavaScript
- PHP on the server side
- jQuery in the original integration

## Project status

Historical utility originally developed in the early 2010s and used in multiple web projects.

It is no longer maintained; the repository preserves the original implementation.

## Author

Valery Kirkizh

[valery@kirkizh.com](mailto:valery@kirkizh.com)
