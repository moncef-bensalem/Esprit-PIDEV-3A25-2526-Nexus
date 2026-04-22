<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=nexus_db', 'root', '');
$stmt = $pdo->query('DESCRIBE review');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
