<?php

use Illuminate\Support\Str;

if(!function_exists('posix_compat_name')) {
    /**
     * Ensures a file or directory name is posix compliant.
     *
     * @param  string  $text The text that we want to ensure is posix compatible.
     *
     * @return string
     */
    function posix_compat_name(string $text): string
    {
        return Str::slug(Str::lower($text));
    }
}

if(!function_exists('strip_newlines')) {
    /**
     * Strip newlines from a string.
     *
     * @param  string|array  $text The text to strip newlines from.
     *
     * return string|array
     */
    function strip_newlines(string|array $text): string|array
    {
        return str_replace("\n", "", $text);
    }
}
