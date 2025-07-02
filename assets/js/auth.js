class AuthManager {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
        this.initializePasswordStrength();
    }

    bindEvents() {
        // Tab switching
        const tabButtons = document.querySelectorAll('.auth-nav-item');
        tabButtons.forEach(button => {
            button.addEventListener('click', (e) => this.switchTab(e.target.dataset.tab));
        });

        // Password visibility toggle
        const togglePassword = document.getElementById('togglePassword');
        if (togglePassword) {
            togglePassword.addEventListener('click', () => this.togglePasswordVisibility());
        }

        // Form validation
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => this.validateForm(e));
        });

        // Real-time validation
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('blur', () => this.validateInput(input));
            input.addEventListener('input', () => this.clearValidation(input));
        });

        // Password strength checker
        const passwordInput = document.getElementById('reg_password');
        if (passwordInput) {
            passwordInput.addEventListener('input', () => this.checkPasswordStrength(passwordInput.value));
        }

        // Confirm password validation
        const confirmPasswordInput = document.getElementById('confirm_password');
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', () => this.validatePasswordMatch());
        }
    }

    switchTab(tabName) {
        // Update tab buttons
        document.querySelectorAll('.auth-nav-item').forEach(button => {
            button.classList.remove('active');
        });
        document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');

        // Update tab content
        document.querySelectorAll('.auth-tab-content').forEach(content => {
            content.classList.remove('active');
        });
        document.getElementById(tabName).classList.add('active');

        // Clear any validation errors
        this.clearAllValidation();
    }

    togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const toggleButton = document.getElementById('togglePassword');
        const icon = toggleButton.querySelector('i');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    validateForm(e) {
        const form = e.target;
        let isValid = true;

        // Clear previous validation
        this.clearAllValidation();

        // Validate all required fields
        const requiredInputs = form.querySelectorAll('input[required]');
        requiredInputs.forEach(input => {
            if (!this.validateInput(input)) {
                isValid = false;
            }
        });

        // Additional validation for registration form
        if (form.querySelector('input[name="register"]')) {
            if (!this.validateRegistrationForm(form)) {
                isValid = false;
            }
        }

        if (!isValid) {
            e.preventDefault();
            this.showAlert('Lütfen tüm alanları doğru şekilde doldurun.', 'danger');
        } else {
            // Show loading state
            const submitButton = form.querySelector('button[type="submit"]');
            this.setLoadingState(submitButton, true);
        }
    }

    validateInput(input) {
        const value = input.value.trim();
        let isValid = true;
        let errorMessage = '';

        // Required field validation
        if (input.hasAttribute('required') && !value) {
            errorMessage = 'Bu alan zorunludur.';
            isValid = false;
        }

        // Email validation
        if (input.type === 'email' && value && !this.isValidEmail(value)) {
            errorMessage = 'Geçerli bir e-posta adresi girin.';
            isValid = false;
        }

        // Password validation
        if (input.type === 'password' && value) {
            const minLength = input.name === 'password' ? 6 : 0;
            if (value.length < minLength) {
                errorMessage = `Şifre en az ${minLength} karakter olmalıdır.`;
                isValid = false;
            }
        }

        // Username validation
        if (input.name === 'username' && value) {
            if (value.length < 3) {
                errorMessage = 'Kullanıcı adı en az 3 karakter olmalıdır.';
                isValid = false;
            } else if (!/^[a-zA-Z0-9_]+$/.test(value)) {
                errorMessage = 'Kullanıcı adı sadece harf, rakam ve alt çizgi içerebilir.';
                isValid = false;
            }
        }

        // Show validation result
        if (!isValid) {
            this.showInputError(input, errorMessage);
        } else {
            this.showInputSuccess(input);
        }

        return isValid;
    }

    validateRegistrationForm(form) {
        let isValid = true;

        // Password match validation
        const password = form.querySelector('input[name="password"]').value;
        const confirmPassword = form.querySelector('input[name="confirm_password"]').value;

        if (password !== confirmPassword) {
            this.showInputError(form.querySelector('input[name="confirm_password"]'), 'Şifreler eşleşmiyor.');
            isValid = false;
        }

        // Terms acceptance
        const termsCheckbox = form.querySelector('#terms');
        if (!termsCheckbox.checked) {
            this.showAlert('Kullanım koşullarını kabul etmelisiniz.', 'danger');
            isValid = false;
        }

        return isValid;
    }

    validatePasswordMatch() {
        const password = document.getElementById('reg_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const confirmPasswordInput = document.getElementById('confirm_password');

        if (confirmPassword && password !== confirmPassword) {
            this.showInputError(confirmPasswordInput, 'Şifreler eşleşmiyor.');
        } else if (confirmPassword) {
            this.showInputSuccess(confirmPasswordInput);
        }
    }

    checkPasswordStrength(password) {
        const strengthIndicator = this.getPasswordStrengthIndicator();
        const strength = this.calculatePasswordStrength(password);

        strengthIndicator.className = 'password-strength-bar';
        
        if (strength < 3) {
            strengthIndicator.classList.add('strength-weak');
        } else if (strength < 5) {
            strengthIndicator.classList.add('strength-medium');
        } else {
            strengthIndicator.classList.add('strength-strong');
        }
    }

    calculatePasswordStrength(password) {
        let strength = 0;
        
        if (password.length >= 6) strength++;
        if (password.length >= 10) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;

        return strength;
    }

    getPasswordStrengthIndicator() {
        let indicator = document.querySelector('.password-strength-bar');
        if (!indicator) {
            const container = document.createElement('div');
            container.className = 'password-strength';
            indicator = document.createElement('div');
            indicator.className = 'password-strength-bar';
            container.appendChild(indicator);
            
            const passwordInput = document.getElementById('reg_password');
            passwordInput.parentNode.insertBefore(container, passwordInput.nextSibling);
        }
        return indicator;
    }

    initializePasswordStrength() {
        const passwordInput = document.getElementById('reg_password');
        if (passwordInput) {
            this.getPasswordStrengthIndicator();
        }
    }

    showInputError(input, message) {
        this.clearInputValidation(input);
        
        input.classList.add('is-invalid');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        
        input.parentNode.appendChild(errorDiv);
    }

    showInputSuccess(input) {
        this.clearInputValidation(input);
        input.classList.add('is-valid');
    }

    clearInputValidation(input) {
        input.classList.remove('is-valid', 'is-invalid');
        const feedback = input.parentNode.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.remove();
        }
    }

    clearValidation(input) {
        this.clearInputValidation(input);
    }

    clearAllValidation() {
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => this.clearInputValidation(input));
        
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            if (!alert.classList.contains('persistent')) {
                alert.remove();
            }
        });
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    showAlert(message, type = 'info') {
        showToast(message, type);
    }

    getAlertIcon(type) {
        const icons = {
            success: 'check-circle',
            danger: 'exclamation-triangle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    }

    setLoadingState(button, isLoading) {
        if (isLoading) {
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Yükleniyor...';
            button.classList.add('loading');
        } else {
            button.disabled = false;
            button.classList.remove('loading');
            // Reset original text would require storing it first
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new AuthManager();
});

// Add some smooth animations on page load
window.addEventListener('load', () => {
    const authCard = document.querySelector('.auth-card');
    authCard.style.opacity = '0';
    authCard.style.transform = 'translateY(20px)';
    
    setTimeout(() => {
        authCard.style.transition = 'all 0.6s ease';
        authCard.style.opacity = '1';
        authCard.style.transform = 'translateY(0)';
    }, 100);
});

// Toast gösterme fonksiyonu
function showToast(message, type = 'info') {
    let toast = document.createElement('div');
    toast.className = `toast-message toast-${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => { toast.classList.add('show'); }, 10);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Login ve kayıt formu submit işlemleri
const loginForm = document.querySelector('#login form');
const registerForm = document.querySelector('#register form');

if (loginForm) {
    loginForm.addEventListener('submit', function(e) {
        setTimeout(() => {
            const errorDiv = document.querySelector('.alert-danger');
            if (errorDiv) {
                showToast(errorDiv.textContent.trim(), 'error');
            }
            const successDiv = document.querySelector('.alert-success');
            if (successDiv) {
                showToast(successDiv.textContent.trim(), 'success');
                window.location.href = 'index.php';
            }
        }, 100);
    });
}

if (registerForm) {
    // Kullanıcı adı anlık kontrolü
    const usernameInput = registerForm.querySelector('input[name="username"]');
    usernameInput.addEventListener('blur', function() {
        const username = usernameInput.value.trim();
        if (username.length > 0) {
            fetch('api/check-username.php?username=' + encodeURIComponent(username))
                .then(res => res.json())
                .then(data => {
                    if (!data.available) {
                        showToast('Bu kullanıcı adı kullanılamaz.', 'error');
                    }
                });
        }
    });
    registerForm.addEventListener('submit', function(e) {
        const errorDiv = document.querySelector('.alert-danger');
        if (errorDiv) {
            showToast(errorDiv.textContent.trim(), 'error');
        }
        const successDiv = document.querySelector('.alert-success');
        if (successDiv) {
            showToast(successDiv.textContent.trim(), 'success');
        }
    });
}