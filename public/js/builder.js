/**
 * AppForge — Builder JavaScript
 * Handles drag-and-drop field reordering, add-field AJAX preview,
 * and module builder interactivity.
 */

const Builder = (() => {

    // ── Config ───────────────────────────────────────────────
    const APP_URL = document.querySelector('meta[name="app-url"]')?.content
        || window.location.origin + '/no-code-app';

    // ── Add Field Form AJAX Submission ───────────────────────
    function initAddFieldForm() {
        const form = document.getElementById('addFieldForm');
        if (!form) return;

        const addBtn = document.getElementById('addFieldBtn');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData(form);

            addBtn.disabled = true;
            addBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Adding...';

            try {
                const res = await fetch(form.action, { method: 'POST', body: fd });
                if (res.ok) {
                    // Reload to show new field in canvas
                    window.location.reload();
                }
            } catch (err) {
                console.error('Field add failed:', err);
                addBtn.disabled = false;
                addBtn.innerHTML = '<i class="bi bi-plus-lg me-1"></i> Add Field';
            }
        });
    }

    // ── Field Type → Choices Toggle ──────────────────────────
    function initTypeToggle() {
        const select = document.getElementById('fieldTypeSelect');
        const choicesGroup = document.getElementById('choicesGroup');
        if (!select || !choicesGroup) return;

        select.addEventListener('change', () => {
            choicesGroup.classList.toggle('d-none', select.value !== 'dropdown');
        });
        // Init on load
        choicesGroup.classList.toggle('d-none', select.value !== 'dropdown');
    }

    // ── Drag & Drop Field Reorder ─────────────────────────────
    function initDragReorder() {
        const canvas = document.getElementById('fieldCanvas');
        if (!canvas) return;

        let dragging = null;

        canvas.addEventListener('dragstart', e => {
            dragging = e.target.closest('.field-card');
            if (!dragging) return;
            setTimeout(() => dragging.classList.add('dragging'), 0);
            e.dataTransfer.effectAllowed = 'move';
        });

        canvas.addEventListener('dragend', () => {
            if (!dragging) return;
            dragging.classList.remove('dragging');

            // Collect ordered IDs and POST to reorder endpoint
            const ids = [...canvas.querySelectorAll('.field-card')]
                .map(c => c.dataset.fieldId)
                .filter(Boolean);

            const token = document.querySelector('[name="_csrf_token"]')?.value || '';
            const appId   = canvas.dataset.appId;
            const moduleId= canvas.dataset.moduleId;

            if (!appId || !moduleId) return;

            const body = ids.map(id => `ids[]=${encodeURIComponent(id)}`).join('&')
                + `&_csrf_token=${encodeURIComponent(token)}`;

            fetch(`${APP_URL}/apps/${appId}/modules/${moduleId}/fields/reorder`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body,
            }).catch(console.error);

            dragging = null;
        });

        canvas.addEventListener('dragover', e => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            if (!dragging) return;

            const afterEl = getDragAfterElement(canvas, e.clientY);
            if (afterEl == null) {
                canvas.appendChild(dragging);
            } else {
                canvas.insertBefore(dragging, afterEl);
            }
        });
    }

    function getDragAfterElement(container, y) {
        const els = [...container.querySelectorAll('.field-card:not(.dragging)')];
        return els.reduce((closest, child) => {
            const box    = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            return (offset < 0 && offset > closest.offset)
                ? { offset, element: child }
                : closest;
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    // ── Icon Picker ───────────────────────────────────────────
    function initIconPicker(containerSel, hiddenId) {
        const container = document.querySelector(containerSel);
        const hidden    = document.getElementById(hiddenId);
        if (!container || !hidden) return;

        container.querySelectorAll('.icon-option, .icon-opt-sm').forEach(btn => {
            btn.addEventListener('click', () => {
                container.querySelectorAll('.icon-option, .icon-opt-sm')
                    .forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                hidden.value = btn.dataset.icon;
            });
        });
    }

    // ── Color Dot Picker ──────────────────────────────────────
    function initColorPicker(containerSel, inputId) {
        const container = document.querySelector(containerSel);
        const input     = document.getElementById(inputId);
        if (!container || !input) return;

        container.querySelectorAll('.color-dot').forEach(dot => {
            dot.addEventListener('click', () => {
                container.querySelectorAll('.color-dot').forEach(d => d.classList.remove('active'));
                dot.classList.add('active');
                input.value = dot.dataset.color;
            });
        });

        input.addEventListener('input', () => {
            container.querySelectorAll('.color-dot').forEach(d => d.classList.remove('active'));
        });
    }

    // ── Init ──────────────────────────────────────────────────
    function init() {
        initAddFieldForm();
        initTypeToggle();
        initDragReorder();
        initIconPicker('#iconPicker', 'selectedIcon');
        initIconPicker('.icon-picker-sm', 'modIcon');
        initColorPicker('#colorPresets', 'color');
        initColorPicker('.color-presets', 'chart_color');
    }

    return { init };
})();

document.addEventListener('DOMContentLoaded', Builder.init);
