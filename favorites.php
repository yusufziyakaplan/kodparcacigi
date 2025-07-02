<?php
session_start();
require_once 'config/database.php';
require_once 'classes/Snippet.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$snippet = new Snippet($db);
$user_id = $_SESSION['user_id'];

$stmt = $snippet->getFavorites($user_id, 100, 0);
$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr" data-theme="<?php echo htmlspecialchars($_SESSION['user_data']['theme_preference'] ?? 'light'); ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Favori Kod Parçacıklarım</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">
        <i class="fas fa-heart text-danger me-2"></i>Favori Kod Parçacıklarım
    </h2>

    <?php if (empty($favorites)): ?>
        <div class="alert alert-info">Henüz favori kod parçacığınız yok.</div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($favorites as $fav): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($fav['title']); ?></h5>
                            <p class="card-text text-muted small">
                                <?php echo htmlspecialchars(mb_strimwidth($fav['description'], 0, 100, '...')); ?>
                            </p>
                            <pre class="small bg-light p-2 rounded"><?php echo htmlspecialchars(mb_strimwidth($fav['code'], 0, 120, '...')); ?></pre>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    <?php echo date('d.m.Y', strtotime($fav['created_at'])); ?>
                                </small>
                                <div class="d-flex gap-2">
                                    <a href="index.php#snippet-<?php echo $fav['id']; ?>" class="btn btn-sm btn-primary">
                                        Görüntüle
                                    </a>
                                    <!-- Favoriden çıkarma -->
                                    <form method="POST" action="toggle_favorite.php" style="display:inline;">
                                        <input type="hidden" name="snippet_id" value="<?php echo $fav['id']; ?>" />
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Favoriden çıkar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
