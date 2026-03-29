<?php
require_once __DIR__ . '/../controllers/QrCodeController.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];

// ========================
// Vérification token
// ========================
$headers = function_exists('getallheaders') ? getallheaders() : [];

if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Token requis'
    ]);
    exit;
}

$controller = new QrCodeController();

// ========================
// GET : générer QR code
// ========================
if ($method === 'GET') {
    $controller->generate();
    exit;
}

// ========================
// Méthodes non autorisées
// ========================
http_response_code(405);
echo json_encode([
    'success' => false,
    'message' => 'Méthode non autorisée'
]);
exit;
?>