<?php

require_once __DIR__ . '/../models/QrCodeModel.php';
require_once __DIR__ . '/../core/Middleware.php';
require_once __DIR__ . '/../utils/phpqrcode/qrlib.php';


class QrCodeController {

    private $model;
    private $user; // utilisateur connecté

    public function __construct() {
        $this->user = Middleware::checkAuth();
        $this->model = new QrCodeModel();
    }

    public function generate($id) {

        // 🔥 START CLEAN BUFFER (IMPORTANT)
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        if (!$this->user) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(["error" => "Unauthorized"]);
            exit;
        }

        $id_etablissement = $this->user->id_etablissement;

        if (!$id_etablissement) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(["error" => "Missing establishment"]);
            exit;
        }

        // ========================
        // VALIDATION
        // ========================
        if (!ctype_digit((string)$id)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(["error" => "Invalid table ID"]);
            exit;
        }

        // ========================
        // TABLE
        // ========================
        $tableData = $this->model->getByIdAndEtablissement($id, $id_etablissement);

        if (!$tableData) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(["error" => "Table not found"]);
            exit;
        }

        $url = $this->model->generateQrUrl(
            $id_etablissement,
            $tableData['id_table']
        );

        // 🔥 CLEAN AGAIN BEFORE IMAGE
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

       
         header('Content-Type: text/plain');

      

        var_dump(extension_loaded('gd'));
var_dump(function_exists('imagepng'));
var_dump(function_exists('imagecreate'));
exit;
    }
}