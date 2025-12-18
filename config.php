<?php
if ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_NAME'] == '127.0.0.1') {
    $host = "localhost";
    $user = "root";
    $pass = "";
    $dbName = "pedeja"; 
} else {
    $host = "localhost";
    $user = "u506280443_rodguidbUser";
    $pass = "5f1WXa:T+";
    $dbName = "u506280443_rodguiDB";
}

$conn = new mysqli($host, $user, $pass, $dbName);

if ($conn->connect_error) {
    die("Erro na ligação: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>