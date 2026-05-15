<?php

$nom = $_POST['nom'];
$telephone = $_POST['telephone'];
$message = $_POST['message'];

$token = "8683356289:AAGcp6rSJrpLRbZBDp__tsYmeY_6DkWOAsk";

$chat_id = "-1003977719477";

$text = "
📩 Nouveau message Gusto

👤 Nom : $nom

📞 Téléphone : $telephone

💬 Message :
$message
";

$url = "https://api.telegram.org/bot$token/sendMessage";

$data = [
    'chat_id' => $chat_id,
    'text' => $text
];

$options = [
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded",
        'method'  => 'POST',
        'content' => http_build_query($data)
    ]
];

$context = stream_context_create($options);

$result = file_get_contents($url, false, $context);

echo json_encode([
    'success' => true
]);