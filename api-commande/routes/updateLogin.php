<?php
require_once './../models/Utilisateur.php';
require_once './../core/Auth.php';
require_once './../core/Middleware.php';

header('Content-Type: application/json');

$user = Middleware::checkAuth();
if (!$user) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

$login    = $_POST['login'] ?? null;
$password = $_POST['password'] ?? null;

if (!$login) {
    echo json_encode(['success' => false, 'message' => 'Login requis']);
    exit;
}

$model = new Utilisateur();
$userId = $user->data->id;

// Mise à jour
$model->updateLogin($userId, $login, $password);

// Générer un nouveau token
$newToken = Auth::generateToken([
    'id_utilisateur' => $userId,
    'login' => $login
]);

echo json_encode([
    'success' => true,
    'message' => 'Informations mises à jour',
    'token'   => $newToken
]);
exit;