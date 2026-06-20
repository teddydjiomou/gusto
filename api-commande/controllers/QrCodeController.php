<?php

require_once __DIR__ . '/../models/QrCodeModel.php';
require_once __DIR__ . '/../core/Middleware.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\RoundBlockSizeMode;

class QrCodeController {

    private $model;
    private $user;

    public function __construct() {
        $this->user = Middleware::checkAuth();
        $this->model = new QrCodeModel();
    }

    public function generate($id) {

        // ================= AUTH =================
        if (!$this->user) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(["error" => "Unauthorized"]);
            exit;
        }

        $id_etablissement = $this->user->id_etablissement;

        if (!$id_etablissement) {
            http_response_code(400);
            echo json_encode(["error" => "Missing establishment"]);
            exit;
        }

        // ================= VALIDATION =================
        if (!ctype_digit((string)$id)) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid table ID"]);
            exit;
        }

        // ================= DATA =================
        $tableData = $this->model->getByIdAndEtablissement($id, $id_etablissement);

        if (!$tableData) {
            http_response_code(404);
            echo json_encode(["error" => "Table not found"]);
            exit;
        }

        $url = $this->model->generateQrUrl(
            $id_etablissement,
            $tableData['id_table']
        );

        // ================= CLEAN BUFFER =================
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        ini_set('zlib.output_compression', 'Off');

        // ================= QR CODE v4.6 =================
        $qrCode = new QrCode($url);
        $qrCode->setEncoding(new Encoding('UTF-8'));
        $qrCode->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh());
        $qrCode->setSize(300);
        $qrCode->setMargin(10);
        $qrCode->setRoundBlockSizeMode(RoundBlockSizeMode::Margin);

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        // ================= OUTPUT =================
        header('Content-Type: ' . $result->getMimeType());
        header('Content-Disposition: attachment; filename="qrcode.png"');
        header('Cache-Control: no-cache, no-store, must-revalidate');

        echo $result->getString();
        exit;
    }
}