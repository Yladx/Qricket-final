// Dark Mode Management
class DarkModeManager {
    constructor() {
        this.STORAGE_KEY = 'theme';
        this.LEGACY_KEY = 'darkMode';
        this.darkModeToggle = document.getElementById('dark-mode-toggle');
        this.darkModeToggleMobile = document.getElementById('dark-mode-toggle-mobile');
        this.sunIcon = document.getElementById('sun-icon');
        this.moonIcon = document.getElementById('moon-icon');
        this.sunIconMobile = document.getElementById('sun-icon-mobile');
        this.moonIconMobile = document.getElementById('moon-icon-mobile');
        this.root = document.documentElement;
        this.init();
    }

    init() {
        this.loadSavedPreference();
        this.setupEventListeners();
    }

    loadSavedPreference() {
        try {
            let storedTheme = localStorage.getItem(this.STORAGE_KEY);
            if (!storedTheme) {
                const legacy = localStorage.getItem(this.LEGACY_KEY);
                if (legacy === 'true' || legacy === 'false') {
                    storedTheme = legacy === 'true' ? 'dark' : 'light';
                }
            }

            if (this.root.dataset.theme && !storedTheme) {
                storedTheme = this.root.dataset.theme;
            }

            if (!storedTheme) {
                const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                storedTheme = prefersDark ? 'dark' : 'light';
            }

            this.setDarkMode(storedTheme === 'dark');
        } catch (error) {
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            this.setDarkMode(prefersDark);
        }
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
        this.applyDocumentTheme(isDark);
        if (isDark) {
            this.showSunIcon();
        } else {
            this.showMoonIcon();
        }
        try {
            localStorage.setItem(this.STORAGE_KEY, isDark ? 'dark' : 'light');
            localStorage.removeItem(this.LEGACY_KEY);
        } catch (error) {
            /* ignore storage failures */
        }
    }

    toggleDarkMode() {
        const isCurrentlyDark = this.root.classList.contains('dark');
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

    applyDocumentTheme(isDark) {
        const theme = isDark ? 'dark' : 'light';
        this.root.classList.toggle('dark', isDark);
        this.root.dataset.theme = theme;
        this.root.style.backgroundColor = isDark ? '#111827' : '#f9fafb';
        this.root.style.color = isDark ? '#f9fafb' : '#111827';
        this.root.style.colorScheme = theme;
    }

    // Public method to check current dark mode state
    isDarkMode() {
        return this.root.classList.contains('dark');
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
