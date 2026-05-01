<?php

namespace Engine;

use PDO;
use Exception;

class TemplateEngine
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get list of available system templates
     */
    public function listTemplates(): array
    {
        return [
            [
                'id'          => 'inventory',
                'name'        => 'Inventory System',
                'description' => 'Track products, categories, stock levels, and pricing.',
                'icon'        => 'bi-box',
                'color'       => '#6366f1',
            ],
            [
                'id'          => 'crm',
                'name'        => 'Sales CRM',
                'description' => 'Manage leads, contacts, and sales opportunities.',
                'icon'        => 'bi-people',
                'color'       => '#f59e0b',
            ],
            [
                'id'          => 'project_mgmt',
                'name'        => 'Project Tracker',
                'description' => 'Manage team projects, tasks, and milestones.',
                'icon'        => 'bi-kanban',
                'color'       => '#3b82f6',
            ],
            [
                'id'          => 'hr',
                'name'        => 'HR Portal',
                'description' => 'Employee records and department management.',
                'icon'        => 'bi-person-vcard',
                'color'       => '#ec4899',
            ],
            [
                'id'          => 'support',
                'name'        => 'Help Desk',
                'description' => 'Internal support ticket tracking system.',
                'icon'        => 'bi-headset',
                'color'       => '#8b5cf6',
            ],
            [
                'id'          => 'fleet',
                'name'        => 'Fleet Manager',
                'description' => 'Track vehicle inventory and maintenance.',
                'icon'        => 'bi-truck',
                'color'       => '#14b8a6',
            ],
            [
                'id'          => 'payroll',
                'name'        => 'Payroll System',
                'description' => 'Complete payroll management with employees, attendance, and payslips.',
                'icon'        => 'bi-cash-coin',
                'color'       => '#10b981',
            ],
        ];
    }

    /**
     * Install a template for a user using native PHP data structures.
     * No SQL parsing — safe, fast, and reliable.
     */
    public function installTemplate(string $templateId, int $userId): bool
    {
        $template = null;
        foreach ($this->listTemplates() as $t) {
            if ($t['id'] === $templateId) { $template = $t; break; }
        }
        if (!$template) throw new Exception("Template not found: $templateId");

        $dataFile = BASE_PATH . '/sql/templates/' . $templateId . '/data.php';
        if (!file_exists($dataFile)) throw new Exception("Template data file missing: $dataFile");

        $data = require $dataFile;

        try {
            $this->db->beginTransaction();

            // 1. Create the App
            $stmt = $this->db->prepare("INSERT INTO apps (name, slug, description, icon, color, owner_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $template['name'],
                $templateId . '-' . bin2hex(random_bytes(4)),
                $template['description'],
                $template['icon'],
                $template['color'],
                $userId
            ]);
            $newAppId = (int)$this->db->lastInsertId();

            // 2. Create Admin Role
            $stmt = $this->db->prepare("INSERT INTO roles (app_id, name, description, is_system) VALUES (?, 'Admin', 'Full access', 1)");
            $stmt->execute([$newAppId]);
            $newRoleId = (int)$this->db->lastInsertId();
            $this->db->prepare("INSERT INTO user_roles (user_id, role_id, app_id) VALUES (?, ?, ?)")
                      ->execute([$userId, $newRoleId, $newAppId]);

            // 3. Insert Modules & track ID mapping
            $moduleMap = []; // templateModuleId => newDbId
            $mStmt = $this->db->prepare("INSERT INTO modules (app_id, name, slug, description, icon, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
            $pStmt = $this->db->prepare("INSERT INTO permissions (role_id, module_id, can_view, can_create, can_edit, can_delete) VALUES (?, ?, 1, 1, 1, 1)");

            foreach ($data['modules'] as $mod) {
                $mStmt->execute([$newAppId, $mod['name'], $mod['slug'], $mod['description'], $mod['icon'], $mod['sort_order']]);
                $newModId = (int)$this->db->lastInsertId();
                $moduleMap[$mod['id']] = $newModId;
                $pStmt->execute([$newRoleId, $newModId]);
            }

            // 4. Insert Fields & track ID mapping
            $fieldMap = []; // templateFieldId => newDbId
            $fStmt = $this->db->prepare("INSERT INTO fields (module_id, name, slug, field_type, is_required, is_unique, is_searchable, show_in_list, options, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($data['fields'] as $f) {
                if (!isset($moduleMap[$f['module_id']])) continue;

                $options = $f['options'] ?? [];
                if ($f['type'] === 'lookup' && isset($options['target_module_id'])) {
                    $options['target_module_id'] = $moduleMap[$options['target_module_id']] ?? null;
                }

                $fStmt->execute([
                    $moduleMap[$f['module_id']],
                    $f['name'], $f['slug'], $f['type'],
                    $f['required'] ?? 0, $f['unique'] ?? 0,
                    $f['searchable'] ?? 0, $f['in_list'] ?? 1,
                    !empty($options) ? json_encode($options) : null,
                    $f['sort'] ?? 0
                ]);
                if (isset($f['id'])) {
                    $fieldMap[$f['id']] = (int)$this->db->lastInsertId();
                }
            }

            // 5. Insert Records
            $rStmt = $this->db->prepare("INSERT INTO records (app_id, module_id, created_by, data) VALUES (?, ?, ?, ?)");
            foreach ($data['records'] as $rec) {
                if (!isset($moduleMap[$rec['module_id']])) continue;
                $rStmt->execute([
                    $newAppId,
                    $moduleMap[$rec['module_id']],
                    $userId,
                    json_encode($rec['data'], JSON_UNESCAPED_UNICODE)
                ]);
            }

            // 6. Insert Dashboard Widgets
            $wStmt = $this->db->prepare("INSERT INTO dashboard_widgets (app_id, user_id, title, widget_type, module_id, field_id, chart_color, width) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($data['widgets'] as $w) {
                $wStmt->execute([
                    $newAppId, $userId,
                    $w['title'], $w['type'],
                    isset($w['module_id']) ? ($moduleMap[$w['module_id']] ?? null) : null,
                    isset($w['field_id']) ? ($fieldMap[$w['field_id']] ?? null) : null,
                    $w['color'] ?? '#6366f1',
                    $w['width'] ?? 6
                ]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
