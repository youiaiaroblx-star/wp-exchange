<?php
header('Content-Type: application/json; charset=utf-8');

$MESSAGES_FILE = 'messages.json';

if (!file_exists($MESSAGES_FILE)) {
    echo json_encode(['messages' => []]);
    exit;
}

$json = file_get_contents($MESSAGES_FILE);
$messages = json_decode($json, true);

if (!is_array($messages)) {
    $messages = [];
}

echo json_encode(['messages' => $messages], JSON_UNESCAPED_UNICODE);
?>