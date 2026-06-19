<?php

require_once __DIR__ . '/../models/Service.php';
require_once __DIR__ . '/../models/QrCode.php';
require_once __DIR__ . '/../core/Middleware.php';

if (!class_exists('QRcode')) {
    require_once __DIR__ . '/../utils/phpqrcode/qrlib.php';
}

class QrCodeController {

    private $serviceModel;
    private $qrModel;
    private $user;

    public function __construct() {
        $this->user = Middleware::checkAuth();
        $this->serviceModel = new Service();
        $this->qrModel = new QrCode();

        error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
    }

    public function generate($id)
    {
        try {

            $id_etablissement = $this->user->id_etablissement;

            if (!$id || !ctype_digit((string)$id)) {
                http_response_code(400);
                echo "Invalid ID";
                exit;
            }

            // 🔥 RECUPERATION SERVICE
            $service = $this->serviceModel->getLastByTable($id, $id_etablissement);

            if (!$service) {
                http_response_code(404);
                echo "Service not found";
                exit;
            }

            $url = $this->qrModel->generateQrUrl(
                $id_etablissement,
                $service['id_table']
            );

            while (ob_get_level()) {
                ob_end_clean();
            }

            header('Content-Type: image/png');
            header('Content-Disposition: attachment; filename="qrcode.png"');

            QRcode::png($url, false, QR_ECLEVEL_H, 8);

            exit;

        } catch (Throwable $e) {
            http_response_code(500);
            echo $e->getMessage();
            exit;
        }
    }
}