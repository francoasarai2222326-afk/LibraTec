<?php
$servername = "localhost";
$username = "root";       // Usuario por defecto de XAMPP
$password = "";           // Contraseña por defecto de XAMPP (vacía)
$database = "biblioteca";      // Cambia por el nombre de tu base de datos

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
