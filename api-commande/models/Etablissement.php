<?php
require_once __DIR__ . '/BaseModel.php';

class Etablissement extends BaseModel {

    // Récupérer tous les établissements
    public function getAllEtablissements() {
        $stmt = $this->getAll("etablissement");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer un établissement par ID
    public function getById($id) {
        $stmt = $this->personnalSelect(
            "etablissement",
            "*",
            "WHERE id_etablissement = ?",
            [$id]
        );
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Créer un nouvel établissement
    public function create($data) {
        // Définir le statut par défaut si absent
        $data['statu'] = $data['statu'] ?? 'Activer';

        $this->insert(
            "etablissement",
            ["logo", "nom", "type", "adresse", "email", "telephone", "site_web", "description", "date_enreg", "statu"],
            [
                $data['logo'],
                $data['nom'],
                $data['type'],
                $data['adresse'],
                $data['email'],
                $data['telephone'],
                $data['site_web'],
                $data['description'],
                date('Y-m-d'),
                $data['statu']
            ]
        );

        return $this->pdo->lastInsertId();
    }

    // Mettre à jour un établissement
    public function update($id, $data) {
        return $this->set(
            "etablissement",
            ["logo", "nom", "type", "adresse", "email", "telephone", "site_web", "description"],
            [
                $data['logo'],
                $data['nom'],
                $data['type'],
                $data['adresse'],
                $data['email'],
                $data['telephone'],
                $data['site_web'],
                $data['description']
            ],
            "WHERE id_etablissement = ?",
            [$id]
        );
    }

    // Changer le statut
    public function toggleStatut($id) {
        $e = $this->getById($id);
        if (!$e) return false;

        if ($e['statu'] === 'Activer') {
            // Désactivation → on ne change QUE le statut
            return $this->set(
                "etablissement",
                ["statu"],
                ["Bloquer"],
                "WHERE id_etablissement = ?",
                [$id]
            );
        } else {
            // Activation → on change le statut ET la date
            return $this->set(
                "etablissement",
                ["statu", "date_enreg"],
                ["Activer", date('Y-m-d')],
                "WHERE id_etablissement = ?",
                [$id]
            );
        }
    }

}
?>
