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
$user_data = $_SESSION['user_data'];

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Profil bilgileri güncelleme
    if (isset($_POST['update_profile'])) {
        $data = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'theme_preference' => $_POST['theme_preference'] ?? 'light'
        ];
        if ($user->updateProfile($user_id, $data)) {
            $_SESSION['user_data']['first_name'] = $data['first_name'];
            $_SESSION['user_data']['last_name'] = $data['last_name'];
            $_SESSION['user_data']['theme_preference'] = $data['theme_preference'];
            $message = 'Profil bilgileri başarıyla güncellendi!';
        } else {
            $message = 'Profil güncelleme başarısız!';
        }
    }

    // Şifre değiştirme
    if (!empty($_POST['new_password'])) {
        $new_password = $_POST['new_password'];
        $stmt = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->execute([password_hash($new_password, PASSWORD_DEFAULT), $user_id]);
        $message = 'Şifre başarıyla değiştirildi!';
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
<html lang="tr" data-theme="<?php echo htmlspecialchars($user_data['theme_preference']); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil ve Ayarlar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4"><i class="fas fa-user-cog me-2"></i>Profil ve Ayarlar</h2>
    <?php if ($message): ?>
        <div class="alert alert-info"><?php echo $message; ?></div>
    <?php endif; ?>

    <!-- Profil Bilgileri Formu -->
    <form method="POST" class="mb-4">
        <input type="hidden" name="update_profile" value="1">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Ad</label>
                <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($user_data['first_name']); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Soyad</label>
                <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($user_data['last_name']); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">E-posta</label>
                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user_data['email']); ?>" disabled>
            </div>
            <div class="col-md-6">
                <label class="form-label">Kullanıcı Adı</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user_data['username']); ?>" disabled>
            </div>
            <div class="col-md-6">
                <label class="form-label">Tema Tercihi</label>
                <select name="theme_preference" class="form-select">
                    <option value="light" <?php if($user_data['theme_preference']==='light') echo 'selected'; ?>>Açık</option>
                    <option value="dark" <?php if($user_data['theme_preference']==='dark') echo 'selected'; ?>>Koyu</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Profil Bilgilerini Güncelle</button>
    </form>

    <!-- Şifre Değiştirme Formu -->
    <form method="POST" class="mb-4">
        <div class="mb-3">
            <label class="form-label">Yeni Şifre</label>
            <input type="password" name="new_password" class="form-control" placeholder="Yeni şifre girin">
        </div>
        <button type="submit" class="btn btn-warning">Şifreyi Değiştir</button>
    </form>

    <!-- Hesap Silme Formu -->
    <form method="POST" onsubmit="return confirm('Hesabınızı silmek istediğinize emin misiniz? Bu işlem geri alınamaz.')">
        <input type="hidden" name="delete_account" value="1">
        <button type="submit" class="btn btn-danger">Hesabımı Sil</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
