<?php

namespace Controllers;

use Core\Controller;
use Core\Request;
use Core\Middleware;

class NotificationController extends Controller
{
    /**
     * Mark all notifications as read for the current user.
     * Returns JSON — called via AJAX from the notification dropdown.
     */
    public function readAll(Request $req, array $params): void
    {
        $user = Middleware::auth();

        $stmt = $this->db->prepare(
            'UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0'
        );
        $stmt->execute([$user['id']]);

        $this->json(['success' => true, 'cleared' => $stmt->rowCount()]);
    }
}
