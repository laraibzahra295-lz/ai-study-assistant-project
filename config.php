<?php

error_reporting(0);
ini_set('display_errors', 0);

/**
 * CORS (for frontend chat apps)
 */
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight request
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
    exit;
}

/**
 * Timezone
 */
date_default_timezone_set('Asia/Karachi');

/**
 * =========================
 * DATABASE CONFIG (optional)
 * =========================
 */
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_NAME', 'ai_study_assistant');
define('DB_USER', 'root');
define('DB_PASS', '');

/**
 * =========================
 * GEMINI API CONFIG
 * =========================
 */

define('GEMINI_API_KEY', 'api key paste'); // <-- change this

define('GEMINI_MODEL', 'gemini-1.5-flash');

/**
 * IMPORTANT:
 * Use v1beta (NOT v1)
 */
define(
    'GEMINI_ENDPOINT',
    'https://generativelanguage.googleapis.com/v1beta/models/'
);

/**
 * =========================
 * HELPERS
 * =========================
 */

/**
 * Send JSON response
 */
function send_json($data, $status = 200)
{
    http_response_code($status);

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    exit;
}

/**
 * Read JSON request body
 */
function read_json_body()
{
    $raw = file_get_contents("php://input");

    $data = json_decode($raw, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return [];
    }

    return $data ?? [];
}
