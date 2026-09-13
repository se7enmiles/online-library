<?php
require 'config.php';

// 1. Empty the session data
$_SESSION = [];

// 2. Delete the session cookie in the browser
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'],
              $params['secure'], $params['httponly']);
}

// 3. Destroy the session file on the server
session_destroy();

// 4. Start a fresh session just to carry the goodbye message
session_start();
setFlash('You are logged out.', 'info');

header('Location: index.php');
exit;
