<?php
$db = new PDO('mysql:host=localhost;dbname=no_code_app', 'root', '');
$logs = $db->query('SELECT * FROM audit_log ORDER BY id DESC LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
print_r($logs);
