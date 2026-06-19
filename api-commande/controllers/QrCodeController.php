<?php
require_once __DIR__ . '/../models/QrCode.php';
require_once __DIR__ . '/../core/Middleware.php';

if (!class_exists('QRcode')) {
    require_once __DIR__ . '/../utils/phpqrcode/qrlib.php';
}

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

    $user = Middleware::checkAuth();
    $id_etablissement = $user->id_etablissement;

    if (!$id || !ctype_digit((string)$id)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Invalid table ID"
        ]);
        return;
    }

    $tableData = $this->model->getByIdAndEtablissement($id, $id_etablissement);

    if (!$tableData) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Table not found"
        ]);
        return;
    }

    $id_table = $tableData['id_table'];
    $nom_table = $tableData['nom'];

    $url = $this->model->generateQrUrl($id_etablissement, $id_table);

    if (empty($url)) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "QR URL generation failed"
        ]);
        return;
    }

    $filename = "qrcode_table_" . preg_replace('/[^a-zA-Z0-9_-]/', '_', $nom_table) . ".png";

    header('Content-Type: image/png');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');

    QRcode::png($url, null, QR_ECLEVEL_H, 8);
    exit;
}
}
