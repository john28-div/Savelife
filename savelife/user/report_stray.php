<?php
require_once __DIR__ . '/../includes/init.php';
require_role('user');

$errors = [];
$old = ['location' => '', 'description' => '', 'contact' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        foreach (array_keys($old) as $k) { $old[$k] = trim($_POST[$k] ?? ''); }
        if ($old['location'] === '' || $old['description'] === '' || $old['contact'] === '') {
            $errors[] = 'Fill in the location, description and contact.';
        }
        if (mb_strlen($old['location']) > 150 || mb_strlen($old['contact']) > 100 || mb_strlen($old['description']) > 1000) {
            $errors[] = 'One of your answers is too long.';
        }
        $uploadError = '';
        $image = upload_image($_FILES['image'] ?? [], $uploadError);
        if ($uploadError !== '') $errors[] = $uploadError;

        if (!$errors) {
            $ins = $pdo->prepare('INSERT INTO stray_reports (user_id, image, location, description, contact) VALUES (?, ?, ?, ?, ?)');
            $ins->execute([$_SESSION['user_id'], $image, $old['location'], $old['description'], $old['contact']]);
            set_flash('Stray report sent. Thank you for helping. The admin will review it.');
            header('Location: ' . BASE_URL . '/user/report_stray.php');
            exit;
        }
    }
}

$page_title = 'Report Stray';
$active = 'stray';
require __DIR__ . '/../includes/header.php';
?>
<main class="wrap">
  <?= render_flash() ?>
  <h1>Report a stray</h1>
  <p class="lead">Seen a stray animal? Tell us where, so the team can check on it.</p>

  <div class="panel">
    <?php if ($errors): ?>
      <div class="alert error" role="alert"><?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

      <label for="image">Photo (optional)</label>
      <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
      <p class="hint">JPG, PNG or WEBP, up to 3 MB.</p>

      <label for="location">Where did you see it?</label>
      <input type="text" id="location" name="location" maxlength="150" placeholder="Street, barangay, landmark" value="<?= e($old['location']) ?>" required>

      <label for="description">Description</label>
      <textarea id="description" name="description" maxlength="1000" placeholder="Type of animal, color, size, condition, how long it has been there" required><?= e($old['description']) ?></textarea>

      <label for="contact">Your contact information</label>
      <input type="text" id="contact" name="contact" maxlength="100" placeholder="Mobile number or email" value="<?= e($old['contact']) ?>" required>

      <button type="submit" class="btn green">Send report</button>
    </form>
  </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
