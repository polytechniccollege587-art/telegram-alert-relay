<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
function reply(int $status, array $body): never { http_response_code($status); echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); exit; }
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
if ($path === '/health') reply(200, ['ok'=>true, 'service'=>'telegram-alert-relay']);
if ($path !== '/notify') reply(404, ['ok'=>false, 'error'=>'Not found']);
if ($method !== 'POST') { header('Allow: POST'); reply(405, ['ok'=>false, 'error'=>'POST required']); }
$secret = (string)(getenv('RELAY_SECRET') ?: ''); $token = (string)(getenv('TELEGRAM_BOT_TOKEN') ?: '');
if ($secret === '' || $token === '') reply(500, ['ok'=>false, 'error'=>'Environment variables are not configured']);
$auth = (string)($_SERVER['HTTP_AUTHORIZATION'] ?? '');
if (!preg_match('/^Bearer\s+(.+)$/i', $auth, $m) || !hash_equals($secret, trim($m[1]))) reply(401, ['ok'=>false, 'error'=>'Unauthorized']);
$data = json_decode(file_get_contents('php://input') ?: '', true);
if (!is_array($data)) reply(400, ['ok'=>false, 'error'=>'Valid JSON required']);
$chat = trim((string)($data['chat_id'] ?? '')); $text = trim((string)($data['text'] ?? ''));
if ($chat === '' || !preg_match('/^-?\d{1,20}$/', $chat)) reply(400, ['ok'=>false, 'error'=>'Numeric chat_id required']);
if ($text === '' || strlen($text) > 4000) reply(400, ['ok'=>false, 'error'=>'text is required and must be at most 4000 bytes']);
$ch = curl_init('https://api.telegram.org/bot'.$token.'/sendMessage');
curl_setopt_array($ch, [CURLOPT_POST=>true, CURLOPT_POSTFIELDS=>json_encode(['chat_id'=>$chat,'text'=>$text,'disable_web_page_preview'=>true], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES), CURLOPT_HTTPHEADER=>['Content-Type: application/json'], CURLOPT_RETURNTRANSFER=>true, CURLOPT_CONNECTTIMEOUT=>10, CURLOPT_TIMEOUT=>20]);
$response = curl_exec($ch); $curlError = curl_error($ch); $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
if ($response === false) reply(502, ['ok'=>false, 'error'=>'Telegram connection failed', 'detail'=>$curlError]);
$result = json_decode($response, true);
if ($http < 200 || $http >= 300 || !is_array($result) || empty($result['ok'])) reply(502, ['ok'=>false, 'error'=>'Telegram rejected message', 'telegram_status'=>$http, 'detail'=>is_array($result) ? ($result['description'] ?? 'Unknown Telegram error') : 'Invalid response']);
reply(200, ['ok'=>true, 'delivered'=>true]);
