<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (!empty($_SESSION['user_id'])) { redirect_by_role(); }

$errors = [];
$old = ['full_name' => '', 'email' => '', 'wants_updates' => 0];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $full_name = trim($_POST['full_name'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = $_POST['password'] ?? '';
        $confirm   = $_POST['confirm_password'] ?? '';
        $wants     = isset($_POST['wants_updates']) ? 1 : 0;

        $old = ['full_name' => $full_name, 'email' => $email, 'wants_updates' => $wants];

        if ($full_name === '' || mb_strlen($full_name) > 100) {
            $errors[] = 'Enter your full name (up to 100 characters).';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 120) {
            $errors[] = 'Enter a valid email address.';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Passwords do not match.';
        }

        if (!$errors) {
            $check = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $check->execute([$email]);
            if ($check->fetch()) {
                $errors[] = 'That email is already registered. Log in instead.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $ins = $pdo->prepare(
                    'INSERT INTO users (full_name, email, password_hash, role, wants_updates)
                     VALUES (?, ?, ?, "user", ?)'
                );
                $ins->execute([$full_name, $email, $hash, $wants]);
                $_SESSION['flash'] = 'Account created. Log in to continue.';
                header('Location: ' . BASE_URL . '/login.php');
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Create account | SaveLife</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:wght@500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
<main class="auth">
  <section class="auth-form">
    <a class="brand" href="login.php">SaveLife</a>
    <h1>Give a stray a second chance.</h1>
    <p class="lead">Sign up to report animals near you, follow pets you love, and start an adoption application.</p>

    <?php if ($errors): ?>
      <div class="alert error" role="alert">
        <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post" id="registerForm" novalidate>
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

      <label for="full_name">Full name</label>
      <input type="text" id="full_name" name="full_name" placeholder="Alex Rivera" value="<?= e($old['full_name']) ?>" required>

      <label for="email">Email</label>
      <input type="email" id="email" name="email" placeholder="you@email.com" value="<?= e($old['email']) ?>" required>

      <label for="password">Password</label>
      <div class="pw">
        <input type="password" id="password" name="password" placeholder="At least 8 characters" required>
        <button type="button" class="toggle" data-target="password">Show</button>
      </div>

      <label for="confirm_password">Confirm password</label>
      <div class="pw">
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter your password" required>
        <button type="button" class="toggle" data-target="confirm_password">Show</button>
      </div>

      <label class="check">
        <input type="checkbox" name="wants_updates" <?= $old['wants_updates'] ? 'checked' : '' ?>>
        Send me adoption updates in my area
      </label>

      <button type="submit" class="btn green">Create account</button>
    </form>

    <p class="switch">Already have an account? <a href="login.php">Log in</a></p>
  </section>

  <aside class="auth-art" aria-hidden="true">
    <div class="caption">
      <h2>Together we’ve saved lives.</h2>
      <p>Your account helps match stray animals with families who will love them.</p>
    </div>
  </aside>
</main>
<script src="assets/js/auth.js"></script>
</body>
</html>
