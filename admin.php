<?php
session_start();
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/Snippet.php';
require_once 'classes/Category.php';
require_once 'classes/Language.php';

// Sadece admin girebilir
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    header('Location: index.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$snippet = new Snippet($db);
$category = new Category($db);
$language = new Language($db);

// Verileri çek
$users = $db->query('SELECT * FROM users')->fetchAll(PDO::FETCH_ASSOC);
$snippets = $db->query('SELECT s.*, u.username, c.name AS category_name, l.name AS language_name 
                        FROM snippets s 
                        LEFT JOIN users u ON s.user_id = u.id
                        LEFT JOIN categories c ON s.category_id = c.id
                        LEFT JOIN languages l ON s.language_id = l.id
                        ORDER BY s.created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
$categories = $db->query('SELECT * FROM categories')->fetchAll(PDO::FETCH_ASSOC);
$languages = $db->query('SELECT * FROM languages')->fetchAll(PDO::FETCH_ASSOC);

function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

?>
<?php
if (isset($_SESSION['flash_message'])) {
    echo '<div class="alert alert-info">'.htmlspecialchars($_SESSION['flash_message']).'</div>';
    unset($_SESSION['flash_message']);
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <title>Admin Paneli - Yönetim</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="container py-4">
    <h2><i class="fas fa-cog me-2"></i>Admin Paneli</h2>

    <ul class="nav nav-tabs mb-3" id="adminTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">
                Kullanıcılar
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="snippets-tab" data-bs-toggle="tab" data-bs-target="#snippets" type="button" role="tab">
                Kod Parçacıkları
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="categories-tab" data-bs-toggle="tab" data-bs-target="#categories" type="button" role="tab">
                Kategoriler
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="languages-tab" data-bs-toggle="tab" data-bs-target="#languages" type="button" role="tab">
                Diller
            </button>
        </li>
    </ul>

    <div class="tab-content" id="adminTabsContent">
        <!-- Kullanıcılar -->
        <div class="tab-pane fade show active" id="users" role="tabpanel">
            <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#userAddModal"><i class="fas fa-plus me-1"></i> Yeni Kullanıcı Ekle</button>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Kullanıcı Adı</th>
                            <th>E-posta</th>
                            <th>Ad</th>
                            <th>Soyad</th>
                            <th>Aktif</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($users as $u): ?>
                        <tr>
                            <td><?= $u['id'] ?></td>
                            <td><?= h($u['username']) ?></td>
                            <td><?= h($u['email']) ?></td>
                            <td><?= h($u['first_name']) ?></td>
                            <td><?= h($u['last_name']) ?></td>
                            <td>
                                <?php if($u['is_active']): ?>
                                    <span class="badge bg-success">Evet</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Hayır</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary me-1" data-bs-toggle="modal" data-bs-target="#userEditModal" 
                                    data-id="<?= $u['id'] ?>" data-username="<?= h($u['username']) ?>" data-email="<?= h($u['email']) ?>"
                                    data-first_name="<?= h($u['first_name']) ?>" data-last_name="<?= h($u['last_name']) ?>" data-is_active="<?= $u['is_active'] ?>">
                                    <i class="fas fa-edit"></i> Düzenle
                                </button>
                                <?php if($u['id'] != 1): ?>
                                    <form method="post" action="admin_action.php" class="d-inline" onsubmit="return confirm('Kullanıcıyı silmek istediğinize emin misiniz?');">
                                        <input type="hidden" name="action" value="delete_user" />
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>" />
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Sil</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Kod Parçacıkları -->
        <div class="tab-pane fade" id="snippets" role="tabpanel">
            <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#snippetAddModal"><i class="fas fa-plus me-1"></i> Yeni Snippet Ekle</button>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Başlık</th>
                            <th>Kullanıcı</th>
                            <th>Kategori</th>
                            <th>Dil</th>
                            <th>Oluşturulma</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($snippets as $s): ?>
                        <tr>
                            <td><?= $s['id'] ?></td>
                            <td><?= h($s['title']) ?></td>
                            <td><?= h($s['username']) ?></td>
                            <td><?= h($s['category_name']) ?></td>
                            <td><?= h($s['language_name']) ?></td>
                            <td><?= date('d.m.Y', strtotime($s['created_at'])) ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary me-1" data-bs-toggle="modal" data-bs-target="#snippetEditModal"
                                    data-id="<?= $s['id'] ?>" data-title="<?= h($s['title']) ?>" data-user_id="<?= $s['user_id'] ?>"
                                    data-category_id="<?= $s['category_id'] ?>" data-language_id="<?= $s['language_id'] ?>" data-code="<?= h($s['code']) ?>"
                                    data-description="<?= h($s['description']) ?>">
                                    <i class="fas fa-edit"></i> Düzenle
                                </button>
                                <form method="post" action="admin_action.php" class="d-inline" onsubmit="return confirm('Snippet silinecek, emin misiniz?');">
                                    <input type="hidden" name="action" value="delete_snippet" />
                                    <input type="hidden" name="snippet_id" value="<?= $s['id'] ?>" />
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Sil</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Kategoriler -->
        <div class="tab-pane fade" id="categories" role="tabpanel">
            <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#categoryAddModal"><i class="fas fa-plus me-1"></i> Yeni Kategori Ekle</button>
            <ul class="list-group">
                <?php foreach($categories as $c): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= h($c['name']) ?>
                        <div>
                            <span class="badge rounded-pill me-3" style="background-color: <?= h($c['color']) ?>;">&nbsp;&nbsp;&nbsp;</span>
                            <button class="btn btn-sm btn-primary me-1" data-bs-toggle="modal" data-bs-target="#categoryEditModal" 
                                data-id="<?= $c['id'] ?>" data-name="<?= h($c['name']) ?>" data-color="<?= h($c['color']) ?>">
                                <i class="fas fa-edit"></i> Düzenle
                            </button>
                            <form method="post" action="admin_action.php" class="d-inline" onsubmit="return confirm('Kategori silinecek, emin misiniz?');">
                                <input type="hidden" name="action" value="delete_category" />
                                <input type="hidden" name="category_id" value="<?= $c['id'] ?>" />
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Sil</button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Diller -->
        <div class="tab-pane fade" id="languages" role="tabpanel">
            <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#languageAddModal"><i class="fas fa-plus me-1"></i> Yeni Dil Ekle</button>
            <ul class="list-group">
                <?php foreach($languages as $l): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= h($l['name']) ?>
                        <div>
                            <span class="badge rounded-pill me-3" style="background-color: <?= h($l['color']) ?>;">&nbsp;&nbsp;&nbsp;</span>
                            <button class="btn btn-sm btn-primary me-1" data-bs-toggle="modal" data-bs-target="#languageEditModal" 
                                data-id="<?= $l['id'] ?>" data-name="<?= h($l['name']) ?>" data-color="<?= h($l['color']) ?>">
                                <i class="fas fa-edit"></i> Düzenle
                            </button>
                            <form method="post" action="admin_action.php" class="d-inline" onsubmit="return confirm('Dil silinecek, emin misiniz?');">
                                <input type="hidden" name="action" value="delete_language" />
                                <input type="hidden" name="language_id" value="<?= $l['id'] ?>" />
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Sil</button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<!-- Modallar -->
<!-- Kullanıcı Ekle -->
<div class="modal fade" id="userAddModal" tabindex="-1" aria-labelledby="userAddLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="admin_action.php" method="post" class="modal-content">
      <input type="hidden" name="action" value="add_user">
      <div class="modal-header">
        <h5 class="modal-title" id="userAddLabel">Yeni Kullanıcı Ekle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
      </div>
      <div class="modal-body">
          <div class="mb-3">
              <label for="username" class="form-label">Kullanıcı Adı</label>
              <input type="text" name="username" id="username" class="form-control" required>
          </div>
          <div class="mb-3">
              <label for="email" class="form-label">E-posta</label>
              <input type="email" name="email" id="email" class="form-control" required>
          </div>
          <div class="mb-3">
              <label for="password" class="form-label">Parola</label>
              <input type="password" name="password" id="password" class="form-control" required>
          </div>
          <div class="mb-3">
              <label for="first_name" class="form-label">Ad</label>
              <input type="text" name="first_name" id="first_name" class="form-control">
          </div>
          <div class="mb-3">
              <label for="last_name" class="form-label">Soyad</label>
              <input type="text" name="last_name" id="last_name" class="form-control">
          </div>
          <div class="form-check mb-3">
              <input type="checkbox" class="form-check-input" id="is_active" name="is_active" checked>
              <label class="form-check-label" for="is_active">Aktif</label>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
        <button type="submit" class="btn btn-success">Ekle</button>
      </div>
    </form>
  </div>
</div>

<!-- Kullanıcı Düzenle -->
<div class="modal fade" id="userEditModal" tabindex="-1" aria-labelledby="userEditLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="admin_action.php" method="post" class="modal-content">
      <input type="hidden" name="action" value="edit_user">
      <input type="hidden" name="user_id" id="edit_user_id" />
      <div class="modal-header">
        <h5 class="modal-title" id="userEditLabel">Kullanıcı Düzenle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
      </div>
      <div class="modal-body">
          <div class="mb-3">
              <label for="edit_username" class="form-label">Kullanıcı Adı</label>
              <input type="text" name="username" id="edit_username" class="form-control" required>
          </div>
          <div class="mb-3">
              <label for="edit_email" class="form-label">E-posta</label>
              <input type="email" name="email" id="edit_email" class="form-control" required>
          </div>
          <div class="mb-3">
              <label for="edit_password" class="form-label">Parola (Boş bırakılırsa değişmez)</label>
              <input type="password" name="password" id="edit_password" class="form-control" placeholder="Yeni parola">
          </div>
          <div class="mb-3">
              <label for="edit_first_name" class="form-label">Ad</label>
              <input type="text" name="first_name" id="edit_first_name" class="form-control">
          </div>
          <div class="mb-3">
              <label for="edit_last_name" class="form-label">Soyad</label>
              <input type="text" name="last_name" id="edit_last_name" class="form-control">
          </div>
          <div class="form-check mb-3">
              <input type="checkbox" class="form-check-input" id="edit_is_active" name="is_active">
              <label class="form-check-label" for="edit_is_active">Aktif</label>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
        <button type="submit" class="btn btn-primary">Kaydet</button>
      </div>
    </form>
  </div>
</div>

<!-- Snippet Ekle -->
<div class="modal fade" id="snippetAddModal" tabindex="-1" aria-labelledby="snippetAddLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form action="admin_action.php" method="post" class="modal-content">
      <input type="hidden" name="action" value="add_snippet">
      <div class="modal-header">
        <h5 class="modal-title" id="snippetAddLabel">Yeni Kod Parçacığı Ekle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
      </div>
      <div class="modal-body">
          <div class="mb-3">
              <label for="snippet_title" class="form-label">Başlık</label>
              <input type="text" name="title" id="snippet_title" class="form-control" required>
          </div>
          <div class="mb-3">
              <label for="snippet_user_id" class="form-label">Kullanıcı</label>
              <select name="user_id" id="snippet_user_id" class="form-select" required>
                <option value="">Seçiniz</option>
                <?php foreach($users as $u): ?>
                    <option value="<?= $u['id'] ?>"><?= h($u['username']) ?></option>
                <?php endforeach; ?>
              </select>
          </div>
          <div class="mb-3">
              <label for="snippet_category_id" class="form-label">Kategori</label>
              <select name="category_id" id="snippet_category_id" class="form-select" required>
                <option value="">Seçiniz</option>
                <?php foreach($categories as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= h($c['name']) ?></option>
                <?php endforeach; ?>
              </select>
          </div>
          <div class="mb-3">
              <label for="snippet_language_id" class="form-label">Dil</label>
              <select name="language_id" id="snippet_language_id" class="form-select" required>
                <option value="">Seçiniz</option>
                <?php foreach($languages as $l): ?>
                    <option value="<?= $l['id'] ?>"><?= h($l['name']) ?></option>
                <?php endforeach; ?>
              </select>
          </div>
          <div class="mb-3">
              <label for="snippet_code" class="form-label">Kod</label>
              <textarea name="code" id="snippet_code" class="form-control" rows="8" required></textarea>
          </div>
          <div class="mb-3">
              <label for="snippet_description" class="form-label">Açıklama</label>
              <textarea name="description" id="snippet_description" class="form-control" rows="3"></textarea>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
        <button type="submit" class="btn btn-success">Ekle</button>
      </div>
    </form>
  </div>
</div>

<!-- Snippet Düzenle -->
<div class="modal fade" id="snippetEditModal" tabindex="-1" aria-labelledby="snippetEditLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form action="admin_action.php" method="post" class="modal-content">
      <input type="hidden" name="action" value="edit_snippet">
      <input type="hidden" name="snippet_id" id="edit_snippet_id" />
      <div class="modal-header">
        <h5 class="modal-title" id="snippetEditLabel">Kod Parçacığını Düzenle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
      </div>
      <div class="modal-body">
          <div class="mb-3">
              <label for="edit_snippet_title" class="form-label">Başlık</label>
              <input type="text" name="title" id="edit_snippet_title" class="form-control" required>
          </div>
          <div class="mb-3">
              <label for="edit_snippet_user_id" class="form-label">Kullanıcı</label>
              <select name="user_id" id="edit_snippet_user_id" class="form-select" required>
                <?php foreach($users as $u): ?>
                    <option value="<?= $u['id'] ?>"><?= h($u['username']) ?></option>
                <?php endforeach; ?>
              </select>
          </div>
          <div class="mb-3">
              <label for="edit_snippet_category_id" class="form-label">Kategori</label>
              <select name="category_id" id="edit_snippet_category_id" class="form-select" required>
                <?php foreach($categories as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= h($c['name']) ?></option>
                <?php endforeach; ?>
              </select>
          </div>
          <div class="mb-3">
              <label for="edit_snippet_language_id" class="form-label">Dil</label>
              <select name="language_id" id="edit_snippet_language_id" class="form-select" required>
                <?php foreach($languages as $l): ?>
                    <option value="<?= $l['id'] ?>"><?= h($l['name']) ?></option>
                <?php endforeach; ?>
              </select>
          </div>
          <div class="mb-3">
              <label for="edit_snippet_code" class="form-label">Kod</label>
              <textarea name="code" id="edit_snippet_code" class="form-control" rows="8" required></textarea>
          </div>
          <div class="mb-3">
              <label for="edit_snippet_description" class="form-label">Açıklama</label>
              <textarea name="description" id="edit_snippet_description" class="form-control" rows="3"></textarea>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
        <button type="submit" class="btn btn-primary">Kaydet</button>
      </div>
    </form>
  </div>
</div>

<!-- Kategori Ekle -->
<div class="modal fade" id="categoryAddModal" tabindex="-1" aria-labelledby="categoryAddLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="admin_action.php" method="post" class="modal-content">
      <input type="hidden" name="action" value="add_category">
      <div class="modal-header">
        <h5 class="modal-title" id="categoryAddLabel">Yeni Kategori Ekle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
      </div>
      <div class="modal-body">
          <div class="mb-3">
              <label for="category_name" class="form-label">Kategori Adı</label>
              <input type="text" name="name" id="category_name" class="form-control" required>
          </div>
          <div class="mb-3">
              <label for="category_color" class="form-label">Renk (Hex kodu)</label>
              <input type="color" name="color" id="category_color" class="form-control form-control-color" value="#0d6efd" required>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
        <button type="submit" class="btn btn-success">Ekle</button>
      </div>
    </form>
  </div>
</div>

<!-- Kategori Düzenle -->
<div class="modal fade" id="categoryEditModal" tabindex="-1" aria-labelledby="categoryEditLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="admin_action.php" method="post" class="modal-content">
      <input type="hidden" name="action" value="edit_category">
      <input type="hidden" name="category_id" id="edit_category_id" />
      <div class="modal-header">
        <h5 class="modal-title" id="categoryEditLabel">Kategori Düzenle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
      </div>
      <div class="modal-body">
          <div class="mb-3">
              <label for="edit_category_name" class="form-label">Kategori Adı</label>
              <input type="text" name="name" id="edit_category_name" class="form-control" required>
          </div>
          <div class="mb-3">
              <label for="edit_category_color" class="form-label">Renk (Hex kodu)</label>
              <input type="color" name="color" id="edit_category_color" class="form-control form-control-color" required>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
        <button type="submit" class="btn btn-primary">Kaydet</button>
      </div>
    </form>
  </div>
</div>

<!-- Dil Ekle -->
<div class="modal fade" id="languageAddModal" tabindex="-1" aria-labelledby="languageAddLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="admin_action.php" method="post" class="modal-content">
      <input type="hidden" name="action" value="add_language">
      <div class="modal-header">
        <h5 class="modal-title" id="languageAddLabel">Yeni Dil Ekle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
      </div>
      <div class="modal-body">
          <div class="mb-3">
              <label for="language_name" class="form-label">Dil Adı</label>
              <input type="text" name="name" id="language_name" class="form-control" required>
          </div>
          <div class="mb-3">
              <label for="language_color" class="form-label">Renk (Hex kodu)</label>
              <input type="color" name="color" id="language_color" class="form-control form-control-color" value="#0d6efd" required>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
        <button type="submit" class="btn btn-success">Ekle</button>
      </div>
    </form>
  </div>
</div>

<!-- Dil Düzenle -->
<div class="modal fade" id="languageEditModal" tabindex="-1" aria-labelledby="languageEditLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="admin_action.php" method="post" class="modal-content">
      <input type="hidden" name="action" value="edit_language">
      <input type="hidden" name="language_id" id="edit_language_id" />
      <div class="modal-header">
        <h5 class="modal-title" id="languageEditLabel">Dil Düzenle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
      </div>
      <div class="modal-body">
          <div class="mb-3">
              <label for="edit_language_name" class="form-label">Dil Adı</label>
              <input type="text" name="name" id="edit_language_name" class="form-control" required>
          </div>
          <div class="mb-3">
              <label for="edit_language_color" class="form-label">Renk (Hex kodu)</label>
              <input type="color" name="color" id="edit_language_color" class="form-control form-control-color" required>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
        <button type="submit" class="btn btn-primary">Kaydet</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
  // Kullanıcı Düzenle modalına veri yükle
  var userEditModal = document.getElementById('userEditModal');
  userEditModal.addEventListener('show.bs.modal', function (event) {
      var button = event.relatedTarget;
      userEditModal.querySelector('#edit_user_id').value = button.getAttribute('data-id');
      userEditModal.querySelector('#edit_username').value = button.getAttribute('data-username');
      userEditModal.querySelector('#edit_email').value = button.getAttribute('data-email');
      userEditModal.querySelector('#edit_first_name').value = button.getAttribute('data-first_name');
      userEditModal.querySelector('#edit_last_name').value = button.getAttribute('data-last_name');
      userEditModal.querySelector('#edit_is_active').checked = button.getAttribute('data-is_active') == 1;
  });

  // Snippet Düzenle modalına veri yükle
  var snippetEditModal = document.getElementById('snippetEditModal');
  snippetEditModal.addEventListener('show.bs.modal', function (event) {
      var button = event.relatedTarget;
      snippetEditModal.querySelector('#edit_snippet_id').value = button.getAttribute('data-id');
      snippetEditModal.querySelector('#edit_snippet_title').value = button.getAttribute('data-title');
      snippetEditModal.querySelector('#edit_snippet_user_id').value = button.getAttribute('data-user_id');
      snippetEditModal.querySelector('#edit_snippet_category_id').value = button.getAttribute('data-category_id');
      snippetEditModal.querySelector('#edit_snippet_language_id').value = button.getAttribute('data-language_id');
      snippetEditModal.querySelector('#edit_snippet_code').value = button.getAttribute('data-code');
      snippetEditModal.querySelector('#edit_snippet_description').value = button.getAttribute('data-description');
  });

  // Kategori Düzenle modalına veri yükle
  var categoryEditModal = document.getElementById('categoryEditModal');
  categoryEditModal.addEventListener('show.bs.modal', function (event) {
      var button = event.relatedTarget;
      categoryEditModal.querySelector('#edit_category_id').value = button.getAttribute('data-id');
      categoryEditModal.querySelector('#edit_category_name').value = button.getAttribute('data-name');
      categoryEditModal.querySelector('#edit_category_color').value = button.getAttribute('data-color');
  });

  // Dil Düzenle modalına veri yükle
  var languageEditModal = document.getElementById('languageEditModal');
  languageEditModal.addEventListener('show.bs.modal', function (event) {
      var button = event.relatedTarget;
      languageEditModal.querySelector('#edit_language_id').value = button.getAttribute('data-id');
      languageEditModal.querySelector('#edit_language_name').value = button.getAttribute('data-name');
      languageEditModal.querySelector('#edit_language_color').value = button.getAttribute('data-color');
  });
</script>
</body>
</html>
