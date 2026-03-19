<?php
require_once __DIR__ . '/BaseModel.php';

class Table extends BaseModel {

    // Récupérer toutes les tables d'un restaurant
    public function getTablesByEtablissement($id_etablissement) {
        $stmt = $this->personnalSelect(
            "tables_restaurant",
            "*",
            "WHERE id_etablissement = ?",
            [$id_etablissement]
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer par ID
    public function getById($id) {
        $stmt = $this->personnalSelect(
            "tables_restaurant",
            "*",
            "WHERE id_table = ?",
            [$id]
        );
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Récupérer par ID et restaurant (sécurisé)
    public function getByIdAndRestaurant($id, $id_etablissement) {
        $stmt = $this->personnalSelect(
            "tables_restaurant",
            "*",
            "WHERE id_table = ? AND id_etablissement = ?",
            [$id, $id_etablissement]
        );
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Créer
    public function create($data) {
        $this->insert(
            "tables_restaurant",
            ["nom", "id_etablissement", "statu"],
            [
                $data['nom'],
                $data['id_etablissement'],
                "Fermer"
            ]
        );

        return $this->pdo->lastInsertId();
    }

    // Mettre à jour une table
    public function update($id, $data) {
        return $this->set(
            "tables_restaurant",
            ["nom", "id_etablissement", "statu"],
            [
                $data['nom'],
                $data['id_etablissement'],
                $data['statu'],
            ],
            "WHERE id_table = ?",
            [$id]
        );
    }

    // Supprimer une table en sécurisant par restaurant
    public function delete($id, $id_etablissement){
        return $this->personalDelete(
            "tables_restaurant",
            "WHERE id_table = ? AND id_etablissement = ?",
            [$id, $id_etablissement]
        );
    }

    public function toggleStatut($id, $id_utilisateur, $id_etablissement) {
        $e = $this->getByIdAndRestaurant($id, $id_etablissement);
        if (!$e) return false;

        if ($e['statu'] === 'Ouvert') {
            // Fermer la table
            $this->set(
                "tables_restaurant",
                ["statu"],
                ["Fermer"],
                "WHERE id_table = ?",
                [$id]
            );

            // Mettre à jour la dernière entrée service ouverte
            $this->set(
                "service",
                ["date_heure_fermeture"],
                [date('Y-m-d H:i:s')],
                 "WHERE id_table = ? AND id_etablissement = ? AND date_heure_fermeture IS NULL ORDER BY date_heure_ouverture DESC LIMIT 1",
                [$id, $id_etablissement]
            );

        } else {
            // Ouvrir la table
            $this->set(
                "tables_restaurant",
                ["statu"],
                ["Ouvert"],
                "WHERE id_table = ?",
                [$id]
            );

            // Créer un nouveau service
            $this->insert(
                "service",
                [
                    "id_table" => $id,
                    "id_utilisateur" => $id_utilisateur,
                    "id_etablissement" => $id_etablissement,
                    "date_heure_ouverture" => date('Y-m-d H:i:s'),
                    "date_heure_fermeture" => null
                ]
            );
        }

        return true;
    }

}
?>