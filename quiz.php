<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/gemini.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    send_json(['error' => 'Method not allowed'], 405);
}

$body  = read_json_body();
$topic = trim((string)($body['topic'] ?? ''));
$count = max(1, min(20, (int)($body['count'] ?? 10)));

if ($topic === '') {
    send_json(['error' => 'Topic is required'], 400);
}

$prompt = "You are a strict exam question generator.

Generate EXACTLY {$count} multiple-choice questions about: {$topic}

RULES:
- Exactly {$count} questions — no more, no less
- Each question has exactly 4 options
- answerIndex is 0-based (0=A, 1=B, 2=C, 3=D)
- Return ONLY valid JSON — no markdown, no extra text

Required format:
{
  \"topic\": \"{$topic}\",
  \"questions\": [
    {
      \"question\": \"Question text here?\",
      \"options\": [\"Option A\", \"Option B\", \"Option C\", \"Option D\"],
      \"answerIndex\": 0,
      \"explanation\": \"Brief explanation\"
    }
  ]
}";

try {
    $text = gemini_generate($prompt, ['responseMimeType' => 'application/json']);

    // Clean response
    $text = trim($text);
    $text = preg_replace('/^```json\s*/i', '', $text);
    $text = preg_replace('/^```\s*/i', '', $text);
    $text = preg_replace('/\s*```$/i', '', $text);
    $text = trim($text);

    $data = json_decode($text, true);

    if (!is_array($data) || empty($data['questions']) || !is_array($data['questions'])) {
        send_json(['error' => 'AI returned invalid response. Please try again.'], 500);
    }

    // Validate and sanitize questions
    $questions = [];
    foreach ($data['questions'] as $q) {
        if (count($questions) >= $count) break;

        if (
            empty($q['question']) ||
            !isset($q['options']) ||
            !is_array($q['options']) ||
            count($q['options']) !== 4
        ) continue;

        $questions[] = [
            'question'    => (string)$q['question'],
            'options'     => array_values(array_map('strval', $q['options'])),
            'answerIndex' => max(0, min(3, (int)($q['answerIndex'] ?? 0))),
            'explanation' => (string)($q['explanation'] ?? ''),
        ];
    }

    if (empty($questions)) {
        send_json(['error' => 'No valid questions generated. Try a different topic.'], 500);
    }

    send_json([
        'topic'     => (string)($data['topic'] ?? $topic),
        'questions' => $questions,
    ]);

} catch (Throwable $e) {
    send_json(['error' => 'Quiz generation failed: ' . $e->getMessage()], 500);
}
