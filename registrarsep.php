<?php
include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $apellido_paterno = $_POST['apellido_paterno'];
    $apellido_materno = $_POST['apellido_materno'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'] ?? NULL;
    $no_control = $_POST['no_control'];
    $contrasena = $_POST['contrasena'];

    // Validar que no existan duplicados de correo o no_control
    $check = $conn->prepare("SELECT * FROM PersonalBiblioteca WHERE correo = ? OR no_control = ?");
    $check->bind_param("ss", $correo, $no_control);
    $check->execute();
    $result = $check->get_result();
    if ($result->num_rows > 0) {
        echo "Error: El correo o número de control ya están registrados.";
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO PersonalBiblioteca (nombre, apellido_paterno, apellido_materno, correo, telefono, no_control, contrasena) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $nombre, $apellido_paterno, $apellido_materno, $correo, $telefono, $no_control, $contrasena);

    if ($stmt->execute()) {
        header("Location: login.php");
        exit;
    } else {
        echo "Error al registrar: " . $conn->error;
    }
}
?>
