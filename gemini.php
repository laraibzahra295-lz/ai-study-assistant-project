<?php

require_once __DIR__ . '/config.php';

/**
 * Call Gemini REST API
 *
 * @param string|array $contents
 * @param array $config
 * @return string
 */

function gemini_generate($contents, array $config = []): string
{

    // API key check
    if (
        empty(GEMINI_API_KEY) ||
        GEMINI_API_KEY === 'PASTE_YOUR_GEMINI_API_KEY_HERE'
    ) {

        send_json([
            'error' =>
                'Gemini API key not configured'
        ], 500);
    }

    /**
     * Build contents
     */

    if (is_string($contents)) {

        $payloadContents = [
            [
                'role' => 'user',

                'parts' => [
                    [
                        'text' => $contents
                    ]
                ]
            ]
        ];

    } else {

        $payloadContents = $contents;
    }

    /**
     * Main request body
     */

    $body = [

        'contents' => $payloadContents
    ];

    /**
     * Generation config
     */

    $generationConfig = [

        'maxOutputTokens' => 8192
    ];

    if (!empty($config['responseMimeType'])) {

        $generationConfig['responseMimeType'] =
            $config['responseMimeType'];
    }

    $body['generationConfig'] =
        $generationConfig;

    /**
     * System instruction
     */

    if (!empty($config['systemInstruction'])) {

        // IMPORTANT:
        // Gemini REST API uses system_instruction

        $body['system_instruction'] = [

            'parts' => [
                [
                    'text' =>
                        $config['systemInstruction']
                ]
            ]
        ];
    }

    /**
     * API URL
     */

    $url =
        GEMINI_ENDPOINT .
        GEMINI_MODEL .
        ':generateContent?key=' .
        urlencode(GEMINI_API_KEY);

    /**
     * CURL request
     */

    $ch = curl_init($url);

    curl_setopt_array($ch, [

        CURLOPT_RETURNTRANSFER => true,

        CURLOPT_POST => true,

        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json'
        ],

        CURLOPT_POSTFIELDS => json_encode(
            $body,
            JSON_UNESCAPED_UNICODE
        ),

        CURLOPT_TIMEOUT => 60,

        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response = curl_exec($ch);

    $curlErr = curl_error($ch);

    $status = curl_getinfo(
        $ch,
        CURLINFO_HTTP_CODE
    );

    curl_close($ch);

    /**
     * CURL error
     */

    if ($response === false) {

        send_json([

            'error' =>
                'Gemini request failed',

            'details' => $curlErr

        ], 500);
    }

    /**
     * Decode response
     */

    $decoded = json_decode(
        $response,
        true
    );

    /**
     * API error
     */

    if (
        $status >= 400 ||
        !is_array($decoded)
    ) {

        send_json([

            'error' =>
                'Gemini API error (HTTP '
                . $status . ')',

            'details' =>
                is_array($decoded)
                ? (
                    $decoded['error']['message']
                    ?? $response
                )
                : $response,

        ], 500);
    }

    /**
     * Extract text
     */

    $text = '';

    foreach (

        $decoded['candidates'][0]['content']['parts']
        ?? []

        as $part

    ) {

        if (isset($part['text'])) {

            $text .= $part['text'];
        }
    }

    return trim($text);
}