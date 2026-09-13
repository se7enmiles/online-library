<?php
// A small JSON endpoint. It is called by JavaScript, not opened in the browser.
require 'config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Please log in.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'POST only.']);
    exit;
}

if (!aiEnabled()) {
    http_response_code(503);
    echo json_encode(['error' => 'No AI key is configured.']);
    exit;
}

// The browser sends JSON, so we read the raw body instead of $_POST.
$input  = json_decode(file_get_contents('php://input'), true) ?? [];
$title  = trim($input['title'] ?? '');
$author = trim($input['author'] ?? '');

if ($title === '' || $author === '') {
    http_response_code(422);
    echo json_encode(['error' => 'A title and an author are needed.']);
    exit;
}

$description = aiDescribeBook($title, $author);

if ($description === null) {
    http_response_code(502);
    echo json_encode(['error' => 'The AI could not describe that book.']);
    exit;
}

echo json_encode(['description' => $description]);
