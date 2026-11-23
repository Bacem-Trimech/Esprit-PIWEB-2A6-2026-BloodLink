<?php
/**
 * Server-side input validation (the JavaScript in assets/js/validation.js repeats the same rules for comfort,
 * but only these PHP checks protect the application). Each method returns an error message or null.
 */
class Validator
{
    public static function username(string $v): ?string
    {
        return preg_match('/^[a-zA-Z0-9_\-]{3,50}$/', $v) ? null : 'Username must be 3 to 50 characters (letters, digits, _ or -).';
    }

    public static function email(string $v): ?string
    {
        return (filter_var($v, FILTER_VALIDATE_EMAIL) && strlen($v) <= 150) ? null : 'Invalid email address.';
    }

    public static function password(string $v): ?string
    {
        return strlen($v) >= 8 ? null : 'Password must be at least 8 characters long.';
    }

    public static function city(string $v): ?string
    {
        return ($v === '' || preg_match('/^[\p{L}\s\'\-]{2,80}$/u', $v)) ? null : 'City must be 2 to 80 letters.';
    }

    public static function birthDate(string $v, bool $required = false): ?string
    {
        if ($v === '') return $required ? 'Date of birth is required.' : null;
        $d = DateTime::createFromFormat('!Y-m-d', $v);
        if (!$d || $d->format('Y-m-d') !== $v) return 'Invalid date of birth.';
        $diff = $d->diff(new DateTime('today'));
        if ($diff->invert) return 'Date of birth cannot be in the future.';
        if ($diff->y < MIN_AGE) return 'You must be at least ' . MIN_AGE . ' years old.';
        return null;
    }

    /** Free text with a length range (names, notes...). Empty is allowed only when $min is 0. */
    public static function text(string $v, int $min, int $max, string $label): ?string
    {
        $len = mb_strlen($v);
        return ($len >= $min && $len <= $max) ? null : $label . ' must be ' . ($min > 0 ? "$min to " : 'at most ') . "$max characters.";
    }

    public static function quantity(string $v): ?string
    {
        return (ctype_digit($v) && (int)$v <= 100000) ? null : 'Quantity must be a whole number between 0 and 100000.';
    }

    public static function dateRange(string $start, string $end): ?string
    {
        $a = DateTime::createFromFormat('!Y-m-d', $start);
        $b = DateTime::createFromFormat('!Y-m-d', $end);
        if (!$a || !$b || $a->format('Y-m-d') !== $start || $b->format('Y-m-d') !== $end) return 'Start and end dates are required.';
        return $b < $a ? 'The end date cannot be before the start date.' : null;
    }
}
