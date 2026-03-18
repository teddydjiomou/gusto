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
    $data = $_POST;
    if (!empty($data['id'])) {
        $controller->update($data['id'], $data);
    } else {
        $controller->store($data);
    }
    exit;
}

// ========================
// PATCH : changer statut
// ========================
if ($method === 'PATCH' && isset($_GET['id'])) {
    $controller->changeStatus($_GET['id']);
    exit;
}

// ========================
// Méthodes non autorisées
// ========================
http_response_code(405);
echo json_encode(['success'=>false,'message'=>'Méthode non autorisée']);
exit;
?>