<?php
require_once __DIR__ . '/BaseModel.php';

class Service extends BaseModel {

    // =========================
    // Tous les services d’un établissement
    // =========================
    public function getServicesByEtablissement($id_etablissement){
        $stmt = $this->personnalSelect(
            "service",
            "*",
            "WHERE id_etablissement = ?",
            [$id_etablissement]
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
?>