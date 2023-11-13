<?php

function encode_string(string $string, ?string $border = '%'): string {
    if ($string == '') {
        return $string;
    }

    $encoded = json_encode($string);
    $encoded = substr_replace($encoded, $border, -1);  // replace last char
    $encoded = substr_replace($encoded, $border, 0, 1);  // replace first char

    return $encoded;
}