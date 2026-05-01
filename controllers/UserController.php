<?php

namespace Controllers;

use Core\Controller;
use Core\Request;
use Core\Middleware;
use Core\Session;

class UserController extends Controller
{
    public function __construct(\PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Display account settings
     */
    public function account(Request $req): void
    {
        $user = Middleware::auth();
        
        $this->view('account.index', [
            'title' => 'Account Settings',
            'user'  => $user
        ]);
    }

    /**
     * Update account details (simplified for now)
     */
    public function update(Request $req): void
    {
        $user = Middleware::auth();
        Middleware::csrf();

        $name  = strip_tags(trim($req->post('name', '')));
        $email = strip_tags(trim($req->post('email', '')));

        if (!$name || !$email) {
            $this->flashError('Name and Email are required.');
            $this->redirect('/account');
        }

        // Check if email is already taken by someone else
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $stmt->execute([$email, $user['id']]);
        if ($stmt->fetch()) {
            $this->flashError('This email is already registered to another account.');
            $this->redirect('/account');
        }

        // Update DB
        $stmt = $this->db->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
        $stmt->execute([$name, $email, $user['id']]);

        // Refresh session data
        $user['name']  = $name;
        $user['email'] = $email;
        Session::set('user', $user);

        $this->flashSuccess('Account updated successfully.');
        $this->redirect('/account');
    }
}
