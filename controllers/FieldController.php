<?php

namespace Controllers;

use Core\Controller;
use Core\Request;
use Core\Middleware;
use Engine\FieldEngine;
use Engine\ModuleEngine;
use Engine\AppEngine;

class FieldController extends Controller
{
    private FieldEngine  $fieldEngine;
    private ModuleEngine $moduleEngine;
    private AppEngine    $appEngine;

    public function __construct(\PDO $db)
    {
        parent::__construct($db);
        $this->fieldEngine  = new FieldEngine($db);
        $this->moduleEngine = new ModuleEngine($db);
        $this->appEngine    = new AppEngine($db);
    }

    public function create(Request $req, array $params): void
    {
        Middleware::auth();
        $app    = $this->appEngine->getApp((int)($params['appId'] ?? 0));
        $module = $this->moduleEngine->getSchema((int)($params['moduleId'] ?? 0));
        $this->view('builder.field_editor', [
            'title'  => 'Add Field',
            'app'    => $app,
            'module' => $module,
            'field'  => null,
            'types'  => $this->fieldEngine->getRegisteredTypes(),
        ], 'builder');
    }

    public function store(Request $req, array $params): void
    {
        Middleware::auth();
        Middleware::csrf();

        $moduleId = (int)($params['moduleId'] ?? 0);
        $appId    = (int)($params['appId']    ?? 0);

        // Parse dropdown choices from newline-separated textarea
        $options = [];
        if ($req->post('field_type') === 'dropdown') {
            $raw     = $req->post('choices', '');
            $choices = array_filter(array_map('trim', explode("\n", $raw)));
            $options = ['choices' => array_values($choices)];
        }

        // Parse validation rules
        $vMin = $req->post('v_min');
        $vMax = $req->post('v_max');
        $vMinLen = $req->post('v_min_length');
        $vMaxLen = $req->post('v_max_length');

        $validation = array_filter([
            'min'        => ($vMin !== null && $vMin !== '') ? (float)$vMin : null,
            'max'        => ($vMax !== null && $vMax !== '') ? (float)$vMax : null,
            'min_length' => ($vMinLen !== null && $vMinLen !== '') ? (int)$vMinLen : null,
            'max_length' => ($vMaxLen !== null && $vMaxLen !== '') ? (int)$vMaxLen : null,
        ], fn($v) => $v !== null);

        $this->fieldEngine->createField($moduleId, [
            'name'          => strip_tags(trim($req->post('name', ''))),
            'field_type'    => $req->post('field_type', 'text'),
            'is_required'   => (int)(bool)$req->post('is_required'),
            'is_unique'     => (int)(bool)$req->post('is_unique'),
            'is_searchable' => (int)(bool)$req->post('is_searchable'),
            'show_in_list'  => (int)(bool)$req->post('show_in_list'),
            'show_in_form'  => (int)(bool)$req->post('show_in_form'),
            'default_value' => $req->post('default_value'),
            'placeholder'   => $req->post('placeholder'),
            'help_text'     => $req->post('help_text'),
            'validation'    => $validation,
            'options'       => array_merge($options, [
                'target_module_id'   => $req->post('target_module_id') ? (int)$req->post('target_module_id') : null,
                'display_field_slug' => $req->post('display_field_slug'),
                'formula'            => $req->post('formula')
            ]),
            'sort_order'    => (int)$req->post('sort_order', 0),
        ]);

        if ($req->isAjax()) {
            $this->json(['success' => true]);
        }
        $this->flashSuccess('Field added.');
        $this->redirect("/apps/{$appId}/modules/{$moduleId}/builder");
    }

    public function edit(Request $req, array $params): void
    {
        Middleware::auth();
        $appId    = (int)($params['appId']    ?? 0);
        $moduleId = (int)($params['moduleId'] ?? 0);
        $fieldId  = (int)($params['id']       ?? 0);

        $app    = $this->appEngine->getApp($appId);
        $module = $this->moduleEngine->getSchema($moduleId);
        $field  = $this->fieldEngine->getField($fieldId);

        $this->view('builder.field_editor', [
            'title'  => 'Edit Field',
            'app'    => $app,
            'module' => $module,
            'field'  => $field,
            'types'  => $this->fieldEngine->getRegisteredTypes(),
        ], 'builder');
    }

    public function update(Request $req, array $params): void
    {
        Middleware::auth();
        Middleware::csrf();

        $appId    = (int)($params['appId']    ?? 0);
        $moduleId = (int)($params['moduleId'] ?? 0);
        $fieldId  = (int)($params['id']       ?? 0);

        $options    = [];
        if ($req->post('field_type') === 'dropdown') {
            $raw     = $req->post('choices', '');
            $choices = array_filter(array_map('trim', explode("\n", $raw)));
            $options = ['choices' => array_values($choices)];
        }

        $vMin = $req->post('v_min');
        $vMax = $req->post('v_max');
        $vMinLen = $req->post('v_min_length');
        $vMaxLen = $req->post('v_max_length');

        $validation = array_filter([
            'min'        => ($vMin !== null && $vMin !== '') ? (float)$vMin : null,
            'max'        => ($vMax !== null && $vMax !== '') ? (float)$vMax : null,
            'min_length' => ($vMinLen !== null && $vMinLen !== '') ? (int)$vMinLen : null,
            'max_length' => ($vMaxLen !== null && $vMaxLen !== '') ? (int)$vMaxLen : null,
        ], fn($v) => $v !== null);

        $this->fieldEngine->updateField($fieldId, [
            'name'          => strip_tags(trim($req->post('name', ''))),
            'field_type'    => $req->post('field_type', 'text'),
            'is_required'   => (int)(bool)$req->post('is_required'),
            'is_unique'     => (int)(bool)$req->post('is_unique'),
            'is_searchable' => (int)(bool)$req->post('is_searchable'),
            'show_in_list'  => (int)(bool)$req->post('show_in_list'),
            'show_in_form'  => (int)(bool)$req->post('show_in_form'),
            'default_value' => $req->post('default_value'),
            'placeholder'   => $req->post('placeholder'),
            'help_text'     => $req->post('help_text'),
            'validation'    => $validation,
            'options'       => array_merge($options, [
                'target_module_id'   => $req->post('target_module_id') ? (int)$req->post('target_module_id') : null,
                'display_field_slug' => $req->post('display_field_slug'),
                'formula'            => $req->post('formula')
            ]),
        ]);

        $this->flashSuccess('Field updated.');
        $this->redirect("/apps/{$appId}/modules/{$moduleId}/builder");
    }

    public function destroy(Request $req, array $params): void
    {
        Middleware::admin();
        $appId    = (int)($params['appId']    ?? 0);
        $moduleId = (int)($params['moduleId'] ?? 0);
        $fieldId  = (int)($params['id']       ?? 0);
        $this->fieldEngine->deleteField($fieldId);
        $this->flashSuccess('Field deleted.');
        $this->redirect("/apps/{$appId}/modules/{$moduleId}/builder");
    }

    public function reorder(Request $req, array $params): void
    {
        Middleware::auth();
        $ids = $req->post('ids', []);
        if (is_array($ids)) {
            $this->fieldEngine->reorderFields($ids);
        }
        $this->json(['success' => true]);
    }

    public function fieldsJson(Request $req, array $params): void
    {
        Middleware::auth();
        $moduleId = (int)($params['moduleId'] ?? 0);
        $schema   = $this->moduleEngine->getSchema($moduleId);
        $this->json($schema['fields']);
    }
}
