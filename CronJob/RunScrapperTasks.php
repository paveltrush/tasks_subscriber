<?php

require 'vendor/autoload.php';

use Crunz\Schedule;

$schedule = new Schedule();
$task = $schedule->run(PHP_BINARY . ' scrapper.php');
$task->everyThirtyMinutes()
    ->between('09:00', '22:00')
    ->description('Running scrapper script each hour');

return $schedule;