<?php

require_once __DIR__ . '/../controllers/QrCodeController.php';
require_once __DIR__ . '/../core/Middleware.php';

$controller = new QrCodeController();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

    if (!$id) {
        http_response_code(400);
        echo "ID required";
        exit;
    }

    $controller->generate($id);
    exit;
}

http_response_code(405);
echo "Method not allowed";