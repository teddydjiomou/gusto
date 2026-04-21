<?php
require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

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

    public function getTable($id_etablissement, $id_table) {

        $stmt = $this->personnalSelect(
            "tables_restaurant",
            "*",
            "WHERE id_etablissement = ? AND id_table = ?",
            [$id_etablissement, $id_table]
        );

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Récupérer par ID et restaurant (sécurisé)
    public function getByIdAndEtablissement($id, $id_etablissement) {
        $stmt = $this->personnalSelect(
            "tables_restaurant",
            "*",
            "WHERE id_table = ? AND id_etablissement = ?",
            [$id, $id_etablissement]
        );
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Créer
    public function create($data, $id_etablissement) {
        $this->insert(
            "tables_restaurant",
            ["nom", "id_etablissement", "statu"],
            [
                $data['nom'],
                $id_etablissement,
                "Fermer"
            ]
        );

        return $this->pdo->lastInsertId();
    }

    public function update($id, $id_etablissement, $data) {
        return $this->set(
            "tables_restaurant",
            ["nom", "statu"], // ❌ on retire id_etablissement
            [
                $data['nom'],
                $data['statu'],
            ],
            "WHERE id_table = ? AND id_etablissement = ?", // ✅ sécurité
            [$id, $id_etablissement]
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
        $e = $this->getByIdAndEtablissement($id, $id_etablissement);
        if (!$e) return false;

        if ($e['statu'] === 'Ouvert') {
            // Fermer la table
            $this->set(
                "tables_restaurant",
                ["statu"],
                ["Fermer"],
                "WHERE id_table = ? AND id_etablissement = ?",
                [$id, $id_etablissement]
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
            $code = substr(bin2hex(random_bytes(3)), 0, 6);
            $this->set(
                "tables_restaurant",
                ["statu"],
                ["Ouvert"],
                "WHERE id_table = ? AND id_etablissement = ?",
                [$id, $id_etablissement]
            );

            // Créer un nouveau service
            $this->insert(
                "service",
                ["id_table", "id_utilisateur", "code", "id_etablissement", "date_heure_ouverture", "date_heure_fermeture"],
                [$id, $id_utilisateur, $code, $id_etablissement, date('Y-m-d H:i:s'), null]
            );
            try {
                $connector = new WindowsPrintConnector("POS-PRINTER"); // ⚠️ nom exact de l'imprimante
                $printer = new Printer($connector);

                // Mise en forme
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer->text("Table : " . $e['nom'] . "\n");
                $printer->text("------------------------\n");

                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text("Code  : $code\n");

                $printer->cut();
                $printer->close();

            } catch (Exception $e) {
                // ⚠️ Important: ne pas bloquer ton app si l'imprimante échoue
                error_log("Erreur impression: " . $e->getMessage());
            }
        }

        return true;
    }

}
?>