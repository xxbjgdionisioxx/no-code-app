/**
 * AppForge — Global JavaScript
 * Bootstrap validation, auto-dismiss alerts, sidebar active state
 */

document.addEventListener('DOMContentLoaded', () => {

    // ── Bootstrap form validation ────────────────────────────
    document.querySelectorAll('form.needs-validation').forEach(form => {
        form.addEventListener('submit', e => {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // ── Auto-dismiss flash alerts after 5s ──────────────────
    document.querySelectorAll('.alert.fade.show').forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert?.close();
        }, 5000);
    });

    // ── Sidebar active link ──────────────────────────────────
    const path = window.location.pathname;
    document.querySelectorAll('.sidebar-link').forEach(link => {
        if (link.getAttribute('href') && path.startsWith(link.getAttribute('href'))) {
            link.classList.add('active');
        }
    });

    // ── Global Deletion Helper (POST-based) ─────────────────
    window.submitDelete = (url, message = 'Are you sure?') => {
        if (!confirm(message)) return;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;

        // Add CSRF token
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        if (token) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = '_csrf_token';
            input.value = token;
            form.appendChild(input);
        }

        document.body.appendChild(form);
        form.submit();
    };

    // ── Confirm buttons ───────────────────────────────
    document.addEventListener('click', e => {
        const btn = e.target.closest('[data-confirm]');
        if (btn) {
            e.preventDefault();
            const url = btn.getAttribute('href');
            submitDelete(url, btn.dataset.confirm);
        }
    });

    // ── Navbar collapse on mobile nav click ─────────────────
    document.querySelectorAll('.navbar-nav .nav-link').forEach(link => {
        link.addEventListener('click', () => {
            const collapse = document.getElementById('mainNav');
            if (collapse?.classList.contains('show')) {
                bootstrap.Collapse.getOrCreateInstance(collapse).hide();
            }
        });
    });

    // ── Tooltip initialization ───────────────────────────────
    try {
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            document.querySelectorAll('[title]:not([data-bs-toggle])').forEach(el => {
                new bootstrap.Tooltip(el, { trigger: 'hover', placement: 'top', boundary: 'viewport' });
            });
        }
    } catch (e) { console.warn('Tooltips failed:', e); }

    // ── Version Indicator (Debug) ─────────────────────────────
    const nav = document.querySelector('.navbar-brand');
    if (nav) {
        const v = document.createElement('span');
        v.className = 'badge bg-dark text-muted ms-2';
        v.style.fontSize = '0.6rem';
        v.innerText = 'v1.0.2-patch';
        nav.appendChild(v);
    }

    // ── Submit button loading state ──────────────────────────
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', () => {
            const btn = form.querySelector('button[type="submit"]');
            if (btn && !form.classList.contains('needs-validation')) {
                btn.disabled = true;
                const icon = btn.querySelector('i');
                if (icon) icon.className = 'spinner-border spinner-border-sm me-1';
            }
        });
    });
});
