<?php
require_once __DIR__ . '/BaseModel.php';

class Service extends BaseModel {

    // =========================
    // Tous les services d’un établissement
    // =========================
    public function getServicesByEtablissement($id_etablissement){

        // 1. récupérer services
        $stmt = $this->personnalSelect(
            "service",
            "*",
            "WHERE id_etablissement = ?",
            [$id_etablissement]
        );

        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($services)) {
            return [];
        }

        // 2. récupérer ids utilisateurs
        $ids = array_unique(array_column($services, 'id_utilisateur'));

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        // 3. récupérer utilisateurs en 1 requête
        $usersStmt = $this->personnalSelect(
            "utilisateur",
            "id_utilisateur, login",
            "WHERE id_utilisateur IN ($placeholders)",
            $ids
        );

        $users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

        // 4. map id => login
        $map = [];
        foreach ($users as $u) {
            $map[$u['id_utilisateur']] = $u['login'];
        }

        // 5. enrichir services
        foreach ($services as $key => $service) {
            $services[$key]['login'] =
                $map[$service['id_utilisateur']] ?? null;
        }

        return $services;
    }

    public function getByIdAndEtablissement($id, $id_etablissement) {

        // récupérer le service
        $stmt = $this->personnalSelect(
            "service",
            "*",
            "WHERE id_table = ? AND id_etablissement = ? ORDER BY id_service DESC LIMIT 1",
            [$id, $id_etablissement]
        );

        $service = $stmt->fetch(PDO::FETCH_ASSOC);

        // si aucun service
        if (!$service) {
            return null;
        }

        // récupérer utilisateur
        $userStmt = $this->personnalSelect(
            "utilisateur",
            "login",
            "WHERE id_utilisateur = ?",
            [$service['id_utilisateur']]
        );

        $user = $userStmt->fetch(PDO::FETCH_ASSOC);

        // ajouter login
        $service['login'] = $user['login'] ?? null;

        return $service;
    }



}
?>