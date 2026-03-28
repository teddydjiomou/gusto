<?php
require_once __DIR__ . '/BaseModel.php';

class QrCode extends BaseModel {

    private $secret = "CLE_SECRETE_GUSTO";

    public function generateQrUrl($id_etablissement, $table)
    {
        // Créer la signature HMAC
        $signature = hash_hmac('sha256', "$id_etablissement:$table", $this->secret);

        // Concaténer id, table et signature
        $data = "$id_etablissement:$table:$signature";

        // Encoder en base64
        $code = base64_encode($data);

        return "http://gusto/web/menu.php?code=" . $code;
    }
}