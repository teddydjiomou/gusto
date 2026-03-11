<?php
require_once __DIR__ . '/../controllers/TableController.php';

header('Content-Type: application/json; charset=utf-8');

$controller = new TableController();
$method = $_SERVER['REQUEST_METHOD'];
$headers = getallheaders();

// ========================
// Vérification du token
// ========================
if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(['success'=>false,'message'=>'Token requis']);
    exit;
}

// ========================
// GET : Liste ou détail
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
// POST : Ajouter ou Modifier
// ========================
if ($method === 'POST') {

    // Récupérer le JSON envoyé
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
// DELETE : Supprimer
// ========================
if ($method === 'DELETE') {

    parse_str(file_get_contents('php://input'), $input);

    if (!isset($input['id'])) {
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