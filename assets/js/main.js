/**
 * Smart Resume Analyzer - Main JavaScript
 * Handles global functionality and utilities
 */

// ==========================================
// Document Ready
// ==========================================

document.addEventListener('DOMContentLoaded', function() {
    initializeTooltips();
    initializePopovers();
    initializeFormValidation();
    initializeScrollAnimations();
});

// ==========================================
// Bootstrap Tooltips & Popovers
// ==========================================

function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

function initializePopovers() {
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
}

// ==========================================
// Form Validation
// ==========================================

function initializeFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
}

// ==========================================
// Scroll Animations
// ==========================================

function initializeScrollAnimations() {
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 100
        });
    }
}

// ==========================================
// Notification System
// ==========================================

function showNotification(message, type = 'info', duration = 3000) {
    const container = document.getElementById('notificationContainer') || createNotificationContainer();
    const toastId = 'toast-' + Date.now();
    
    const bgClass = {
        'success': 'bg-success',
        'error': 'bg-danger',
        'warning': 'bg-warning',
        'info': 'bg-info'
    }[type] || 'bg-info';

    const iconClass = {
        'success': 'fa-check-circle',
        'error': 'fa-exclamation-circle',
        'warning': 'fa-exclamation-triangle',
        'info': 'fa-info-circle'
    }[type] || 'fa-info-circle';

    const toastHTML = `
        <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header ${bgClass} text-white">
                <i class="fas ${iconClass} me-2"></i>
                <strong class="me-auto">Notification</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                ${escapeHtml(message)}
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', toastHTML);
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { delay: duration });
    toast.show();

    // Remove from DOM when hidden
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}

function createNotificationContainer() {
    const container = document.createElement('div');
    container.id = 'notificationContainer';
    container.className = 'position-fixed bottom-0 end-0 p-3';
    container.style.zIndex = '11';
    document.body.appendChild(container);
    return container;
}

// ==========================================
// API Helpers
// ==========================================

/**
 * Make API request with proper error handling
 */
async function apiCall(endpoint, options = {}) {
    const defaultOptions = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    };

    const finalOptions = { ...defaultOptions, ...options };

    try {
        const response = await fetch(endpoint, finalOptions);
        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || `HTTP ${response.status}`);
        }

        return data;
    } catch (error) {
        console.error('API Error:', error);
        showNotification(error.message || 'An error occurred', 'error');
        throw error;
    }
}

/**
 * Upload file with progress tracking
 */
async function uploadFile(endpoint, fileInputId, onProgress = null) {
    const fileInput = document.getElementById(fileInputId);
    if (!fileInput || !fileInput.files.length) {
        throw new Error('No file selected');
    }

    const formData = new FormData();
    formData.append(fileInput.name, fileInput.files[0]);

    // Add CSRF token if available
    const csrfToken = getCsrfToken();
    if (csrfToken) {
        formData.append('csrf_token', csrfToken);
    }

    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();

        // Progress tracking
        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                const percentComplete = (e.loaded / e.total) * 100;
                if (onProgress) onProgress(percentComplete);
            }
        });

        xhr.addEventListener('load', () => {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    resolve(JSON.parse(xhr.responseText));
                } catch (e) {
                    reject(new Error('Invalid response format'));
                }
            } else {
                reject(new Error(`HTTP ${xhr.status}`));
            }
        });

        xhr.addEventListener('error', () => {
            reject(new Error('Network error'));
        });

        xhr.open('POST', endpoint);
        xhr.send(formData);
    });
}

// ==========================================
// CSRF Token Management
// ==========================================

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : null;
}

function generateCsrfToken() {
    return Math.random().toString(36).substr(2);
}

// ==========================================
// Cookie Management
// ==========================================

/**
 * Set cookie
 */
function setCookie(name, value, days = 7) {
    const date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    const expires = "expires=" + date.toUTCString();
    document.cookie = name + "=" + encodeURIComponent(value) + ";" + expires + ";path=/";
}

/**
 * Get cookie
 */
function getCookie(name) {
    const nameEQ = name + "=";
    const cookies = document.cookie.split(';');
    for (let i = 0; i < cookies.length; i++) {
        let cookie = cookies[i].trim();
        if (cookie.indexOf(nameEQ) === 0) {
            return decodeURIComponent(cookie.substring(nameEQ.length));
        }
    }
    return null;
}

/**
 * Delete cookie
 */
function deleteCookie(name) {
    setCookie(name, "", -1);
}

// ==========================================
// String Utilities
// ==========================================

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

/**
 * Truncate string
 */
function truncateString(str, length = 50, suffix = '...') {
    if (str.length <= length) return str;
    return str.substr(0, length - suffix.length) + suffix;
}

/**
 * Format bytes to human-readable
 */
function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

// ==========================================
// Date Utilities
// ==========================================

/**
 * Format date to readable format
 */
function formatDate(date, format = 'MM/dd/yyyy') {
    if (typeof date === 'string') {
        date = new Date(date);
    }

    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');

    return format
        .replace('yyyy', year)
        .replace('MM', month)
        .replace('dd', day)
        .replace('HH', hours)
        .replace('mm', minutes);
}

/**
 * Get time ago string
 */
function timeAgo(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);

    let interval = seconds / 31536000;
    if (interval > 1) return Math.floor(interval) + " years ago";

    interval = seconds / 2592000;
    if (interval > 1) return Math.floor(interval) + " months ago";

    interval = seconds / 86400;
    if (interval > 1) return Math.floor(interval) + " days ago";

    interval = seconds / 3600;
    if (interval > 1) return Math.floor(interval) + " hours ago";

    interval = seconds / 60;
    if (interval > 1) return Math.floor(interval) + " minutes ago";

    return "Just now";
}

// ==========================================
// DOM Utilities
// ==========================================

/**
 * Hide element with animation
 */
function hideElement(element, duration = 300) {
    if (!element) return;
    element.style.opacity = '1';
    element.style.transition = `opacity ${duration}ms ease-out`;
    element.style.opacity = '0';
    
    setTimeout(() => {
        element.style.display = 'none';
    }, duration);
}

/**
 * Show element with animation
 */
function showElement(element, display = 'block', duration = 300) {
    if (!element) return;
    element.style.display = display;
    element.style.opacity = '0';
    element.style.transition = `opacity ${duration}ms ease-in`;
    
    setTimeout(() => {
        element.style.opacity = '1';
    }, 10);
}

/**
 * Toggle element visibility
 */
function toggleElement(element) {
    if (element.style.display === 'none') {
        showElement(element);
    } else {
        hideElement(element);
    }
}

// ==========================================
// Validation Utilities
// ==========================================

/**
 * Validate email format
 */
function isValidEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * Validate password strength
 */
function getPasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;
    if (/[^a-zA-Z\d]/.test(password)) strength++;
    
    return ['Weak', 'Fair', 'Good', 'Strong'][strength];
}

/**
 * Validate URL format
 */
function isValidUrl(url) {
    try {
        new URL(url);
        return true;
    } catch (e) {
        return false;
    }
}

// ==========================================
// Debounce & Throttle
// ==========================================

/**
 * Debounce function calls
 */
function debounce(func, wait = 300) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Throttle function calls
 */
function throttle(func, limit = 300) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// ==========================================
// Logging
// ==========================================

/**
 * Log message with timestamp
 */
function log(message, type = 'info') {
    const timestamp = new Date().toLocaleTimeString();
    const prefix = `[${timestamp}] [${type.toUpperCase()}]`;
    
    switch (type) {
        case 'error':
            console.error(prefix, message);
            break;
        case 'warn':
            console.warn(prefix, message);
            break;
        case 'success':
            console.log(`%c${prefix} ${message}`, 'color: green;');
            break;
        default:
            console.log(prefix, message);
    }
}

// ==========================================
// Export for use in other scripts
// ==========================================

window.AppUtils = {
    showNotification,
    apiCall,
    uploadFile,
    getCsrfToken,
    setCookie,
    getCookie,
    deleteCookie,
    escapeHtml,
    truncateString,
    formatBytes,
    formatDate,
    timeAgo,
    hideElement,
    showElement,
    toggleElement,
    isValidEmail,
    getPasswordStrength,
    isValidUrl,
    debounce,
    throttle,
    log
};
