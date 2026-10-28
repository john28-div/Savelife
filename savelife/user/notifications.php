<?php
require_once __DIR__ . '/../includes/init.php';
require_role('user');

$stmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC, id DESC LIMIT 50');
$stmt->execute([$_SESSION['user_id']]);
$notes = $stmt->fetchAll();

// Mark as read after loading, so this visit still shows which ones were new.
$pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0')->execute([$_SESSION['user_id']]);

$page_title = 'Notifications';
$active = 'notes';
require __DIR__ . '/../includes/header.php';
?>
<main class="wrap">
  <h1>Notifications</h1>
  <p class="lead">Updates about your adoption requests.</p>

  <?php if (!$notes): ?>
    <div class="empty">No notifications yet.</div>
  <?php else: ?>
    <div class="list">
      <?php foreach ($notes as $n): ?>
        <div class="note <?= $n['is_read'] ? '' : 'unread' ?>">
          <?= e($n['message']) ?>
          <small><?= e(date('M j, Y g:i A', strtotime($n['created_at']))) ?></small>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
