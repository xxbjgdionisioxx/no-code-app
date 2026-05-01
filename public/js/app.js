/**
 * Modulyn — Global JavaScript
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
            if (typeof bootstrap !== 'undefined') {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                bsAlert?.close();
            }
        }, 5000);
    });

    // ── Active state: sidebar + navbar ───────────────────────
    const currentPath = window.location.pathname.replace(/\/$/, '');

    function hrefPath(href) {
        if (!href || href === '#') return null;
        try {
            return new URL(href, window.location.origin).pathname.replace(/\/$/, '');
        } catch (e) {
            return href.split('?')[0].replace(/\/$/, '');
        }
    }

    function matchScore(href) {
        const linkPath = hrefPath(href);
        if (!linkPath) return 0;
        if (currentPath === linkPath) return 1000;
        if (currentPath.startsWith(linkPath + '/')) return linkPath.length;
        return 0;
    }

    // Sidebar Active State
    let bestSidebarScore = 0;
    let bestSidebarLink  = null;
    document.querySelectorAll('.sidebar-link').forEach(link => {
        const score = matchScore(link.getAttribute('href'));
        if (score > bestSidebarScore) {
            bestSidebarScore = score;
            bestSidebarLink  = link;
        }
    });
    if (bestSidebarLink) bestSidebarLink.classList.add('active');

    // Navbar Active State
    let bestNavScore = 0;
    let bestNavLink  = null;
    document.querySelectorAll('.app-navbar .navbar-nav .nav-link:not([data-bs-toggle])').forEach(link => {
        const score = matchScore(link.getAttribute('href'));
        if (score > bestNavScore) {
            bestNavScore = score;
            bestNavLink  = link;
        }
    });
    if (bestNavLink) bestNavLink.classList.add('active');

    // ── Progress bar on navigation ───────────────────────────
    const bar = document.getElementById('page-progress');

    function startProgress() {
        if (!bar) return;
        bar.style.transition = 'none';
        bar.style.width = '0%';
        bar.classList.add('active');
        bar.offsetHeight; // force reflow
        bar.style.transition = 'width 10s cubic-bezier(0.1,0.05,0,1)';
        bar.style.width = '85%';
    }

    function finishProgress() {
        if (!bar) return;
        bar.style.transition = 'width 0.2s ease, opacity 0.3s ease 0.1s';
        bar.style.width = '100%';
        setTimeout(() => {
            bar.style.opacity = '0';
            setTimeout(() => { bar.style.width = '0%'; bar.classList.remove('active'); }, 300);
        }, 150);
    }

    document.addEventListener('click', e => {
        const link = e.target.closest('a[href]');
        if (!link) return;
        const href = link.getAttribute('href');
        if (!href || href === '#' || href.startsWith('javascript') || link.dataset.bsToggle || link.dataset.confirm) return;

        try {
            const url = new URL(href, window.location.origin);
            if (url.origin !== window.location.origin || link.target === '_blank') return;
        } catch (_) { return; }

        startProgress();
    });

    window.addEventListener('pageshow', finishProgress);

    // ── Mobile Navbar Auto-collapse ─────────────────────────
    document.querySelectorAll('.navbar-nav .nav-link:not([data-bs-toggle])').forEach(link => {
        link.addEventListener('click', () => {
            const collapse = document.getElementById('mainNav');
            if (collapse?.classList.contains('show') && typeof bootstrap !== 'undefined') {
                bootstrap.Collapse.getOrCreateInstance(collapse).hide();
            }
        });
    });

    // ── Tooltips ─────────────────────────────────────────────
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        document.querySelectorAll('[title]:not([data-bs-toggle])').forEach(el => {
            new bootstrap.Tooltip(el, { trigger: 'hover', placement: 'top', boundary: 'viewport' });
        });
    }

    // ── Submit loading state ────────────────────────────────
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

    // ── Notifications ───────────────────────────────────────
    window.markAllRead = function(e) {
        if (e) e.preventDefault();
        const token  = document.querySelector('meta[name="csrf-token"]')?.content;
        const appUrl = document.querySelector('meta[name="app-url"]')?.content ?? '';
        fetch(appUrl + '/notifications/read-all', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: token ? `_csrf_token=${encodeURIComponent(token)}` : ''
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                document.querySelectorAll('.notif-item.unread').forEach(el => {
                    el.classList.remove('unread');
                    el.querySelector('.flex-shrink-0')?.remove();
                });
                document.querySelector('.notification-badge')?.remove();
                document.getElementById('markAllRead')?.remove();
            }
        }).catch(console.error);
    };

    document.addEventListener('click', e => {
        const item = e.target.closest('.notif-item');
        if (item && item.classList.contains('unread')) {
            item.classList.remove('unread');
            item.querySelector('.flex-shrink-0')?.remove();
            const remaining = document.querySelectorAll('.notif-item.unread').length;
            const badge = document.querySelector('.notification-badge');
            if (badge) {
                if (remaining > 0) badge.textContent = remaining;
                else badge.remove();
            }
        }
    });

    // ── Confirmation Modal ───────────────────────────────────
    (function() {
        const confirmModal = document.getElementById('app-confirm-modal');
        const confirmModalText = document.getElementById('confirm-modal-text');
        const confirmModalCancel = document.getElementById('confirm-modal-cancel');
        const confirmModalProceed = document.getElementById('confirm-modal-proceed');
        let pendingAction = null;

        document.addEventListener('click', e => {
            const confirmBtn = e.target.closest('[data-confirm]');
            if (confirmBtn) {
                e.preventDefault();
                e.stopPropagation();
                
                const message = confirmBtn.getAttribute('data-confirm');
                const href = confirmBtn.getAttribute('href');
                const form = confirmBtn.closest('form');

                if (confirmModalText) confirmModalText.textContent = message || "This action cannot be undone.";
                confirmModal?.classList.add('active');

                pendingAction = () => {
                    if (href && href !== '#') window.location.href = href;
                    else if (form) form.submit();
                };
            }
        });

        confirmModalCancel?.addEventListener('click', () => confirmModal?.classList.remove('active'));
        confirmModalProceed?.addEventListener('click', () => {
            if (pendingAction) {
                confirmModal?.classList.remove('active');
                pendingAction();
            }
        });
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && confirmModal?.classList.contains('active')) confirmModal.classList.remove('active');
        });
        confirmModal?.addEventListener('click', e => {
            if (e.target === confirmModal) confirmModal.classList.remove('active');
        });
    })();

    // ── Table Sorting & Filtering ───────────────────────────
    function sortTable(table, colIndex, asc = true) {
        const tbody = table.querySelector('tbody');
        if (!tbody) return;
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const sortedRows = rows.sort((a, b) => {
            const aCol = (a.children[colIndex]?.textContent || '').trim();
            const bCol = (b.children[colIndex]?.textContent || '').trim();
            const aNum = parseFloat(aCol.replace(/[^0-9.-]+/g, ''));
            const bNum = parseFloat(bCol.replace(/[^0-9.-]+/g, ''));
            if (!isNaN(aNum) && !isNaN(bNum)) return asc ? aNum - bNum : bNum - aNum;
            const aDate = Date.parse(aCol);
            const bDate = Date.parse(bCol);
            if (!isNaN(aDate) && !isNaN(bDate)) return asc ? aDate - bDate : bDate - aDate;
            return asc ? aCol.localeCompare(bCol, undefined, {numeric: true, sensitivity: 'base'}) : bCol.localeCompare(aCol, undefined, {numeric: true, sensitivity: 'base'});
        });
        tbody.append(...sortedRows);
        table.querySelectorAll('th').forEach((h, idx) => {
            h.classList.remove('sort-asc', 'sort-desc');
            if (idx === colIndex) h.classList.add(asc ? 'sort-asc' : 'sort-desc');
        });
    }

    function filterTable(table, query) {
        const tbody = table.querySelector('tbody');
        if (!tbody) return;
        const q = query.toLowerCase();
        let visibleCount = 0;
        tbody.querySelectorAll('tr').forEach(row => {
            const isMatch = row.textContent.toLowerCase().includes(q);
            row.style.display = isMatch ? '' : 'none';
            if (isMatch) visibleCount++;
        });
        let emptyMsg = table.parentElement.querySelector('.table-empty-msg');
        if (visibleCount === 0 && q !== '') {
            if (!emptyMsg) {
                emptyMsg = document.createElement('div');
                emptyMsg.className = 'table-empty-msg text-center py-4 text-muted small';
                emptyMsg.innerHTML = '<i class="bi bi-search me-2"></i>No matching records found.';
                table.after(emptyMsg);
            }
        } else emptyMsg?.remove();
    }

    document.querySelectorAll('table').forEach(table => {
        const headers = table.querySelectorAll('thead th');
        headers.forEach((th, idx) => {
            if (th.textContent.trim().toLowerCase() === 'actions' || th.classList.contains('pe-3')) return;
            th.classList.add('sortable-header');
            th.addEventListener('click', () => sortTable(table, idx, !th.classList.contains('sort-asc')));
        });
        const pageSearch = document.querySelector('input[name="search"]');
        if (pageSearch && table.id === 'recordsTable') {
            pageSearch.addEventListener('input', (e) => filterTable(table, e.target.value));
        } else if (table.rows.length > 5 && !table.dataset.noFilter) {
            const wrapper = document.createElement('div');
            wrapper.className = 'table-filter-wrapper';
            wrapper.innerHTML = `<div class="input-group input-group-sm table-filter-input"><span class="input-group-text bg-transparent border-secondary text-muted"><i class="bi bi-filter"></i></span><input type="text" class="form-control" placeholder="Quick filter..."></div>`;
            table.parentElement.insertBefore(wrapper, table);
            wrapper.querySelector('input').addEventListener('input', (e) => filterTable(table, e.target.value));
        }
    });

    // ── App Search ───────────────────────────────────────────
    const appSearch = document.getElementById('app-search');
    if (appSearch) {
        appSearch.addEventListener('input', (e) => {
            const q = e.target.value.toLowerCase();
            document.querySelectorAll('.app-card').forEach(card => {
                const container = card.closest('.col-xl-3');
                if (container) container.style.display = card.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    }

    // ── Real-time Formulas ──────────────────────────────────
    function evaluateFormula(formula, values) {
        let expression = formula;
        for (const [slug, val] of Object.entries(values)) {
            const regex = new RegExp(`\\{\\{${slug}\\}\\}`, 'g');
            expression = expression.replace(regex, parseFloat(val) || 0);
        }
        expression = expression.replace(/\{\{[^}]+\}\}/g, '0');
        
        try {
            if (!/^[0-9\.\+\-\*\/\(\)\s]+$/.test(expression)) return expression;
            const res = Function(`"use strict"; return (${expression})`)();
            return isNaN(res) ? 0 : res;
        } catch (e) {
            return 0;
        }
    }

    function updateFormulas() {
        const fields = Array.from(document.querySelectorAll('input[id^="field_"], select[id^="field_"], textarea[id^="field_"]'));
        const values = {};
        fields.forEach(f => {
            const slug = f.id.replace('field_', '');
            values[slug] = f.value;
        });

        document.querySelectorAll('[data-formula]').forEach(f => {
            const formula = f.dataset.formula;
            if (!formula) return;
            const result = evaluateFormula(formula, values);
            f.value = typeof result === 'number' ? result.toFixed(2) : result;
        });
    }

    document.addEventListener('input', e => {
        if (e.target.id && e.target.id.startsWith('field_')) {
            updateFormulas();
        }
    });
    updateFormulas();
});
