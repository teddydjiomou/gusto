<?php
require_once __DIR__ . '/BaseModel.php';

class Contrat extends BaseModel {

    private function generateLicenceCode($idEtablissement, $dateValidite) {
        // 1️⃣ Récupérer le nom de l'établissement
        $stmt = $this->personnalSelect(
            "etablissement",
            "nom",
            "WHERE id_etablissement = ?",
            [$idEtablissement]
        );
        $etablissement = $stmt->fetch(PDO::FETCH_ASSOC);
        $nomEtablissement = $etablissement ? preg_replace('/\s+/', '', strtoupper($etablissement['nom'])) : "UNKNOWN";

        // 2️⃣ Formater les dates
        $dateCreation = date('Ymd');
        $dateValidite = date('Ymd', strtotime($dateValidite));

        // 3️⃣ Partie aléatoire
        $random = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));

        // 4️⃣ Retourner le code licence
        return "{$nomEtablissement}-{$dateCreation}-{$dateValidite}-{$random}";
    }
    // Récupérer tous les établissements
    public function getAllContrats() {
        $stmt = $this->getAll("contrat");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer un établissement par ID
    public function getById($id) {
        $stmt = $this->personnalSelect(
            "contrat",
            "*",
            "WHERE id_contrat = ?",
            [$id]
        );
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Créer un nouvel établissement
    public function create($data) {
        // Définir le statut par défaut si absent
        $data['statu'] = $data['statu'] ?? 'Valide';

         $licence = $data['code'] ?? $this->generateLicenceCode($data['id_etablissement'], $data['date_validite']);

        $this->insert(
            "contrat",
            ["id_etablissement", "code", "date_validite", "description", "statu"],
            [
                $data['id_etablissement'],
                $licence,
                $data['date_validite'],
                $data['description'],
                $data['statu']
            ]
        );

        return $this->pdo->lastInsertId();
    }

    // Mettre à jour un établissement
    public function update($id, $data) {
        $existing = $this->getById($id);

    $code = $data['code'] ?? $existing['code'];
        return $this->set(
            "contrat",
            ["id_etablissement", "code", "date_validite", "description", "statu"],
            [
                $data['id_etablissement'],
                $code,
                $data['date_validite'],
                $data['description'],
                $data['statu'] ?? $existing['statu']
            ],
            "WHERE id_contrat = ?",
            [$id]
        );
    }

    // Changer le statut
    public function toggleStatut($id) {
        $e = $this->getById($id);
        if (!$e) return false;

        if ($e['statu'] === 'Valide') {
            // Désactivation → on ne change QUE le statut
            return $this->set(
                "contrat",
                ["statu"],
                ["Expiré"],
                "WHERE id_contrat = ?",
                [$id]
            );
        } else {
            // Activation → on change le statut, date_validite et génère un nouveau code
            $nouveauCode = $this->generateLicenceCode($e['id_etablissement'], date('Y-m-d'));
            return $this->set(
                "contrat",
                ["statu", "date_validite", "code"],
                ["Valide", date('Y-m-d'), $nouveauCode],
                "WHERE id_contrat = ?",
                [$id]
            );
        }
    }

}
?>
