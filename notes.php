<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/gemini.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    send_json(['error' => 'Method not allowed'], 405);
}

$body    = read_json_body();
$content = trim((string)($body['content'] ?? ''));

if ($content === '') {
    send_json(['error' => 'Content is required'], 400);
}

$prompt = 'You are an expert teacher and study notes creator.

Create HIGH QUALITY STUDY NOTES from the material below.

IMPORTANT: Return ONLY valid JSON. No markdown, no code blocks, no extra text.

Required format:
{
  "summary": "2-3 sentence simple summary",
  "keyPoints": ["point 1", "point 2", "point 3", "point 4", "point 5"],
  "notes": "Well-structured detailed notes with headings and bullet points"
}

RULES:
- Return ONLY the JSON object
- summary: 2-3 sentences, easy to understand
- keyPoints: 4-6 short, clear bullet points
- notes: detailed, well-organized, student-friendly

MATERIAL:
"""' . $content . '"""';

try {
    $text = gemini_generate($prompt, ['responseMimeType' => 'application/json']);

    // Clean response
    $text = trim($text);
    $text = preg_replace('/^```json\s*/i', '', $text);
    $text = preg_replace('/^```\s*/i', '', $text);
    $text = preg_replace('/\s*```$/i', '', $text);
    $text = trim($text);

    $data = json_decode($text, true);

    if (!is_array($data)) {
        send_json(['error' => 'AI returned invalid response. Please try again.', 'raw' => substr($text, 0, 200)], 500);
    }

    send_json([
        'summary'   => (string)($data['summary'] ?? ''),
        'keyPoints' => isset($data['keyPoints']) && is_array($data['keyPoints'])
            ? array_values(array_map('strval', $data['keyPoints']))
            : [],
        'notes'     => (string)($data['notes'] ?? ''),
    ]);

} catch (Throwable $e) {
    send_json(['error' => 'Notes generation failed: ' . $e->getMessage()], 500);
}
