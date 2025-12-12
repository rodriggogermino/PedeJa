<?php
$conn = new mysqli("localhost", "root", "", "pedeja");

if ($conn->connect_error) {
    die("Erro na ligação: " . $conn->connect_error);
}
?>
