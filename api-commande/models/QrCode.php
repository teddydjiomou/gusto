<?php

require __DIR__ . '/../../vendor/autoload.php';

class QrCode extends BaseModel {

    private $secret;

    public function __construct()
    {
        $this->secret = getenv('SECRET_KEY');
    }

    public function generateQrUrl($id_etablissement, $id_table)
    {
        $signature = hash_hmac(
            'sha256',
            $id_etablissement . ":" . $id_table,
            $this->secret
        );

        $data = $id_etablissement . ":" . $id_table . ":" . $signature;

        $code = rtrim(strtr(base64_encode($data), '+/', '-_'), '=');

        return "https://gusto-api-48f214a89058.herokuapp.com/web/check.php?code=" . $code;
    }
}