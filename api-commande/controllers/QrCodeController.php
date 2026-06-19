<?php
require_once __DIR__ . '/../models/QrCode.php';
require_once __DIR__ . '/../core/Middleware.php';
require_once __DIR__ . '/../utils/phpqrcode/qrlib.php';


class QrCodeController {

    private $model;

    public function __construct() {
        // 🔐 Auth obligatoire (employé)
        $user = Middleware::checkAuth();
        $this->model = new QrCode();

        // Nettoyage warnings
        error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
    }

    public function generate($id) {

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $user = Middleware::checkAuth();

    if (!$user) {
        http_response_code(401);
        echo json_encode(["error" => "Unauthorized"]);
        return;
    }

    $id_etablissement = $user->id_etablissement ?? null;

    if (!$id_etablissement) {
        http_response_code(400);
        echo json_encode(["error" => "Missing etablissement"]);
        return;
    }

    if (!class_exists('QRcode')) {
        require_once __DIR__ . '/../utils/phpqrcode/qrlib.php';
    }

    $tableData = $this->model->getByIdAndEtablissement($id, $id_etablissement);

    if (!$tableData) {
        http_response_code(404);
        echo json_encode(["error" => "Table not found"]);
        return;
    }

    $url = $this->model->generateQrUrl($id_etablissement, $tableData['id_table']);

    header('Content-Type: image/png');
    QRcode::png($url);
    exit;
}
}
