<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    send_json(['error' => 'Method not allowed'], 405);
}

$body   = read_json_body();
$action = (string)($body['action'] ?? 'login');
$name   = trim((string)($body['name']     ?? ''));
$email  = trim((string)($body['email']    ?? ''));
$pass   = (string)($body['password'] ?? '');

if ($email === '' || $pass === '') {
    send_json(['error' => 'Email and password are required'], 400);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    send_json(['error' => 'Invalid email address'], 400);
}

try {
    if ($action === 'signup') {
        if ($name === '')        send_json(['error' => 'Name is required'], 400);
        if (strlen($pass) < 6)   send_json(['error' => 'Password must be at least 6 characters'], 400);

        $check = db()->prepare('SELECT id FROM users WHERE email = ?');
        $check->execute([$email]);
        if ($check->fetchColumn()) {
            send_json(['error' => 'Email already registered'], 409);
        }

        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $ins  = db()->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
        $ins->execute([$name, $email, $hash]);

        $userId = (int)db()->lastInsertId();

        send_json([
            'message' => 'Account created successfully',
            'user'    => ['id' => $userId, 'name' => $name, 'email' => $email],
        ], 201);
    }

    // ---- login ----
    $stmt = db()->prepare('SELECT id, name, email, password FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($pass, $user['password'])) {
        send_json(['error' => 'Invalid email or password'], 401);
    }

    send_json([
        'message' => 'Login successful',
        'user'    => ['id' => (int)$user['id'], 'name' => $user['name'], 'email' => $user['email']],
    ]);

} catch (Throwable $e) {
    send_json(['error' => 'Auth failed: ' . $e->getMessage()], 500);
}
