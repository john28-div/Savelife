<?php
require_once __DIR__ . '/../includes/init.php';
require_role('admin');

$count = function (string $sql) use ($pdo): int { return (int)$pdo->query($sql)->fetchColumn(); };
$available = $count("SELECT COUNT(*) FROM animals WHERE status = 'Available'");
$adopted   = $count("SELECT COUNT(*) FROM animals WHERE status = 'Adopted'");
$pending   = $count("SELECT COUNT(*) FROM adoption_requests WHERE status = 'Pending'");
$newStray  = $count("SELECT COUNT(*) FROM stray_reports WHERE status = 'Reported'");

$recent = $pdo->query(
    "SELECT r.*, a.name AS animal_name FROM adoption_requests r
     JOIN animals a ON a.id = r.animal_id
     WHERE r.status = 'Pending' ORDER BY r.date_requested DESC LIMIT 5"
)->fetchAll();

$page_title = 'Dashboard';
$active = 'dash';
require __DIR__ . '/../includes/header.php';
?>
<main class="wrap">
  <?= render_flash() ?>
  <h1>Admin dashboard</h1>
  <p class="lead">Overview of animals, requests and stray reports.</p>

  <div class="stats">
    <div class="stat"><b><?= $available ?></b><span>Available animals</span></div>
    <div class="stat"><b><?= $adopted ?></b><span>Adopted animals</span></div>
    <div class="stat"><b><?= $pending ?></b><span>Pending requests</span></div>
    <div class="stat"><b><?= $newStray ?></b><span>New stray reports</span></div>
  </div>

  <div class="section-head">
    <h2>Latest pending requests</h2>
    <a href="requests.php">Review all</a>
  </div>
  <?php if (!$recent): ?>
    <div class="empty">No pending requests.</div>
  <?php else: ?>
    <div class="table-wrap"><table>
      <tr><th>Applicant</th><th>Animal</th><th>Location</th><th>Date</th></tr>
      <?php foreach ($recent as $r): ?>
        <tr>
          <td><?= e($r['applicant_name']) ?></td>
          <td><?= e($r['animal_name']) ?></td>
          <td><?= e($r['location']) ?></td>
          <td><?= e(date('M j, Y', strtotime($r['date_requested']))) ?></td>
        </tr>
      <?php endforeach; ?>
    </table></div>
  <?php endif; ?>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
