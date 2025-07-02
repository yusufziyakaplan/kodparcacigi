<?php
session_start();
require_once 'config/database.php';
require_once 'classes/User.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$user_id = $_SESSION['user_id'];
$user_data = $_SESSION['user_data'] ?? [];

// Basit CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = $_SESSION['flash_message'] ?? '';
unset($_SESSION['flash_message']);

// İşlemler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['flash_message'] = 'Geçersiz istek!';
        header('Location: settings.php');
        exit();
    }

    // Tema tercihi
    if (isset($_POST['theme_preference'])) {
        $theme = ($_POST['theme_preference'] === 'dark') ? 'dark' : 'light';
        $data = [
            'first_name' => $user_data['first_name'],
            'last_name' => $user_data['last_name'],
            'theme_preference' => $theme
        ];
        if ($user->updateProfile($user_id, $data)) {
            $_SESSION['user_data']['theme_preference'] = $theme;
            $_SESSION['flash_message'] = 'Tema tercihi güncellendi!';
            header('Location: settings.php');
            exit();
        }
    }

    // Şifre güncelleme
    if (isset($_POST['new_password']) && trim($_POST['new_password']) !== '') {
        $new_password = trim($_POST['new_password']);
        if (strlen($new_password) < 6) {
            $message = 'Şifre en az 6 karakter olmalıdır.';
        } else {
            $stmt = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
            $stmt->execute([password_hash($new_password, PASSWORD_DEFAULT), $user_id]);
            $_SESSION['flash_message'] = 'Şifre başarıyla değiştirildi!';
            header('Location: settings.php');
            exit();
        }
    }

    // Hesap silme
    if (isset($_POST['delete_account'])) {
        $stmt = $db->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$user_id]);
        session_destroy();
        header('Location: login.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="tr" data-theme="<?php echo htmlspecialchars($user_data['theme_preference'] ?? 'light'); ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Hesap Ayarları</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>
<div class="container mt-5">
    <h2>Hesap Ayarları</h2>

    <?php if ($message): ?>
        <div class="alert alert-warning"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if ($_SESSION['flash_message'] ?? ''): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['flash_message']); ?></div>
    <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>

    <form method="POST" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>" />

        <div class="mb-3">
            <label for="theme_preference" class="form-label">Tema Tercihi</label>
            <select id="theme_preference" name="theme_preference" class="form-select">
                <option value="light" <?php echo (($user_data['theme_preference'] ?? 'light') === 'light') ? 'selected' : ''; ?>>Açık</option>
                <option value="dark" <?php echo (($user_data['theme_preference'] ?? '') === 'dark') ? 'selected' : ''; ?>>Koyu</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="new_password" class="form-label">Yeni Şifre</label>
            <input type="password" id="new_password" name="new_password" class="form-control" placeholder="En az 6 karakter" minlength="6" />
        </div>

        <button type="submit" class="btn btn-primary">Kaydet</button>
    </form>

    <form method="POST" class="mt-4">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>" />
        <input type="hidden" name="delete_account" value="1" />
        <button type="submit" class="btn btn-danger" onclick="return confirm('Hesabınızı silmek istediğinize emin misiniz? Bu işlem geri alınamaz.')">
            Hesabımı Sil
        </button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
