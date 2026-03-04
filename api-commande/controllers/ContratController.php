<?php
require_once __DIR__ . '/../models/Contrat.php';
require_once __DIR__ . '/../core/Middleware.php';

class ContratController {

    private $contrat;

    public function __construct() {
        Middleware::checkAuth();
        $this->contrat = new Contrat();
        error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
    }

    // =========================
    // LISTE
    // =========================
    public function index() {
        header('Content-Type: application/json; charset=utf-8');

        $data = $this->contrat->getAllContrats();
        $rows = [];

        foreach ($data as $e) {
            if ($e['statu'] === 'Valide') {
                $statutHTML = "<span class='statu-valide'>Valide</span>";
                $btnClass = 'danger';
                $btnText  = 'Bloquer';
            } else {
                $statutHTML = "<span class='statu-expire'>Expiré</span>";
                $btnClass = 'success';
                $btnText  = 'Renouveler';
            }
            $rows[] = [
                $e['code'],
                $e['date_validite'],
                $statutHTML,
                "<button class='btn btn-sm btn-primary edit-contrat' data-id='{$e['id_contrat']}'>Modifier</button>
                 <button class='btn btn-sm btn-$btnClass change-contrat' data-id='{$e['id_contrat']}'>$btnText</button>"
            ];
        }

        echo json_encode(['success'=>true,'data'=>$rows]);
        exit;
    }

    public function show($id) {
        header('Content-Type: application/json; charset=utf-8');

        $e = $this->contrat->getById($id);
        if ($e) {
            echo json_encode(['success'=>true, 'data'=>$e]);
        } else {
            echo json_encode(['success'=>false, 'message'=>'Contrat introuvable']);
        }
        exit;
    }


    // =========================
    // AJOUT
    // =========================
    public function store($data) {
        header('Content-Type: application/json; charset=utf-8');

        $id = $this->contrat->create($data);
        $e  = $this->contrat->getById($id);

        if ($e['statu'] === 'Valide') {
            $statutHTML = "<span class='statu-valide'>Valide</span>";
            $btnClass = 'danger';
            $btnText  = 'Bloquer';
        } else {
            $statutHTML = "<span class='statu-expire'>Expiré</span>";
            $btnClass = 'success';
            $btnText  = 'Renouveler';
        }

        $row = [
            $e['code'],
            $e['date_validite'],
            $statutHTML,
           "<button class='btn btn-sm btn-primary edit-contrat' data-id='{$e['id_contrat']}'>Modifier</button>
           <button class='btn btn-sm btn-$btnClass change-contrat' data-id='{$e['id_contrat']}'>$btnText</button>"
        ];

        echo json_encode(['success'=>true,'data'=>$row]);
        exit;
    }

    // =========================
// MODIFIER
    // =========================
    public function update($id, $data) {
        header('Content-Type: application/json; charset=utf-8');

        // Récupération de l'existant
        $e = $this->contrat->getById($id);
        if (!$e) {
            echo json_encode(['success'=>false,'message'=>'Contrat introuvable']);
            exit;
        }

        // Mise à jour
        $this->contrat->update($id, $data);

        // Relecture
        $e = $this->contrat->getById($id);

        if ($e['statu'] === 'Valide') {
            $statutHTML = "<span class='statu-valide'>Valide</span>";
            $btnClass = 'danger';
            $btnText  = 'Bloquer';
        } else {
            $statutHTML = "<span class='statu-expire'>Expiré</span>";
            $btnClass = 'success';
            $btnText  = 'Renouveler';
        }

        // Ligne tableau
        $row = [
                $e['code'],
                $e['date_validite'],
                $statutHTML,
                "<button class='btn btn-sm btn-primary edit-contrat' data-id='{$e['id_contrat']}'>Modifier</button>
                 <button class='btn btn-sm btn-$btnClass change-contrat' data-id='{$e['id_contrat']}'>$btnText</button>"
            ];

        echo json_encode(['success'=>true,'data'=>$row]);
        exit;
    }

    // =========================
    // CHANGER STATUT
    // =========================
    public function changeStatus($id) {
        header('Content-Type: application/json; charset=utf-8');

        $this->contrat->toggleStatut($id);
        $e = $this->contrat->getById($id);

        if ($e['statu'] === 'Valide') {
            $statutHTML = "<span class='statu-valide'>Valide</span>";
            $btnClass = 'danger';
            $btnText  = 'Bloquer';
        } else {
            $statutHTML = "<span class='statu-expire'>Expiré</span>";
            $btnClass = 'success';
            $btnText  = 'Renouveler';
        }

        $row = [
            $e['code'],
            $e['date_validite'],
            $statutHTML,
            "<button class='btn btn-sm btn-primary edit-contrat' data-id='{$e['id_contrat']}'>Modifier</button>
            <button class='btn btn-sm btn-$btnClass change-contrat' data-id='{$e['id_contrat']}'>$btnText</button>"
        ];

        echo json_encode(['success'=>true,'data'=>$row]);
        exit;
    }

}
