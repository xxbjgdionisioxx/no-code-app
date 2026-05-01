<?php

namespace Engine;

/**
 * FieldEngine — Field type registry, rendering, validation, and sanitization.
 *
 * Supported built-in types: text, number, date, email, dropdown, file, textarea, checkbox
 * Custom types can be registered via registerCustomType().
 */
class FieldEngine
{
    /** @var array<string, array{render: callable, validate: callable, cast: callable}> */
    private array $registry = [];

    public function __construct(private \PDO $db)
    {
        $this->registerBuiltInTypes();
    }

    // ── Field CRUD ───────────────────────────────────────────

    public function createField(int $moduleId, array $data): int
    {
        $slug = $this->generateFieldSlug($moduleId, $data['name']);
        $stmt = $this->db->prepare(
            'INSERT INTO fields (module_id, name, slug, field_type, is_required, is_unique,
             is_searchable, show_in_list, default_value, placeholder, help_text,
             validation, options, sort_order)
             VALUES (:module_id,:name,:slug,:field_type,:is_required,:is_unique,
             :is_searchable,:show_in_list,:default_value,:placeholder,:help_text,
             :validation,:options,:sort_order)'
        );
        $stmt->execute([
            ':module_id'     => $moduleId,
            ':name'          => $data['name'],
            ':slug'          => $slug,
            ':field_type'    => $data['field_type'],
            ':is_required'   => (int)($data['is_required']   ?? 0),
            ':is_unique'     => (int)($data['is_unique']      ?? 0),
            ':is_searchable' => (int)($data['is_searchable']  ?? 1),
            ':show_in_list'  => (int)($data['show_in_list']   ?? 1),
            ':default_value' => $data['default_value']        ?? null,
            ':placeholder'   => $data['placeholder']          ?? null,
            ':help_text'     => $data['help_text']            ?? null,
            ':validation'    => isset($data['validation']) ? json_encode($data['validation']) : null,
            ':options'       => isset($data['options'])    ? json_encode($data['options'])    : null,
            ':sort_order'    => (int)($data['sort_order']     ?? 0),
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function updateField(int $fieldId, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE fields SET name=:name, field_type=:field_type, is_required=:is_required,
             is_unique=:is_unique, is_searchable=:is_searchable, show_in_list=:show_in_list,
             default_value=:default_value, placeholder=:placeholder, help_text=:help_text,
             validation=:validation, options=:options WHERE id=:id'
        );
        return $stmt->execute([
            ':name'          => $data['name'],
            ':field_type'    => $data['field_type'],
            ':is_required'   => (int)($data['is_required']   ?? 0),
            ':is_unique'     => (int)($data['is_unique']      ?? 0),
            ':is_searchable' => (int)($data['is_searchable']  ?? 1),
            ':show_in_list'  => (int)($data['show_in_list']   ?? 1),
            ':default_value' => $data['default_value']        ?? null,
            ':placeholder'   => $data['placeholder']          ?? null,
            ':help_text'     => $data['help_text']            ?? null,
            ':validation'    => isset($data['validation']) ? json_encode($data['validation']) : null,
            ':options'       => isset($data['options'])    ? json_encode($data['options'])    : null,
            ':id'            => $fieldId,
        ]);
    }

    public function deleteField(int $fieldId): bool
    {
        return $this->db->prepare('DELETE FROM fields WHERE id=?')->execute([$fieldId]);
    }

    public function getField(int $fieldId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM fields WHERE id=?');
        $stmt->execute([$fieldId]);
        $f = $stmt->fetch();
        if (!$f) return null;
        $f['validation'] = $f['validation'] ? json_decode($f['validation'], true) : [];
        $f['options']    = $f['options']    ? json_decode($f['options'], true)    : [];
        return $f;
    }

    public function reorderFields(array $orderedIds): void
    {
        $stmt = $this->db->prepare('UPDATE fields SET sort_order=? WHERE id=?');
        foreach ($orderedIds as $pos => $id) {
            $stmt->execute([$pos, (int)$id]);
        }
    }

    // ── Rendering ────────────────────────────────────────────

    /**
     * Render a form field group (label + input + help text).
     */
    public function renderFormField(array $field, mixed $value = null): string
    {
        $type    = $field['field_type'];
        $handler = $this->registry[$type] ?? $this->registry['text'];
        $value ??= $field['default_value'];
        $input   = ($handler['render'])($field, $value);
        $safeId  = htmlspecialchars($field['slug'], ENT_QUOTES);
        $safeName= htmlspecialchars($field['name'], ENT_QUOTES);
        $badge   = $field['is_required'] ? '<span class="text-danger ms-1">*</span>' : '';
        $help    = $field['help_text'] ? '<div class="form-text">' . htmlspecialchars($field['help_text'], ENT_QUOTES) . '</div>' : '';

        return "<div class=\"mb-3 field-group\" data-field-id=\"{$field['id']}\" data-field-type=\"{$type}\">
            <label for=\"field_{$safeId}\" class=\"form-label fw-semibold\">{$safeName}{$badge}</label>
            {$input}{$help}
        </div>";
    }

    /**
     * Render a display-safe read-only value for record show pages.
     */
    public function renderDisplayValue(array $field, mixed $value): string
    {
        if ($value === null || $value === '') {
            return '<span class="text-muted fst-italic">—</span>';
        }
        return match ($field['field_type']) {
            'checkbox' => $value ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>',
            'file'     => '<a href="' . APP_URL . '/uploads/' . htmlspecialchars($value) . '" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-file-earmark"></i> View File</a>',
            'email'    => '<a href="mailto:' . htmlspecialchars($value) . '">' . htmlspecialchars($value) . '</a>',
            default    => nl2br(htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8')),
        };
    }

    // ── Validation ───────────────────────────────────────────

    /**
     * Validate a single field value. Returns array of error strings.
     */
    public function validate(array $field, mixed $value): array
    {
        $errors = [];
        $rules  = $field['validation'] ?? [];

        if ($field['is_required'] && ($value === null || $value === '')) {
            return ["{$field['name']} is required."];
        }
        if ($value === null || $value === '') return [];

        $handler = $this->registry[$field['field_type']] ?? null;
        if ($handler) {
            $errors = array_merge($errors, ($handler['validate'])($field, $value));
        }

        if (isset($rules['min']) && is_numeric($value) && $value < $rules['min'])
            $errors[] = "{$field['name']} must be at least {$rules['min']}.";
        if (isset($rules['max']) && is_numeric($value) && $value > $rules['max'])
            $errors[] = "{$field['name']} must not exceed {$rules['max']}.";
        if (isset($rules['min_length']) && mb_strlen((string)$value) < $rules['min_length'])
            $errors[] = "{$field['name']} must be at least {$rules['min_length']} characters.";
        if (isset($rules['max_length']) && mb_strlen((string)$value) > $rules['max_length'])
            $errors[] = "{$field['name']} must not exceed {$rules['max_length']} characters.";

        return $errors;
    }

    /**
     * Validate all fields in a module schema and return errors + sanitized values.
     */
    public function validateAll(array $schema, array $postData): array
    {
        $errors = [];
        $values = [];
        foreach ($schema['fields'] as $field) {
            $raw       = $postData["field_{$field['slug']}"] ?? null;
            $sanitized = $this->sanitize($field, $raw);
            $errs      = $this->validate($field, $sanitized);
            if (!empty($errs)) {
                $errors[$field['slug']] = $errs;
            } else {
                $values[$field['slug']] = $sanitized;
            }
        }
        return ['errors' => $errors, 'values' => $values];
    }

    // ── Sanitization ─────────────────────────────────────────

    public function sanitize(array $field, mixed $value): mixed
    {
        if ($value === null) return null;
        return match ($field['field_type']) {
            'number'   => is_numeric($value) ? (float)$value : null,
            'checkbox' => ($value === 'on' || $value === '1' || $value === true) ? '1' : '0',
            'email'    => filter_var(trim((string)$value), FILTER_SANITIZE_EMAIL),
            'file'     => $value,
            default    => strip_tags(trim((string)$value)),
        };
    }

    // ── Custom Type Registration ──────────────────────────────

    /**
     * Register a custom field type. Extend FieldEngine with new input types.
     *
     * Example — Color Picker:
     *   $fieldEngine->registerCustomType('color_picker', [
     *       'render'   => fn($field, $val) => "<input type='color' name='field_{$field['slug']}' value='{$val}'>",
     *       'validate' => fn($field, $val) => preg_match('/^#[0-9A-Fa-f]{6}$/', $val) ? [] : ['Invalid color'],
     *       'cast'     => fn($val) => (string)$val,
     *   ]);
     */
    public function registerCustomType(string $type, array $handler): void
    {
        if (!isset($handler['render'], $handler['validate'], $handler['cast'])) {
            throw new \InvalidArgumentException("Handler must define 'render', 'validate', and 'cast'.");
        }
        $this->registry[$type] = $handler;
    }

    public function getRegisteredTypes(): array
    {
        return array_keys($this->registry);
    }

    // ── Built-in Type Handlers ───────────────────────────────

    private function registerBuiltInTypes(): void
    {
        // Text
        $this->registry['text'] = [
            'render' => function ($f, $v) {
                $v   = htmlspecialchars((string)($v ?? ''), ENT_QUOTES);
                $ph  = htmlspecialchars($f['placeholder'] ?? '', ENT_QUOTES);
                $req = $f['is_required'] ? 'required' : '';
                $rules = $f['validation'] ?? [];
                $min = isset($rules['min_length']) ? "minlength=\"{$rules['min_length']}\"" : '';
                $max = isset($rules['max_length']) ? "maxlength=\"{$rules['max_length']}\"" : '';
                return "<input type=\"text\" class=\"form-control\" id=\"field_{$f['slug']}\" name=\"field_{$f['slug']}\" value=\"{$v}\" placeholder=\"{$ph}\" {$req} {$min} {$max}>";
            },
            'validate' => fn($f, $v) => [],
            'cast'     => fn($v) => (string)$v,
        ];

        // Textarea
        $this->registry['textarea'] = [
            'render' => function ($f, $v) {
                $v   = htmlspecialchars((string)($v ?? ''), ENT_QUOTES);
                $ph  = htmlspecialchars($f['placeholder'] ?? '', ENT_QUOTES);
                $req = $f['is_required'] ? 'required' : '';
                return "<textarea class=\"form-control\" id=\"field_{$f['slug']}\" name=\"field_{$f['slug']}\" rows=\"4\" placeholder=\"{$ph}\" {$req}>{$v}</textarea>";
            },
            'validate' => fn($f, $v) => [],
            'cast'     => fn($v) => (string)$v,
        ];

        // Number
        $this->registry['number'] = [
            'render' => function ($f, $v) {
                $v   = htmlspecialchars((string)($v ?? ''), ENT_QUOTES);
                $req = $f['is_required'] ? 'required' : '';
                $r   = $f['validation'] ?? [];
                $min = isset($r['min']) ? "min=\"{$r['min']}\"" : '';
                $max = isset($r['max']) ? "max=\"{$r['max']}\"" : '';
                return "<input type=\"number\" class=\"form-control\" id=\"field_{$f['slug']}\" name=\"field_{$f['slug']}\" value=\"{$v}\" {$req} {$min} {$max} step=\"any\">";
            },
            'validate' => fn($f, $v) => is_numeric($v) ? [] : ["{$f['name']} must be a number."],
            'cast'     => fn($v) => (float)$v,
        ];

        // Date
        $this->registry['date'] = [
            'render' => function ($f, $v) {
                $v   = htmlspecialchars((string)($v ?? ''), ENT_QUOTES);
                $req = $f['is_required'] ? 'required' : '';
                return "<input type=\"date\" class=\"form-control\" id=\"field_{$f['slug']}\" name=\"field_{$f['slug']}\" value=\"{$v}\" {$req}>";
            },
            'validate' => function ($f, $v) {
                $d = \DateTime::createFromFormat('Y-m-d', $v);
                return ($d && $d->format('Y-m-d') === $v) ? [] : ["{$f['name']} must be a valid date."];
            },
            'cast' => fn($v) => (string)$v,
        ];

        // Email
        $this->registry['email'] = [
            'render' => function ($f, $v) {
                $v   = htmlspecialchars((string)($v ?? ''), ENT_QUOTES);
                $ph  = htmlspecialchars($f['placeholder'] ?? '', ENT_QUOTES);
                $req = $f['is_required'] ? 'required' : '';
                return "<input type=\"email\" class=\"form-control\" id=\"field_{$f['slug']}\" name=\"field_{$f['slug']}\" value=\"{$v}\" placeholder=\"{$ph}\" {$req}>";
            },
            'validate' => fn($f, $v) => filter_var($v, FILTER_VALIDATE_EMAIL) ? [] : ["{$f['name']} must be a valid email."],
            'cast'     => fn($v) => (string)$v,
        ];

        // Dropdown
        $this->registry['dropdown'] = [
            'render' => function ($f, $v) {
                $req     = $f['is_required'] ? 'required' : '';
                $choices = $f['options']['choices'] ?? [];
                $html    = "<select class=\"form-select\" id=\"field_{$f['slug']}\" name=\"field_{$f['slug']}\" {$req}>";
                $html   .= '<option value="">— Select —</option>';
                foreach ($choices as $opt) {
                    $sel   = ($v == $opt) ? 'selected' : '';
                    $o     = htmlspecialchars($opt, ENT_QUOTES);
                    $html .= "<option value=\"{$o}\" {$sel}>{$o}</option>";
                }
                return $html . '</select>';
            },
            'validate' => function ($f, $v) {
                $choices = $f['options']['choices'] ?? [];
                return (!empty($choices) && !in_array($v, $choices, true))
                    ? ["{$f['name']} must be one of: " . implode(', ', $choices) . "."]
                    : [];
            },
            'cast' => fn($v) => (string)$v,
        ];

        // Checkbox
        $this->registry['checkbox'] = [
            'render' => function ($f, $v) {
                $checked = ($v === '1' || $v === 'on' || $v === true) ? 'checked' : '';
                return "<div class=\"form-check\">
                    <input class=\"form-check-input\" type=\"checkbox\" id=\"field_{$f['slug']}\"
                           name=\"field_{$f['slug']}\" value=\"on\" {$checked}>
                    <label class=\"form-check-label\" for=\"field_{$f['slug']}\">Yes</label>
                </div>";
            },
            'validate' => fn($f, $v) => [],
            'cast'     => fn($v) => ($v === 'on' || $v === '1') ? '1' : '0',
        ];

        // File
        $this->registry['file'] = [
            'render' => function ($f, $v) {
                $req  = ($f['is_required'] && !$v) ? 'required' : '';
                $curr = $v ? "<div class=\"mt-1 small\"><i class=\"bi bi-paperclip\"></i> Current: <a href=\"" . APP_URL . "/uploads/" . htmlspecialchars($v) . "\" target=\"_blank\">" . htmlspecialchars($v) . "</a></div>" : '';
                return "<input type=\"file\" class=\"form-control\" id=\"field_{$f['slug']}\" name=\"field_{$f['slug']}\" {$req}>{$curr}";
            },
            'validate' => fn($f, $v) => [],
            'cast'     => fn($v) => (string)$v,
        ];

        // Lookup (Relationship)
        $this->registry['lookup'] = [
            'render' => function ($f, $v) {
                $targetModId = (int)($f['options']['target_module_id'] ?? 0);
                if (!$targetModId) return '<div class="text-danger small">Error: No target module selected.</div>';

                // Fetch all records for the target module
                $stmt = $this->db->prepare('SELECT id, data FROM records WHERE module_id = ? ORDER BY id DESC');
                $stmt->execute([$targetModId]);
                $records = $stmt->fetchAll();

                $req = $f['is_required'] ? 'required' : '';
                $html = "<select class=\"form-select\" id=\"field_{$f['slug']}\" name=\"field_{$f['slug']}\" {$req}>";
                $html .= '<option value="">— Select Linked Record —</option>';

                foreach ($records as $r) {
                    $data = json_decode($r['data'] ?? '{}', true) ?? [];
                    // Use first key as label, or ID if empty
                    $label = !empty($data) ? reset($data) : "Record #{$r['id']}";
                    $sel = ($v == $r['id']) ? 'selected' : '';
                    $html .= "<option value=\"{$r['id']}\" {$sel}>" . htmlspecialchars((string)$label) . "</option>";
                }
                return $html . '</select>';
            },
            'validate' => function ($f, $v) {
                if (!$v) return [];
                $targetModId = (int)($f['options']['target_module_id'] ?? 0);
                $stmt = $this->db->prepare('SELECT id FROM records WHERE id = ? AND module_id = ?');
                $stmt->execute([$v, $targetModId]);
                return $stmt->fetch() ? [] : ["Selected record for {$f['name']} does not exist in target module."];
            },
            'cast' => fn($v) => (int)$v,
        ];
    }

    // ── Helpers ──────────────────────────────────────────────

    private function generateFieldSlug(int $moduleId, string $name): string
    {
        $base  = strtolower(preg_replace('/[^a-z0-9]+/i', '_', trim($name)));
        $base  = trim($base, '_') ?: 'field';
        $slug  = $base;
        $i     = 1;
        $check = $this->db->prepare('SELECT id FROM fields WHERE module_id=? AND slug=?');
        while (true) {
            $check->execute([$moduleId, $slug]);
            if (!$check->fetch()) break;
            $slug = $base . '_' . $i++;
        }
        return $slug;
    }
}
