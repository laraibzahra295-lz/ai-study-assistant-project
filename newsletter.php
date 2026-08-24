<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    send_json(['error' => 'Method not allowed'], 405);
}

$body  = read_json_body();
$email = trim((string)($body['email'] ?? ''));

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    send_json(['error' => 'Valid email is required'], 400);
}

try {
    // Check if already subscribed
    $check = db()->prepare('SELECT id FROM newsletter WHERE email = ?');
    $check->execute([$email]);
    $existing = $check->fetchColumn();

    if ($existing) {
        send_json(['message' => 'You are already subscribed!']);
    }

    $stmt = db()->prepare('INSERT INTO newsletter (email) VALUES (?)');
    $stmt->execute([$email]);

    send_json(['message' => 'Successfully subscribed to our newsletter!'], 201);

} catch (Throwable $e) {
    // Handle duplicate key gracefully
    if (strpos($e->getMessage(), 'UNIQUE') !== false || strpos($e->getMessage(), 'Duplicate') !== false) {
        send_json(['message' => 'You are already subscribed!']);
    }
    send_json(['error' => 'Failed to subscribe: ' . $e->getMessage()], 500);
}
