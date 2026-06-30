<?php

class Task extends Model
{
    private $filePath;

    public function __construct()
    {
        $this->filePath = ROOT_PATH . '/tasks.json';
    }

    private function readTasks()
    {
        if (!file_exists($this->filePath)) {
            return [];
        }

        $jsonContent = file_get_contents($this->filePath);
        $tasks = json_decode($jsonContent, true);

        if (!is_array($tasks)) {
            return [];
        }

        return $tasks;
    }

    private function saveTasks($tasks)
    {
        file_put_contents(
            $this->filePath,
            json_encode(array_values($tasks), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    public function getTasks($statusFilter = null, $searchQuery = null)
    {
        $tasks = $this->readTasks();

        if ($statusFilter !== null && $statusFilter !== '') {
            $tasks = array_filter($tasks, function ($task) use ($statusFilter) {
                return isset($task['status']) && $task['status'] === $statusFilter;
            });
        }

        if ($searchQuery !== null && trim($searchQuery) !== '') {
            $searchQuery = trim($searchQuery);

            $tasks = array_filter($tasks, function ($task) use ($searchQuery) {
                return isset($task['title']) && stripos($task['title'], $searchQuery) !== false;
            });
        }

        return array_values($tasks);
    }

    public function getTaskById($id)
    {
        $tasks = $this->readTasks();

        foreach ($tasks as $task) {
            if (isset($task['id']) && (int)$task['id'] === (int)$id) {
                return $task;
            }
        }

        return null;
    }
}