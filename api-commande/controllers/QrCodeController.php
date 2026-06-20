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

    header('Content-Type: image/png');

QRcode::png("https://google.com");

exit;
}