<?php
session_start();
include("conexion.php");

// Verificar sesión activa y que sea personal
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== "personal") {
    header("Location: login.html");
    exit();
}

// Verificar si enviaron no_control
if (!isset($_GET['id_alumno'])) {
    header("Location: estudiantes.php?msg=error");
    exit();
}

$no_control = $_GET['id_alumno']; // AQUÍ RECIBIMOS NO_CONTROL

// Primero obtener el id_alumno REAL
$sql = "SELECT id_alumno FROM Alumnos WHERE no_control = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $no_control);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: estudiantes.php?msg=error");
    exit();
}

$row = $result->fetch_assoc();
$id_alumno = $row['id_alumno'];

// Ahora sí eliminar usando id_alumno (ON DELETE CASCADE hará el resto)
$sqlDelete = "DELETE FROM Alumnos WHERE id_alumno = ?";
$stmt2 = $conn->prepare($sqlDelete);
$stmt2->bind_param("i", $id_alumno);

if ($stmt2->execute()) {
    header("Location: estudiantes.php?msg=eliminado");
} else {
    header("Location: estudiantes.php?msg=error");
}

exit();
?>
