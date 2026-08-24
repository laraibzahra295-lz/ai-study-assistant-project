<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    send_json(['error' => 'Method not allowed'], 405);
}

$body    = read_json_body();
$name    = trim((string)($body['name']    ?? ''));
$email   = trim((string)($body['email']   ?? ''));
$subject = trim((string)($body['subject'] ?? ''));
$message = trim((string)($body['message'] ?? ''));

if ($name === '' || $email === '' || $subject === '' || $message === '') {
    send_json(['error' => 'All fields are required'], 400);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    send_json(['error' => 'Invalid email address'], 400);
}

try {
    $stmt = db()->prepare(
        'INSERT INTO contacts (name, email, subject, message) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$name, $email, $subject, $message]);
    send_json(['message' => "Message sent! We'll get back to you within 24 hours."], 201);
} catch (Throwable $e) {
    send_json(['error' => 'Failed to save message: ' . $e->getMessage()], 500);
}
