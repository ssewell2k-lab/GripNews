<?php
/**
 * Newsletter Signup Endpoint
 * Stores email signups in /gripnews.uk/data/newsletter.json
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$email = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL);

if (!$email) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email address']);
    exit;
}

$source = preg_replace('/[^a-z0-9_-]/i', '', $input['source'] ?? 'popup');
$page   = preg_replace('/[^a-z0-9\/_.-]/i', '', $input['page'] ?? '/');

$dataFile = __DIR__ . '/data/newsletter.json';

// Load existing subscribers
$subscribers = [];
if (file_exists($dataFile)) {
    $subscribers = json_decode(file_get_contents($dataFile), true) ?: [];
}

// Check for duplicate
$exists = false;
foreach ($subscribers as $sub) {
    if (strtolower($sub['email']) === strtolower($email)) {
        $exists = true;
        break;
    }
}

if (!$exists) {
    $subscribers[] = [
        'email'      => $email,
        'source'     => $source,
        'page'       => $page,
        'ip'         => $_SERVER['REMOTE_ADDR'] ?? '',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'created_at' => date('c')
    ];

    // Ensure data directory exists
    if (!is_dir(__DIR__ . '/data')) {
        mkdir(__DIR__ . '/data', 0755, true);
    }

    file_put_contents($dataFile, json_encode($subscribers, JSON_PRETTY_PRINT));
}

echo json_encode([
    'success' => true,
    'message' => $exists ? 'Already subscribed' : 'Subscribed successfully'
]);