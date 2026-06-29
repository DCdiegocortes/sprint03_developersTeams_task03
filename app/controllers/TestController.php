<?php

class TestController extends ApplicationController
{
    private const VALID_STATUSES = ['PENDING', 'IN_PROGRESS', 'FINISHED'];

    public function indexAction()
    {
        $model = new Task();

        $editId = $_GET['edit_id'] ?? null;
        $statusFilter = $_GET['status_filter'] ?? null;
        $searchQuery = trim($_GET['search'] ?? '');

        $this->view->current_status = $statusFilter;
        $this->view->current_search = $searchQuery;
        $this->view->update_message = $_GET['updated'] ?? null;
        $this->view->update_error = $_GET['error'] ?? null;

        $this->view->tasks = $model->getTasks(
            $statusFilter !== '' ? $statusFilter : null,
            $searchQuery !== '' ? $searchQuery : null
        );

        $this->view->editing_task = null;

        if ($editId !== null && is_numeric($editId)) {
            $this->view->editing_task = $model->getTaskById((int) $editId);
        }
    }

    public function updateAction()
    {
        $taskId = $_GET['id'] ?? null;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $taskId === null || !is_numeric($taskId)) {
            header('Location: /test?error=invalid_request');
            exit;
        }

        $title = trim($_POST['profile_title'] ?? '');
        $description = trim($_POST['profile_description'] ?? '');
        $userName = trim($_POST['profile_user_name'] ?? '');
        $status = strtoupper(trim($_POST['profile_status'] ?? 'PENDING'));

        if ($title === '' || $description === '' || $userName === '') {
            header('Location: /test?edit_id=' . urlencode((string) $taskId) . '&error=empty_fields');
            exit;
        }

        if (!in_array($status, self::VALID_STATUSES, true)) {
            header('Location: /test?edit_id=' . urlencode((string) $taskId) . '&error=invalid_status');
            exit;
        }

        $model = new Task();

        $updated = $model->updateTask((int) $taskId, [
            'title' => $title,
            'description' => $description,
            'user_name' => $userName,
            'status' => $status
        ]);

        if (!$updated) {
            header('Location: /test?edit_id=' . urlencode((string) $taskId) . '&error=not_found');
            exit;
        }

        header('Location: /test?edit_id=' . urlencode((string) $taskId) . '&updated=1');
        exit;
    }

    public function playAction()
    {
        $taskId = $_GET['id'] ?? null;

        if ($taskId !== null && is_numeric($taskId)) {
            $model = new Task();
            $model->updateTaskStatus((int) $taskId, 'IN_PROGRESS');
        }

        header('Location: /test');
        exit;
    }

    public function finishAction()
    {
        $taskId = $_GET['id'] ?? null;

        if ($taskId !== null && is_numeric($taskId)) {
            $model = new Task();
            $model->updateTaskStatus((int) $taskId, 'FINISHED');
        }

        header('Location: /test');
        exit;
    }
}