// Dark Mode Management
class DarkModeManager {
    constructor() {
        this.darkModeToggle = document.getElementById('dark-mode-toggle');
        this.darkModeToggleMobile = document.getElementById('dark-mode-toggle-mobile');
        this.sunIcon = document.getElementById('sun-icon');
        this.moonIcon = document.getElementById('moon-icon');
        this.sunIconMobile = document.getElementById('sun-icon-mobile');
        this.moonIconMobile = document.getElementById('moon-icon-mobile');
        this.init();
    }

    init() {
        this.loadSavedPreference();
        this.setupEventListeners();
    }

    loadSavedPreference() {
        const isDarkMode = localStorage.getItem('darkMode') === 'true';
        this.setDarkMode(isDarkMode);
    }

    setupEventListeners() {
        if (this.darkModeToggle) {
            this.darkModeToggle.addEventListener('click', () => {
                this.toggleDarkMode();
            });
        }
        
        if (this.darkModeToggleMobile) {
            this.darkModeToggleMobile.addEventListener('click', () => {
                this.toggleDarkMode();
            });
        }
    }

    setDarkMode(isDark) {
        if (isDark) {
            document.documentElement.classList.add('dark');
            this.showSunIcon();
        } else {
            document.documentElement.classList.remove('dark');
            this.showMoonIcon();
        }
        localStorage.setItem('darkMode', isDark.toString());
    }

    toggleDarkMode() {
        const isCurrentlyDark = document.documentElement.classList.contains('dark');
        this.setDarkMode(!isCurrentlyDark);
    }

    showSunIcon() {
        if (this.sunIcon) this.sunIcon.classList.remove('hidden');
        if (this.moonIcon) this.moonIcon.classList.add('hidden');
        if (this.sunIconMobile) this.sunIconMobile.classList.remove('hidden');
        if (this.moonIconMobile) this.moonIconMobile.classList.add('hidden');
    }

    showMoonIcon() {
        if (this.sunIcon) this.sunIcon.classList.add('hidden');
        if (this.moonIcon) this.moonIcon.classList.remove('hidden');
        if (this.sunIconMobile) this.sunIconMobile.classList.add('hidden');
        if (this.moonIconMobile) this.moonIconMobile.classList.remove('hidden');
    }

    // Public method to check current dark mode state
    isDarkMode() {
        return document.documentElement.classList.contains('dark');
    }

    // Public method to force dark mode
    enableDarkMode() {
        this.setDarkMode(true);
    }

    // Public method to force light mode
    enableLightMode() {
        this.setDarkMode(false);
    }
}

// Initialize dark mode when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.darkModeManager = new DarkModeManager();
});
