<?php

require_once __DIR__ . '/../models/QrCode.php';
require_once __DIR__ . '/../core/Middleware.php';

if (!class_exists('QRcode')) {
    require_once __DIR__ . '/../utils/phpqrcode/qrlib.php';
}

class QrCodeController {

    private $model;

    public function __construct() {
        $this->model = new QrCode();
    }

    public function generate($id) {

        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        // 🔥 IMPORTANT : évite les sorties cassées
        if (ob_get_length()) ob_clean();

        // ========================
        // AUTH (on garde ton middleware tel quel)
        // ========================
        $user = Middleware::checkAuth();

        if (!$user) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(["error" => "Unauthorized"]);
            exit;
        }

        $id_etablissement = $user->id_etablissement ?? null;

        if (!$id_etablissement) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(["error" => "Missing establishment"]);
            exit;
        }

        // ========================
        // VALIDATION ID
        // ========================
        if (!ctype_digit((string)$id)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(["error" => "Invalid table ID"]);
            exit;
        }

        // ========================
        // GET TABLE
        // ========================
        $tableData = $this->model->getByIdAndEtablissement($id, $id_etablissement);

        if (!$tableData) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(["error" => "Table not found"]);
            exit;
        }

        // ========================
        // GENERATE URL
        // ========================
        $url = $this->model->generateQrUrl(
            $id_etablissement,
            $tableData['id_table']
        );

        if (!$url) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(["error" => "QR URL generation failed"]);
            exit;
        }

        // ========================
        // FILE NAME
        // ========================
        $filename = "qrcode_" . preg_replace('/[^a-zA-Z0-9_-]/', '_', $tableData['nom']) . ".png";

        // ========================
        // IMPORTANT FIX SERVER OUTPUT
        // ========================
        if (ob_get_length()) ob_clean();
        ini_set('zlib.output_compression', 'Off');

        // ========================
        // IMAGE HEADERS (ONLY HERE)
        // ========================
        header('Content-Type: image/png');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Cache-Control: no-cache, no-store, must-revalidate');

        // ========================
        // GENERATE QR
        // ========================
        QRcode::png($url, null, QR_ECLEVEL_H, 8);

        exit;
    }
}