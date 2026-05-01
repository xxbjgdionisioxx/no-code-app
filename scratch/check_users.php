<?php
$db = new PDO('mysql:host=localhost;dbname=no_code_app', 'root', '');
$res = $db->query('SELECT * FROM users')->fetchAll(PDO::FETCH_ASSOC);
print_r($res);
