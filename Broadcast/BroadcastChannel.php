<?php

namespace Broadcast;

use Message\TaskCollection;

interface BroadcastChannel
{
    public function sendTasksNotification(TaskCollection $tasks);

}