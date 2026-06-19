<?php

require_once __DIR__ . '/../models/QrCode.php';

if (!class_exists('QRcode')) {
    require_once __DIR__ . '/../utils/phpqrcode/qrlib.php';
}

class QrCodeController {

    private $model;

    public function __construct() {
        $this->model = new QrCode();
    }

    public function generate($id)
{
    // 🔥 BLOQUE TOUTE SORTIE PHP
    error_reporting(0);
    ini_set('display_errors', 0);

    while (ob_get_level()) {
        ob_end_clean();
    }

    // ========================
    // AUTH
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
    // VALIDATION
    // ========================
    if (!ctype_digit((string)$id)) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(["error" => "Invalid ID"]);
        exit;
    }

    // ========================
    // GET DATA
    // ========================
    $tableData = $this->model->getByIdAndEtablissement($id, $id_etablissement);

    if (!$tableData) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(["error" => "Table not found"]);
        exit;
    }

    $id_table = $tableData['id_table'];

    // ========================
    // GENERATE URL
    // ========================
    $url = $this->model->generateQrUrl($id_etablissement, $id_table);

    // ========================
    // OUTPUT QR
    // ========================
    header('Content-Type: image/png');
    header('Content-Disposition: attachment; filename="qrcode.png"');
    header('Cache-Control: no-store, no-cache, must-revalidate');

    QRcode::png($url, null, QR_ECLEVEL_H, 8);

    exit;
}
}