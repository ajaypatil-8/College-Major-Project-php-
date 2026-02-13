/**
 * ====================================
 * THEME SYSTEM - JavaScript
 * ====================================
 */

(function() {
    'use strict';
    
    // Get theme from localStorage or default to 'light'
    const getTheme = () => {
        return localStorage.getItem('crowdspark-theme') || 'light';
    };
    
    // Set theme
    const setTheme = (theme) => {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('crowdspark-theme', theme);
        
        // Update button state
        updateThemeButton(theme);
    };
    
    // Toggle theme
    const toggleTheme = () => {
        const currentTheme = getTheme();
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        setTheme(newTheme);
        
        // Add animation effect
        document.body.style.transition = 'background-color 0.5s ease';
        setTimeout(() => {
            document.body.style.transition = '';
        }, 500);
    };
    
    // Update theme button appearance
    const updateThemeButton = (theme) => {
        const button = document.querySelector('.theme-toggle');
        if (button) {
            if (theme === 'dark') {
                button.setAttribute('aria-label', 'Switch to light mode');
                button.setAttribute('title', 'Switch to light mode');
            } else {
                button.setAttribute('aria-label', 'Switch to dark mode');
                button.setAttribute('title', 'Switch to dark mode');
            }
        }
    };
    
    // Initialize theme on page load
    const initTheme = () => {
        const savedTheme = getTheme();
        setTheme(savedTheme);
    };
    
    // Create and inject theme toggle button
    const createThemeToggle = () => {
        // Check if button already exists
        if (document.querySelector('.theme-toggle')) {
            return;
        }
        
        const button = document.createElement('button');
        button.className = 'theme-toggle';
        button.setAttribute('aria-label', 'Toggle theme');
        button.innerHTML = `
            <i class="fas fa-moon"></i>
            <i class="fas fa-sun"></i>
        `;
        
        button.addEventListener('click', toggleTheme);
        
        document.body.appendChild(button);
    };
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            initTheme();
            createThemeToggle();
        });
    } else {
        initTheme();
        createThemeToggle();
    }
    
    // Expose functions globally if needed
    window.CrowdSparkTheme = {
        toggle: toggleTheme,
        set: setTheme,
        get: getTheme
    };
    
})();