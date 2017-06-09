# Phone Normalization

A small PHP utility for validating and normalizing Russian and Kazakhstan phone numbers entered in common user-facing formats.

The function was designed for web forms where users should be able to enter a phone number naturally instead of being forced to match one exact formatting convention.

## Problem

The same phone number can be entered in many different ways:

```text
+79211234567
+7 921 123 45 67
+7 (921) 123-45-67
89211234567
921 123 45 67
```

Requiring users to guess one specific format makes forms unnecessarily difficult to use.

This helper accepts these variations and converts valid input to one consistent representation suitable for application logic or database storage:

```text
+7xxxxxxxxxx
```

Invalid input returns `false`.

## Usage

```php
$phone = validate_russian_phone_number('+7 (921) 123-45-67');

if ($phone === false) {
    // Invalid phone number
} else {
    echo $phone;
}
```

Result:

```text
+79211234567
```

Other accepted examples:

```php
validate_russian_phone_number('+79211234567');
validate_russian_phone_number('+7 921 123 45 67');
validate_russian_phone_number('89211234567');
validate_russian_phone_number('921 123 45 67');
```

All of them are normalized to:

```text
+79211234567
```

## How it works

The function:
1. Trims the input.
2. Removes formatting characters while preserving digits and `+`.
3. Accepts numbers beginning with `+7`, `7`, `8` or directly with the remaining 10 digits.
4. Normalizes the prefix to `+7`.
5. Removes any remaining non-digit characters from the number body.
6. Verifies that the final value has exactly 10 digits after `+7`.

If the normalized value does not match the expected format, the function returns `false`.

## Scope

The utility was written specifically for phone numbers using the `+7` numbering format used by Russia and Kazakhstan at the time.

It performs structural validation and normalization only. It does not verify whether a number is actually assigned, reachable or belongs to a particular mobile or geographic range.

## Technology

- PHP
- Regular expressions

## Project status

Historical utility originally used in web projects during the 2010s.

It is no longer maintained; the repository preserves the original implementation.

## Author

Valery Kirkizh

[valery@kirkizh.com](mailto:valery@kirkizh.com)
