/**
 * YouTube Processor - Main JavaScript
 */

// Wait for document to fully load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    initTooltips();
    
    // Initialize dropdowns
    initDropdowns();
    
    // Initialize mobile menu
    initMobileMenu();
    
    // Initialize tabs
    initTabs();
    
    // Initialize modals
    initModals();
    
    // Initialize darkmode toggle if exists
    initDarkMode();
    
    // Initialize copy buttons
    initCopyButtons();
});

/**
 * Initialize tooltip functionality
 */
function initTooltips() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    
    tooltips.forEach(function(tooltip) {
        const text = tooltip.getAttribute('data-tooltip');
        
        // Create tooltip element
        const tooltipElement = document.createElement('span');
        tooltipElement.classList.add('tooltip-text');
        tooltipElement.innerText = text;
        
        // Add tooltip class to parent
        tooltip.classList.add('tooltip');
        
        // Append tooltip text to parent
        tooltip.appendChild(tooltipElement);
    });
}

/**
 * Initialize dropdown functionality
 */
function initDropdowns() {
    const dropdowns = document.querySelectorAll('.dropdown');
    
    dropdowns.forEach(function(dropdown) {
        const trigger = dropdown.querySelector('.dropdown-trigger');
        const menu = dropdown.querySelector('.dropdown-menu');
        
        if (!trigger || !menu) return;
        
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            
            // Close all other dropdowns
            dropdowns.forEach(function(d) {
                if (d !== dropdown) {
                    d.querySelector('.dropdown-menu')?.classList.add('hidden');
                }
            });
            
            // Toggle current dropdown
            menu.classList.toggle('hidden');
        });
    });
    
    // Close all dropdowns when clicking outside
    document.addEventListener('click', function() {
        dropdowns.forEach(function(dropdown) {
            const menu = dropdown.querySelector('.dropdown-menu');
            if (menu) menu.classList.add('hidden');
        });
    });
}

/**
 * Initialize mobile menu
 */
function initMobileMenu() {
    const menuButton = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileSidebar');
    const closeButton = document.getElementById('closeSidebar');
    
    if (!menuButton || !mobileMenu) return;
    
    menuButton.addEventListener('click', function() {
        mobileMenu.classList.remove('hidden');
    });
    
    if (closeButton) {
        closeButton.addEventListener('click', function() {
            mobileMenu.classList.add('hidden');
        });
    }
    
    // Close when clicking outside
    mobileMenu.addEventListener('click', function(e) {
        if (e.target === mobileMenu) {
            mobileMenu.classList.add('hidden');
        }
    });
}

/**
 * Initialize tabs functionality
 */
function initTabs() {
    const tabContainers = document.querySelectorAll('.tabs-container');
    
    tabContainers.forEach(function(container) {
        const tabs = container.querySelectorAll('.tab');
        const contents = container.querySelectorAll('.tab-content');
        
        tabs.forEach(function(tab, index) {
            tab.addEventListener('click', function() {
                // Deactivate all tabs
                tabs.forEach(t => t.classList.remove('active'));
                
                // Hide all contents
                contents.forEach(c => c.classList.add('hidden'));
                
                // Activate current tab and show content
                tab.classList.add('active');
                contents[index].classList.remove('hidden');
            });
        });
    });
}

/**
 * Initialize modal functionality
 */
function initModals() {
    const modalTriggers = document.querySelectorAll('[data-modal-target]');
    
    modalTriggers.forEach(function(trigger) {
        const modalId = trigger.getAttribute('data-modal-target');
        const modal = document.getElementById(modalId);
        
        if (!modal) return;
        
        const modalOverlay = modal.closest('.modal-overlay');
        const closeButtons = modal.querySelectorAll('[data-modal-close]');
        
        trigger.addEventListener('click', function() {
            modalOverlay.classList.remove('hidden');
        });
        
        // Close when clicking close buttons
        closeButtons.forEach(function(btn) {
            btn.addEventListener('click', function() {
                modalOverlay.classList.add('hidden');
            });
        });
        
        // Close when clicking overlay
        modalOverlay.addEventListener('click', function(e) {
            if (e.target === modalOverlay) {
                modalOverlay.classList.add('hidden');
            }
        });
    });
}

