<?php
// Shared by add-book.php and edit-book.php.
// Both pages prepare $book (the values) and $errors (the messages) before requiring this file.
?>

<?php if ($errors): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" novalidate>
    <div class="mb-3">
        <label class="form-label" for="title">Title</label>
        <input class="form-control" type="text" id="title" name="title"
               value="<?= htmlspecialchars($book['title']) ?>" required>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label" for="author">Author</label>
            <input class="form-control" type="text" id="author" name="author"
                   value="<?= htmlspecialchars($book['author']) ?>" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label" for="genre">Genre</label>
            <input class="form-control" type="text" id="genre" name="genre" list="genres"
                   value="<?= htmlspecialchars($book['genre']) ?>" required>
            <datalist id="genres">
                <option value="Fantasy"><option value="Science Fiction"><option value="History">
                <option value="Romance"><option value="Fable"><option value="Poetry">
            </datalist>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label" for="year">Year <span class="text-muted">(optional)</span></label>
        <input class="form-control" type="number" id="year" name="year" style="max-width:160px"
               value="<?= htmlspecialchars((string) $book['year']) ?>">
    </div>

    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="form-label mb-0" for="description">Description <span class="text-muted">(optional)</span></label>

            <?php if (aiEnabled()): ?>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="aiButton">
                    ✨ Generate description
                </button>
            <?php endif; ?>
        </div>

        <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars((string) $book['description']) ?></textarea>
        <div class="form-text" id="aiStatus"></div>
    </div>

    <button class="btn btn-primary" type="submit">Save book</button>
    <a href="my-books.php" class="btn btn-link">Cancel</a>
</form>

<?php if (aiEnabled()): ?>
<script>
// Ask the server for a description without reloading the page.
document.getElementById('aiButton').addEventListener('click', async () => {
    const button = document.getElementById('aiButton');
    const status = document.getElementById('aiStatus');
    const title  = document.getElementById('title').value.trim();
    const author = document.getElementById('author').value.trim();

    if (!title || !author) {
        status.textContent = 'Fill in the title and the author first.';
        return;
    }

    button.disabled = true;
    status.textContent = 'Asking the AI…';

    try {
        const response = await fetch('ai-description.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ title, author })
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || 'Something went wrong.');
        }

        document.getElementById('description').value = data.description;
        status.textContent = 'Written by AI — read it and edit it before saving.';
    } catch (error) {
        status.textContent = error.message;
    } finally {
        button.disabled = false;
    }
});
</script>
<?php endif; ?>
