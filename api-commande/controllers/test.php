<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../utils/phpqrcode/qrlib.php';

header('Content-Type: image/png');

QRcode::png("HELLO TEST");

exit;