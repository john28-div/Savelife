<?php
require_once __DIR__ . '/../includes/init.php';
require_role('admin');

$id   = (int)($_GET['id'] ?? 0);
$from = (int)($_GET['from_stray'] ?? ($_POST['stray_id'] ?? 0));   // optional: create from a stray report

$animal = ['name' => '', 'type' => 'Dog', 'age' => '', 'details' => '', 'image' => null, 'status' => 'Available'];

if ($id) {
    $s = $pdo->prepare('SELECT * FROM animals WHERE id = ?');
    $s->execute([$id]);
    $found = $s->fetch();
    if (!$found) { header('Location: ' . BASE_URL . '/admin/animals.php'); exit; }
    $animal = $found;
} elseif ($from) {
    $s = $pdo->prepare('SELECT * FROM stray_reports WHERE id = ?');
    $s->execute([$from]);
    if ($stray = $s->fetch()) {
        $animal['image']   = $stray['image'];
        $animal['details'] = $stray['description'];
    } else {
        $from = 0;
    }
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $animal['name']    = trim($_POST['name'] ?? '');
        $animal['type']    = trim($_POST['type'] ?? '');
        $animal['age']     = trim($_POST['age'] ?? '');
        $animal['details'] = trim($_POST['details'] ?? '');
        $status            = $_POST['status'] ?? 'Available';
        if ($id) $animal['status'] = in_array($status, ['Available', 'Adopted'], true) ? $status : 'Available';

        if ($animal['name'] === '' || $animal['type'] === '' || $animal['age'] === '') {
            $errors[] = 'Name, type and age are required.';
        }
        if (mb_strlen($animal['name']) > 80 || mb_strlen($animal['type']) > 40 || mb_strlen($animal['age']) > 40) {
            $errors[] = 'Name, type or age is too long.';
        }
        $uploadError = '';
        $newImage = upload_image($_FILES['image'] ?? [], $uploadError);
        if ($uploadError !== '') $errors[] = $uploadError;

        if (!$errors) {
            $path = $newImage ?? $animal['image'];
            if ($id) {
                $pdo->prepare('UPDATE animals SET name=?, type=?, age=?, details=?, image=?, status=? WHERE id=?')
                    ->execute([$animal['name'], $animal['type'], $animal['age'], $animal['details'], $path, $animal['status'], $id]);
                set_flash('Animal updated.');
            } else {
                $pdo->prepare('INSERT INTO animals (name, type, age, details, image) VALUES (?, ?, ?, ?, ?)')
                    ->execute([$animal['name'], $animal['type'], $animal['age'], $animal['details'], $path]);
                if ($from) {
                    $pdo->prepare("UPDATE stray_reports SET status = 'Rescued' WHERE id = ?")->execute([$from]);
                }
                set_flash('Animal added.');
            }
            header('Location: ' . BASE_URL . '/admin/animals.php');
            exit;
        }
    }
}

$page_title = $id ? 'Edit animal' : 'Add animal';
$active = 'animals';
require __DIR__ . '/../includes/header.php';
?>
<main class="wrap">
  <h1><?= $id ? 'Edit animal' : 'Add animal' ?></h1>
  <?php if ($from && !$id): ?><p class="lead">Created from a stray report. The photo and description are filled in for you.</p><?php endif; ?>

  <div class="panel">
    <?php if ($errors): ?>
      <div class="alert error" role="alert"><?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="stray_id" value="<?= (int)$from ?>">

      <?php if ($animal['image']): ?>
        <label>Current photo</label>
        <img src="<?= img_url($animal['image']) ?>" alt="" style="width:160px;height:120px;object-fit:cover;border-radius:12px">
      <?php endif; ?>

      <label for="image"><?= $animal['image'] ? 'Replace photo' : 'Photo' ?></label>
      <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
      <p class="hint">JPG, PNG or WEBP, up to 3 MB.</p>

      <label for="name">Name</label>
      <input type="text" id="name" name="name" maxlength="80" value="<?= e($animal['name']) ?>" required>

      <label for="type">Type of animal</label>
      <input type="text" id="type" name="type" maxlength="40" list="types" value="<?= e($animal['type']) ?>" required>
      <datalist id="types"><option value="Dog"><option value="Cat"><option value="Other"></datalist>

      <label for="age">Age</label>
      <input type="text" id="age" name="age" maxlength="40" placeholder="e.g. 3 months, 2 years" value="<?= e($animal['age']) ?>" required>

      <label for="details">Details</label>
      <textarea id="details" name="details" placeholder="Temperament, health, vaccinations, story"><?= e($animal['details']) ?></textarea>

      <?php if ($id): ?>
        <label for="status">Status</label>
        <select id="status" name="status">
          <option value="Available" <?= $animal['status'] === 'Available' ? 'selected' : '' ?>>Available</option>
          <option value="Adopted" <?= $animal['status'] === 'Adopted' ? 'selected' : '' ?>>Adopted</option>
        </select>
      <?php endif; ?>

      <button type="submit" class="btn green">Save animal</button>
    </form>
  </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
