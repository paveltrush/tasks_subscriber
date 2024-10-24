<?php

namespace Message;

use Illuminate\Support\Fluent;

/**
 * @property string $name
 * @property string $id
 * @property string $pay
 */
class Task extends Fluent
{
    public function __construct($attributes)
    {
        parent::__construct(['name' => $attributes['name'], 'id' => $attributes['id'], 'pay' => $attributes['pay']]);
    }
}