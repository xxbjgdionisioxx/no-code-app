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
             is_searchable, show_in_list, show_in_form, default_value, placeholder, help_text,
             validation, options, sort_order)
             VALUES (:module_id,:name,:slug,:field_type,:is_required,:is_unique,
             :is_searchable,:show_in_list,:show_in_form,:default_value,:placeholder,:help_text,
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
            ':show_in_form'  => (int)($data['show_in_form']   ?? 1),
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
             show_in_form=:show_in_form, default_value=:default_value, placeholder=:placeholder, 
             help_text=:help_text, validation=:validation, options=:options WHERE id=:id'
        );
        return $stmt->execute([
            ':name'          => $data['name'],
            ':field_type'    => $data['field_type'],
            ':is_required'   => (int)($data['is_required']   ?? 0),
            ':is_unique'     => (int)($data['is_unique']      ?? 0),
            ':is_searchable' => (int)($data['is_searchable']  ?? 1),
            ':show_in_list'  => (int)($data['show_in_list']   ?? 1),
            ':show_in_form'  => (int)($data['show_in_form']   ?? 1),
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

    // ── Field Rendering ──────────────────────────────────────

    /**
     * Render the form input for a field.
     */
    public function renderFormField(array $field, mixed $value = null): string
    {
        $type = $field['field_type'];
        $handler = $this->registry[$type] ?? $this->registry['text'];

        $required = $field['is_required'] ? '<span class="text-danger">*</span>' : '';
        $help = $field['help_text'] ? "<div class=\"form-text\">" . htmlspecialchars($field['help_text']) . "</div>" : '';

        $html = "<div class=\"field-container\" data-field-slug=\"{$field['slug']}\">";
        $html .= "<label class=\"form-label fw-bold small mb-1\">" . htmlspecialchars($field['name']) . " {$required}</label>";
        $html .= $handler['render']($field, $value);
        $html .= $help;
        $html .= "</div>";

        return $html;
    }

    /**
     * Validate and cast all form inputs for a module.
     * Returns ['errors' => [...], 'values' => [...]]
     */
    public function validateAll(array $schema, array $postData): array
    {
        $errors = [];
        $values = [];

        foreach ($schema['fields'] as $field) {
            $slug = $field['slug'];
            $val  = $postData["field_{$slug}"] ?? null;
            
            $type = $field['field_type'];
            $handler = $this->registry[$type] ?? $this->registry['text'];

            // Run validation
            $fieldErrors = $handler['validate']($field, $val);
            if (!empty($fieldErrors)) {
                $errors[$slug] = $fieldErrors;
            }

            // Cast value
            $values[$slug] = $handler['cast']($val);
        }

        return ['errors' => $errors, 'values' => $values];
    }

    /**
     * Render the human-readable display value for a field (e.g., in a table or detail view).
     */
    public function renderDisplayValue(array $field, mixed $value): string
    {
        if ($value === null || $value === '') return '<span class="text-muted">N/A</span>';

        if ($field['field_type'] === 'lookup') {
            return $this->resolveLookupLabel($field, $value);
        }

        if ($field['field_type'] === 'file') {
            $url = APP_URL . "/uploads/" . htmlspecialchars($value);
            return "<a href=\"{$url}\" target=\"_blank\" class=\"btn btn-xs btn-outline-primary\">View File</a>";
        }

        if ($field['field_type'] === 'checkbox') {
            return $value ? '✅ Yes' : '❌ No';
        }

        return htmlspecialchars((string)$value);
    }

    /**
     * Perform on-the-fly SQL lookup to fetch the chosen display label for a related record.
     */
    private function resolveLookupLabel(array $field, mixed $value): string
    {
        $targetModId = (int)($field['options']['target_module_id'] ?? 0);
        $displaySlug = $field['options']['display_field_slug'] ?? null;

        if (!$targetModId) return htmlspecialchars((string)$value);

        $stmt = $this->db->prepare('SELECT data FROM records WHERE id = ? AND module_id = ?');
        $stmt->execute([$value, $targetModId]);
        $row = $stmt->fetch();

        if (!$row) return "<span class='text-muted small'>ID: {$value} (Deleted)</span>";

        $data = json_decode($row['data'] ?? '{}', true);
        if ($displaySlug && isset($data[$displaySlug])) {
            return htmlspecialchars((string)$data[$displaySlug]);
        }

        // Fallback: first field in data
        return htmlspecialchars((string)(!empty($data) ? reset($data) : "ID: {$value}"));
    }

    // ── Internal ─────────────────────────────────────────────

    public function getRegisteredTypes(): array
    {
        return array_keys($this->registry);
    }

    private function registerBuiltInTypes(): void
    {
        $this->registry['text'] = [
            'render' => fn($f, $v) => "<input type=\"text\" class=\"form-control\" id=\"field_{$f['slug']}\" name=\"field_{$f['slug']}\" value=\"" . htmlspecialchars((string)$v) . "\" placeholder=\"" . htmlspecialchars($f['placeholder'] ?? '') . "\">",
            'validate' => function ($f, $v) {
                $errs = [];
                if ($f['is_required'] && empty($v)) $errs[] = "{$f['name']} is required.";
                return $errs;
            },
            'cast' => fn($v) => (string)$v,
        ];

        $this->registry['number'] = [
            'render' => fn($f, $v) => "<input type=\"number\" step=\"any\" class=\"form-control\" id=\"field_{$f['slug']}\" name=\"field_{$f['slug']}\" value=\"" . htmlspecialchars((string)$v) . "\" placeholder=\"" . htmlspecialchars($f['placeholder'] ?? '') . "\">",
            'validate' => function ($f, $v) {
                $errs = [];
                if ($f['is_required'] && ($v === null || $v === '')) $errs[] = "{$f['name']} is required.";
                if ($v !== null && $v !== '' && !is_numeric($v)) $errs[] = "{$f['name']} must be a number.";
                return $errs;
            },
            'cast' => fn($v) => (float)$v,
        ];

        $this->registry['textarea'] = [
            'render' => fn($f, $v) => "<textarea class=\"form-control\" id=\"field_{$f['slug']}\" name=\"field_{$f['slug']}\" rows=\"3\" placeholder=\"" . htmlspecialchars($f['placeholder'] ?? '') . "\">" . htmlspecialchars((string)$v) . "</textarea>",
            'validate' => function ($f, $v) {
                if ($f['is_required'] && empty($v)) return ["{$f['name']} is required."];
                return [];
            },
            'cast' => fn($v) => (string)$v,
        ];

        $this->registry['email'] = [
            'render' => fn($f, $v) => "<input type=\"email\" class=\"form-control\" id=\"field_{$f['slug']}\" name=\"field_{$f['slug']}\" value=\"" . htmlspecialchars((string)$v) . "\" placeholder=\"" . htmlspecialchars($f['placeholder'] ?? '') . "\">",
            'validate' => function ($f, $v) {
                $errs = [];
                if ($f['is_required'] && empty($v)) $errs[] = "{$f['name']} is required.";
                if ($v && !filter_var($v, FILTER_VALIDATE_EMAIL)) $errs[] = "Invalid email format for {$f['name']}.";
                return $errs;
            },
            'cast' => fn($v) => (string)$v,
        ];

        $this->registry['date'] = [
            'render' => fn($f, $v) => "<input type=\"date\" class=\"form-control\" id=\"field_{$f['slug']}\" name=\"field_{$f['slug']}\" value=\"" . htmlspecialchars((string)$v) . "\">",
            'validate' => fn($f, $v) => ($f['is_required'] && empty($v)) ? ["{$f['name']} is required."] : [],
            'cast' => fn($v) => (string)$v,
        ];

        $this->registry['checkbox'] = [
            'render' => function ($f, $v) {
                $chk = $v ? 'checked' : '';
                return "<div class=\"form-check\"><input type=\"checkbox\" class=\"form-check-input\" id=\"field_{$f['slug']}\" name=\"field_{$f['slug']}\" value=\"1\" {$chk}><label class=\"form-check-label small text-muted\">Check if active</label></div>";
            },
            'validate' => fn($f, $v) => [],
            'cast' => fn($v) => (bool)$v,
        ];

        $this->registry['dropdown'] = [
            'render' => function ($f, $v) {
                $choices = $f['options']['choices'] ?? [];
                $html = "<select class=\"form-select\" id=\"field_{$f['slug']}\" name=\"field_{$f['slug']}\">";
                $html .= '<option value="">— Select —</option>';
                foreach ($choices as $c) {
                    $sel = ($v == $c) ? 'selected' : '';
                    $html .= "<option value=\"" . htmlspecialchars($c) . "\" {$sel}>" . htmlspecialchars($c) . "</option>";
                }
                return $html . "</select>";
            },
            'validate' => function ($f, $v) {
                if ($f['is_required'] && empty($v)) return ["Please select an option for {$f['name']}."];
                return [];
            },
            'cast' => fn($v) => (string)$v,
        ];

        $this->registry['file'] = [
            'render' => function ($f, $v) {
                $html = "<input type=\"file\" class=\"form-control\" id=\"field_{$f['slug']}\" name=\"field_{$f['slug']}\">";
                if ($v) {
                    $html .= "<div class=\"mt-1 small text-success\"><i class=\"bi bi-file-earmark-check\"></i> Current: " . htmlspecialchars((string)$v) . "</div>";
                }
                return $html;
            },
            'validate' => fn($f, $v) => [],
            'cast' => fn($v) => (string)$v,
        ];

        // Lookup Relationship
        $this->registry['lookup'] = [
            'render' => function ($f, $v) {
                $targetModId = (int)($f['options']['target_module_id'] ?? 0);
                if (!$targetModId) return '<div class="text-danger small">Error: No target module selected.</div>';

                $stmt = $this->db->prepare('SELECT id, data FROM records WHERE module_id = ? ORDER BY id DESC');
                $stmt->execute([$targetModId]);
                $records = $stmt->fetchAll();

                $req = $f['is_required'] ? 'required' : '';
                $html = "<select class=\"form-select\" id=\"field_{$f['slug']}\" name=\"field_{$f['slug']}\" {$req}>";
                $html .= '<option value="">— Select Linked Record —</option>';

                foreach ($records as $r) {
                    $data = json_decode($r['data'] ?? '{}', true) ?? [];
                    $displaySlug = $f['options']['display_field_slug'] ?? null;
                    
                    if ($displaySlug && isset($data[$displaySlug])) {
                        $label = $data[$displaySlug];
                    } else {
                        $label = !empty($data) ? reset($data) : "Record #{$r['id']}";
                    }
                    
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

        // Formula (Calculated)
        $this->registry['formula'] = [
            'render' => function ($f, $v) {
                $formula = htmlspecialchars($f['options']['formula'] ?? '');
                return "<input type=\"text\" class=\"form-control bg-light\" id=\"field_{$f['slug']}\" 
                               name=\"field_{$f['slug']}\" value=\"" . htmlspecialchars((string)$v) . "\" 
                               data-formula=\"{$formula}\" readonly>";
            },
            'validate' => fn($f, $v) => [],
            'cast'     => fn($v) => (float)$v,
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
