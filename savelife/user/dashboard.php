<?php
require_once __DIR__ . '/../includes/init.php';
require_role('user');

$available = (int)$pdo->query("SELECT COUNT(*) FROM animals WHERE status = 'Available'")->fetchColumn();
$adopted   = (int)$pdo->query("SELECT COUNT(*) FROM animals WHERE status = 'Adopted'")->fetchColumn();
$latest    = $pdo->query("SELECT * FROM animals WHERE status = 'Available' ORDER BY created_at DESC, id DESC LIMIT 4")->fetchAll();

$page_title = 'Home';
$active = 'home';
require __DIR__ . '/../includes/header.php';
?>
<section class="hero">
  <div class="hero-inner">
    <span class="pill"><?= $available > 0 ? $available . ($available === 1 ? ' animal needs' : ' animals need') . ' a home' : 'New animals are added regularly' ?></span>
    <h1>Every stray deserves a second life.</h1>
    <p>Report stray animals near you and help match them with loving families.<?= $adopted > 0 ? ' So far, ' . $adopted . ($adopted === 1 ? ' animal has' : ' animals have') . ' found a home through SaveLife.' : '' ?></p>
    <div class="row">
      <a class="btn orange inline" href="<?= BASE_URL ?>/user/report_stray.php">Report a stray</a>
      <a class="btn ghost inline" href="<?= BASE_URL ?>/user/animals.php">Find a pet</a>
    </div>
  </div>
</section>

<main class="wrap">
  <?= render_flash() ?>
  <div class="section-head">
    <h2>Newly added</h2>
    <a href="<?= BASE_URL ?>/user/animals.php">See all animals</a>
  </div>
  <?php if (!$latest): ?>
    <div class="empty">No animals are listed yet. Check back soon.</div>
  <?php else: ?>
    <div class="grid"><?php foreach ($latest as $a) render_animal_card($a); ?></div>
  <?php endif; ?>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
