<?php
require_once __DIR__ . '/../controllers/ServiceController.php';

header('Content-Type: application/json; charset=utf-8');

$controller = new ServiceController();
$method = $_SERVER['REQUEST_METHOD'];

// ========================
// GET
// ========================
if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $controller->show($_GET['id']);
    } else {
        $controller->index();
    }
    exit;
}

// ========================
// POST : créer ou modifier
// ========================
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        echo json_encode(['success'=>false,'message'=>'Données invalides']);
        exit;
    }

    if (!empty($input['id'])) {
        $controller->store($input); // si tu veux update, ajoute update() dans le controller
    } else {
        $controller->store($input);
    }
    exit;
}

// ========================
// PATCH : changer statut
// ========================
if ($method === 'PATCH') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['id'])) {
        echo json_encode(['success'=>false,'message'=>'ID requis']);
        exit;
    }
    $controller->changeStatus($input['id']);
    exit;
}

// ========================
// Méthodes non autorisées
// ========================
http_response_code(405);
echo json_encode(['success'=>false,'message'=>'Méthode non autorisée']);
exit;
?>