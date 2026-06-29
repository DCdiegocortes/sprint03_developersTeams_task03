<?php

class Task extends Model
{
    private $filePath;

    private const VALID_STATUSES = ['PENDING', 'IN_PROGRESS', 'FINISHED'];

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

        if ($searchQuery !== null && $searchQuery !== '') {
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
            if (isset($task['id']) && (int) $task['id'] === (int) $id) {
                return $task;
            }
        }

        return null;
    }

    public function updateTask($id, $data)
    {
        if (!$this->isValidTaskData($data)) {
            return false;
        }

        $tasks = $this->readTasks();

        foreach ($tasks as &$task) {
            if (isset($task['id']) && (int) $task['id'] === (int) $id) {
                $status = strtoupper(trim($data['status']));

                $task['title'] = trim($data['title']);
                $task['description'] = trim($data['description']);
                $task['user_name'] = trim($data['user_name']);
                $task['status'] = $status;

                if ($status === 'FINISHED') {
                    $task['finished_at'] = $task['finished_at'] ?? date('Y-m-d H:i:s');
                } else {
                    $task['finished_at'] = null;
                }

                $this->saveTasks($tasks);
                return true;
            }
        }

        return false;
    }

    public function updateTaskStatus($id, $newStatus)
    {
        $newStatus = strtoupper(trim($newStatus));

        if (!in_array($newStatus, self::VALID_STATUSES, true)) {
            return false;
        }

        $tasks = $this->readTasks();

        foreach ($tasks as &$task) {
            if (isset($task['id']) && (int) $task['id'] === (int) $id) {
                $task['status'] = $newStatus;

                if ($newStatus === 'FINISHED') {
                    $task['finished_at'] = $task['finished_at'] ?? date('Y-m-d H:i:s');
                } else {
                    $task['finished_at'] = null;
                }

                $this->saveTasks($tasks);
                return true;
            }
        }

        return false;
    }

    private function isValidTaskData($data)
    {
        $title = trim($data['title'] ?? '');
        $description = trim($data['description'] ?? '');
        $userName = trim($data['user_name'] ?? '');
        $status = strtoupper(trim($data['status'] ?? ''));

        if ($title === '' || $description === '' || $userName === '') {
            return false;
        }

        return in_array($status, self::VALID_STATUSES, true);
    }
}