<?php
require 'config.php';

requireLogin();

$q       = trim($_GET['q'] ?? '');
$results = null;      // null = we haven't searched yet
$failed  = false;

if ($q !== '') {
    $results = searchBookApi($q);

    if ($results === null) {
        $failed  = true;
        $results = [];
    }
}

$pageTitle = 'Find a book online';
require 'includes/header.php';
?>

<h1 class="h3 mb-3">Find a book online</h1>
<p class="text-muted">
    Search the Open Library, then use the result to fill in the add-book form.
</p>

<form method="get" action="api-search.php" class="row g-2 mb-4">
    <div class="col-md-8">
        <input class="form-control" type="search" name="q" autofocus
               placeholder="Title, author, ISBN…" value="<?= htmlspecialchars($q) ?>">
    </div>
    <div class="col-md-2 d-grid">
        <button class="btn btn-primary" type="submit">Search</button>
    </div>
</form>

<?php if ($failed): ?>
    <div class="alert alert-warning">
        Could not reach the Open Library just now. Check your internet connection,
        or <a href="add-book.php">add the book by hand</a>.
    </div>
<?php endif; ?>

<?php if ($results !== null && !$failed): ?>
    <p class="text-muted"><?= count($results) ?> results for “<?= htmlspecialchars($q) ?>”</p>
<?php endif; ?>

<?php if ($results): ?>
    <div class="row g-4">
        <?php foreach ($results as $book): ?>
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body d-flex gap-3">
                        <?php if ($book['cover']): ?>
                            <img src="<?= htmlspecialchars($book['cover']) ?>" alt=""
                                 width="70" class="rounded" style="height:100px;object-fit:cover">
                        <?php endif; ?>
                        <div>
                            <h6 class="mb-1"><?= htmlspecialchars($book['title']) ?></h6>
                            <p class="text-muted small mb-1"><?= htmlspecialchars($book['author']) ?></p>
                            <?php if ($book['year']): ?>
                                <span class="badge text-bg-light border"><?= (int) $book['year'] ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0">
                        <!-- Everything the form needs, passed along in the URL -->
                        <a class="btn btn-sm btn-outline-primary"
                           href="add-book.php?<?= http_build_query([
                               'title'  => $book['title'],
                               'author' => $book['author'],
                               'genre'  => $book['genre'],
                               'year'   => $book['year'],
                           ]) ?>">Add to my books</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php elseif ($results !== null && !$failed): ?>
    <div class="alert alert-light border text-center py-4">
        Nothing found. Try fewer words, or <a href="add-book.php">add the book by hand</a>.
    </div>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>
