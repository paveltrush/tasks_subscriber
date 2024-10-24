<?php

namespace Cache;

interface Cache
{
    public static function store(string $key, $value): bool;
    public static function get(string $key);

    public static function delete(string $key): bool;

    public static function exists(string $key): bool;
}