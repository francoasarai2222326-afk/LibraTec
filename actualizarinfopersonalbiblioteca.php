<?php
session_start();
include("conexion.php");

// Verificar sesión
if (!isset($_SESSION['usuario']) || $_SESSION['tipo_usuario'] !== "personal") {
    header("Location: login.html");
    exit();
}

$no_control = $_SESSION['usuario'];

// Obtener nombre del usuario (para navbar)
$sqlNombre = "SELECT nombre, apellido_paterno, apellido_materno FROM PersonalBiblioteca WHERE no_control = ?";
$stmtNombre = $conn->prepare($sqlNombre);
$stmtNombre->bind_param("s", $no_control);
$stmtNombre->execute();
$resNom = $stmtNombre->get_result();
$dataNom = $resNom->fetch_assoc();
$nombreCompleto = $dataNom['nombre'] . " " . $dataNom['apellido_paterno'] . " " . $dataNom['apellido_materno'];

// Obtener datos del personal para el formulario
$sql = "SELECT * FROM PersonalBiblioteca WHERE no_control = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $no_control);
$stmt->execute();
$res = $stmt->get_result();
$personal = $res->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Actualizar Información - Personal Biblioteca</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Segoe UI", sans-serif; }

    body { background-color: #eef3f7; }

    /* NAVBAR */
    .navbar {
        position: fixed; top: 0; left: 0; right: 0;
        background: linear-gradient(90deg, #0040a0, #002b80);
        color: white; display: flex; align-items: center;
        padding: 10px 20px; z-index: 9999;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        flex-wrap: wrap;
    }
    .navbar .logo { font-weight: 700; font-size: 1.4rem; letter-spacing: 2px; }
    .navbar .nav-links { margin-left: auto; display: flex; gap: 1rem; flex-wrap: wrap; }
    .navbar .nav-links a {
        background: transparent; border: 2px solid white; border-radius: 6px;
        color: white; font-weight: 600; padding: 6px 15px; text-decoration: none;
        transition: .25s;
    }
    .navbar .nav-links a:hover { background: white; color: #0040a0; }
    .navbar .user-name { margin-left: 1.5rem; font-weight: 600; }

    .main { padding: 100px 20px; max-width: 600px; margin: auto; }

    form {
        background: #fff;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 2px 7px rgba(0,0,0,0.15);
    }
    form h2 {
        color: #002b80; text-align: center; margin-bottom: 1.5rem;
    }

    label { font-weight: 600; margin-top: 10px; display: block; }
    input {
        width: 100%; padding: 10px;
        border-radius: 6px; border: 1px solid #ccc; margin-top: 5px;
    }

    button {
        margin-top: 25px; width: 100%;
        padding: 12px; border: none; border-radius: 8px;
        background: #007bff; color: white; font-weight: 600;
        cursor: pointer; transition: .3s;
    }
    button:hover { background: #0056b3; }

    a.back {
        display: block; text-align: center;
        margin-top: 15px; text-decoration: none;
        color: #0040a0; font-weight: 700;
    }
</style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar">
  <div class="logo">LIBRATEC</div>
  <div class="nav-links">
    <a href="catalogo.php">Catálogo</a>
    <a href="estudiantes.php">Estudiantes</a>
    <a href="personalbiblioteca.php">Personal Bibliotecario</a>
    <a href="registros.php">Registros</a>
    <a href="prestamos.php">Préstamos</a>
    <a href="multas.php">Multas</a>
     <a href="miperfilpersonalbiblioteca.php">Mi Perfil</a>
    <a href="home.php">Salir</a>
  </div>
  <div class="user-name"><?php echo htmlspecialchars($nombreCompleto); ?></div>
</nav>

<main class="main">

<form action="procesaractualizarinfopersonalbiblioteca.php" method="POST">

    <h2>Actualizar Información</h2>

    <input type="hidden" name="no_control" value="<?php echo htmlspecialchars($personal['no_control']); ?>">

    <label>Nombre</label>
    <input type="text" name="nombre" value="<?php echo htmlspecialchars($personal['nombre']); ?>" required>

    <label>Apellido Paterno</label>
    <input type="text" name="apellido_paterno" value="<?php echo htmlspecialchars($personal['apellido_paterno']); ?>" required>

    <label>Apellido Materno</label>
    <input type="text" name="apellido_materno" value="<?php echo htmlspecialchars($personal['apellido_materno']); ?>" required>

    <label>Correo</label>
    <input type="email" name="correo" value="<?php echo htmlspecialchars($personal['correo']); ?>" required>

    <label>Teléfono</label>
    <input type="text" name="telefono" value="<?php echo htmlspecialchars($personal['telefono']); ?>" required>

    <label>Contraseña (dejar en blanco si no desea cambiar)</label>
    <input type="password" name="contrasena">

    <button type="submit">Guardar Cambios</button>
</form>

<a href="miperfilpersonalbiblioteca.php" class="back">← Regresar a mi perfil</a>

</main>

</body>
</html>