/**
 * Initialize dark mode toggle
 */
function initDarkMode() {
    const darkModeToggle = document.getElementById('darkModeToggle');
    
    if (!darkModeToggle) return;
    
    // Check for saved dark mode preference
    const isDarkMode = localStorage.getItem('darkMode') === 'true';
    
    // Set initial state
    if (isDarkMode) {
        document.documentElement.classList.add('dark-mode');
        darkModeToggle.checked = true;
    }
    
    // Handle toggle change
    darkModeToggle.addEventListener('change', function() {
        if (this.checked) {
            document.documentElement.classList.add('dark-mode');
            localStorage.setItem('darkMode', 'true');
        } else {
            document.documentElement.classList.remove('dark-mode');
            localStorage.setItem('darkMode', 'false');
        }
    });
}

/**
 * Initialize copy buttons
 */
function initCopyButtons() {
    const copyButtons = document.querySelectorAll('.copy-btn');
    
    copyButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const textToCopy = btn.getAttribute('data-copy-text');
            const originalText = btn.innerHTML;
            
            if (!textToCopy) return;
            
            // Create a temporary textarea element to copy from
            const textarea = document.createElement('textarea');
            textarea.value = textToCopy;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            
            // Change button text temporarily
            btn.innerHTML = '<i class="fas fa-check"></i> Đã sao chép!';
            
            // Reset button text after 2 seconds
            setTimeout(function() {
                btn.innerHTML = originalText;
            }, 2000);
        });
    });
}

/**
 * Format file size in human-readable format
 * 
 * @param {number} bytes File size in bytes
 * @returns {string} Formatted file size
 */
function formatFileSize(bytes) {
    if (bytes < 1024) {
        return bytes + ' B';
    } else if (bytes < 1048576) {
        return (bytes / 1024).toFixed(2) + ' KB';
    } else if (bytes < 1073741824) {
        return (bytes / 1048576).toFixed(2) + ' MB';
    } else {
        return (bytes / 1073741824).toFixed(2) + ' GB';
    }
}

/**
 * Format duration in seconds to HH:MM:SS
 * 
 * @param {number} seconds Duration in seconds
 * @returns {string} Formatted duration
 */
function formatDuration(seconds) {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds / 60) % 60);
    const secs = Math.floor(seconds % 60);
    
    return [
        hours.toString().padStart(2, '0'),
        minutes.toString().padStart(2, '0'),
        secs.toString().padStart(2, '0')
    ].join(':');
}

/**
 * Show a toast notification
 * 
 * @param {string} message Message to display
 * @param {string} type Notification type (success, error, info)
 * @param {number} duration Duration in milliseconds
 */
function showToast(message, type = 'info', duration = 5000) {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    // Add icon based on type
    let icon;
    switch (type) {
        case 'success':
            icon = '<i class="fas fa-check-circle mr-2"></i>';
            break;
        case 'error':
            icon = '<i class="fas fa-exclamation-circle mr-2"></i>';
            break;
        default:
            icon = '<i class="fas fa-info-circle mr-2"></i>';
            break;
    }
    
    toast.innerHTML = `
        <div class="flex items-center">
            ${icon}
            <span>${message}</span>
        </div>
    `;
    
    // Add to document
    document.body.appendChild(toast);
    
    // Show toast
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);
    
    // Hide and remove toast after duration
    setTimeout(() => {
        toast.classList.remove('show');
        
        // Remove from DOM after animation
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, duration);
}

/**
 * Make AJAX request
 * 
 * @param {string} url Request URL
 * @param {Object} options Request options
 * @returns {Promise} Promise resolving to response
 */
function ajax(url, options = {}) {
    // Default options
    const defaultOptions = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    };
    
    // Merge options
    const requestOptions = {...defaultOptions, ...options};
    
    // Handle body data
    if (requestOptions.body && typeof requestOptions.body === 'object') {
        requestOptions.body = JSON.stringify(requestOptions.body);
    }
    
    // Make request
    return fetch(url, requestOptions)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            
            // Parse response based on Content-Type
            const contentType = response.headers.get('Content-Type');
            
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                return response.text();
            }
        });
}
