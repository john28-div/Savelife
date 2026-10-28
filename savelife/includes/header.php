<?php
// Expects: $pdo, $page_title, $active. Prints <head> and the top navigation.
$isAdmin = ($_SESSION['role'] ?? '') === 'admin';
$unread = 0;
if (!$isAdmin) {
    $q = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
    $q->execute([$_SESSION['user_id']]);
    $unread = (int)$q->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($page_title) ?> | SaveLife</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:wght@500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/auth.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
</head>
<body>
<header class="topbar">
  <a class="brand" href="<?= BASE_URL ?><?= $isAdmin ? '/admin/dashboard.php' : '/user/dashboard.php' ?>">SaveLife<?= $isAdmin ? ' Admin' : '' ?></a>
  <nav>
    <?php if ($isAdmin): ?>
      <?= nav_link('/admin/dashboard.php', 'dash', 'Dashboard', $active) ?>
      <?= nav_link('/admin/animals.php', 'animals', 'Animals', $active) ?>
      <?= nav_link('/admin/requests.php', 'requests', 'Adoption requests', $active) ?>
      <?= nav_link('/admin/strays.php', 'strays', 'Stray reports', $active) ?>
    <?php else: ?>
      <?= nav_link('/user/dashboard.php', 'home', 'Home', $active) ?>
      <?= nav_link('/user/animals.php', 'animals', 'Find A Pet', $active) ?>
      <?= nav_link('/user/report_stray.php', 'stray', 'Report Stray', $active) ?>
      <?= nav_link('/user/my_requests.php', 'requests', 'My Requests', $active) ?>
      <?= nav_link('/user/notifications.php', 'notes', 'Notifications', $active, $unread) ?>
      <span class="who"><?= e($_SESSION['full_name']) ?></span>
    <?php endif; ?>
    <a href="<?= BASE_URL ?>/logout.php">Log out</a>
  </nav>
</header>
