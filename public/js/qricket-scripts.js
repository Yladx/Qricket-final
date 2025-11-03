// Qricket General JavaScript Functions

// Profile section switching
function showSection(sectionId, linkElement) {
    // Hide all sections
    const sections = document.querySelectorAll('.section-content');
    if (sections && sections.length > 0) {
        sections.forEach(section => {
            if (section && section.classList) {
                section.classList.add('hidden');
            }
        });
    }
    
    // Show the selected section
    const targetSection = document.getElementById(sectionId);
    if (targetSection && targetSection.classList) {
        targetSection.classList.remove('hidden');
    }
    
    // Update active state in sidebar
    const sidebarLinks = document.querySelectorAll('.sidebar-link');
    if (sidebarLinks && sidebarLinks.length > 0) {
        sidebarLinks.forEach(link => {
            if (link && link.classList) {
                link.classList.remove('active');
            }
        });
    }
    
    // Add active state to clicked link if provided
    if (linkElement && linkElement.classList) {
        linkElement.classList.add('active');
    } else if (typeof window.event !== 'undefined' && window.event && window.event.target && window.event.target.classList) {
        window.event.target.classList.add('active');
    }
}

// Initialize profile page
function initializeProfilePage() {
    const accountSettingsLink = document.querySelector('a[href="#account-settings"]');
    if (accountSettingsLink) {
        accountSettingsLink.classList.add('active');
    }
}

// Mobile menu toggle functionality
function initializeMobileMenu() {
    const menuToggle = document.getElementById('menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (menuToggle && mobileMenu) {
        menuToggle.addEventListener('click', function() {
            mobileMenu.classList.toggle('-translate-x-full');
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!mobileMenu.contains(event.target) && !menuToggle.contains(event.target)) {
                mobileMenu.classList.add('-translate-x-full');
            }
        });
    }
}

// Reusable: initialize password visibility toggles
function initializePasswordToggles() {
    document.querySelectorAll('[data-password-toggle]')
        .forEach(function(button) {
            const targetSelector = button.getAttribute('data-target');
            if (!targetSelector) return;
            const input = document.querySelector(targetSelector);
            if (!input) return;
            const openIcon = button.querySelector('[data-icon-open]');
            const closeIcon = button.querySelector('[data-icon-closed]');
            button.addEventListener('click', function() {
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                if (openIcon && closeIcon) {
                    openIcon.classList.toggle('hidden', !isPassword);
                    closeIcon.classList.toggle('hidden', isPassword);
                }
            });
        });
}

// Initialize all Qricket features
function initializeQricket() {
    // Initialize mobile menu
    initializeMobileMenu();
    
    // Initialize profile page if on profile page
    if (document.querySelector('.section-content')) {
        initializeProfilePage();
    }

    // Initialize password toggles globally
    initializePasswordToggles();
}

// Helper function to get theme-aware SweetAlert configuration
function getThemeAwareSwalConfig() {
    const isDark = document.documentElement.classList.contains('dark') || (localStorage.getItem('darkMode') === 'true');
    return {
        background: isDark ? '#111827' : '#ffffff',
        color: isDark ? '#e5e7eb' : '#111827',
        customClass: {
            popup: `rounded-2xl shadow-2xl ${isDark ? 'ring-1 ring-gray-700' : 'ring-1 ring-gray-200'}`,
            title: 'text-xl font-semibold',
            htmlContainer: isDark ? 'text-gray-300' : 'text-gray-600',
            confirmButton: (isDark ? 'bg-red-500 hover:bg-red-400' : 'bg-red-600 hover:bg-red-700') + ' text-white px-4 py-2 rounded-lg',
            cancelButton: (isDark ? 'bg-gray-700 hover:bg-gray-600 text-gray-200' : 'bg-gray-100 hover:bg-gray-200 text-gray-700') + ' px-4 py-2 rounded-lg'
        },
        buttonsStyling: false
    };
}

// Run initialization when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeQricket();
});
