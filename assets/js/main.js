/**
 * Ana JavaScript Dosyası
 * Gelişmiş Forum Sistemi
 * Erişilebilirlik odaklı, modern interaktif özellikler
 */

(function() {
    'use strict';
    
    // DOM yüklendiğinde çalış
    document.addEventListener('DOMContentLoaded', function() {
        initMobileMenu();
        initNotifications();
        initFormValidation();
        initTooltips();
        initAccessibility();
        initDarkMode();
    });
    
    /**
     * Mobil Menü İnit
     */
    function initMobileMenu() {
        const toggle = document.querySelector('.mobile-menu-toggle');
        const nav = document.querySelector('nav');
        
        if (toggle && nav) {
            toggle.addEventListener('click', function() {
                nav.classList.toggle('active');
                const isOpen = nav.classList.contains('active');
                toggle.setAttribute('aria-expanded', isOpen);
                toggle.innerHTML = isOpen ? '✕' : '☰';
            });
            
            // ESC ile kapat
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && nav.classList.contains('active')) {
                    nav.classList.remove('active');
                    toggle.setAttribute('aria-expanded', 'false');
                    toggle.innerHTML = '☰';
                }
            });
        }
    }
    
    /**
     * Bildirim Sistemi
     */
    function initNotifications() {
        const bell = document.querySelector('.notification-bell');
        const dropdown = document.querySelector('.notification-dropdown');
        
        if (bell && dropdown) {
            bell.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const isOpen = dropdown.classList.contains('active');
                dropdown.classList.toggle('active');
                bell.setAttribute('aria-expanded', !isOpen);
                
                if (!isOpen) {
                    loadNotifications();
                }
            });
            
            // Dışarı tıklandığında kapat
            document.addEventListener('click', function(e) {
                if (!bell.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.remove('active');
                    bell.setAttribute('aria-expanded', 'false');
                }
            });
            
            // ESC ile kapat
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && dropdown.classList.contains('active')) {
                    dropdown.classList.remove('active');
                    bell.setAttribute('aria-expanded', 'false');
                    bell.focus();
                }
            });
        }
    }
    
    /**
     * Bildirimleri Yükle (AJAX)
     */
    function loadNotifications() {
        // Bu fonksiyon gelecekte AJAX ile bildirimleri yükleyecek
        console.log('Bildirimler yükleniyor...');
    }
    
    /**
     * Form Doğrulama
     */
    function initFormValidation() {
        const forms = document.querySelectorAll('form[data-validate="true"]');
        
        forms.forEach(function(form) {
            form.addEventListener('submit', function(e) {
                if (!validateForm(form)) {
                    e.preventDefault();
                }
            });
            
            // Anlık doğrulama
            const inputs = form.querySelectorAll('input, textarea');
            inputs.forEach(function(input) {
                input.addEventListener('blur', function() {
                    validateField(input);
                });
            });
        });
    }
    
    /**
     * Form Doğrulama
     */
    function validateForm(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('[required]');
        
        inputs.forEach(function(input) {
            if (!validateField(input)) {
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    /**
     * Alan Doğrulama
     */
    function validateField(field) {
        const value = field.value.trim();
        const type = field.type;
        const formGroup = field.closest('.form-group');
        let error = '';
        
        if (field.hasAttribute('required') && value === '') {
            error = 'Bu alan zorunludur.';
        } else if (type === 'email' && value !== '' && !isValidEmail(value)) {
            error = 'Geçerli bir e-posta adresi girin.';
        } else if (field.hasAttribute('minlength')) {
            const minLength = parseInt(field.getAttribute('minlength'));
            if (value.length < minLength) {
                error = `En az ${minLength} karakter olmalıdır.`;
            }
        }
        
        if (formGroup) {
            const errorElement = formGroup.querySelector('.form-error') || createErrorElement();
            
            if (error) {
                formGroup.classList.add('has-error');
                errorElement.textContent = error;
                errorElement.style.display = 'block';
                field.setAttribute('aria-invalid', 'true');
                field.setAttribute('aria-describedby', errorElement.id);
                
                if (!formGroup.querySelector('.form-error')) {
                    formGroup.appendChild(errorElement);
                }
                
                return false;
            } else {
                formGroup.classList.remove('has-error');
                errorElement.style.display = 'none';
                field.removeAttribute('aria-invalid');
                field.removeAttribute('aria-describedby');
                return true;
            }
        }
        
        return error === '';
    }
    
    /**
     * Hata Elementi Oluştur
     */
    function createErrorElement() {
        const error = document.createElement('span');
        error.className = 'form-error';
        error.id = 'error-' + Date.now();
        error.setAttribute('role', 'alert');
        error.setAttribute('aria-live', 'polite');
        return error;
    }
    
    /**
     * E-posta Doğrulama
     */
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    /**
     * Tooltip İnit
     */
    function initTooltips() {
        const tooltipTriggers = document.querySelectorAll('[data-tooltip]');
        
        tooltipTriggers.forEach(function(trigger) {
            trigger.addEventListener('mouseenter', showTooltip);
            trigger.addEventListener('mouseleave', hideTooltip);
            trigger.addEventListener('focus', showTooltip);
            trigger.addEventListener('blur', hideTooltip);
        });
    }
    
    /**
     * Tooltip Göster
     */
    function showTooltip(e) {
        const text = e.target.getAttribute('data-tooltip');
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = text;
        tooltip.setAttribute('role', 'tooltip');
        tooltip.id = 'tooltip-' + Date.now();
        
        document.body.appendChild(tooltip);
        
        const rect = e.target.getBoundingClientRect();
        tooltip.style.position = 'absolute';
        tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
        tooltip.style.left = (rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2)) + 'px';
        
        e.target.setAttribute('aria-describedby', tooltip.id);
        e.target._tooltip = tooltip;
    }
    
    /**
     * Tooltip Gizle
     */
    function hideTooltip(e) {
        if (e.target._tooltip) {
            e.target._tooltip.remove();
            e.target.removeAttribute('aria-describedby');
            delete e.target._tooltip;
        }
    }
    
    /**
     * Erişilebilirlik Geliştirmeleri
     */
    function initAccessibility() {
        // Klavye navigasyonu için focus görünürlüğü
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                document.body.classList.add('user-is-tabbing');
            }
        });
        
        document.addEventListener('mousedown', function() {
            document.body.classList.remove('user-is-tabbing');
        });
        
        // Skip link'i göster
        const skipLink = document.querySelector('.skip-to-content');
        if (skipLink) {
            skipLink.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(skipLink.getAttribute('href'));
                if (target) {
                    target.focus();
                    target.scrollIntoView();
                }
            });
        }
        
        // Modal/Dialog ESC ile kapatma
        document.querySelectorAll('[role="dialog"]').forEach(function(dialog) {
            dialog.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeDialog(dialog);
                }
            });
        });
    }
    
    /**
     * Dialog Kapat
     */
    function closeDialog(dialog) {
        dialog.style.display = 'none';
        dialog.setAttribute('aria-hidden', 'true');
        
        // Focus'u geri döndür
        const trigger = dialog._trigger;
        if (trigger) {
            trigger.focus();
        }
    }
    
    /**
     * Karanlık Mod
     */
    function initDarkMode() {
        const toggle = document.querySelector('.dark-mode-toggle');
        
        if (toggle) {
            // Kayıtlı tercihi kontrol et
            const darkMode = localStorage.getItem('darkMode');
            if (darkMode === 'enabled') {
                document.documentElement.classList.add('dark-mode');
                toggle.setAttribute('aria-checked', 'true');
            }
            
            toggle.addEventListener('click', function() {
                const isDark = document.documentElement.classList.toggle('dark-mode');
                toggle.setAttribute('aria-checked', isDark);
                
                if (isDark) {
                    localStorage.setItem('darkMode', 'enabled');
                } else {
                    localStorage.setItem('darkMode', 'disabled');
                }
            });
        }
        
        // Sistem tercihini kontrol et
        if (window.matchMedia && !localStorage.getItem('darkMode')) {
            const darkModeQuery = window.matchMedia('(prefers-color-scheme: dark)');
            if (darkModeQuery.matches) {
                document.documentElement.classList.add('dark-mode');
            }
            
            darkModeQuery.addEventListener('change', function(e) {
                if (!localStorage.getItem('darkMode')) {
                    if (e.matches) {
                        document.documentElement.classList.add('dark-mode');
                    } else {
                        document.documentElement.classList.remove('dark-mode');
                    }
                }
            });
        }
    }
    
    /**
     * Beğeni/Beğenmeme Sistemi
     */
    window.handleLike = function(type, id, action) {
        // AJAX ile beğeni işlemi
        fetch('ajax/like.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                type: type,
                id: id,
                action: action
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // UI'yi güncelle
                updateLikeUI(type, id, data.likes, data.dislikes, data.userAction);
                
                // Bildirim göster
                showNotification('Başarılı!', 'success');
            } else {
                showNotification(data.message || 'Bir hata oluştu!', 'error');
            }
        })
        .catch(error => {
            console.error('Like error:', error);
            showNotification('Bir hata oluştu!', 'error');
        });
    };
    
    /**
     * Beğeni UI Güncelle
     */
    function updateLikeUI(type, id, likes, dislikes, userAction) {
        const container = document.querySelector(`[data-${type}-id="${id}"]`);
        if (container) {
            const likeBtn = container.querySelector('.like-btn');
            const dislikeBtn = container.querySelector('.dislike-btn');
            
            if (likeBtn) {
                likeBtn.textContent = `👍 ${likes}`;
                likeBtn.classList.toggle('active', userAction === 1);
            }
            
            if (dislikeBtn) {
                dislikeBtn.textContent = `👎 ${dislikes}`;
                dislikeBtn.classList.toggle('active', userAction === -1);
            }
        }
    }
    
    /**
     * Bildirim Göster
     */
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        notification.setAttribute('role', 'alert');
        notification.setAttribute('aria-live', 'polite');
        
        document.body.appendChild(notification);
        
        setTimeout(function() {
            notification.classList.add('show');
        }, 10);
        
        setTimeout(function() {
            notification.classList.remove('show');
            setTimeout(function() {
                notification.remove();
            }, 300);
        }, 3000);
    }
    
    /**
     * AJAX Form Gönderimi
     */
    window.submitAjaxForm = function(form, callback) {
        const formData = new FormData(form);
        const url = form.action || window.location.href;
        
        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (callback) {
                callback(data);
            }
        })
        .catch(error => {
            console.error('Form error:', error);
            showNotification('Bir hata oluştu!', 'error');
        });
    };
    
    /**
     * Anket Sonuçlarını Göster
     */
    window.showPollResults = function(pollId) {
        const pollContainer = document.querySelector(`[data-poll-id="${pollId}"]`);
        if (pollContainer) {
            const form = pollContainer.querySelector('.poll-form');
            const results = pollContainer.querySelector('.poll-results');
            
            if (form && results) {
                form.style.display = 'none';
                results.style.display = 'block';
                
                // Animasyon
                const bars = results.querySelectorAll('.poll-result-fill');
                bars.forEach(function(bar, index) {
                    setTimeout(function() {
                        bar.style.width = bar.getAttribute('data-width');
                    }, index * 100);
                });
            }
        }
    };
    
})();

// Tooltip stilleri için CSS
const style = document.createElement('style');
style.textContent = `
    .tooltip {
        position: absolute;
        background: rgba(0, 0, 0, 0.9);
        color: white;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 14px;
        z-index: 1000;
        pointer-events: none;
    }
    
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 4px;
        color: white;
        font-weight: 500;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        transform: translateX(400px);
        transition: transform 0.3s ease-in-out;
        z-index: 1000;
    }
    
    .notification.show {
        transform: translateX(0);
    }
    
    .notification-success {
        background: #2ecc71;
    }
    
    .notification-error {
        background: #e74c3c;
    }
    
    .notification-info {
        background: #3498db;
    }
    
    .notification-warning {
        background: #f39c12;
    }
    
    body.user-is-tabbing *:focus {
        outline: 3px solid #3498db !important;
        outline-offset: 2px !important;
    }
`;
document.head.appendChild(style);
