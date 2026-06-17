<?php

class TestController extends ApplicationController
{
    public function indexAction()
    {
        // Instanciamos el modelo. El framework buscará automáticamente "TaskModel.class.php"
        $taskModel = new TaskModel();
        
        // Capturamos filtros desde la URL (?status_filter=...&search=...)
        $statusFilter = $_GET['status_filter'] ?? null;
        $searchQuery = $_GET['search'] ?? null;
        
        // Almacenamos las tareas reales traídas de la base de datos
        $this->view->tasks = $taskModel->getTasks($statusFilter, $searchQuery);
        
        // Guardamos los valores de los inputs para que no se borren al pulsar buscar
        $this->view->current_status = $statusFilter;
        $this->view->current_search = $searchQuery;
    }
    
    public function checkAction()
    {
        echo "hello from test::check";
    }
}