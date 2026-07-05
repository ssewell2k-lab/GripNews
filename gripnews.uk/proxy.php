<?php
/**
 * GripNews API Proxy
 * Proxies requests to external APIs (hotspots, observations)
 * Migrated from chatbox.gripai.uk/proxy.php
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=25');

$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : '';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 200;

$allowed = [
    'hotspots' => "https://api.gripai.uk/v1/live/hotspots?limit={$limit}",
    'observations' => "https://gripai.uk/chatter/observations"
];

if (!isset($allowed[$endpoint])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid endpoint']);
    exit;
}

$url = $allowed[$endpoint];
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, $endpoint === 'observations' ? 30 : 10);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || $response === false) {
    http_response_code(502);
    echo json_encode(['error' => 'Upstream error']);
    exit;
}

echo $response;
