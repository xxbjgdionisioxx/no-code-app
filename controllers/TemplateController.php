<?php

namespace Controllers;

use Core\Controller;
use Core\Request;
use Core\Middleware;
use Engine\TemplateEngine;

class TemplateController extends Controller
{
    private $templateEngine;

    public function __construct(\PDO $db)
    {
        parent::__construct($db);
        $this->templateEngine = new TemplateEngine($db);
    }

    /**
     * Show the template gallery
     */
    public function index(Request $req): void
    {
        $user = Middleware::auth();
        $templates = $this->templateEngine->listTemplates();

        $this->view('templates/index', [
            'title'     => 'Template Gallery',
            'templates' => $templates,
            'user'      => $user
        ]);
    }

    /**
     * Handle template installation
     */
    public function install(Request $req, array $params): void
    {
        $user = Middleware::auth();
        $templateId = $params['id'] ?? null;

        try {
            // Install the template for the current user
            $this->templateEngine->installTemplate($templateId, $user['id']);
            
            $this->flash('success', 'Template installed successfully!');
            $this->redirect('/apps');
        } catch (\Exception $e) {
            $this->flash('danger', 'Error installing template: ' . $e->getMessage());
            $this->redirect('/templates');
        }
    }
}
