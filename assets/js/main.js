// Theme Management
class ThemeManager {
    constructor() {
        this.currentTheme = localStorage.getItem('theme') || 'light';
        this.init();
    }

    init() {
        this.applyTheme();
        this.bindEvents();
    }

    applyTheme() {
        document.documentElement.setAttribute('data-theme', this.currentTheme);
        const themeIcon = document.getElementById('themeIcon');
        if (themeIcon) {
            themeIcon.className = this.currentTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        }
    }

    toggleTheme() {
        this.currentTheme = this.currentTheme === 'light' ? 'dark' : 'light';
        localStorage.setItem('theme', this.currentTheme);
        this.applyTheme();
        
        // Update user preference in database
        this.updateThemePreference();
    }

    async updateThemePreference() {
        try {
            const response = await fetch('api/update-theme-preference.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    theme: this.currentTheme
                })
            });
            
            if (!response.ok) {
                console.error('Failed to update theme preference');
            }
        } catch (error) {
            console.error('Error updating theme preference:', error);
        }
    }

    bindEvents() {
        const themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', () => this.toggleTheme());
        }
    }
}

// Snippet Manager
class SnippetManager {
    constructor() {
        this.currentSnippet = null;
        this.init();
    }

    init() {
        this.bindEvents();
        this.initializeEditor();
    }

