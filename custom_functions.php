<?php
function array_diff_2d($array1, $array2): array
{
    $diff = [];

    foreach ($array1 as $key => $value1) {
        if (!in_array($value1, $array2)) {
            $diff[$key] = $value1;
        }
    }

    return $diff;
}