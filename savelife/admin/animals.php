<?php
require_once __DIR__ . '/../includes/init.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $id = (int)($_POST['animal_id'] ?? 0);
    if ($id && ($_POST['action'] ?? '') === 'delete') {
        $pdo->prepare('DELETE FROM animals WHERE id = ?')->execute([$id]);
        set_flash('Animal deleted.');
    }
    header('Location: ' . BASE_URL . '/admin/animals.php');
    exit;
}

$animals = $pdo->query('SELECT * FROM animals ORDER BY created_at DESC, id DESC')->fetchAll();

$page_title = 'Animals';
$active = 'animals';
require __DIR__ . '/../includes/header.php';
?>
<main class="wrap">
  <?= render_flash() ?>
  <div class="section-head" style="margin-top:0">
    <div><h1>Animals</h1><p class="lead" style="margin:0">Add, edit or remove animals shown to users.</p></div>
    <a class="btn green inline" href="animal_form.php">Add animal</a>
  </div>

  <?php if (!$animals): ?>
    <div class="empty">No animals yet. Add the first one.</div>
  <?php else: ?>
    <div class="table-wrap"><table>
      <tr><th>Photo</th><th>Name</th><th>Type</th><th>Age</th><th>Status</th><th></th></tr>
      <?php foreach ($animals as $a): ?>
        <tr>
          <td><img src="<?= img_url($a['image']) ?>" alt=""></td>
          <td><?= e($a['name']) ?></td>
          <td><?= e($a['type']) ?></td>
          <td><?= e($a['age']) ?></td>
          <td><span class="status <?= e(strtolower($a['status'])) ?>"><?= e($a['status']) ?></span></td>
          <td>
            <a class="btn ghost inline" href="animal_form.php?id=<?= (int)$a['id'] ?>">Edit</a>
            <form method="post" onsubmit="return confirm('Delete this animal and its adoption requests?');">
              <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
              <input type="hidden" name="animal_id" value="<?= (int)$a['id'] ?>">
              <input type="hidden" name="action" value="delete">
              <button class="btn danger inline" type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </table></div>
  <?php endif; ?>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
