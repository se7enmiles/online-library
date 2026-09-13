<?php
require 'config.php';

// Already logged in? Nothing to do here.
if (isLoggedIn()) {
    header('Location: account.php');
    exit;
}

$errors = [];
$name   = '';
$email  = '';

// The form posts to this same file. Only run the code below on a POST request.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Read the fields. trim() removes accidental spaces at the ends.
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    // 2. Validate
    if ($name === '') {
        $errors[] = 'Please enter your name.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'The password must be at least 8 characters long.';
    }
    if ($password !== $confirm) {
        $errors[] = 'The two passwords do not match.';
    }

    // 3. Is the email already taken?
    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with that email already exists.';
        }
    }

    // 4. All good — hash the password and save the user.
    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
        $stmt->execute([$name, $email, $hash]);

        // Log the new user in straight away.
        $_SESSION['user_id'] = $pdo->lastInsertId();

        setFlash('Welcome to BookLoop, ' . $name . '!');
        header('Location: index.php');
        exit;
    }
}

$pageTitle = 'Create account';
require 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <h1 class="h3 mb-4">Create your account</h1>

        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="register.php" novalidate>
            <div class="mb-3">
                <label class="form-label" for="name">Name</label>
                <input class="form-control" type="text" id="name" name="name"
                       value="<?= htmlspecialchars($name) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input class="form-control" type="email" id="email" name="email"
                       value="<?= htmlspecialchars($email) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input class="form-control" type="password" id="password" name="password" required>
                <div class="form-text">At least 8 characters.</div>
            </div>
            <div class="mb-4">
                <label class="form-label" for="confirm">Repeat password</label>
                <input class="form-control" type="password" id="confirm" name="confirm" required>
            </div>
            <button class="btn btn-primary w-100" type="submit">Create account</button>
        </form>

        <p class="text-center text-muted mt-3 mb-0">
            Already have an account? <a href="login.php">Log in</a>
        </p>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
