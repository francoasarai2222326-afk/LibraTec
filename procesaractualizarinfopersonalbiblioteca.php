<?php
include("conexion.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: miperfilpersonalbiblioteca.php");
    exit();
}

$no_control = $_POST['no_control'];
$nombre = $_POST['nombre'];
$ap_paterno = $_POST['apellido_paterno'];
$ap_materno = $_POST['apellido_materno'];
$correo = $_POST['correo'];
$telefono = $_POST['telefono'];
$nueva_contra = $_POST['contrasena'];

// Obtener contraseña actual
$sql_pass = "SELECT contrasena FROM PersonalBiblioteca WHERE no_control = ?";
$stmt_pass = $conn->prepare($sql_pass);
$stmt_pass->bind_param("s", $no_control);
$stmt_pass->execute();
$res_pass = $stmt_pass->get_result();

if ($res_pass->num_rows === 0) {
    // No se encontró usuario
    header("Location: miperfilpersonalbiblioteca.php?msg=error");
    exit();
}

$data_pass = $res_pass->fetch_assoc();
$contrasena_actual = $data_pass['contrasena'];

// Si la nueva contraseña está vacía → quedarse con la actual
if (empty($nueva_contra)) {
    $contrasena_final = $contrasena_actual;
} else {
    // Crear contraseña hasheada
    $contrasena_final = password_hash($nueva_contra, PASSWORD_BCRYPT);
}

// Actualizar tabla PersonalBiblioteca
$sql = "UPDATE PersonalBiblioteca SET 
            nombre = ?, 
            apellido_paterno = ?, 
            apellido_materno = ?, 
            correo = ?, 
            telefono = ?, 
            contrasena = ?
        WHERE no_control = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssss", $nombre, $ap_paterno, $ap_materno, $correo, $telefono, $contrasena_final, $no_control);

if ($stmt->execute()) {
    // Actualizar sesión con el nombre completo nuevo (opcional)
    $_SESSION['nombre'] = $nombre . " " . $ap_paterno . " " . $ap_materno;

    header("Location: miperfilpersonalbiblioteca.php?msg=actualizado");
    exit();
} else {
    header("Location: miperfilpersonalbiblioteca.php?msg=error");
    exit();
}