    bindEvents() {
        // New snippet form submission
        const newSnippetForm = document.getElementById('newSnippetForm');
        if (newSnippetForm) {
            newSnippetForm.addEventListener('submit', (e) => this.handleNewSnippet(e));
        }

        // Edit snippet form submission
        const editSnippetForm = document.getElementById('editSnippetForm');
        if (editSnippetForm) {
            editSnippetForm.addEventListener('submit', (e) => this.handleEditSnippet(e));
        }

        // Copy code buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('copy-code-btn') || e.target.closest('.copy-code-btn')) {
                this.copyCode(e.target);
            }
        });

        // Search functionality
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            this.debounce(searchInput, 'input', () => this.handleSearch(), 300);
        }
    }

    initializeEditor() {
        // Initialize Prism.js for syntax highlighting
        if (typeof Prism !== 'undefined') {
            Prism.highlightAll();
        }
    }

    async handleNewSnippet(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            const response = await fetch('api/create-snippet.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showToast('Kod parçacığı başarıyla oluşturuldu!', 'success');
                this.closeModal('newSnippetModal');
                this.refreshSnippetsList();
                e.target.reset();
            } else {
                this.showToast(result.message || 'Bir hata oluştu!', 'error');
            }
        } catch (error) {
            console.error('Error creating snippet:', error);
            this.showToast('Bir hata oluştu!', 'error');
        }
    }

    async handleEditSnippet(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            const response = await fetch('api/update-snippet.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showToast('Kod parçacığı başarıyla güncellendi!', 'success');
                this.closeModal('editSnippetModal');
                this.refreshSnippetsList();
            } else {
                this.showToast(result.message || 'Bir hata oluştu!', 'error');
            }
        } catch (error) {
            console.error('Error updating snippet:', error);
            this.showToast('Bir hata oluştu!', 'error');
        }
    }

    async deleteSnippet(snippetId) {
        if (!confirm('Bu kod parçacığını silmek istediğinizden emin misiniz?')) {
            return;
        }

        try {
            const response = await fetch('api/delete-snippet.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: snippetId
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showToast('Kod parçacığı başarıyla silindi!', 'success');
                this.refreshSnippetsList();
            } else {
                this.showToast(result.message || 'Bir hata oluştu!', 'error');
            }
        } catch (error) {
            console.error('Error deleting snippet:', error);
            this.showToast('Bir hata oluştu!', 'error');
        }
    }

    async toggleFavorite(snippetId) {
        try {
            const response = await fetch('api/toggle-favorite.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    snippet_id: snippetId
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showToast(result.message, 'success');
                this.updateFavoriteButton(snippetId, result.is_favorite);
            } else {
                this.showToast(result.message || 'Bir hata oluştu!', 'error');
            }
        } catch (error) {
            console.error('Error toggling favorite:', error);
            this.showToast('Bir hata oluştu!', 'error');
        }
    }

    async viewSnippet(snippetId) {
        try {
            const response = await fetch(`api/get-snippet.php?id=${snippetId}`);
            const result = await response.json();
            
            if (result.success) {
                this.displaySnippetDetail(result.snippet);
                this.openModal('snippetDetailModal');
                
                // Increment view count
                this.incrementViewCount(snippetId);
            } else {
                this.showToast(result.message || 'Kod parçacığı bulunamadı!', 'error');
            }
        } catch (error) {
            console.error('Error fetching snippet:', error);
            this.showToast('Bir hata oluştu!', 'error');
        }
    }

    async editSnippet(snippetId) {
        try {
            const response = await fetch(`api/get-snippet.php?id=${snippetId}`);
            const result = await response.json();
            
            if (result.success) {
                this.populateEditForm(result.snippet);
                this.openModal('editSnippetModal');
            } else {
                this.showToast(result.message || 'Kod parçacığı bulunamadı!', 'error');
            }
        } catch (error) {
            console.error('Error fetching snippet:', error);
            this.showToast('Bir hata oluştu!', 'error');
        }
    }

    async incrementViewCount(snippetId) {
        try {
            await fetch('api/increment-view.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    snippet_id: snippetId
                })
            });
        } catch (error) {
            console.error('Error incrementing view count:', error);
        }
    }

    displaySnippetDetail(snippet) {
        document.getElementById('detailTitle').textContent = snippet.title;
        document.getElementById('detailDescription').textContent = snippet.description;
        document.getElementById('detailLanguage').textContent = snippet.language_name;
        document.getElementById('detailCode').textContent = snippet.code;
        document.getElementById('detailDate').textContent = this.formatDate(snippet.created_at);
        document.getElementById('detailViews').textContent = snippet.view_count;
        
        // Update tags
        const tagsContainer = document.getElementById('detailTags');
        tagsContainer.innerHTML = '';
        if (snippet.tags) {
            const tags = snippet.tags.split(',');
            const tagColors = snippet.tag_colors ? snippet.tag_colors.split(',') : [];
            
            tags.forEach((tag, index) => {
                const tagElement = document.createElement('span');
                tagElement.className = 'badge me-1';
                tagElement.style.backgroundColor = tagColors[index] || '#6B7280';
                tagElement.textContent = tag.trim();
                tagsContainer.appendChild(tagElement);
            });
        }
        
        // Re-highlight code
        if (typeof Prism !== 'undefined') {
            Prism.highlightAll();
        }
    }

    populateEditForm(snippet) {
        document.getElementById('editSnippetId').value = snippet.id;
        document.getElementById('editTitle').value = snippet.title;
        document.getElementById('editDescription').value = snippet.description;
        document.getElementById('editLanguage').value = snippet.language_id;
        document.getElementById('editCategory').value = snippet.category_id || '';
        document.getElementById('editCode').value = snippet.code;
        document.getElementById('editIsPublic').checked = snippet.is_public == 1;
        
        // Set tags
        if (snippet.tags) {
            document.getElementById('editTags').value = snippet.tags.replace(/,/g, ', ');
        }
    }

    async copyCode(button) {
        const codeElement = button.closest('.code-preview, .code-editor').querySelector('code, textarea');
        const code = codeElement.textContent || codeElement.value;
        
        try {
            await navigator.clipboard.writeText(code);
            
            // Visual feedback
            const originalIcon = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check"></i>';
            button.classList.add('btn-success');
            
            setTimeout(() => {
                button.innerHTML = originalIcon;
                button.classList.remove('btn-success');
            }, 2000);
            
            this.showToast('Kod panoya kopyalandı!', 'success');
        } catch (error) {
            console.error('Error copying code:', error);
            this.showToast('Kopyalama işlemi başarısız!', 'error');
        }
    }

    handleSearch() {
        const searchInput = document.querySelector('input[name="search"]');
        const searchTerm = searchInput.value.trim();
        
        if (searchTerm.length >= 2 || searchTerm.length === 0) {
            this.refreshSnippetsList();
        }
    }

    refreshSnippetsList() {
        // This would typically reload the page or fetch new data via AJAX
        // For now, we'll just reload the page
        window.location.reload();
    }

    updateFavoriteButton(snippetId, isFavorite) {
        const favoriteButtons = document.querySelectorAll(`[onclick="toggleFavorite(${snippetId})"]`);
        favoriteButtons.forEach(button => {
            const icon = button.querySelector('i');
            if (isFavorite) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                button.classList.add('text-danger');
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                button.classList.remove('text-danger');
            }
        });
    }

    openModal(modalId) {
        const modal = new bootstrap.Modal(document.getElementById(modalId));
        modal.show();
    }

    closeModal(modalId) {
        const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
        if (modal) {
            modal.hide();
        }
    }

    showToast(message, type = 'info') {
        const toastContainer = this.getToastContainer();
        const toastId = 'toast-' + Date.now();
        
        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = `toast align-items-center text-white bg-${type === 'error' ? 'danger' : type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${this.getToastIcon(type)} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        toastContainer.appendChild(toast);
        
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        // Remove toast after it's hidden
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }

    getToastContainer() {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '1055';
            document.body.appendChild(container);
        }
        return container;
    }

    getToastIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-triangle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('tr-TR', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    debounce(element, event, callback, delay) {
        let timeoutId;
        element.addEventListener(event, () => {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(callback, delay);
        });
    }
}

// Global functions for onclick handlers
window.viewSnippet = (id) => snippetManager.viewSnippet(id);
window.editSnippet = (id) => snippetManager.editSnippet(id);
window.deleteSnippet = (id) => snippetManager.deleteSnippet(id);
window.toggleFavorite = (id) => snippetManager.toggleFavorite(id);

// Initialize managers when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.themeManager = new ThemeManager();
    window.snippetManager = new SnippetManager();
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
});

// Auto-resize textareas
document.addEventListener('input', (e) => {
    if (e.target.tagName === 'TEXTAREA') {
        e.target.style.height = 'auto';
        e.target.style.height = e.target.scrollHeight + 'px';
    }
});

// Add smooth scrolling to anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth'
            });
        }
    });
});