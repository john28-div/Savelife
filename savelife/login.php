<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (!empty($_SESSION['user_id'])) { redirect_by_role(); }

$error = '';
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $error = 'Your session expired. Please try again.';
    } else {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare('SELECT id, full_name, password_hash, role FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);   // prevents session fixation
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role']      = $user['role'];
            redirect_by_role();            // admin -> admin dashboard, user -> user dashboard
        }
        // Same message for wrong email or wrong password on purpose.
        $error = 'Incorrect email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Log in | SaveLife</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:wght@500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
<main class="auth">
  <section class="auth-form">
    <a class="brand" href="login.php">SaveLife</a>
    <h1>Welcome back.</h1>
    <p class="lead">Log in to find a pet, report a stray, or check your adoption requests.</p>

    <?php if ($flash): ?><div class="alert ok" role="status"><?= e($flash) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert error" role="alert"><?= e($error) ?></div><?php endif; ?>

    <form method="post" novalidate>
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

      <label for="email">Email</label>
      <input type="email" id="email" name="email" placeholder="you@email.com" value="<?= e($email) ?>" required autofocus>

      <label for="password">Password</label>
      <div class="pw">
        <input type="password" id="password" name="password" placeholder="Enter your password" required>
        <button type="button" class="toggle" data-target="password">Show</button>
      </div>

      <button type="submit" class="btn orange">Log in</button>
    </form>

    <p class="switch">New to SaveLife? <a href="register.php">Create an account</a></p>
  </section>

  <aside class="auth-art" aria-hidden="true">
    <div class="caption">
      <h2>Every stray deserves a second life.</h2>
      <p>Join SaveLife to report animals near you and match them with loving families.</p>
    </div>
  </aside>
</main>
<script src="assets/js/auth.js"></script>
</body>
</html>
