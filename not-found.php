<?php
require 'config.php';

http_response_code(404);

$pageTitle = 'Page not found';
require 'includes/header.php';
?>

<div class="text-center py-5">
    <div style="font-size:4rem">📚</div>
    <h1 class="h3 mt-3">We couldn't find that page</h1>
    <p class="text-muted">The link may be old, or the book may have been removed.</p>
    <a href="index.php" class="btn btn-primary mt-2">Back to the catalog</a>
</div>

<?php require 'includes/footer.php'; ?>
