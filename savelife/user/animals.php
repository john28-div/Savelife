<?php
require_once __DIR__ . '/../includes/init.php';
require_role('user');

$q    = trim($_GET['q'] ?? '');
$type = trim($_GET['type'] ?? '');

$sql = "SELECT * FROM animals WHERE status = 'Available'";
$params = [];
if ($q !== '') {
    $sql .= ' AND (name LIKE ? OR details LIKE ?)';
    $params[] = "%$q%";
    $params[] = "%$q%";
}
if ($type !== '') {
    $sql .= ' AND type = ?';
    $params[] = $type;
}
$sql .= ' ORDER BY created_at DESC, id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$animals = $stmt->fetchAll();

$types = $pdo->query("SELECT DISTINCT type FROM animals WHERE status = 'Available' ORDER BY type")->fetchAll(PDO::FETCH_COLUMN);

$page_title = 'Find A Pet';
$active = 'animals';
require __DIR__ . '/../includes/header.php';
?>
<main class="wrap">
  <?= render_flash() ?>
  <h1>Find a pet</h1>
  <p class="lead">Animals waiting for a home right now.</p>

  <form class="filters" method="get">
    <input type="text" name="q" placeholder="Search by name or details" value="<?= e($q) ?>">
    <select name="type">
      <option value="">All types</option>
      <?php foreach ($types as $t): ?>
        <option value="<?= e($t) ?>" <?= $t === $type ? 'selected' : '' ?>><?= e($t) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn green inline" type="submit">Search</button>
    <?php if ($q !== '' || $type !== ''): ?><a class="btn ghost inline" href="animals.php">Clear</a><?php endif; ?>
  </form>

  <?php if (!$animals): ?>
    <div class="empty">No animals match your search.</div>
  <?php else: ?>
    <div class="grid"><?php foreach ($animals as $a) render_animal_card($a); ?></div>
  <?php endif; ?>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
