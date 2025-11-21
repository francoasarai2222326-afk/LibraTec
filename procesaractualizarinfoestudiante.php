<?php
include("conexion.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: miperfilestudiante.php");
    exit();
}

$no_control = $_POST['no_control'];
$nombre = $_POST['nombre'];
$ap_paterno = $_POST['apellido_paterno'];
$ap_materno = $_POST['apellido_materno'];
$correo = $_POST['correo'];
$telefono = $_POST['telefono'];
$carrera = $_POST['carrera'];
$nueva_contra = $_POST['contrasena'];

// Obtener contraseña actual
$sql_pass = "SELECT contrasena FROM Alumnos WHERE no_control = ?";
$stmt_pass = $conn->prepare($sql_pass);
$stmt_pass->bind_param("s", $no_control);
$stmt_pass->execute();
$res_pass = $stmt_pass->get_result();
$data_pass = $res_pass->fetch_assoc();
$contrasena_actual = $data_pass['contrasena'];

// Si la nueva contraseña está vacía → quedarse con la actual
if (empty($nueva_contra)) {
    $contrasena_final = $contrasena_actual;
} else {
    // Crear contraseña hasheada
    $contrasena_final = password_hash($nueva_contra, PASSWORD_BCRYPT);
}

// Actualizar tabla Alumnos
$sql = "UPDATE Alumnos SET 
            nombre = ?, 
            apellido_paterno = ?, 
            apellido_materno = ?, 
            correo = ?, 
            telefono = ?, 
            carrera = ?, 
            contrasena = ?
        WHERE no_control = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssss", $nombre, $ap_paterno, $ap_materno, $correo, $telefono, $carrera, $contrasena_final, $no_control);

if ($stmt->execute()) {
    // Actualizar sesión con el nombre completo nuevo
    $_SESSION['nombre'] = $nombre . " " . $ap_paterno . " " . $ap_materno;

    header("Location: miperfilestudiante.php?msg=actualizado");
    exit();
} else {
    header("Location: miperfilestudiante.php?msg=error");
    exit();
}
?>
