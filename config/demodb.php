<?php
$host = "localhost";
$db   = "crowdspark";
$user = "demouser";
$pass = "demopass";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=UTF8",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("DB Error");
}
?>