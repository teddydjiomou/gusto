<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Response.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Middleware {

    public static function checkAuth() {
        $config = require __DIR__ . '/../config/config.php';
        $secret = $config['JWT_SECRET'];
        $algo   = $config['JWT_ALGO'];

        // ✅ FIX IMPORTANT
        $headers = getallheaders();
        if (!$headers) $headers = [];

        $authHeader =
            $headers['Authorization']
            ?? $headers['authorization']
            ?? null;

        if (!$authHeader) {
            Response::error("Token required", 401);
            exit;
        }

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            Response::error("Invalid token format", 401);
            exit;
        }

        $token = $matches[1];

        try {
            $decoded = JWT::decode($token, new Key($secret, $algo));
            return $decoded->data;
        } catch (Exception $e) {
            Response::error("Invalid token: " . $e->getMessage(), 401);
            exit;
        }
    }
}