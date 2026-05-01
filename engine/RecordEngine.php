<?php

namespace Engine;

/**
 * RecordEngine — Dynamic CRUD operations against the meta-table structure.
 *
 * Uses a hybrid EAV strategy:
 *   - `records`       — header row per logical record
 *   - `record_values` — individual field↔value pairs
 *   - `records.data`  — JSON snapshot for fast list queries
 *
 * All queries use PDO prepared statements exclusively.
 */
class RecordEngine
{
    public function __construct(
        private \PDO         $db,
        private FieldEngine  $fieldEngine,
        private \Plugins\PluginManager $plugins
    ) {}

    // ── Create ───────────────────────────────────────────────

    /**
     * Insert a new record for a module.
     *
     * @param int   $appId
     * @param int   $moduleId
     * @param int   $userId     Creator's user ID
     * @param array $values     Sanitized field values keyed by field slug
     * @param array $files      $_FILES array subset for file fields
     * @param array $schema     Module schema (from ModuleEngine::getSchema)
     * @return int  New record ID
     */
    public function createRecord(int $appId, int $moduleId, int $userId, array $values, array $files, array $schema): int
    {
        $values = $this->sanitizeValues($values);
        $this->db->beginTransaction();

        try {
            // Handle file uploads — replaces file field value with stored filename
            $values = $this->handleFileUploads($values, $files, $schema['fields']);

            // Check uniqueness constraints
            $this->enforceUniqueConstraints($moduleId, $values, $schema['fields']);

            // Insert record header
            $stmt = $this->db->prepare(
                'INSERT INTO records (app_id, module_id, created_by, data) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$appId, $moduleId, $userId, json_encode($values)]);
            $recordId = (int)$this->db->lastInsertId();

            // Insert EAV values
            $this->insertEavValues($recordId, $values, $schema['fields']);

            $this->db->commit();

            $this->plugins->fire('record_created', ['record_id' => $recordId, 'module_id' => $moduleId, 'values' => $values]);
            return $recordId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ── Read ─────────────────────────────────────────────────

    /**
     * Retrieve a single record with all its field values.
     * Returns the record header merged with a 'values' associative array.
     */
    public function getRecord(int $recordId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM records WHERE id = ?');
        $stmt->execute([$recordId]);
        $record = $stmt->fetch();
        if (!$record) return null;

        // Use the JSON snapshot — it's faster than EAV re-join for single reads
        $record['values'] = $record['data'] ? json_decode($record['data'], true) : [];
        return $record;
    }

    /**
     * List all records for a module with filtering, search, and pagination.
     * Uses the JSON snapshot column for performance.
     *
     * @param int    $moduleId
     * @param array  $options  ['page' => 1, 'per_page' => 25, 'search' => '', 'sort_field' => '', 'sort_dir' => 'asc']
     * @param array  $schema   Module schema
     * @return array ['records' => [...], 'total' => N, 'pages' => N]
     */
    public function listRecords(int $moduleId, array $options, array $schema): array
    {
        $page    = max(1, (int)($options['page'] ?? 1));
        $perPage = min(200, max(1, (int)($options['per_page'] ?? DEFAULT_PAGE_SIZE)));
        $offset  = ($page - 1) * $perPage;
        $search  = trim($options['search'] ?? '');

        // Build base query — use JSON_EXTRACT for JSON column filtering
        $params = [$moduleId];
        $where  = 'r.module_id = ?';

        if ($search) {
            // Full-text style search across all searchable field values via EAV table
            $searchableFieldIds = array_column(
                array_filter($schema['fields'], fn($f) => $f['is_searchable']),
                'id'
            );

            if (!empty($searchableFieldIds)) {
                $placeholders = implode(',', array_fill(0, count($searchableFieldIds), '?'));
                $where .= " AND r.id IN (
                    SELECT rv.record_id FROM record_values rv
                    WHERE rv.field_id IN ({$placeholders}) AND rv.value LIKE ?
                )";
                $params = array_merge($params, $searchableFieldIds, ["%{$search}%"]);
            }
        }

        // Count total matching records
        $countSql  = "SELECT COUNT(*) FROM records r WHERE {$where}";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // Sorting — only allow sort on known fields (prevent injection)
        $sortDir = strtoupper($options['sort_dir'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
        $orderBy = 'r.id ' . $sortDir;

        // Fetch records
        $listSql  = "SELECT r.* FROM records r WHERE {$where} ORDER BY {$orderBy} LIMIT ? OFFSET ?";
        $listStmt = $this->db->prepare($listSql);
        $listStmt->execute(array_merge($params, [$perPage, $offset]));
        $rows = $listStmt->fetchAll();

        // Decode JSON snapshot for each record
        foreach ($rows as &$row) {
            $row['values'] = $row['data'] ? json_decode($row['data'], true) : [];
        }
        unset($row);

        return [
            'records' => $rows,
            'total'   => $total,
            'pages'   => (int)ceil($total / $perPage),
            'page'    => $page,
            'per_page'=> $perPage,
        ];
    }

    // ── Update ───────────────────────────────────────────────

    /**
     * Update an existing record.
     */
    public function updateRecord(int $recordId, int $userId, array $values, array $files, array $schema): bool
    {
        $values = $this->sanitizeValues($values);
        $this->db->beginTransaction();

        try {
            $old = $this->getRecord($recordId);
            if (!$old) throw new \RuntimeException("Record [{$recordId}] not found.");

            $values = $this->handleFileUploads($values, $files, $schema['fields'], $old['values']);
            $this->enforceUniqueConstraints($schema['id'], $values, $schema['fields'], $recordId);

            // Update JSON snapshot
            $merged = array_merge($old['values'], $values);
            $stmt   = $this->db->prepare(
                'UPDATE records SET data = ?, updated_by = ? WHERE id = ?'
            );
            $stmt->execute([json_encode($merged), $userId, $recordId]);

            // Upsert EAV values
            $this->upsertEavValues($recordId, $values, $schema['fields']);

            $this->db->commit();
            $this->plugins->fire('record_updated', ['record_id' => $recordId, 'old' => $old['values'], 'new' => $merged]);
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ── Delete ───────────────────────────────────────────────

    /**
     * Hard-delete a record and all its EAV values (FK cascade handles record_values).
     */
    public function deleteRecord(int $recordId): bool
    {
        $record = $this->getRecord($recordId);
        $stmt   = $this->db->prepare('DELETE FROM records WHERE id = ?');
        $result = $stmt->execute([$recordId]);
        if ($result && $record) {
            $this->plugins->fire('record_deleted', ['record_id' => $recordId, 'old' => $record['values']]);
        }
        return $result;
    }

    // ── EAV Helpers ──────────────────────────────────────────

    private function insertEavValues(int $recordId, array $values, array $fields): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO record_values (record_id, field_id, value) VALUES (?, ?, ?)'
        );
        foreach ($fields as $field) {
            if (array_key_exists($field['slug'], $values)) {
                $stmt->execute([$recordId, $field['id'], (string)($values[$field['slug']] ?? '')]);
            }
        }
    }

    private function upsertEavValues(int $recordId, array $values, array $fields): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO record_values (record_id, field_id, value) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE value = VALUES(value)'
        );
        foreach ($fields as $field) {
            if (array_key_exists($field['slug'], $values)) {
                $stmt->execute([$recordId, $field['id'], (string)($values[$field['slug']] ?? '')]);
            }
        }
    }

    // ── File Upload Handler ───────────────────────────────────

    /**
     * Process uploaded files for file-type fields.
     * Validates MIME type, size, and stores with a random name.
     *
     * @param array $values     Current field values (by slug)
     * @param array $files      $_FILES array
     * @param array $fields     Field definitions
     * @param array $existing   Existing values (for edit — keep old file if no new upload)
     */
    private function handleFileUploads(array $values, array $files, array $fields, array $existing = []): array
    {
        foreach ($fields as $field) {
            if ($field['field_type'] !== 'file') continue;

            $inputName = "field_{$field['slug']}";
            $fileInfo  = $files[$inputName] ?? null;

            // No new file uploaded — keep existing value
            if (!$fileInfo || $fileInfo['error'] === UPLOAD_ERR_NO_FILE) {
                $values[$field['slug']] = $existing[$field['slug']] ?? null;
                continue;
            }

            if ($fileInfo['error'] !== UPLOAD_ERR_OK) {
                throw new \RuntimeException("File upload error for field '{$field['name']}': code {$fileInfo['error']}");
            }

            // Size validation
            if ($fileInfo['size'] > UPLOAD_MAX_SIZE) {
                throw new \RuntimeException("File for '{$field['name']}' exceeds maximum size of " . (UPLOAD_MAX_SIZE / 1024 / 1024) . "MB.");
            }

            // MIME type validation — check real MIME, not just extension
            $finfo    = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($fileInfo['tmp_name']);
            if (!in_array($mimeType, UPLOAD_ALLOWED_TYPES, true)) {
                throw new \RuntimeException("File type '{$mimeType}' is not allowed for field '{$field['name']}'.");
            }

            // Generate a random, safe filename
            $ext      = pathinfo($fileInfo['name'], PATHINFO_EXTENSION);
            $ext      = preg_replace('/[^a-z0-9]/i', '', $ext);
            $filename = bin2hex(random_bytes(16)) . '.' . strtolower($ext);
            $destDir  = STORAGE_PATH;

            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }

            $destPath = $destDir . '/' . $filename;
            if (!move_uploaded_file($fileInfo['tmp_name'], $destPath)) {
                throw new \RuntimeException("Failed to store uploaded file for field '{$field['name']}'.");
            }

            $values[$field['slug']] = $filename;
        }

        return $values;
    }

    // ── Uniqueness Constraint ────────────────────────────────

    /**
     * Enforce is_unique constraints against existing EAV values.
     * Throws RuntimeException with a user-friendly message on violation.
     */
    private function enforceUniqueConstraints(int $moduleId, array $values, array $fields, int $excludeRecordId = 0): void
    {
        $uniqueFields = array_filter($fields, fn($f) => $f['is_unique']);

        foreach ($uniqueFields as $field) {
            $slug  = $field['slug'];
            $value = $values[$slug] ?? null;
            if ($value === null || $value === '') continue;

            // Check if any other record in this module has this value for this field
            $sql = 'SELECT rv.record_id FROM record_values rv
                    INNER JOIN records r ON r.id = rv.record_id
                    WHERE r.module_id = ? AND rv.field_id = ? AND rv.value = ?';
            $params = [$moduleId, $field['id'], (string)$value];

            if ($excludeRecordId > 0) {
                $sql    .= ' AND rv.record_id != ?';
                $params[] = $excludeRecordId;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            if ($stmt->fetch()) {
                throw new \RuntimeException("The value '{$value}' for field '{$field['name']}' already exists. It must be unique.");
            }
        }
    }

    /**
     * Sanitize incoming field values to strip unwanted browser/proxy metadata.
     * (e.g., "From http://localhost" or correlation UUIDs)
     */
    private function sanitizeValues(array $values): array
    {
        foreach ($values as $slug => &$val) {
            if (is_string($val) && str_contains($val, 'From http')) {
                $lines = explode("\n", str_replace("\r\n", "\n", $val));
                $cleanLines = [];
                foreach ($lines as $line) {
                    $trimmed = trim($line);
                    if ($trimmed === '') continue;
                    // Skip lines containing "From http"
                    if (str_contains($trimmed, 'From http')) continue;
                    // Skip standalone UUID-like lines
                    if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $trimmed)) continue;
                    $cleanLines[] = $line;
                }
                $val = trim(implode("\n", $cleanLines));
            }
        }
        return $values;
    }
}
