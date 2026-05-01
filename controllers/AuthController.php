<?php

namespace Controllers;

use Core\Controller;
use Core\Request;
use Core\Session;
use Core\Middleware;
use Core\CSRF;

/**
 * AuthController — Login, register, and logout.
 */
class AuthController extends Controller
{
    public function showLogin(Request $req): void
    {
        if (Session::get('user')) {
            $this->redirect('/apps');
        }
        $this->view('auth.login', ['title' => 'Login'], 'auth');
    }

    public function login(Request $req): void
    {
        Middleware::throttle('login', 10, 60);
        Middleware::csrf();

        $email    = trim($req->post('email', ''));
        $password = $req->post('password', '');

        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? AND is_active = 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            Session::old($req->postAll());
            $this->flashError('Invalid email or password.');
            $this->redirect('/login');
        }

        // Store minimal user data in session (never store password)
        Session::set('user', [
            'id'       => $user['id'],
            'name'     => $user['name'],
            'email'    => $user['email'],
            'is_admin' => (bool)$user['is_admin'],
        ]);

        $this->flashSuccess('Welcome back, ' . $user['name'] . '!');
        $this->redirect('/apps');
    }

    public function showRegister(Request $req): void
    {
        $this->view('auth.register', ['title' => 'Create Account'], 'auth');
    }

    public function register(Request $req): void
    {
        Middleware::throttle('register', 5, 300);
        Middleware::csrf();

        $name     = strip_tags(trim($req->post('name', '')));
        $email    = filter_var(trim($req->post('email', '')), FILTER_SANITIZE_EMAIL);
        $password = $req->post('password', '');
        $confirm  = $req->post('password_confirm', '');

        $errors = [];
        if (mb_strlen($name) < 2)        $errors[] = 'Name must be at least 2 characters.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
        if (mb_strlen($password) < 8)    $errors[] = 'Password must be at least 8 characters.';
        if ($password !== $confirm)       $errors[] = 'Passwords do not match.';

        if (!empty($errors)) {
            Session::old($req->postAll());
            $this->flash('error', implode(' ', $errors));
            $this->redirect('/register');
        }

        // Check email uniqueness
        $exists = $this->db->prepare('SELECT id FROM users WHERE email = ?');
        $exists->execute([$email]);
        if ($exists->fetch()) {
            $this->flashError('That email is already registered.');
            $this->redirect('/register');
        }

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $this->db->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
        $stmt->execute([$name, $email, $hash]);

        $this->flashSuccess('Account created! Please log in.');
        $this->redirect('/login');
    }

    public function logout(Request $req): void
    {
        Session::destroy();
        $this->redirect('/login');
    }
}
