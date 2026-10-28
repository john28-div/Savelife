<?php
require_once __DIR__ . '/../includes/init.php';
require_role('admin');

$statuses = ['Reported', 'Reviewed', 'Rescued', 'Closed'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $id     = (int)($_POST['report_id'] ?? 0);
    $status = $_POST['status'] ?? '';
    if ($id && in_array($status, $statuses, true)) {
        $pdo->prepare('UPDATE stray_reports SET status = ? WHERE id = ?')->execute([$status, $id]);
        set_flash('Report updated.');
    }
    header('Location: ' . BASE_URL . '/admin/strays.php');
    exit;
}

$reports = $pdo->query(
    'SELECT s.*, u.full_name FROM stray_reports s JOIN users u ON u.id = s.user_id
     ORDER BY s.created_at DESC, s.id DESC'
)->fetchAll();

$page_title = 'Stray reports';
$active = 'strays';
require __DIR__ . '/../includes/header.php';
?>
<main class="wrap">
  <?= render_flash() ?>
  <h1>Stray reports</h1>
  <p class="lead">Reports sent by users. Mark progress, or add the animal to the adoption list.</p>

  <?php if (!$reports): ?>
    <div class="empty">No stray reports yet.</div>
  <?php else: ?>
    <div class="list">
      <?php foreach ($reports as $s): ?>
        <article class="row-card">
          <img src="<?= img_url($s['image']) ?>" alt="">
          <div>
            <h3><?= e($s['location']) ?> <span class="status <?= e(strtolower($s['status'])) ?>"><?= e($s['status']) ?></span></h3>
            <p><?= e($s['description']) ?></p>
            <p><strong>Reported by:</strong> <?= e($s['full_name']) ?> &middot; <strong>Contact:</strong> <?= e($s['contact']) ?></p>
            <p><small><?= e(date('M j, Y g:i A', strtotime($s['created_at']))) ?></small></p>
          </div>
          <div class="row-actions">
            <form method="post">
              <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
              <input type="hidden" name="report_id" value="<?= (int)$s['id'] ?>">
              <select name="status" onchange="this.form.submit()" aria-label="Report status">
                <?php foreach ($statuses as $st): ?>
                  <option value="<?= e($st) ?>" <?= $st === $s['status'] ? 'selected' : '' ?>><?= e($st) ?></option>
                <?php endforeach; ?>
              </select>
            </form>
            <a class="btn green inline" href="animal_form.php?from_stray=<?= (int)$s['id'] ?>">Add as animal</a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
