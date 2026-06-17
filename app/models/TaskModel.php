<?php

class TaskModel {
    private ?PDO $db = null;

    public function __construct() {
        global $dbh;
        
        // Si la conexión global no está iniciada, forzamos la inclusión de db.inc.php
        if (!isset($dbh)) {
            // __DIR__ es 'app/models'. Subimos dos niveles para llegar a la raíz y entrar a config/
            require_once __DIR__ . '/../../config/db.inc.php';
        }
        
        $this->db = $dbh;
    }
    
    public function getTasks($statusFilter = null, $searchQuery = null) {
        $sql = "SELECT * FROM task WHERE 1=1";
        $params = [];

        // Filtro por Estado (Punto 2)
        if (!empty($statusFilter)) {
            $sql .= " AND status = :status";
            $params[':status'] = $statusFilter;
        }

        // Filtro por Palabra Clave / Título (Punto 2)
        if (!empty($searchQuery)) {
            $sql .= " AND title LIKE :search";
            $params[':search'] = '%' . $searchQuery . '%';
        }

        // Orden descendente (Punto 2)
        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}