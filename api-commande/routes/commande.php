<?php
require_once __DIR__ . '/../controllers/CommandeController.php';

header('Content-Type: application/json; charset=utf-8');

// ========================
// Récupération du token
// ========================
$headers = getallheaders();
$token = $headers['Authorization'] ?? null;

// ========================
// Méthode HTTP
// ========================
$method = $_SERVER['REQUEST_METHOD'];

// ========================
// Instanciation du controller
// ========================
$controller = new CommandeController();

// ========================
// GET : Liste ou détail (CLIENT + EMPLOYÉ)
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
    $data = $_POST;
    // 🔥 UPDATE → TOKEN OBLIGATOIRE
    if (!empty($data['id'])) {
        if (!$token) {
            http_response_code(401);
            echo json_encode(['success'=>false,'message'=>'Token requis pour modifier']);
            exit;
        }
        $controller->update($data['id'], $data);
    } 
    // ✅ STORE → CLIENT AUTORISÉ
    else {
        $controller->store($data);
    }

    exit;
}


// ========================
// DELETE : Supprimer Commande
// ========================
if ($method === 'DELETE') {

    if (!$token) {
        http_response_code(401);
        echo json_encode(['success'=>false,'message'=>'Token requis']);
        exit;
    }

    if (!isset($_GET['id'])) {
        echo json_encode(['success' => false, 'message' => 'ID requis']);
        exit;
    }

    $controller->delete($_GET['id']);
    exit;
}

// ========================
// PATCH : changer statut 
// ========================
if ($method === 'PATCH') {

    if (!$token) {
        http_response_code(401);
        echo json_encode(['success'=>false,'message'=>'Token requis']);
        exit;
    }

    if (!isset($_GET['id'])) {
        echo json_encode(['success'=>false,'message'=>'ID requis']);
        exit;
    }

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
