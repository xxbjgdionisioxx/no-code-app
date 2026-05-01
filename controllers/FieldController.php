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
        $module = $this->moduleEngine->getModule((int)($params['moduleId'] ?? 0));
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
        $validation = array_filter([
            'min'        => $req->post('v_min') !== '' ? (float)$req->post('v_min') : null,
            'max'        => $req->post('v_max') !== '' ? (float)$req->post('v_max') : null,
            'min_length' => $req->post('v_min_length') !== '' ? (int)$req->post('v_min_length') : null,
            'max_length' => $req->post('v_max_length') !== '' ? (int)$req->post('v_max_length') : null,
        ], fn($v) => $v !== null);

        $this->fieldEngine->createField($moduleId, [
            'name'          => strip_tags(trim($req->post('name', ''))),
            'field_type'    => $req->post('field_type', 'text'),
            'is_required'   => (int)(bool)$req->post('is_required'),
            'is_unique'     => (int)(bool)$req->post('is_unique'),
            'is_searchable' => (int)(bool)$req->post('is_searchable', '1'),
            'show_in_list'  => (int)(bool)$req->post('show_in_list', '1'),
            'default_value' => $req->post('default_value'),
            'placeholder'   => $req->post('placeholder'),
            'help_text'     => $req->post('help_text'),
            'validation'    => $validation,
            'options'       => $options,
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
        $module = $this->moduleEngine->getModule($moduleId);
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

        $validation = array_filter([
            'min'        => $req->post('v_min')        !== '' ? (float)$req->post('v_min')        : null,
            'max'        => $req->post('v_max')        !== '' ? (float)$req->post('v_max')        : null,
            'min_length' => $req->post('v_min_length') !== '' ? (int)$req->post('v_min_length')   : null,
            'max_length' => $req->post('v_max_length') !== '' ? (int)$req->post('v_max_length')   : null,
        ], fn($v) => $v !== null);

        $this->fieldEngine->updateField($fieldId, [
            'name'          => strip_tags(trim($req->post('name', ''))),
            'field_type'    => $req->post('field_type', 'text'),
            'is_required'   => (int)(bool)$req->post('is_required'),
            'is_unique'     => (int)(bool)$req->post('is_unique'),
            'is_searchable' => (int)(bool)$req->post('is_searchable', '1'),
            'show_in_list'  => (int)(bool)$req->post('show_in_list', '1'),
            'default_value' => $req->post('default_value'),
            'placeholder'   => $req->post('placeholder'),
            'help_text'     => $req->post('help_text'),
            'validation'    => $validation,
            'options'       => $options,
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
}
