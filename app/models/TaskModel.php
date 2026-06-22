<?php

class TaskModel {
    private $jsonPath;

    public function __construct() {
        $this->jsonPath = __DIR__ . '/../../config/tasks.json';
    }

    private function readJson() {
        if (!file_exists($this->jsonPath)) {
            return [];
        }
        $content = file_get_contents($this->jsonPath);
        return json_decode($content, true) ?? [];
    }

    public function getTasks($statusFilter = null, $searchQuery = null) {
        $tasks = $this->readJson();

        if (!empty($statusFilter)) {
            $tasks = array_filter($tasks, function($task) use ($statusFilter) {
                return $task['status'] === $statusFilter;
            });
        }

        if (!empty($searchQuery)) {
            $tasks = array_filter($tasks, function($task) use ($searchQuery) {
                return stripos($task['title'], $searchQuery) !== false;
            });
        }

        usort($tasks, function($a, $b) {
            return $b['id'] - $a['id'];
        });

        return array_values($tasks);
    }
}