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
        <label class="form-label" for="description">Description <span class="text-muted">(optional)</span></label>
        <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars((string) $book['description']) ?></textarea>
    </div>

    <button class="btn btn-primary" type="submit">Save book</button>
    <a href="my-books.php" class="btn btn-link">Cancel</a>
</form>
