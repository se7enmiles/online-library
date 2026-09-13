<?php
require 'config.php';

if (isLoggedIn()) {
    header('Location: account.php');
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // 1. Find the user by email
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // 2. Compare the typed password with the stored hash
    if ($user && password_verify($password, $user['password'])) {

        // 3. Success — give the session a fresh id, then remember the user
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];

        setFlash('Welcome back, ' . $user['name'] . '!');
        header('Location: index.php');
        exit;
    }

    // Same message for a wrong email and a wrong password, on purpose.
    $error = 'Wrong email or password.';
}

$pageTitle = 'Log in';
require 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <h1 class="h3 mb-4">Log in</h1>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="login.php">
            <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input class="form-control" type="email" id="email" name="email"
                       value="<?= htmlspecialchars($email) ?>" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label" for="password">Password</label>
                <input class="form-control" type="password" id="password" name="password" required>
            </div>
            <button class="btn btn-primary w-100" type="submit">Log in</button>
        </form>

        <p class="text-center text-muted mt-3 mb-0">
            No account yet? <a href="register.php">Create one</a>
        </p>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
