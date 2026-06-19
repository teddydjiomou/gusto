<?php

require_once __DIR__ . '/../models/QrCode.php';
require_once __DIR__ . '/../core/Middleware.php';
require_once __DIR__ . '/../utils/phpqrcode/qrlib.php';

class QrCodeController {

    private $qrModel;
    private $user;

    public function __construct() {
        $this->user = Middleware::checkAuth();
        $this->qrModel = new QrCode();
    }

    public function generate($id)
    {
        try {

            // sécurité auth
            if (!$this->user || !isset($this->user->id_etablissement)) {
                http_response_code(401);
                echo "Unauthorized";
                exit;
            }

            $id_etablissement = $this->user->id_etablissement;

            // validation id
            if (!$id || !is_numeric($id)) {
                http_response_code(400);
                echo "Invalid ID";
                exit;
            }

            // génération URL QR
            $url = $this->qrModel->generateQrUrl($id_etablissement, $id);

            // nettoyage buffer (IMPORTANT)
            while (ob_get_level()) {
                ob_end_clean();
            }

            header('Content-Type: image/png');
            header('Content-Disposition: attachment; filename="qrcode.png"');

            // IMPORTANT : appel correct
            \QRcode::png($url, false, QR_ECLEVEL_H, 8);

            exit;

        } catch (Throwable $e) {
            http_response_code(500);
            echo $e->getMessage();
            exit;
        }
    }
}