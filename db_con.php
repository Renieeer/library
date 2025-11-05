<?php

$hostname = 'localhost';
$username = 'root';
$password = '';
$dbname = 'library_dbb';
$charset = 'utf8mb4';

try {
    $connection = new PDO("mysql:hostname= $hostname;dbname=$dbname", $username, $password);
   
} catch (PDOException $e) {
    
    echo json_encode(["success" => false, "message" => "Connection failed: " . $e->getMessage()]);
    exit;
}
