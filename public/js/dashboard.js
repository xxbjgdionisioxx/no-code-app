/**
 * AppForge — Dashboard Chart.js helper
 * Additional chart utilities and shared configuration.
 */

const DashboardCharts = (() => {

    // Shared chart defaults for dark theme
    const defaults = {
        color: '#94a3b8',
        borderColor: '#1e2d47',
        font: { family: "'Inter', system-ui, sans-serif" },
    };

    // Apply global Chart.js defaults
    function applyDefaults() {
        if (typeof Chart === 'undefined') return;

        Chart.defaults.color = defaults.color;
        Chart.defaults.borderColor = defaults.borderColor;
        Chart.defaults.font.family = defaults.font.family;
        Chart.defaults.plugins.legend.labels.padding = 16;
        Chart.defaults.plugins.legend.labels.usePointStyle = true;
        Chart.defaults.plugins.tooltip.backgroundColor = '#1a2035';
        Chart.defaults.plugins.tooltip.borderColor = '#1e2d47';
        Chart.defaults.plugins.tooltip.borderWidth = 1;
        Chart.defaults.plugins.tooltip.titleColor = '#e2e8f0';
        Chart.defaults.plugins.tooltip.bodyColor = '#94a3b8';
        Chart.defaults.plugins.tooltip.padding = 12;
        Chart.defaults.plugins.tooltip.cornerRadius = 8;
    }

    // Generate a palette of visually distinct HSL colors
    function generatePalette(count) {
        const colors = [];
        for (let i = 0; i < count; i++) {
            const hue = (i * 47 + 220) % 360;
            colors.push(`hsl(${hue}, 65%, 55%)`);
        }
        return colors;
    }

    // Create a metric card animation (count up effect)
    function animateMetric(element, targetValue, duration = 800) {
        const start = performance.now();
        const startVal = 0;

        function tick(now) {
            const elapsed = now - start;
            const progress = Math.min(elapsed / duration, 1);
            // Ease-out cubic
            const ease = 1 - Math.pow(1 - progress, 3);
            const current = Math.round(startVal + (targetValue - startVal) * ease);
            element.textContent = current.toLocaleString();

            if (progress < 1) {
                requestAnimationFrame(tick);
            }
        }

        requestAnimationFrame(tick);
    }

    // Init
    function init() {
        applyDefaults();
    }

    return { init, applyDefaults, generatePalette, animateMetric };
})();

document.addEventListener('DOMContentLoaded', DashboardCharts.init);
