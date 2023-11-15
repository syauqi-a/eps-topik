<?php

function encode_string(string $string, ?string $border = '%'): string {
    if ($string == '') {
        return $string;
    }

    $encoded = json_encode($string);

    $pattern = '/^<p>|<\/p>$/';
    $replaced = preg_replace($pattern, '', $encoded);

    return $replaced;
}

function custom_trim(string $string, array $wrapper_tags = ['p']): string {
    foreach ($wrapper_tags as $tag) {
        $pattern = "/<{$tag}><\/{$tag}>/";
        $trimed = preg_replace($pattern, '', trim($string));
    }

    return $trimed;
}