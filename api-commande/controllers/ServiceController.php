<?php
require_once __DIR__ . '/../models/Service.php';
require_once __DIR__ . '/../core/Middleware.php';

class ServiceController {

    private $service;
    private $user;

    public function __construct(){
        $this->user = Middleware::checkAuth(); // récupère JWT décodé
        $this->service = new Service();
        error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
    }

    // =========================
    // LISTE DES SERVICES
    // =========================
    public function index(){
        header('Content-Type: application/json; charset=utf-8');

        $id_etablissement = $this->user->id_etablissement;
        $services = $this->service->getServicesByEtablissement($id_etablissement);

        echo json_encode(['success'=>true, 'data'=>$services]);
        exit;
    }

    public function show($id) {
        header('Content-Type: application/json; charset=utf-8');

        $id_etablissement = $this->user->id_etablissement;
        $e = $this->service->getByIdAndEtablissement($id, $id_etablissement);

        if ($e) {
            echo json_encode(['success'=>true, 'data'=>$e]);
        } else {
            echo json_encode(['success'=>false, 'message'=>'Service not found']);
        }
        exit;
    }

}
?>