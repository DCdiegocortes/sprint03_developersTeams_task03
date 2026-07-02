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
                if (isset($task['id']) && (int) $task['id'] > $lastId) {
                    $lastId = (int) $task['id'];
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

    public function editAction()
    {
        $this->view->message = null;
        $this->view->error = null;
        $this->view->task = null;

        $id = $_POST['id'] ?? $_GET['id'] ?? null;

        if ($id === null || !is_numeric($id)) {
            $this->view->error = 'ID de tarea inválido.';
            return;
        }

        $id = (int) $id;
        $model = new Task();

        $task = $model->getTaskById($id);

        if ($task === null) {
            $this->view->error = 'No se encontró la tarea indicada.';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $userName = trim($_POST['user_name'] ?? '');
            $status = strtoupper(trim($_POST['status'] ?? 'PENDING'));

            if ($title === '' || $description === '' || $userName === '') {
                $this->view->error = 'Todos los campos son obligatorios.';
                $this->view->task = $task;
                return;
            }

            if (!in_array($status, self::VALID_STATUSES, true)) {
                $this->view->error = 'El estado no es válido.';
                $this->view->task = $task;
                return;
            }

            $updated = $model->updateTask($id, [
                'title' => $title,
                'description' => $description,
                'user_name' => $userName,
                'status' => $status
            ]);

            if (!$updated) {
                $this->view->error = 'No se pudo actualizar la tarea.';
                $this->view->task = $task;
                return;
            }

            $this->view->message = '✅ Tarea actualizada correctamente.';
            $this->view->task = $model->getTaskById($id);
            return;
        }

        $this->view->task = $task;
    }

    public function playAction()
    {
        $id = $_GET['id'] ?? null;

        if ($id !== null && is_numeric($id)) {
            $model = new Task();
            $model->updateTaskStatus((int) $id, 'IN_PROGRESS');
        }

        header('Location: /test');
        exit;
    }

    public function finishAction()
    {
        $id = $_GET['id'] ?? null;

        if ($id !== null && is_numeric($id)) {
            $model = new Task();
            $model->updateTaskStatus((int) $id, 'FINISHED');
        }

        header('Location: /test');
        exit;
    }

    public function deleteAction()
    {
        $this->view->message = null;
        $this->view->error = null;
        $this->view->task = null;

        $id = $_POST['id'] ?? $_GET['id'] ?? null;

        if ($id === null || !is_numeric($id)) {
            $this->view->error = 'ID de tarea inválido.';
            return;
        }

        $id = (int) $id;
        $jsonPath = ROOT_PATH . '/tasks.json';

        if (!file_exists($jsonPath)) {
            $this->view->error = 'No existe el archivo de tareas.';
            return;
        }

        $jsonContent = file_get_contents($jsonPath);
        $tasks = json_decode($jsonContent, true);

        if (!is_array($tasks)) {
            $this->view->error = 'El archivo de tareas no tiene un formato válido.';
            return;
        }

        $taskIndex = null;

        foreach ($tasks as $index => $task) {
            if (isset($task['id']) && (int) $task['id'] === $id) {
                $taskIndex = $index;
                break;
            }
        }

        if ($taskIndex === null) {
            $this->view->error = 'No se encontró la tarea indicada.';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $deletedTaskTitle = $tasks[$taskIndex]['title'] ?? '';

            unset($tasks[$taskIndex]);

            file_put_contents(
                $jsonPath,
                json_encode(array_values($tasks), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            $this->view->message = $deletedTaskTitle !== ''
                ? 'La tarea "' . $deletedTaskTitle . '" fue eliminada correctamente.'
                : 'La tarea fue eliminada correctamente.';

            $this->view->task = null;
            return;
        }

        $this->view->task = $tasks[$taskIndex];
    }
}