<?php

namespace Message;

class TaskCollection
{
    /**
     * @var Task[]
     */
    private array $tasks;

    /**
     * @param Task[] $tasks
     */
    public function __construct(array $tasks = [])
    {
        $this->tasks = $tasks;
    }

    public function addTask(Task $task): void
    {
        $this->tasks[] = $task;
    }

    public function isEmpty(): bool
    {
        return empty($this->tasks);
    }

    public function getTasks(): array
    {
        return $this->tasks;
    }

    public function toMonospacedText(): string
    {
        $text = "\n";

        foreach ($this->tasks as $task) {
            $text .= "* ". $task->name ." - ". $task->pay . "\n";
        }

        return $text;
    }

    public function toArray(): array
    {
        $toArray = [];

        foreach ($this->tasks as $task) {
            $toArray[] = $task->toArray();
        }

        return $toArray;
    }
}