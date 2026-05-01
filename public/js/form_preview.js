/**
 * AppForge — Live Form Preview
 * Updates the right-panel preview in real-time as the user
 * configures a field in the field editor view.
 */

document.addEventListener('DOMContentLoaded', () => {

    const preview     = document.getElementById('liveFieldPreview');
    const labelInput  = document.getElementById('fieldLabel');
    const typeSelect  = document.getElementById('fieldTypeSelect');
    const isRequired  = document.getElementById('isRequired');
    const choicesTxt  = document.querySelector('[name="choices"]');
    const placeholder = document.querySelector('[name="placeholder"]');
    const helpText    = document.querySelector('[name="help_text"]');

    if (!preview || !labelInput || !typeSelect) return;

    function renderPreview() {
        const label = labelInput.value.trim() || 'Untitled Field';
        const type  = typeSelect.value;
        const req   = isRequired?.checked;
        const ph    = placeholder?.value || `Enter ${label.toLowerCase()}...`;
        const help  = helpText?.value || '';

        let input = '';

        switch (type) {
            case 'textarea':
                input = `<textarea class="form-control" placeholder="${esc(ph)}" rows="3" disabled></textarea>`;
                break;

            case 'dropdown':
                const choices = choicesTxt?.value.split('\n').map(s => s.trim()).filter(Boolean) || [];
                input = `<select class="form-select" disabled>
                    <option>— Select —</option>
                    ${choices.map(c => `<option>${esc(c)}</option>`).join('')}
                </select>`;
                break;

            case 'checkbox':
                input = `<div class="form-check">
                    <input type="checkbox" class="form-check-input" disabled>
                    <label class="form-check-label">Yes</label>
                </div>`;
                break;

            case 'file':
                input = `<input type="file" class="form-control" disabled>
                    <div class="form-text">Max size: 10MB. Images, PDFs, Excel accepted.</div>`;
                break;

            case 'number':
                input = `<input type="number" class="form-control" placeholder="${esc(ph)}" disabled>`;
                break;

            case 'date':
                input = `<input type="date" class="form-control" disabled>`;
                break;

            case 'email':
                input = `<input type="email" class="form-control" placeholder="${esc(ph)}" disabled>`;
                break;

            default: // text
                input = `<input type="text" class="form-control" placeholder="${esc(ph)}" disabled>`;
        }

        const badge    = req ? '<span class="text-danger ms-1">*</span>' : '';
        const helpHtml = help ? `<div class="form-text text-muted">${esc(help)}</div>` : '';

        preview.innerHTML = `
            <div class="mb-4 field-group" style="animation:fadeIn .2s ease both">
                <label class="form-label fw-semibold">${esc(label)}${badge}</label>
                ${input}
                ${helpHtml}
            </div>
            <div class="text-center">
                <button class="btn btn-primary btn-sm px-4 mt-2" disabled>
                    <i class="bi bi-save me-1"></i> Submit (Preview)
                </button>
            </div>
        `;
    }

    function esc(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // ── Event listeners ──────────────────────────────────────
    [labelInput, typeSelect, isRequired, choicesTxt, placeholder, helpText]
        .filter(Boolean)
        .forEach(el => el.addEventListener('input', renderPreview));

    typeSelect.addEventListener('change', () => {
        // Show/hide choices textarea
        const cg = document.getElementById('choicesGroup');
        const nv = document.getElementById('numValidation');
        const tv = document.getElementById('textValidation');
        if (cg) cg.classList.toggle('d-none', typeSelect.value !== 'dropdown');
        if (nv) nv.classList.toggle('d-none', typeSelect.value !== 'number');
        if (tv) tv.classList.toggle('d-none', !['text', 'textarea'].includes(typeSelect.value));
        renderPreview();
    });

    // Initial render
    renderPreview();
});
