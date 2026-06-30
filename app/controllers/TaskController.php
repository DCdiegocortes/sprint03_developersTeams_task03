<?php

class TaskController extends ApplicationController
{
    private const VALID_STATUSES = ['PENDING', 'IN_PROGRESS', 'FINISHED'];

    public function createAction()
    {
        $this->view->message = null;
        $this->view->error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userName = trim($_POST['user_name'] ?? '');
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $status = strtoupper(trim($_POST['status'] ?? 'PENDING'));

            if ($userName === '' || $title === '' || $description === '') {
                $this->view->error = 'Todos los campos son obligatorios.';
                return;
            }

            if (!in_array($status, self::VALID_STATUSES, true)) {
                $this->view->error = 'El estado no es válido.';
                return;
            }

            $jsonPath = ROOT_PATH . '/tasks.json';

            if (!file_exists($jsonPath)) {
                file_put_contents($jsonPath, json_encode([], JSON_PRETTY_PRINT));
            }

            $jsonContent = file_get_contents($jsonPath);
            $tasks = json_decode($jsonContent, true);

            if (!is_array($tasks)) {
                $tasks = [];
            }

            $lastId = 0;
            foreach ($tasks as $task) {
                if (isset($task['id']) && $task['id'] > $lastId) {
                    $lastId = $task['id'];
                }
            }

            $newTask = [
                'id' => $lastId + 1,
                'user_name' => $userName,
                'title' => $title,
                'description' => $description,
                'status' => $status,
                'created_at' => date('Y-m-d H:i:s'),
                'finished_at' => ($status === 'FINISHED') ? date('Y-m-d H:i:s') : null
            ];

            $tasks[] = $newTask;

            file_put_contents(
                $jsonPath,
                json_encode($tasks, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            $this->view->message = '✅ Tarea creada correctamente.';
        }
    }
}