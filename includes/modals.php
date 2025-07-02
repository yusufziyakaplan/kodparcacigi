<!-- New Snippet Modal -->
<div class="modal fade" id="newSnippetModal" tabindex="-1" aria-labelledby="newSnippetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newSnippetModalLabel">
                    <i class="fas fa-plus me-2"></i>Yeni Kod Parçacığı
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="newSnippetForm" method="POST" action="api/create-snippet.php">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="title" class="form-label">Başlık</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Açıklama</label>
                                <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="language" class="form-label">Dil</label>
                                        <select class="form-select" id="language" name="language_id" required>
                                            <option value="">Dil seçin...</option>
                                            <?php foreach($languages as $lang): ?>
                                                <option value="<?php echo $lang['id']; ?>">
                                                    <?php echo htmlspecialchars($lang['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="category" class="form-label">Kategori</label>
                                        <select class="form-select" id="category" name="category_id">
                                            <option value="">Kategori seçin...</option>
                                            <?php foreach($categories as $cat): ?>
                                                <option value="<?php echo $cat['id']; ?>">
                                                    <?php echo htmlspecialchars($cat['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="tags" class="form-label">Etiketler</label>
                                <input type="text" class="form-control" id="tags" name="tags" placeholder="Etiketleri virgülle ayırın">
                                <div class="form-text">Örnek: react, javascript, hooks</div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_public" name="is_public" value="1">
                                    <label class="form-check-label" for="is_public">
                                        <i class="fas fa-globe me-1"></i>Herkese açık
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Önizleme</label>
                                <div class="code-preview" style="height: 120px; overflow-y: auto;">
                                    <pre><code id="codePreview">// Kod buraya yazılacak...</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="code" class="form-label">Kod</label>
                        <div class="code-editor">
                            <div class="code-editor-header">
                                <div class="d-flex align-items-center">
                                    <span class="language-dot me-2" style="background-color: #6c757d;"></span>
                                    <span class="text-light" id="selectedLanguage">Dil seçilmedi</span>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-sm btn-outline-light copy-code-btn">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-light" onclick="toggleFullscreen(this)">
                                        <i class="fas fa-expand"></i>
                                    </button>
                                </div>
                            </div>
                            <textarea class="form-control" id="code" name="code" rows="12" required placeholder="Kodunuzu buraya yazın..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="submit" form="newSnippetForm" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Kaydet
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Snippet Modal -->
<div class="modal fade" id="editSnippetModal" tabindex="-1" aria-labelledby="editSnippetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSnippetModalLabel">
                    <i class="fas fa-edit me-2"></i>Kod Parçacığını Düzenle
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editSnippetForm" method="POST" action="api/update-snippet.php">
                    <input type="hidden" id="editSnippetId" name="id">
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="editTitle" class="form-label">Başlık</label>
                                <input type="text" class="form-control" id="editTitle" name="title" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="editDescription" class="form-label">Açıklama</label>
                                <textarea class="form-control" id="editDescription" name="description" rows="2"></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editLanguage" class="form-label">Dil</label>
                                        <select class="form-select" id="editLanguage" name="language_id" required>
                                            <option value="">Dil seçin...</option>
                                            <?php foreach($languages as $lang): ?>
                                                <option value="<?php echo $lang['id']; ?>">
                                                    <?php echo htmlspecialchars($lang['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editCategory" class="form-label">Kategori</label>
                                        <select class="form-select" id="editCategory" name="category_id">
                                            <option value="">Kategori seçin...</option>
                                            <?php foreach($categories as $cat): ?>
                                                <option value="<?php echo $cat['id']; ?>">
                                                    <?php echo htmlspecialchars($cat['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="editTags" class="form-label">Etiketler</label>
                                <input type="text" class="form-control" id="editTags" name="tags" placeholder="Etiketleri virgülle ayırın">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="editIsPublic" name="is_public" value="1">
                                    <label class="form-check-label" for="editIsPublic">
                                        <i class="fas fa-globe me-1"></i>Herkese açık
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editCode" class="form-label">Kod</label>
                        <div class="code-editor">
                            <div class="code-editor-header">
                                <div class="d-flex align-items-center">
                                    <span class="language-dot me-2" style="background-color: #6c757d;"></span>
                                    <span class="text-light" id="editSelectedLanguage">Dil seçilmedi</span>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-sm btn-outline-light copy-code-btn">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            <textarea class="form-control" id="editCode" name="code" rows="12" required></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="submit" form="editSnippetForm" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Güncelle
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Snippet Detail Modal -->
<div class="modal fade" id="snippetDetailModal" tabindex="-1" aria-labelledby="snippetDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="snippetDetailModalLabel">
                    <i class="fas fa-code me-2"></i><span id="detailTitle">Kod Detayı</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <p class="text-muted mb-1" id="detailDescription">Açıklama</p>
                            <div class="d-flex align-items-center mb-2">
                                <span class="language-dot me-2" style="background-color: #6c757d;"></span>
                                <span class="text-muted" id="detailLanguage">Dil</span>
                                <span class="mx-2">•</span>
                                <i class="fas fa-calendar me-1"></i>
                                <span class="text-muted" id="detailDate">Tarih</span>
                                <span class="mx-2">•</span>
                                <i class="fas fa-eye me-1"></i>
                                <span class="text-muted" id="detailViews">0</span>
                            </div>
                            <div id="detailTags" class="mb-3">
                                <!-- Tags will be populated here -->
                            </div>
                        </div>
                        
                        <div class="code-editor">
                            <div class="code-editor-header">
                                <div class="d-flex align-items-center">
                                    <span class="language-dot me-2" style="background-color: #6c757d;"></span>
                                    <span class="text-light" id="detailCodeLanguage">Code</span>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-sm btn-outline-light copy-code-btn">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-light" onclick="downloadSnippet()">
                                        <i class="fas fa-download"></i>
                                    </button>
                                </div>
                            </div>
                            <pre class="line-numbers"><code id="detailCode" class="language-javascript"></code></pre>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-info-circle me-2"></i>Bilgiler
                                </h6>
                                <div class="snippet-info">
                                    <!-- Additional info will be populated here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" onclick="shareSnippet()">
                    <i class="fas fa-share me-1"></i>Paylaş
                </button>
                <button type="button" class="btn btn-outline-primary" onclick="toggleFavorite()">
                    <i class="far fa-heart me-1"></i>Favorilere Ekle
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<script>
// Modal-specific functions
function toggleFullscreen(button) {
    const modal = button.closest('.modal');
    if (modal.classList.contains('modal-fullscreen')) {
        modal.classList.remove('modal-fullscreen');
        button.innerHTML = '<i class="fas fa-expand"></i>';
    } else {
        modal.classList.add('modal-fullscreen');
        button.innerHTML = '<i class="fas fa-compress"></i>';
    }
}

function downloadSnippet() {
    const title = document.getElementById('detailTitle').textContent;
    const code = document.getElementById('detailCode').textContent;
    const language = document.getElementById('detailLanguage').textContent.toLowerCase();
    
    const blob = new Blob([code], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `${title.replace(/[^a-zA-Z0-9]/g, '_')}.${getFileExtension(language)}`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

function getFileExtension(language) {
    const extensions = {
        'javascript': 'js',
        'typescript': 'ts',
        'python': 'py',
        'php': 'php',
        'html': 'html',
        'css': 'css',
        'sql': 'sql',
        'java': 'java',
        'csharp': 'cs',
        'cpp': 'cpp'
    };
    return extensions[language] || 'txt';
}

function shareSnippet() {
    if (navigator.share) {
        navigator.share({
            title: document.getElementById('detailTitle').textContent,
            text: document.getElementById('detailDescription').textContent,
            url: window.location.href
        });
    } else {
        // Fallback: copy to clipboard
        const url = window.location.href;
        navigator.clipboard.writeText(url).then(() => {
            snippetManager.showToast('Link panoya kopyalandı!', 'success');
        });
    }
}

// Language selection handling
document.addEventListener('DOMContentLoaded', function() {
    const languageSelects = document.querySelectorAll('#language, #editLanguage');
    languageSelects.forEach(select => {
        select.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const languageName = selectedOption.textContent;
            const languageSpan = this.closest('.modal').querySelector('#selectedLanguage, #editSelectedLanguage');
            if (languageSpan) {
                languageSpan.textContent = languageName;
            }
        });
    });
    
    // Code preview update
    const codeTextarea = document.getElementById('code');
    if (codeTextarea) {
        codeTextarea.addEventListener('input', function() {
            const preview = document.getElementById('codePreview');
            if (preview) {
                preview.textContent = this.value.substring(0, 200) + (this.value.length > 200 ? '...' : '');
            }
        });
    }
});
</script>