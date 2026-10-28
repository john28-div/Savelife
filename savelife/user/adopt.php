<?php
require_once __DIR__ . '/../includes/init.php';
require_role('user');

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM animals WHERE id = ? AND status = 'Available'");
$stmt->execute([$id]);
$animal = $stmt->fetch();
if (!$animal) {
    set_flash('That animal is no longer available.');
    header('Location: ' . BASE_URL . '/user/animals.php');
    exit;
}

$dup = $pdo->prepare("SELECT COUNT(*) FROM adoption_requests WHERE user_id = ? AND animal_id = ? AND status = 'Pending'");
$dup->execute([$_SESSION['user_id'], $id]);
$already = $dup->fetchColumn() > 0;

$errors = [];
$old = ['applicant_name' => $_SESSION['full_name'], 'location' => '', 'reason' => '', 'experience' => '', 'contact' => ''];
$limits = ['applicant_name' => 100, 'location' => 150, 'reason' => 1000, 'experience' => 1000, 'contact' => 100];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = 'Your session expired. Please try again.';
    } elseif ($already) {
        $errors[] = 'You already have a pending request for this animal.';
    } else {
        foreach (array_keys($old) as $k) { $old[$k] = trim($_POST[$k] ?? ''); }
        foreach ($limits as $k => $max) {
            if ($old[$k] === '') { $errors[] = 'Fill in all fields.'; break; }
        }
        foreach ($limits as $k => $max) {
            if (mb_strlen($old[$k]) > $max) { $errors[] = 'One of your answers is too long.'; break; }
        }
        if (!$errors) {
            $ins = $pdo->prepare(
                'INSERT INTO adoption_requests (user_id, animal_id, applicant_name, location, reason, experience, contact)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $ins->execute([$_SESSION['user_id'], $id, $old['applicant_name'], $old['location'], $old['reason'], $old['experience'], $old['contact']]);
            set_flash('Request sent for ' . $animal['name'] . '. You will be notified once the admin reviews it.');
            header('Location: ' . BASE_URL . '/user/my_requests.php');
            exit;
        }
    }
}

$page_title = 'Adopt ' . $animal['name'];
$active = 'animals';
require __DIR__ . '/../includes/header.php';
?>
<main class="wrap">
  <h1>Adoption request</h1>
  <div class="animal-summary">
    <img src="<?= img_url($animal['image']) ?>" alt="<?= e($animal['name']) ?>">
    <div>
      <h3><?= e($animal['name']) ?></h3>
      <p><?= e($animal['type']) ?>, <?= e($animal['age']) ?></p>
      <p><?= e($animal['details']) ?></p>
    </div>
  </div>

  <div class="panel">
    <?php if ($already): ?>
      <div class="alert ok">You already have a pending request for <?= e($animal['name']) ?>. Check <a href="my_requests.php">My Requests</a> for updates.</div>
    <?php endif; ?>
    <?php if ($errors): ?>
      <div class="alert error" role="alert"><?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
    <?php endif; ?>

    <form method="post">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

      <label>Animal you chose</label>
      <input type="text" value="<?= e($animal['name']) ?> (<?= e($animal['type']) ?>)" disabled>

      <label for="applicant_name">Your name</label>
      <input type="text" id="applicant_name" name="applicant_name" maxlength="100" value="<?= e($old['applicant_name']) ?>" required>

      <label for="location">Your location</label>
      <input type="text" id="location" name="location" maxlength="150" placeholder="Barangay, municipality" value="<?= e($old['location']) ?>" required>

      <label for="reason">Why do you want to adopt?</label>
      <textarea id="reason" name="reason" maxlength="1000" required><?= e($old['reason']) ?></textarea>

      <label for="experience">Experience with pets</label>
      <textarea id="experience" name="experience" maxlength="1000" placeholder="Pets you have owned, how you cared for them" required><?= e($old['experience']) ?></textarea>

      <label for="contact">Contact information</label>
      <input type="text" id="contact" name="contact" maxlength="100" placeholder="Mobile number or email" value="<?= e($old['contact']) ?>" required>

      <button type="submit" class="btn orange" <?= $already ? 'disabled' : '' ?>>Send request</button>
    </form>
  </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
