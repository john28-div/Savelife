<?php
// Shared helper functions.

// Flash messages (shown once after a redirect).
function set_flash(string $message): void {
    $_SESSION['flash'] = $message;
}
function render_flash(): string {
    if (empty($_SESSION['flash'])) return '';
    $m = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return '<div class="alert ok" role="status">' . e($m) . '</div>';
}

// Save a notification for a user.
function notify(PDO $pdo, int $user_id, string $message): void {
    $pdo->prepare('INSERT INTO notifications (user_id, message) VALUES (?, ?)')
        ->execute([$user_id, $message]);
}

// Validate and save an uploaded image into /uploads.
// Returns the relative path (e.g. "uploads/abc.jpg"), or null if no file / error.
// $error is filled when something is wrong.
function upload_image(array $file, string &$error): ?string {
    $error = '';
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK) { $error = 'Upload failed. Try a smaller image.'; return null; }
    if ($file['size'] > 3 * 1024 * 1024) { $error = 'Image must be 3 MB or smaller.'; return null; }

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($allowed[$mime])) { $error = 'Only JPG, PNG or WEBP images are allowed.'; return null; }

    $dir = __DIR__ . '/../uploads';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $name = bin2hex(random_bytes(12)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
        $error = 'Could not save the image.';
        return null;
    }
    return 'uploads/' . $name;
}

// URL for an animal / stray image (falls back to a placeholder).
function img_url(?string $path): string {
    return $path ? BASE_URL . '/' . e($path) : BASE_URL . '/assets/img/placeholder.svg';
}

// Top navigation link.
function nav_link(string $href, string $key, string $label, string $active, int $badge = 0): string {
    $cls = ($key === $active) ? ' class="active"' : '';
    $b = $badge > 0 ? ' <span class="badge-dot">' . $badge . '</span>' : '';
    return '<a' . $cls . ' href="' . BASE_URL . e($href) . '">' . e($label) . $b . '</a>';
}

// Animal card used on Home and Find A Pet.
function render_animal_card(array $a): void { ?>
  <article class="card">
    <img src="<?= img_url($a['image']) ?>" alt="<?= e($a['name']) ?>" loading="lazy">
    <div class="card-body">
      <div class="meta"><span class="tag"><?= e($a['type']) ?></span><span><?= e($a['age']) ?></span></div>
      <h3><?= e($a['name']) ?></h3>
      <p><?= e(mb_strimwidth((string)($a['details'] ?? ''), 0, 110, '…')) ?></p>
      <a class="btn orange inline" href="<?= BASE_URL ?>/user/adopt.php?id=<?= (int)$a['id'] ?>">Adopt <?= e($a['name']) ?></a>
    </div>
  </article>
<?php }
