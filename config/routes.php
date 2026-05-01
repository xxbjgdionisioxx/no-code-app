<?php
/**
 * Route definitions.
 *
 * Format: $router->METHOD('pattern', 'Controller@method')
 * Pattern supports {param} placeholders.
 */

// ── Auth ────────────────────────────────────────────────────
$router->get('/login',           'AuthController@showLogin');
$router->post('/login',          'AuthController@login');
$router->get('/register',        'AuthController@showRegister');
$router->post('/register',       'AuthController@register');
$router->get('/logout',          'AuthController@logout');
$router->get('/account',         'UserController@account');
$router->post('/account',        'UserController@update');
$router->post('/notifications/read-all', 'NotificationController@readAll');

// ── Home ────────────────────────────────────────────────────
$router->get('/',                'AppController@index');

// ── App Management ──────────────────────────────────────────
$router->get('/apps',            'AppController@index');
$router->get('/apps/create',     'AppController@create');
$router->post('/apps',           'AppController@store');
$router->get('/apps/{id}',       'AppController@show');
$router->get('/apps/{id}/edit',  'AppController@edit');
$router->post('/apps/{id}',      'AppController@update');
$router->match(['GET', 'POST'], '/apps/{id}/delete', 'AppController@destroy');

// ── Module Builder ──────────────────────────────────────────
$router->get('/apps/{appId}/modules',             'ModuleController@index');
$router->get('/apps/{appId}/modules/create',      'ModuleController@create');
$router->post('/apps/{appId}/modules',            'ModuleController@store');
$router->get('/apps/{appId}/modules/{id}/edit',   'ModuleController@edit');
$router->post('/apps/{appId}/modules/{id}',       'ModuleController@update');
$router->match(['GET', 'POST'], '/apps/{appId}/modules/{id}/delete', 'ModuleController@destroy');
$router->get('/apps/{appId}/modules/{id}/builder','ModuleController@builder');

// ── Field Builder ───────────────────────────────────────────
$router->get('/apps/{appId}/modules/{moduleId}/fields/create',      'FieldController@create');
$router->post('/apps/{appId}/modules/{moduleId}/fields',            'FieldController@store');
$router->get('/apps/{appId}/modules/{moduleId}/fields/{id}/edit',   'FieldController@edit');
$router->post('/apps/{appId}/modules/{moduleId}/fields/{id}',       'FieldController@update');
$router->match(['GET', 'POST'], '/apps/{appId}/modules/{moduleId}/fields/{id}/delete', 'FieldController@destroy');
$router->post('/apps/{appId}/modules/{moduleId}/fields/reorder',    'FieldController@reorder');
$router->get('/apps/{appId}/modules/{moduleId}/fields/json',       'FieldController@fieldsJson');

// ── Templates ───────────────────────────────────────────────
$router->get('/templates',              'TemplateController@index');
$router->post('/templates/{id}/install', 'TemplateController@install');

// ── Dashboard ───────────────────────────────────────────────
$router->get('/apps/{appId}/dashboard',                   'DashboardController@index');
$router->get('/apps/{appId}/dashboard/widgets/create',    'DashboardController@createWidget');
$router->post('/apps/{appId}/dashboard/widgets',          'DashboardController@storeWidget');
$router->get('/apps/{appId}/dashboard/widgets/{id}/edit', 'DashboardController@editWidget');
$router->post('/apps/{appId}/dashboard/widgets/{id}',     'DashboardController@updateWidget');
$router->match(['GET', 'POST'], '/apps/{appId}/dashboard/widgets/{id}/delete', 'DashboardController@destroyWidget');
$router->get('/apps/{appId}/dashboard/data/{widgetId}',   'DashboardController@widgetData');

// ── RBAC ────────────────────────────────────────────────────
$router->get('/apps/{appId}/roles',                     'RoleController@index');
$router->get('/apps/{appId}/roles/create',              'RoleController@create');
$router->post('/apps/{appId}/roles',                    'RoleController@store');
$router->get('/apps/{appId}/roles/{id}/edit',           'RoleController@edit');
$router->post('/apps/{appId}/roles/{id}',               'RoleController@update');
$router->match(['GET', 'POST'], '/apps/{appId}/roles/{id}/delete', 'RoleController@destroy');
$router->get('/apps/{appId}/roles/{id}/permissions',    'RoleController@permissions');
$router->post('/apps/{appId}/roles/{id}/permissions',   'RoleController@savePermissions');
$router->get('/apps/{appId}/users',                     'RoleController@users');
$router->post('/apps/{appId}/users',                    'RoleController@storeUser');
$router->post('/apps/{appId}/users/{userId}/role',      'RoleController@assignRole');

// ── Dynamic Record CRUD ─────────────────────────────────────
$router->get('/apps/{appId}/{moduleSlug}/export',      'RecordController@export');
$router->get('/apps/{appId}/{moduleSlug}',              'RecordController@index');
$router->get('/apps/{appId}/{moduleSlug}/create',       'RecordController@create');
$router->post('/apps/{appId}/{moduleSlug}',             'RecordController@store');
$router->get('/apps/{appId}/{moduleSlug}/{id}',         'RecordController@show');
    $router->get('/apps/{appId}/{moduleSlug}/{id}/edit',    'RecordController@edit');
    $router->post('/apps/{appId}/{moduleSlug}/{id}',        'RecordController@update');
    $router->match(['GET', 'POST'], '/apps/{appId}/{moduleSlug}/{id}/delete', 'RecordController@destroy');

// ── REST API ────────────────────────────────────────────────
$router->get('/api/{appSlug}/{moduleSlug}',           'ApiController@index');
$router->post('/api/{appSlug}/{moduleSlug}',          'ApiController@store');
$router->get('/api/{appSlug}/{moduleSlug}/{id}',      'ApiController@show');
$router->put('/api/{appSlug}/{moduleSlug}/{id}',      'ApiController@update');
$router->delete('/api/{appSlug}/{moduleSlug}/{id}',   'ApiController@destroy');
