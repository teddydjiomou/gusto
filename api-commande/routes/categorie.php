<?php
require_once __DIR__ . '/../controllers/CategorieController.php';

header('Content-Type: application/json; charset=utf-8');

$controller = new CategorieController();
$method = $_SERVER['REQUEST_METHOD'];

// ========================
// GET : lister ou récupérer une catégorie
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
// POST : ajouter ou modifier
// ========================
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        echo json_encode(['success'=>false,'message'=>'Données invalides']);
        exit;
    }

    if (!empty($input['id'])) {
        $controller->update($input['id'], $input);
    } else {
        $controller->store($input);
    }
    exit;
}

// ========================
// DELETE : supprimer
// ========================
if ($method === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['id'])) {
        echo json_encode(['success'=>false,'message'=>'ID requis']);
        exit;
    }

    $controller->delete($input['id']);
    exit;
}

// ========================
// Méthodes non autorisées
// ========================
http_response_code(405);
echo json_encode(['success'=>false,'message'=>'Méthode non autorisée']);
exit;
?>