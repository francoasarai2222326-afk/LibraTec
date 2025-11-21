<?php
include("conexion.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['control'];
    $password = $_POST['password'];
    $tipo = $_POST['tipo_usuario'];

    if ($tipo == "estudiante") {
        $sql = "SELECT * FROM Alumnos WHERE no_control = ?";
    } else {
        $sql = "SELECT * FROM PersonalBiblioteca WHERE no_control = ?";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $data = $res->fetch_assoc();
        $password_db = $data['contrasena'];

        // Verificamos si la contraseña en la BD está hasheada (si comienza con '$2y$' es bcrypt)
        if (strpos($password_db, '$2y$') === 0 || strpos($password_db, '$2a$') === 0 || strpos($password_db, '$2b$') === 0) {
            // Contraseña hasheada, usar password_verify
            $password_correcto = password_verify($password, $password_db);
        } else {
            // Contraseña en texto plano, comparar directamente
            $password_correcto = ($password === $password_db);
        }

        if ($password_correcto) {
            // Login exitoso
            $_SESSION['tipo_usuario'] = $tipo;
            $_SESSION['usuario'] = $data['no_control'];
            $_SESSION['nombre'] = $data['nombre'] . ' ' . $data['apellido_paterno'] . ' ' . $data['apellido_materno'];

            header("Location: catalogo.php");
            exit();
        } else {
            // Contraseña incorrecta
            echo "<script>alert('Usuario o contraseña incorrectos'); window.location='login.php';</script>";
        }
    } else {
        // Usuario no encontrado
        echo "<script>alert('Usuario o contraseña incorrectos'); window.location='login.php';</script>";
    }
}
?>
