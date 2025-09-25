/**
 * LAN Master - Enhanced JavaScript functionality
 * Handles dark mode, loading states, and improved user interactions
 */

class LanMaster {
    constructor() {
        this.init();
    }

    init() {
        this.setupDarkMode();
        this.setupLoadingStates();
        this.setupFormEnhancements();
        this.setupTableEnhancements();
        this.setupErrorHandling();
        this.setupKeyboardShortcuts();
    }

    /**
     * Dark Mode Toggle Functionality
     */
    setupDarkMode() {
        // Create dark mode toggle button
        const toggleButton = document.createElement('button');
        toggleButton.className = 'theme-toggle';
        toggleButton.innerHTML = '🌙';
        toggleButton.setAttribute('aria-label', 'Toggle dark mode');
        toggleButton.title = 'Toggle dark mode (Ctrl+D)';
        document.body.appendChild(toggleButton);

        // Check for saved theme preference or default to light mode
        const savedTheme = localStorage.getItem('theme') || 'light';
        this.setTheme(savedTheme);

        // Toggle theme on button click
        toggleButton.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            this.setTheme(newTheme);
        });
    }

    setTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        
        const toggleButton = document.querySelector('.theme-toggle');
        if (toggleButton) {
            toggleButton.innerHTML = theme === 'dark' ? '☀️' : '🌙';
        }
    }

    /**
     * Loading States for AJAX Calls
     */
    setupLoadingStates() {
        // Override XMLHttpRequest to add loading states
        const originalOpen = XMLHttpRequest.prototype.open;
        const originalSend = XMLHttpRequest.prototype.send;

        XMLHttpRequest.prototype.open = function(method, url, ...args) {
            this._url = url;
            return originalOpen.apply(this, [method, url, ...args]);
        };

        XMLHttpRequest.prototype.send = function(data) {
            if (this._url && this._url.includes('ajax_igdb.php')) {
                this.addEventListener('loadstart', () => {
                    this.showLoadingState();
                });

                this.addEventListener('loadend', () => {
                    this.hideLoadingState();
                });
            }
            return originalSend.apply(this, [data]);
        };
    }

    showLoadingState() {
        // Add loading spinner to IGDB buttons
        const igdbButtons = document.querySelectorAll('button[onclick*="igdb"], input[onclick*="igdb"]');
        igdbButtons.forEach(button => {
            if (!button.querySelector('.loading-spinner')) {
                const spinner = document.createElement('span');
                spinner.className = 'loading-spinner';
                button.appendChild(spinner);
                button.disabled = true;
            }
        });

        // Show loading message
        this.showMessage('Loading game data...', 'info');
    }

    hideLoadingState() {
        // Remove loading spinners
        const spinners = document.querySelectorAll('.loading-spinner');
        spinners.forEach(spinner => spinner.remove());

        // Re-enable buttons
        const igdbButtons = document.querySelectorAll('button[onclick*="igdb"], input[onclick*="igdb"]');
        igdbButtons.forEach(button => {
            button.disabled = false;
        });
    }

    /**
     * Enhanced Form Functionality
     */
    setupFormEnhancements() {
        // Real-time validation
        document.querySelectorAll('input[required], select[required], textarea[required]').forEach(field => {
            field.addEventListener('blur', () => {
                this.validateField(field);
            });

            field.addEventListener('input', () => {
                if (field.classList.contains('error')) {
                    this.validateField(field);
                }
            });
        });

        // Email validation
        document.querySelectorAll('input[type="email"]').forEach(field => {
            field.addEventListener('blur', () => {
                this.validateEmail(field);
            });
        });

        // Password validation
        document.querySelectorAll('input[type="password"]').forEach(field => {
            field.addEventListener('input', () => {
                this.validatePassword(field);
            });
        });

        // Real-time search functionality
        const searchInputs = document.querySelectorAll('input[name="search"], input[type="search"]');
        searchInputs.forEach(input => {
            let searchTimeout;
            input.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.performSearch(e.target.value);
                }, 300);
            });
        });

        // Form submission validation
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', (e) => {
                // Skip validation for game update/delete forms to prevent interference
                const action = form.querySelector('input[name="action"]')?.value;
                if (action === 'update_game' || action === 'delete_game') {
                    return; // Allow normal form submission
                }
                
                if (!this.validateForm(form)) {
                    e.preventDefault();
                    this.showMessage('Please fix the errors below before submitting.', 'error');
                }
            });
        });
    }

    validateField(field) {
        const value = field.value.trim();
        const fieldName = field.getAttribute('name') || field.getAttribute('id') || 'Field';
        
        if (field.hasAttribute('required') && !value) {
            this.showFieldError(field, `${this.getFieldDisplayName(fieldName)} is required`);
            return false;
        } else if (value) {
            // Additional validation based on field type
            if (field.type === 'email') {
                return this.validateEmail(field);
            } else if (field.type === 'password') {
                return this.validatePassword(field);
            } else if (field.type === 'url') {
                return this.validateUrl(field);
            } else if (field.type === 'number') {
                return this.validateNumber(field);
            }
            
            this.showFieldSuccess(field);
            return true;
        } else {
            this.clearFieldError(field);
            return true;
        }
    }

    validateEmail(field) {
        const email = field.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email && !emailRegex.test(email)) {
            this.showFieldError(field, 'Please enter a valid email address');
            return false;
        } else if (email) {
            this.showFieldSuccess(field);
            return true;
        }
        return true;
    }

    validatePassword(field) {
        const password = field.value;
        const minLength = 6;
        
        if (password && password.length < minLength) {
            this.showFieldError(field, `Password must be at least ${minLength} characters long`);
            return false;
        } else if (password) {
            this.showFieldSuccess(field);
            return true;
        }
        return true;
    }

    validateUrl(field) {
        const url = field.value.trim();
        try {
            if (url) {
                new URL(url);
                this.showFieldSuccess(field);
            }
            return true;
        } catch {
            this.showFieldError(field, 'Please enter a valid URL');
            return false;
        }
    }

    validateNumber(field) {
        const value = field.value.trim();
        const min = field.getAttribute('min');
        const max = field.getAttribute('max');
        
        if (value && isNaN(value)) {
            this.showFieldError(field, 'Please enter a valid number');
            return false;
        }
        
        if (value && min && parseFloat(value) < parseFloat(min)) {
            this.showFieldError(field, `Value must be at least ${min}`);
            return false;
        }
        
        if (value && max && parseFloat(value) > parseFloat(max)) {
            this.showFieldError(field, `Value must be no more than ${max}`);
            return false;
        }
        
        if (value) {
            this.showFieldSuccess(field);
        }
        return true;
    }

    validateForm(form) {
        let isValid = true;
        const fields = form.querySelectorAll('input, select, textarea');
        
        fields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });
        
        return isValid;
    }

    getFieldDisplayName(fieldName) {
        const displayNames = {
            'email': 'Email',
            'password': 'Password',
            'username': 'Username',
            'title': 'Game Title',
            'slug': 'Slug',
            'genre': 'Genre',
            'subgenre': 'Subgenre',
            'r_year': 'Release Year',
            'image_url': 'Image URL',
            'system_requirements': 'System Requirements'
        };
        
        return displayNames[fieldName] || fieldName.charAt(0).toUpperCase() + fieldName.slice(1);
    }

    showFieldError(field, message) {
        this.clearFieldError(field);
        field.classList.add('error');
        field.classList.remove('success');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.textContent = message;
        
        field.parentNode.insertBefore(errorDiv, field.nextSibling);
    }

    showFieldSuccess(field) {
        this.clearFieldError(field);
        field.classList.remove('error');
        field.classList.add('success');
    }

    clearFieldError(field) {
        const existingError = field.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }
        field.classList.remove('error', 'success');
    }

    handleSearch(query) {
        if (query.length < 2) return;
        
        // Add search suggestions (placeholder for future enhancement)
        console.log('Search query:', query);
    }

    performSearch(query) {
        this.handleSearch(query);
    }

    /**
     * Table Enhancements
     */
    setupTableEnhancements() {
        // Add data labels for responsive tables
        const tables = document.querySelectorAll('table');
        tables.forEach(table => {
            const headers = table.querySelectorAll('th');
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                cells.forEach((cell, index) => {
                    if (headers[index]) {
                        cell.setAttribute('data-label', headers[index].textContent);
                    }
                });
            });
        });
    }

    /**
     * Enhanced Error Handling
     */
    setupErrorHandling() {
        // Global error handler
        window.addEventListener('error', (e) => {
            console.error('JavaScript error:', e.error);
            this.showMessage('An error occurred. Please try again.', 'error');
        });

        // Handle AJAX errors
        document.addEventListener('ajaxError', (e) => {
            this.showMessage('Failed to load data. Please check your connection.', 'error');
        });
    }

    showMessage(message, type = 'info', duration = 5000) {
        // Remove any existing messages of the same type
        const existingMessages = document.querySelectorAll(`.toast-message.${type}`);
        existingMessages.forEach(msg => msg.remove());
        
        const messageDiv = document.createElement('div');
        messageDiv.className = `toast-message ${type}`;
        messageDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 20px;
            border-radius: 8px;
            color: white;
            z-index: 10000;
            max-width: 350px;
            min-width: 250px;
            word-wrap: break-word;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 14px;
            line-height: 1.4;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateX(100%);
            transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out;
            opacity: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        `;
        
        // Add icon based on type
        const icon = document.createElement('span');
        icon.style.cssText = 'font-size: 16px; flex-shrink: 0;';
        
        switch(type) {
            case 'error':
                messageDiv.style.backgroundColor = '#e74c3c';
                messageDiv.style.borderLeft = '4px solid #c0392b';
                icon.textContent = '❌';
                break;
            case 'success':
                messageDiv.style.backgroundColor = '#27ae60';
                messageDiv.style.borderLeft = '4px solid #229954';
                icon.textContent = '✅';
                break;
            case 'warning':
                messageDiv.style.backgroundColor = '#f39c12';
                messageDiv.style.borderLeft = '4px solid #e67e22';
                icon.textContent = '⚠️';
                break;
            case 'info':
            default:
                messageDiv.style.backgroundColor = '#3498db';
                messageDiv.style.borderLeft = '4px solid #2980b9';
                icon.textContent = 'ℹ️';
        }
        
        // Add close button
        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = '×';
        closeBtn.style.cssText = `
            background: none;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            padding: 0;
            margin-left: auto;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0.7;
            transition: opacity 0.2s;
        `;
        
        closeBtn.addEventListener('mouseenter', () => {
            closeBtn.style.opacity = '1';
        });
        
        closeBtn.addEventListener('mouseleave', () => {
            closeBtn.style.opacity = '0.7';
        });
        
        closeBtn.addEventListener('click', () => {
            this.hideMessage(messageDiv);
        });
        
        // Create message content
        const messageContent = document.createElement('span');
        messageContent.textContent = message;
        messageContent.style.flex = '1';
        
        messageDiv.appendChild(icon);
        messageDiv.appendChild(messageContent);
        messageDiv.appendChild(closeBtn);
        
        document.body.appendChild(messageDiv);
        
        // Animate in
        requestAnimationFrame(() => {
            messageDiv.style.transform = 'translateX(0)';
            messageDiv.style.opacity = '1';
        });
        
        // Auto-hide after duration
        if (duration > 0) {
            setTimeout(() => {
                this.hideMessage(messageDiv);
            }, duration);
        }
        
        return messageDiv;
    }
    
    hideMessage(messageDiv) {
        if (!messageDiv || !messageDiv.parentNode) return;
        
        messageDiv.style.transform = 'translateX(100%)';
        messageDiv.style.opacity = '0';
        
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.parentNode.removeChild(messageDiv);
            }
        }, 300);
    }

    /**
     * Keyboard Shortcuts
     */
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl+D for dark mode toggle
            if (e.ctrlKey && e.key === 'd') {
                e.preventDefault();
                const currentTheme = document.documentElement.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                this.setTheme(newTheme);
            }

            // Ctrl+/ for search focus
            if (e.ctrlKey && e.key === '/') {
                e.preventDefault();
                const searchInput = document.querySelector('input[name="search"], input.text_search');
                if (searchInput) {
                    searchInput.focus();
                }
            }

            // Escape to close modals/messages
            if (e.key === 'Escape') {
                const messages = document.querySelectorAll('.toast-message');
                messages.forEach(msg => msg.remove());
            }
        });
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new LanMaster();
});

// Export for potential module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = LanMaster;
}