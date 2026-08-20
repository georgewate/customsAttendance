

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import './qr-scanner';

window.Alpine = Alpine;
window.Chart = Chart;

window.setTheme = function (theme) {
    localStorage.setItem('theme', theme);
    document.documentElement.classList.toggle('dark', theme === 'dark');
    document.dispatchEvent(new CustomEvent('themechange'));
};

window.toggleTheme = function () {
    const isDark = document.documentElement.classList.contains('dark');
    window.setTheme(isDark ? 'light' : 'dark');
};

Alpine.start();