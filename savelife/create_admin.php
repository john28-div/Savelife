<?php
// ONE-TIME SETUP. Creates the admin account with a hashed password.
// Works only while no admin exists. DELETE THIS FILE after you use it.
require_once 'includes/db.php';
require_once 'includes/auth.php';

$exists = $pdo->query('SELECT COUNT(*) FROM users WHERE role = "admin"')->fetchColumn() > 0;
$msg = '';

if (!$exists && $_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    if (filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($pass) >= 8) {
        $stmt = $pdo->prepare('INSERT INTO users (full_name, email, password_hash, role) VALUES ("Administrator", ?, ?, "admin")');
        $stmt->execute([$email, password_hash($pass, PASSWORD_DEFAULT)]);
        $msg = 'Admin created. Delete create_admin.php now.';
        $exists = true;
    } else {
        $msg = 'Use a valid email and a password of at least 8 characters.';
    }
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><title>Create admin</title>
<link rel="stylesheet" href="assets/css/auth.css"></head>
<body style="display:grid;place-items:center;min-height:100vh">
<section class="auth-form" style="max-width:420px">
  <h1>Create admin</h1>
  <?php if ($msg): ?><div class="alert ok"><?= e($msg) ?></div><?php endif; ?>
  <?php if ($exists): ?>
    <p class="lead">An admin already exists. Delete this file.</p>
  <?php else: ?>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <label for="email">Admin email</label>
      <input type="email" id="email" name="email" required>
      <label for="password">Admin password (8+ characters)</label>
      <input type="password" id="password" name="password" required>
      <button class="btn green" type="submit">Create admin</button>
    </form>
  <?php endif; ?>
</section></body></html>
