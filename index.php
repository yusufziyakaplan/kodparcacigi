<?php
session_start();
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/Snippet.php';
require_once 'classes/Category.php';
require_once 'classes/Language.php';
require_once 'classes/Tag.php';

$database = new Database();
$db = $database->getConnection();

$snippet = new Snippet($db);
$category = new Category($db);
$language = new Language($db);
$tag = new Tag($db);
$user = new User($db);

// Kullanıcı ID'sini sessiondan al
$user_id = $_SESSION['user_id'] ?? null;

// Filtreler
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$language_filter = $_GET['language'] ?? '';

// Toplam sayıyı çek
$total_snippets = $snippet->getSnippetCount($user_id, $search, $category_filter, $language_filter);
$snippet->incrementViewCount($snippet_id, $user_id, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);



// Oturum kontrolü ve yönlendirme kaldırıldı
$user_id = $_SESSION['user_id'] ?? null;
if ($user_id) {
    if (!isset($_SESSION['user_data']) || !is_array($_SESSION['user_data'])) {
        // user_data yoksa veritabanından çek
        $stmt = $db->prepare("SELECT id, username, email, first_name, last_name, avatar, theme_preference FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $user_data = $_SESSION['user_data'];
    }
} else {
    $user_data = [
        'id' => null,
        'username' => '',
        'email' => '',
        'first_name' => '',
        'last_name' => '',
        'avatar' => '',
        'theme_preference' => 'light'
    ];
}

// Get filters and search parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$language_filter = isset($_GET['language']) ? $_GET['language'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Get snippets
$stmt = $snippet->read($user_id, $search, $category_filter, $language_filter, $sort, $order, $limit, $offset);
$snippets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories
$stmt = $category->read($user_id);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get languages
$stmt = $language->read();
$languages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get popular tags
$stmt = $tag->getPopularTags(20);
$popular_tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user stats
$user_stats = $user->getUserStats($user_id);
?>

<!DOCTYPE html>
<html lang="tr" data-theme="<?php echo $user_data['theme_preference']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akıllı Kod Parçacığı Yöneticisi</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Prism.js for syntax highlighting -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-gradient-primary fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <i class="fas fa-code me-2 fs-4"></i>
                <span class="fw-bold">Snippet Manager</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="fas fa-home me-1"></i>Ana Sayfa
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="favorites.php">
                            <i class="fas fa-heart me-1"></i>Favoriler
                        </a>
                    </li>
                   
                </ul>
                
                <div class="d-flex align-items-center">
                    <!-- Search -->
                    <form class="d-flex me-3" method="GET" action="index.php">
                        <div class="input-group">
                            <input class="form-control" type="search" name="search" placeholder="Ara..." value="<?php echo htmlspecialchars($search); ?>">
                            <button class="btn btn-outline-light" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                    
                    <!-- New Snippet Button -->
                    <button class="btn btn-success me-3" data-bs-toggle="modal" data-bs-target="#newSnippetModal">
                        <i class="fas fa-plus me-1"></i>Yeni
                    </button>
                    
                    <!-- Theme Toggle -->
                    <button class="btn btn-outline-light me-3" id="themeToggle">
                        <i class="fas fa-moon" id="themeIcon"></i>
                    </button>
                    
                    <!-- User Dropdown -->
<div class="dropdown">
    <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
        <i class="fas fa-user me-1"></i>
        <?php echo htmlspecialchars(($user_data['first_name'] ?? '') . ' ' . ($user_data['last_name'] ?? '')); ?>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        <li>
            <a class="dropdown-item" href="profile.php">
                <i class="fas fa-user me-2"></i>Profil
            </a>
        </li>
        <?php if (!empty($user_data['role']) && $user_data['role'] === 'admin'): ?>
        <li>
            <a class="dropdown-item" href="admin.php">
                <i class="fas fa-cog me-2"></i>Admin Panel
            </a>
        </li>
        <?php endif; ?>
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item" href="logout.php">
                <i class="fas fa-sign-out-alt me-2"></i>Çıkış
            </a>
        </li>
    </ul>
</div>

                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid mt-5 pt-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 col-md-4 mb-4">
                <div class="card glass-card sticky-top">
                    <div class="card-body">
                        <!-- User Stats -->
                      

                        <!-- Categories -->
                        <h6 class="fw-bold mb-3">
                            <i class="fas fa-folder me-2"></i>Kategoriler
                        </h6>
                        <div class="category-list">
                            <a href="index.php" class="category-item <?php echo empty($category_filter) ? 'active' : ''; ?>">
                                <i class="fas fa-list me-2"></i>Tümü
                            </a>
                            <?php foreach($categories as $cat): ?>
                                <a href="index.php?category=<?php echo $cat['id']; ?>" 
                                   class="category-item <?php echo $category_filter == $cat['id'] ? 'active' : ''; ?>">
                                    <i class="<?php echo $cat['icon']; ?> me-2" style="color: <?php echo $cat['color']; ?>"></i>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                    <span class="badge bg-secondary ms-auto"><?php echo $cat['snippet_count']; ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>

                        <!-- Languages -->
                        <h6 class="fw-bold mb-3 mt-4">
                            <i class="fas fa-code me-2"></i>Diller
                        </h6>
                        <div class="language-list">
                            <a href="index.php" class="language-item <?php echo empty($language_filter) ? 'active' : ''; ?>">
                                <span class="language-dot" style="background-color: #6c757d;"></span>
                                Tümü
                            </a>
                            <?php foreach($languages as $lang): ?>
                                <a href="index.php?language=<?php echo $lang['id']; ?>" 
                                   class="language-item <?php echo $language_filter == $lang['id'] ? 'active' : ''; ?>">
                                    <span class="language-dot" style="background-color: <?php echo $lang['color']; ?>"></span>
                                    <?php echo htmlspecialchars($lang['name']); ?>
                                    <span class="badge bg-secondary ms-auto"><?php echo $lang['snippet_count']; ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>

                        <!-- Popular Tags -->
                        <h6 class="fw-bold mb-3 mt-4">
                            <i class="fas fa-tags me-2"></i>Popüler Etiketler
                        </h6>
                        <div class="tag-cloud">
                            <?php foreach($popular_tags as $tag): ?>
                                <span class="tag-item" style="background-color: <?php echo $tag['color']; ?>">
                                    <?php echo htmlspecialchars($tag['name']); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9 col-md-8">
                <!-- Filters and Sort -->
                <div class="card glass-card mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h4 class="mb-0">
                                    <i class="fas fa-code-branch me-2"></i>
                                    Kod Parçacıkları
                                    <span class="badge bg-primary ms-2"><?php echo $total_snippets; ?></span>

                                </h4>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-end">
                                    <div class="dropdown">
                                        <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-sort me-1"></i>Sırala
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="?sort=created_at&order=DESC">En Yeni</a></li>
                                            <li><a class="dropdown-item" href="?sort=created_at&order=ASC">En Eski</a></li>
                                            <li><a class="dropdown-item" href="?sort=view_count&order=DESC">En Çok Görüntülenen</a></li>
                                            <li><a class="dropdown-item" href="?sort=title&order=ASC">Alfabetik</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Snippets Grid -->
                <div class="row" id="snippetsGrid">
                    <?php if(empty($snippets)): ?>
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="fas fa-code fs-1 text-muted mb-3"></i>
                                <h5 class="text-muted">Henüz kod parçacığı bulunmuyor</h5>
                                <p class="text-muted">İlk kod parçacığınızı oluşturmak için "Yeni" butonuna tıklayın.</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newSnippetModal">
                                    <i class="fas fa-plus me-1"></i>İlk Snippet'inizi Oluşturun
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach($snippets as $snippet_item): ?>
                            <div class="col-xl-4 col-lg-6 col-md-12 mb-4">
                                <div class="card snippet-card h-100 animate__animated animate__fadeIn">
                                    <div class="card-body">
                                        <div class="d-flex justify-between align-items-start mb-2">
                                            <h6 class="card-title mb-0"><?php echo htmlspecialchars($snippet_item['title']); ?></h6>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#" onclick="viewSnippet(<?php echo $snippet_item['id']; ?>)">
                                                        <i class="fas fa-eye me-2"></i>Görüntüle
                                                    </a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="editSnippet(<?php echo $snippet_item['id']; ?>)">
                                                        <i class="fas fa-edit me-2"></i>Düzenle
                                                    </a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="toggleFavorite(<?php echo $snippet_item['id']; ?>)">
                                                        <i class="fas fa-heart me-2"></i>Favorilere Ekle
                                                    </a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteSnippet(<?php echo $snippet_item['id']; ?>)">
                                                        <i class="fas fa-trash me-2"></i>Sil
                                                    </a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        
                                        <p class="card-text text-muted small mb-3"><?php echo htmlspecialchars(substr($snippet_item['description'], 0, 100)) . (strlen($snippet_item['description']) > 100 ? '...' : ''); ?></p>
                                        
                                        <!-- Code Preview -->
                                        <div class="code-preview mb-3">
                                            <pre class="line-numbers"><code class="language-<?php echo $snippet_item['prism_class']; ?>"><?php echo htmlspecialchars(substr($snippet_item['code'], 0, 200)) . (strlen($snippet_item['code']) > 200 ? '...' : ''); ?></code></pre>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div class="d-flex align-items-center">
                                                <span class="language-dot me-2" style="background-color: <?php echo $snippet_item['language_color']; ?>"></span>
                                                <small class="text-muted"><?php echo htmlspecialchars($snippet_item['language_name']); ?></small>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-eye me-1 text-muted"></i>
                                                <small class="text-muted"><?php echo $snippet_item['view_count']; ?></small>
                                            </div>
                                        </div>
                                        
                                        <!-- Tags -->
                                        <?php if(!empty($snippet_item['tags'])): ?>
                                            <div class="tags mb-2">
                                                <?php 
                                                $tags = explode(',', $snippet_item['tags']);
                                                $tag_colors = explode(',', $snippet_item['tag_colors']);
                                                foreach($tags as $index => $tag): 
                                                ?>
                                                    <span class="badge me-1" style="background-color: <?php echo $tag_colors[$index] ?? '#6B7280'; ?>">
                                                        <?php echo htmlspecialchars($tag); ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                <?php echo date('d.m.Y', strtotime($snippet_item['created_at'])); ?>
                                            </small>
                                            <button class="btn btn-sm btn-primary" onclick="viewSnippet(<?php echo $snippet_item['id']; ?>)">
                                                <i class="fas fa-eye me-1"></i>Görüntüle
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals will be included here -->
    <?php include 'includes/modals.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/line-numbers/prism-line-numbers.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>