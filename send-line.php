<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['ok' => false]));
}

// ===== TOKEN + USER ID ของคุณ (แปะแล้ว) =====
$TOKEN  = 'bMQlBWWllb+Bfz1Bk8ZgOySwHKe5+l6rKpA1j7yLbO0Ev/qOK7AIXn8HuWnGDJZknI50VybVGIExUIKMvxJnji1f0ReBBZ6lS7OqIuJHmlHWprkMLR64DU6WEEXRYUKFRCyKAEBvkoiLFcdmgglXeAdB04t89/1O/w1cDnyilFU=';
$USER_ID = 'Ue0a42a45b110d74ec3a61d4cd57229a8';
$MESSAGES_FILE = 'messages.json';

// ===== รับข้อมูลจากฟอร์ม =====
$name    = trim($_POST['name']    ?? '');
$phone   = trim($_POST['phone']   ?? '');
$message = trim($_POST['message'] ?? '');

if (empty($name) || empty($phone) || empty($message)) {
    http_response_code(400);
    exit(json_encode(['ok' => false, 'msg' => 'ข้อมูลไม่ครบ']));
}

// ===== ประกอบข้อความเพื่อส่ง LINE =====
$lineText = "📩 มีลูกค้าติดต่อใหม่\n"
          . "ชื่อ: {$name}\n"
          . "เบอร์: {$phone}\n"
          . "ข้อความ: {$message}";

// ===== ส่งไป LINE Messaging API =====
$payload = json_encode([
    'to' => $USER_ID,
    'messages' => [['type' => 'text', 'text' => $lineText]]
]);

$ch = curl_init('https://api.line.me/v2/bot/message/push');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $TOKEN,
    ],
    CURLOPT_TIMEOUT        => 10,
]);
$res  = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($code !== 200) {
    http_response_code(500);
    exit(json_encode(['ok' => false, 'msg' => 'ส่งไป LINE ไม่ได้']));
}

// ===== บันทึกข้อความลงไฟล์ =====
$messageRecord = [
    'name'      => $name,
    'phone'     => $phone,
    'message'   => $message,
    'timestamp' => date('Y-m-d H:i:s')
];

$messages = [];
if (file_exists($MESSAGES_FILE)) {
    $json = file_get_contents($MESSAGES_FILE);
    $decoded = json_decode($json, true);
    if (is_array($decoded)) {
        $messages = $decoded;
    }
}

$messages[] = $messageRecord;

file_put_contents($MESSAGES_FILE, json_encode($messages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode(['ok' => true, 'msg' => 'ส่งสำเร็จ']);
?>