<?php
session_start();
include("conexion.php");

// Verificar sesión activa y que sea personal
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== "personal") {
    header("Location: login.html");
    exit();
}

// Verificar si enviaron no_control
if (!isset($_GET['no_control'])) {
    header("Location: personalbiblioteca.php?msg=error");
    exit();
}

$no_control = $_GET['no_control'];

// Verificar que exista el registro antes de eliminar
$sql = "SELECT no_control FROM PersonalBiblioteca WHERE no_control = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $no_control);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: personalbiblioteca.php?msg=error");
    exit();
}

// Eliminar registro
$sqlDelete = "DELETE FROM PersonalBiblioteca WHERE no_control = ?";
$stmt2 = $conn->prepare($sqlDelete);
$stmt2->bind_param("s", $no_control);

if ($stmt2->execute()) {
    header("Location: personalbiblioteca.php?msg=eliminado");
} else {
    header("Location: personalbiblioteca.php?msg=error");
}

exit();
?>
