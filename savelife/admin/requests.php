<?php
require_once __DIR__ . '/../includes/init.php';
require_role('admin');

// Approve or decline a request.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $rid    = (int)($_POST['request_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($rid && in_array($action, ['approve', 'decline'], true)) {
        try {
            $pdo->beginTransaction();

            $s = $pdo->prepare(
                'SELECT r.*, a.name AS animal_name, a.status AS animal_status
                 FROM adoption_requests r JOIN animals a ON a.id = r.animal_id
                 WHERE r.id = ? FOR UPDATE'
            );
            $s->execute([$rid]);
            $r = $s->fetch();
            if (!$r || $r['status'] !== 'Pending') {
                throw new RuntimeException('This request was already handled.');
            }

            if ($action === 'approve') {
                if ($r['animal_status'] !== 'Available') {
                    throw new RuntimeException('This animal is no longer available.');
                }
                $pdo->prepare("UPDATE adoption_requests SET status = 'Approved' WHERE id = ?")->execute([$rid]);
                $pdo->prepare("UPDATE animals SET status = 'Adopted' WHERE id = ?")->execute([$r['animal_id']]);
                notify($pdo, (int)$r['user_id'], 'Good news! Your adoption request for ' . $r['animal_name'] . ' was accepted.');

                // Other pending requests for the same animal are declined automatically.
                $o = $pdo->prepare("SELECT id, user_id FROM adoption_requests WHERE animal_id = ? AND status = 'Pending' AND id <> ?");
                $o->execute([$r['animal_id'], $rid]);
                foreach ($o->fetchAll() as $other) {
                    $pdo->prepare("UPDATE adoption_requests SET status = 'Declined' WHERE id = ?")->execute([$other['id']]);
                    notify($pdo, (int)$other['user_id'], $r['animal_name'] . ' was adopted by another applicant, so your request was declined.');
                }
                $msg = 'Request approved. ' . $r['animal_name'] . ' is now marked as adopted.';
            } else {
                $pdo->prepare("UPDATE adoption_requests SET status = 'Declined' WHERE id = ?")->execute([$rid]);
                notify($pdo, (int)$r['user_id'], 'Your adoption request for ' . $r['animal_name'] . ' was declined.');
                $msg = 'Request declined. ' . $r['animal_name'] . ' stays available.';
            }

            $pdo->commit();
            set_flash($msg);
        } catch (RuntimeException $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            set_flash($ex->getMessage());
        } catch (PDOException $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            set_flash('Something went wrong. Please try again.');
        }
    }
    header('Location: ' . BASE_URL . '/admin/requests.php' . (($_GET['show'] ?? '') === 'all' ? '?show=all' : ''));
    exit;
}

$show = (($_GET['show'] ?? 'pending') === 'all') ? 'all' : 'pending';
$sql = 'SELECT r.*, a.name AS animal_name, a.type, a.image
        FROM adoption_requests r JOIN animals a ON a.id = r.animal_id'
     . ($show === 'pending' ? " WHERE r.status = 'Pending'" : '')
     . ' ORDER BY r.date_requested DESC, r.id DESC';
$requests = $pdo->query($sql)->fetchAll();

$page_title = 'Adoption requests';
$active = 'requests';
require __DIR__ . '/../includes/header.php';
?>
<main class="wrap">
  <?= render_flash() ?>
  <h1>Adoption requests</h1>
  <div class="tabs">
    <a class="<?= $show === 'pending' ? 'on' : '' ?>" href="requests.php">Pending</a>
    <a class="<?= $show === 'all' ? 'on' : '' ?>" href="requests.php?show=all">All requests</a>
  </div>

  <?php if (!$requests): ?>
    <div class="empty"><?= $show === 'pending' ? 'No pending requests.' : 'No requests yet.' ?></div>
  <?php else: ?>
    <div class="list">
      <?php foreach ($requests as $r): ?>
        <article class="row-card">
          <img src="<?= img_url($r['image']) ?>" alt="<?= e($r['animal_name']) ?>">
          <div>
            <h3><?= e($r['animal_name']) ?> <span class="status <?= e(strtolower($r['status'])) ?>"><?= e($r['status']) ?></span></h3>
            <p><strong>Applicant:</strong> <?= e($r['applicant_name']) ?> &middot; <strong>Location:</strong> <?= e($r['location']) ?></p>
            <p><strong>Contact:</strong> <?= e($r['contact']) ?></p>
            <p><strong>Reason:</strong> <?= e($r['reason']) ?></p>
            <p><strong>Experience:</strong> <?= e($r['experience']) ?></p>
            <p><small>Sent <?= e(date('M j, Y g:i A', strtotime($r['date_requested']))) ?></small></p>
          </div>
          <?php if ($r['status'] === 'Pending'): ?>
            <div class="row-actions">
              <form method="post" onsubmit="return confirm('Approve this request? The animal will be marked as adopted and other pending requests for it will be declined.');">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="request_id" value="<?= (int)$r['id'] ?>">
                <input type="hidden" name="action" value="approve">
                <button class="btn green inline" type="submit">Approve</button>
              </form>
              <form method="post" onsubmit="return confirm('Decline this request?');">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="request_id" value="<?= (int)$r['id'] ?>">
                <input type="hidden" name="action" value="decline">
                <button class="btn danger inline" type="submit">Decline</button>
              </form>
            </div>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
