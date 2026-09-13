<?php
// BookLoop — talking to an AI model.
// The key lives in config.local.php, which is NOT shared and NOT committed.

const AI_URL   = 'https://api.anthropic.com/v1/messages';
const AI_MODEL = 'claude-sonnet-4-6';

/**
 * Do we have a key configured?
 */
function aiEnabled(): bool
{
    return defined('AI_API_KEY') && AI_API_KEY !== '';
}

/**
 * Send a prompt, get plain text back. Returns null when something goes wrong.
 */
function askAi(string $prompt, int $maxTokens = 400): ?string
{
    if (!aiEnabled()) {
        return null;
    }

    $response = httpPostJson(
        AI_URL,
        [
            'model'      => AI_MODEL,
            'max_tokens' => $maxTokens,
            'messages'   => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ],
        [
            'x-api-key: ' . AI_API_KEY,
            'anthropic-version: 2023-06-01',
        ]
    );

    if ($response['status'] !== 200 || !isset($response['data']['content'][0]['text'])) {
        return null;
    }

    return trim($response['data']['content'][0]['text']);
}

/**
 * A short blurb for one book.
 */
function aiDescribeBook(string $title, string $author): ?string
{
    $prompt = "Write a short description of the book \"$title\" by $author "
            . "for a library catalogue. Two or three sentences, no spoilers, "
            . "plain text only, no heading and no quotation marks around it. "
            . "If you are not sure the book exists, reply exactly: UNKNOWN";

    $text = askAi($prompt, 300);

    return ($text === null || $text === 'UNKNOWN') ? null : $text;
}

/**
 * Recommendations based on what the user likes and what is in the catalog.
 * We ask for JSON so we can render the answer ourselves instead of dumping text.
 */
function aiRecommend(string $taste, array $catalog): ?array
{
    if (!$catalog) {
        return [];
    }

    // Only send what the model needs: id, title, author, genre.
    $lines = [];
    foreach ($catalog as $book) {
        $lines[] = $book['id'] . ' | ' . $book['title'] . ' | '
                 . $book['author'] . ' | ' . $book['genre'];
    }

    $prompt = "Here is a library catalogue, one book per line as id | title | author | genre:\n\n"
            . implode("\n", $lines)
            . "\n\nA reader says: \"" . $taste . "\"\n\n"
            . "Choose up to 3 books from the catalogue for this reader. "
            . "Reply with JSON only, no markdown fences, in exactly this shape:\n"
            . '[{"id": 1, "reason": "one short sentence"}]' . "\n"
            . "Use only ids from the catalogue above. If nothing fits, reply []";

    $text = askAi($prompt, 500);

    if ($text === null) {
        return null;
    }

    // Models sometimes wrap JSON in ```json fences even when asked not to.
    $text = trim(preg_replace('/^```(json)?|```$/m', '', $text));

    $picks = json_decode($text, true);

    return is_array($picks) ? $picks : null;
}
