<?php
require_once __DIR__ . '/../includes/init.php';
require_role('user');

$stmt = $pdo->prepare(
    'SELECT r.*, a.name AS animal_name, a.type, a.image
     FROM adoption_requests r JOIN animals a ON a.id = r.animal_id
     WHERE r.user_id = ? ORDER BY r.date_requested DESC, r.id DESC'
);
$stmt->execute([$_SESSION['user_id']]);
$requests = $stmt->fetchAll();

$messages = [
    'Pending'  => 'Waiting for the admin to review your request.',
    'Approved' => 'Your request was accepted. The admin will contact you using the details you provided.',
    'Declined' => 'Your request was declined.',
];

$page_title = 'My Requests';
$active = 'requests';
require __DIR__ . '/../includes/header.php';
?>
<main class="wrap">
  <?= render_flash() ?>
  <h1>My requests</h1>
  <p class="lead">Track the adoption requests you sent.</p>

  <?php if (!$requests): ?>
    <div class="empty">You have not sent any requests yet. <a href="animals.php">Find a pet</a></div>
  <?php else: ?>
    <div class="list">
      <?php foreach ($requests as $r): ?>
        <article class="row-card" style="grid-template-columns:110px 1fr auto">
          <img src="<?= img_url($r['image']) ?>" alt="<?= e($r['animal_name']) ?>">
          <div>
            <h3><?= e($r['animal_name']) ?> <small style="font-family:'DM Sans',sans-serif;color:var(--muted);font-weight:400">(<?= e($r['type']) ?>)</small></h3>
            <p><?= e($messages[$r['status']] ?? '') ?></p>
            <p>Sent <?= e(date('M j, Y g:i A', strtotime($r['date_requested']))) ?></p>
          </div>
          <span class="status <?= e(strtolower($r['status'])) ?>"><?= e($r['status']) ?></span>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
