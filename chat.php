<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/gemini.php';

// Allow only POST
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {

    send_json([
        'error' => 'Method not allowed'
    ], 405);
}

/**
 * Accept:
 * - application/json
 * - multipart/form-data
 */

$contentType =
    $_SERVER['CONTENT_TYPE'] ?? '';

if (
    strpos(
        $contentType,
        'multipart/form-data'
    ) !== false
) {

    $raw =
        $_POST['messages'] ?? '[]';

    $messages =
        json_decode($raw, true) ?? [];

} else {

    $body = read_json_body();

    $messages =
        $body['messages'] ?? [];
}

// Validate messages
if (
    !is_array($messages) ||
    empty($messages)
) {

    send_json([
        'error' =>
            '"messages" array is required'
    ], 400);
}

// System prompt
$systemInstruction =
    "You are a friendly and helpful AI study assistant. "
    . "Give accurate and concise answers. "
    . "Use simple explanations, short paragraphs, "
    . "examples, and bullet points when useful.";

// Convert messages for Gemini
$contents = [];

foreach ($messages as $m) {

    $role =
        (($m['role'] ?? 'user') === 'assistant')
        ? 'model'
        : 'user';

    $content =
        trim((string)(
            $m['content'] ?? ''
        ));

    // Skip empty messages
    if ($content === '') {
        continue;
    }

    $contents[] = [

        'role' => $role,

        'parts' => [
            [
                'text' => $content
            ]
        ]
    ];
}

// Check again after cleanup
if (empty($contents)) {

    send_json([
        'error' => 'No valid messages found'
    ], 400);
}

try {

    // Generate AI response
    $reply = gemini_generate(

        $contents,

        [
            'systemInstruction' =>
                $systemInstruction
        ]
    );

    // Success response
    send_json([
        'reply' => $reply
    ]);

} catch (Throwable $e) {

    send_json([

        'error' => 'Chat failed',

        'details' => $e->getMessage()

    ], 500);
}