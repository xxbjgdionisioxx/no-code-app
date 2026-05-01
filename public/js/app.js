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

    // ── Active state: sidebar + navbar ───────────────────────
    const currentPath = window.location.pathname.replace(/\/$/, '');

    /**
     * Extract the pathname portion from any href (handles both absolute
     * URLs like "http://localhost/no-code-app/apps/1/dashboard" and
     * relative paths like "/apps/1/dashboard").
     */
    function hrefPath(href) {
        if (!href || href === '#') return null;
        try {
            // new URL() resolves relative URLs too when given a base
            return new URL(href, window.location.origin).pathname.replace(/\/$/, '');
        } catch (e) {
            return href.split('?')[0].replace(/\/$/, '');
        }
    }

    /**
     * Score a link href against the current pathname.
     * Returns 0 (no match), or a positive integer (higher = better match).
     */
    function matchScore(href) {
        const linkPath = hrefPath(href);
        if (!linkPath) return 0;
        if (currentPath === linkPath) return 1000;                          // exact
        if (currentPath.startsWith(linkPath + '/')) return linkPath.length; // prefix
        return 0;
    }

    // Sidebar: mark the BEST matching link only
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

    // Navbar top links: mark BEST matching
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
        // Force reflow
        bar.offsetHeight; // eslint-disable-line
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

    // Trigger progress + instant active highlight on any real navigation link click
    document.addEventListener('click', e => {
        const link = e.target.closest('a[href]');
        if (!link) return;
        const href = link.getAttribute('href');

        // Skip: empty, hash-only, JS links, dropdown toggles, confirm-delete links
        if (!href || href === '#' || href.startsWith('javascript') ||
            link.dataset.bsToggle || link.dataset.confirm) return;

        // Skip external links (different origin)
        try {
            const url = new URL(href, window.location.origin);
            if (url.origin !== window.location.origin) return;
            if (link.target === '_blank') return;
        } catch (_) { return; }

        // Instantly mark as active for immediate click feedback
        if (link.classList.contains('sidebar-link')) {
            document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
            link.classList.add('active');
        }
        if (link.classList.contains('nav-link') && !link.dataset.bsToggle) {
            document.querySelectorAll('.app-navbar .nav-link').forEach(l => l.classList.remove('active'));
            link.classList.add('active');
        }

        startProgress();
    });

    // Finish bar once page is fully loaded (handles fast navigations)
    window.addEventListener('pageshow', finishProgress);

    // ── Navbar collapse on mobile: only close for real nav clicks (not dropdowns) ──
    document.querySelectorAll('.navbar-nav .nav-link:not([data-bs-toggle])').forEach(link => {
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

// ── Notification: Mark all read ─────────────────────────────────
function markAllRead(e) {
    e.preventDefault();
    const token  = document.querySelector('meta[name="csrf-token"]')?.content;
    const appUrl = document.querySelector('meta[name="app-url"]')?.content ?? '';
    fetch(appUrl + '/notifications/read-all', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: token ? `_csrf_token=${encodeURIComponent(token)}` : ''
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            // Remove unread dot indicators and badge
            document.querySelectorAll('.notif-item.unread').forEach(el => {
                el.classList.remove('unread');
                el.querySelector('.flex-shrink-0')?.remove();
            });
            document.querySelector('.notification-badge')?.remove();
            document.getElementById('markAllRead')?.remove();
        }
    })
    .catch(console.error);
}

// ── Notification item: mark as read on click ─────────────────────
document.addEventListener('click', e => {
    const item = e.target.closest('.notif-item');
    if (item && item.classList.contains('unread')) {
        item.classList.remove('unread');
        item.querySelector('.flex-shrink-0')?.remove();
        // Recompute badge count
        const remaining = document.querySelectorAll('.notif-item.unread').length;
        const badge = document.querySelector('.notification-badge');
        if (badge) {
            if (remaining > 0) badge.textContent = remaining;
            else badge.remove();
        }
    }

// ── Custom Confirmation Modal Logic ─────────────────────
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
                if (href && href !== '#') {
                    window.location.href = href;
                } else if (form) {
                    form.submit();
                }
            };
        }
    });

    confirmModalCancel?.addEventListener('click', () => {
        confirmModal?.classList.remove('active');
        pendingAction = null;
    });

    confirmModalProceed?.addEventListener('click', (e) => {
        if (pendingAction) {
            confirmModal?.classList.remove('active');
            pendingAction();
        }
    });

    // Close modal on escape key
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && confirmModal?.classList.contains('active')) {
            confirmModal?.classList.remove('active');
            pendingAction = null;
        }
    });

    // Close modal on overlay click
    confirmModal?.addEventListener('click', e => {
        if (e.target === confirmModal) {
            confirmModal?.classList.remove('active');
            pendingAction = null;
        }
    });
})();

