<?php

class TestController extends ApplicationController
{
    public function indexAction()
    {
        $model = new Task();

        $statusFilter = $_GET['status_filter'] ?? null;
        $searchQuery = trim($_GET['search'] ?? '');

        if ($statusFilter === '') {
            $statusFilter = null;
        }

        if ($searchQuery === '') {
            $searchQuery = null;
        }

        $this->view->tasks = $model->getTasks($statusFilter, $searchQuery);
        $this->view->current_status = $statusFilter;
        $this->view->current_search = $searchQuery;
    }

    public function checkAction()
    {
        echo "hello from test::check";
    }
}