<?php

function encode_string(string $string, ?string $wrapp_pattern = null,  string $wrapp_replacement = '%'): string {
    if ($string == '') {
        return $string;
    }

    $encoded = json_encode($string);

    $wrapp_pattern ??= '/^<p>|<\/p>$/';

    if (preg_match('/^\/\^[\s\S]+\|[\s\S]+\$\/[gimsuy]*$/', $wrapp_pattern) == false) {
        throw new Exception('Wrapper pattern (' . $wrapp_pattern . ') is invalid regex pattern!');
    }

    $replaced = preg_replace($wrapp_pattern, $wrapp_replacement, $encoded);

    return $replaced;
}

function custom_trim(string $string, array $wrapper_tags = ['p']): string {
    foreach ($wrapper_tags as $tag) {
        $pattern = "/<{$tag}><\/{$tag}>/";
        $string = preg_replace($pattern, '', trim($string));
    }

    return $string;
}