// ── Table Sorting & Filtering ───────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    /**
     * Sorts an HTML table by a specific column index.
     */
    function sortTable(table, colIndex, asc = true) {
        const tbody = table.querySelector('tbody');
        if (!tbody) return;

        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        // Sort rows
        const sortedRows = rows.sort((a, b) => {
            const aCol = a.children[colIndex]?.textContent.trim() || '';
            const bCol = b.children[colIndex]?.textContent.trim() || '';

            // Handle numbers
            const aNum = parseFloat(aCol.replace(/[^0-9.-]+/g, ''));
            const bNum = parseFloat(bCol.replace(/[^0-9.-]+/g, ''));
            
            if (!isNaN(aNum) && !isNaN(bNum)) {
                return asc ? aNum - bNum : bNum - aNum;
            }

            // Handle dates
            const aDate = Date.parse(aCol);
            const bDate = Date.parse(bCol);
            if (!isNaN(aDate) && !isNaN(bDate)) {
                return asc ? aDate - bDate : bDate - aDate;
            }

            // Fallback to string compare
            return asc 
                ? aCol.localeCompare(bCol, undefined, {numeric: true, sensitivity: 'base'})
                : bCol.localeCompare(aCol, undefined, {numeric: true, sensitivity: 'base'});
        });

        // Re-append sorted rows
        while (tbody.firstChild) tbody.removeChild(tbody.firstChild);
        tbody.append(...sortedRows);

        // Update headers UI
        const headers = table.querySelectorAll('th');
        headers.forEach((h, idx) => {
            h.classList.remove('sort-asc', 'sort-desc');
            if (idx === colIndex) h.classList.add(asc ? 'sort-asc' : 'sort-desc');
        });
    }

    /**
     * Filters table rows based on a search string.
     */
    function filterTable(table, query) {
        const tbody = table.querySelector('tbody');
        if (!tbody) return;
        
        const q = query.toLowerCase();
        const rows = tbody.querySelectorAll('tr');
        let visibleCount = 0;

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const isMatch = text.includes(q);
            row.style.display = isMatch ? '' : 'none';
            if (isMatch) visibleCount++;
        });

        // Show empty state if no matches
        let emptyMsg = table.parentElement.querySelector('.table-empty-msg');
        if (visibleCount === 0 && q !== '') {
            if (!emptyMsg) {
                emptyMsg = document.createElement('div');
                emptyMsg.className = 'table-empty-msg text-center py-4 text-muted small';
                emptyMsg.innerHTML = '<i class="bi bi-search me-2"></i>No matching records found.';
                table.after(emptyMsg);
            }
        } else {
            emptyMsg?.remove();
        }
    }

    // Initialize all tables
    document.querySelectorAll('table').forEach(table => {
        // 1. Setup Sorting
        const headers = table.querySelectorAll('thead th');
        headers.forEach((th, idx) => {
            // Skip action columns
            if (th.textContent.trim().toLowerCase() === 'actions' || th.classList.contains('pe-3')) return;
            
            th.classList.add('sortable-header');
            let asc = true;
            th.addEventListener('click', () => {
                asc = th.classList.contains('sort-asc') ? false : true;
                sortTable(table, idx, asc);
            });
        });

        // 2. Setup Filtering (only if table is reasonably large or explicitly requested)
        // We look for an existing search input first
        const pageSearch = document.querySelector('input[name="search"]');
        if (pageSearch && table.id === 'recordsTable') {
            // Link existing server-side search with client-side live filtering
            pageSearch.addEventListener('input', (e) => filterTable(table, e.target.value));
        } else if (table.rows.length > 5 && !table.dataset.noFilter) {
            // Auto-inject a small search box for other tables
            const wrapper = document.createElement('div');
            wrapper.className = 'table-filter-wrapper';
            wrapper.innerHTML = `
                <div class="input-group input-group-sm table-filter-input">
                    <span class="input-group-text bg-transparent border-secondary text-muted">
                        <i class="bi bi-filter"></i>
                    </span>
                    <input type="text" class="form-control" placeholder="Quick filter...">
                </div>
            `;
            table.parentElement.insertBefore(wrapper, table);
            wrapper.querySelector('input').addEventListener('input', (e) => filterTable(table, e.target.value));
        }
    });

    // 3. Setup App Card Filtering
    const appSearch = document.getElementById('app-search');
    if (appSearch) {
        appSearch.addEventListener('input', (e) => {
            const q = e.target.value.toLowerCase();
            const cards = document.querySelectorAll('.app-card');
            cards.forEach(card => {
                const container = card.closest('.col-xl-3');
                const text = card.textContent.toLowerCase();
                if (container) container.style.display = text.includes(q) ? '' : 'none';
            });
        });
    }
});
