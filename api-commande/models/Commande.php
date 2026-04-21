<?php
require_once __DIR__ . '/BaseModel.php';

class Commande extends BaseModel {

    // Récupérer toutes les tables d'un restaurant
    public function getByEtablissementGroupedByTicket($id_etablissement) {

        $stmt = $this->personnalSelect(
            "commande",
            "*",
            "WHERE id_etablissement = ?",
            [$id_etablissement]
        );

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $grouped = [];

        foreach ($rows as $row) {

            $ticket = $row['id_ticket'];

            // Initialisation du ticket
            if (!isset($grouped[$ticket])) {
                $grouped[$ticket] = [
                    'id_ticket' => $ticket,
                    'id_table' => $row['id_table'],
                    'id_etablissement' => $row['id_etablissement'],
                    'montant_total' => 0,
                    'date_enreg' => $row['date_enreg'],
                    'commandes' => []
                ];
            }
            else {
                // ✅ garder la date la plus récente du ticket
                 if (strtotime($row['date_enreg']) > strtotime($grouped[$ticket]['date_enreg'])) {
                    $grouped[$ticket]['date_enreg'] = $row['date_enreg'];
                }
            }

            // Sécuriser JSON
            $items = json_decode($row['commande'], true) ?? [];

            foreach ($items as $item) {

                // Sécurisation des valeurs
                $prix = (float)($item['prix'] ?? 0);
                $quantite = (int)($item['quantite'] ?? 0);
                $total = round((float)($item['total'] ?? ($prix * $quantite)), 2);

                $grouped[$ticket]['commandes'][] = [
                    'id' => $item['id'] ?? null,
                    'libelle' => $item['libelle'] ?? '',
                    'prix' => $prix,
                    'quantite' => $quantite,
                    'total' => $total,
                    'etat' => $row['etat']
                ];

                // ✅ TOTAL FIABLE (sans SQL)
                $grouped[$ticket]['montant_total'] += $total;
            }

            // arrondi final du ticket
            $grouped[$ticket]['montant_total'] = round($grouped[$ticket]['montant_total'], 2);
        }

        usort($grouped, function ($a, $b) {
            return strtotime($b['date_enreg']) - strtotime($a['date_enreg']);
        });

        return array_values($grouped);
    }

    // Récupérer par ID
    public function getById($id) {
        $stmt = $this->personnalSelect(
            "commande",
            "*",
            "WHERE id_commande = ?",
            [$id]
        );
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Récupérer par ID et restaurant (sécurisé)
    public function getByIdAndEtablissement($id, $id_etablissement) {
        $stmt = $this->personnalSelect(
            "commande",
            "*",
            "WHERE id_commande = ? AND id_etablissement = ?",
            [$id, $id_etablissement]
        );
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function isTableActive($id_table, $id_etablissement) {
        $stmt = $this->personnalSelect(
            "service",
            "*",
            "WHERE id_table = ? AND id_etablissement = ? AND date_heure_fermeture IS NULL ORDER BY id_service DESC LIMIT 1",
            [$id_table, $id_etablissement]
        );

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    // Créer
    public function create($data, $id_etablissement) {
        $this->insert(
            "commande",
            ["id_ticket",'id_table','id_etablissement','commande','montant_total','date_enreg','etat'],
            [   
                $data['id_ticket'],
                $data['id_table'],
                $id_etablissement,
                json_encode($data['commande']),
                $data['montant_total'],
                date("Y-m-d H:i:s", time() + 3600),
                "En attente"
            ]
        );

        return $this->pdo->lastInsertId();
    }

    // Mettre à jour une table
    public function update($id, $id_etablissement, $data) {
        return $this->set(
            "commande",
            ['id_table','commande','montant_total','etat'],
            [
                $data['id_table'],
                json_encode($data['commande']),
                $data['montant_total'],
                $data['etat'],
            ],
            "WHERE id_commande = ? AND id_etablissement = ?",
            [$id, $id_etablissement]
        );
    }

    // Supprimer une table en sécurisant par restaurant
    public function delete($id, $id_etablissement){
        return $this->personalDelete(
            "commande",
            "WHERE id_commande = ? AND id_etablissement = ?",
            [$id, $id_etablissement]
        );
    }

    public function getByServiceRange($id_etablissement, $debut, $fin){
        return $this->personnalSelect(
            "commande",
            "*",
            "WHERE id_etablissement = ?
             AND date_enreg BETWEEN ? AND ?",
            [$id_etablissement, $debut, $fin]
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function toggleStatut($id, $id_etablissement) {
        // Récupérer la commande sécurisée
        $e = $this->getByIdAndEtablissement($id, $id_etablissement);
        if (!$e) return false;

        // Définir la progression des statuts
        $next = [
            'En attente' => 'Servi',
            'Servi' => 'Payé'
        ];

        // Vérifier s'il y a un statut suivant
        if (isset($next[$e['etat']])) {
            $this->set(
                "commande",
                ["etat"],
                [$next[$e['etat']]],
                "WHERE id_commande = ? AND id_etablissement = ?",
                [$id, $id_etablissement]
            );
        }

        return true;
    }

}
?>