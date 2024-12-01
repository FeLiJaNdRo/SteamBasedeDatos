<?php
$host = 'localhost';
$port = '5432';
$dbname = 'postgres';
$user = 'felijandro';
$password = '1234';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
} catch (PDOException $e) {
    die("Error en la conexión: " . $e->getMessage());
}
?>

