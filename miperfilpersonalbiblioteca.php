<?php
session_start();
include("conexion.php");

// Verificar sesión y que sea personal bibliotecario
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== "personal") {
    header("Location: login.php");
    exit();
}

$no_control = $_SESSION['usuario'];
$tipo_usuario = $_SESSION['tipo_usuario'];

// Obtener nombre completo para navbar desde PersonalBiblioteca
$sql = "SELECT nombre, apellido_paterno, apellido_materno FROM PersonalBiblioteca WHERE no_control = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $no_control);
$stmt->execute();
$resultado = $stmt->get_result();

$nombreCompleto = "";
if ($resultado->num_rows > 0) {
    $datos = $resultado->fetch_assoc();
    $nombreCompleto = $datos['nombre'] . " " . $datos['apellido_paterno'] . " " . $datos['apellido_materno'];
}

// Obtener datos completos del personal para mostrar en la tarjeta
$sqlPersonal = "SELECT * FROM PersonalBiblioteca WHERE no_control = ? LIMIT 1";
$stmtPersonal = $conn->prepare($sqlPersonal);
$stmtPersonal->bind_param("s", $no_control);
$stmtPersonal->execute();
$resultPersonal = $stmtPersonal->get_result();

if ($resultPersonal->num_rows == 0) {
    echo "Personal bibliotecario no encontrado.";
    exit();
}

$personal = $resultPersonal->fetch_assoc();
$foto = (!empty($personal['foto'])) ? $personal['foto'] : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Mi Perfil - LIBRATEC</title>
<style>
/* --- Copiar el CSS tal cual del ejemplo que diste --- */
* {
    margin: 0; padding: 0; box-sizing: border-box;
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
}
body {
    background-color: #eef3f7;
    padding: 20px;
    color: #002b80;
}
nav.navbar {
    position: fixed;
    top: 0; left: 0; right: 0;
    background: linear-gradient(90deg, #0040a0, #002b80);
    color: white;
    display: flex;
    align-items: center;
    padding: 10px 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    z-index: 1000;
}
nav .logo {
    font-weight: 700;
    font-size: 1.4rem;
    letter-spacing: 2px;
}
nav .nav-links {
    margin-left: auto;
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}
nav .nav-links a {
    background: transparent;
    border: 2px solid white;
    border-radius: 6px;
    color: white;
    font-weight: 600;
    padding: 6px 15px;
    text-decoration: none;
    transition: .25s;
}
nav .nav-links a:hover {
    background-color: white;
    color: #0040a0;
}
nav .user-name {
    margin-left: 1.5rem;
    font-weight: 600;
}

.container {
    max-width: 900px;
    margin: 100px auto 40px;
    background: white;
    border-radius: 12px;
    padding: 20px 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
h2 {
    margin-bottom: 15px;
    font-weight: 700;
}
.card {
    display: flex;
    align-items: center;
    gap: 25px;
    margin-bottom: 30px;
    background: #f9f9f9;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 0 10px #bbb;
}
.card img, .card .default-icon {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #0040a0;
    flex-shrink: 0;
}
.card .default-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #c4c4c4;
    color: white;
    font-size: 60px;
    user-select: none;
}
.card-info p {
    margin-bottom: 8px;
    font-size: 1.1rem;
}

/* botón actualizar (estilo integrado, discreto y consistente con la UI) */
.update-btn {
    display: inline-block;
    margin-top: 12px;
    padding: 10px 16px;
    background: #ffc107;
    color: #000;
    border-radius: 8px;
    font-weight: 700;
    text-decoration: none;
    border: 0;
    cursor: pointer;
    transition: background .18s;
}
.update-btn:hover { background: #ffb000; }
@media (max-width: 768px) {
    .card {
        flex-direction: column;
        text-align: center;
    }
    .card img, .card .default-icon {
        margin-bottom: 15px;
    }
    body {
        padding: 10px;
    }
    .container {
        margin-top: 120px;
        padding: 15px 20px;
    }
    nav.navbar {
        flex-wrap: wrap;
    }
    nav .nav-links {
        margin: 10px 0 0;
        width: 100%;
        justify-content: center;
    }
    nav .user-name {
        margin-left: 0;
        width: 100%;
        text-align: center;
        margin-top: 10px;
        font-size: 1.1rem;
    }
}
</style>
</head>
<body>

<nav class="navbar">
  <div class="logo">LIBRATEC</div>
  <div class="nav-links">
    <?php if ($tipo_usuario === "estudiante"): ?>
      <a href="catalogo.php">Catálogo</a>
      <a href="misprestamos.php">Mis Préstamos</a>
      <a href="mismultas.php">Mis Multas</a>
      <a href="miperfilestudiante.php">Mi Perfil</a>
      <a href="home.php">Salir</a>
    <?php elseif ($tipo_usuario === "personal"): ?>
      <a href="catalogo.php">Catálogo</a>
      <a href="estudiantes.php">Estudiantes</a>
      <a href="personalbiblioteca.php">Personal Bibliotecario</a>
      <a href="registros.php">Registros</a>
      <a href="prestamos.php">Préstamos</a>
      <a href="multas.php">Multas</a>
      <a href="miperfilpersonalbiblioteca.php">Mi Perfil</a>
      <a href="home.php">Salir</a>
    <?php endif; ?>
  </div>
  <div class="user-name"><?php echo htmlspecialchars($nombreCompleto); ?></div>
</nav>

<div class="container">
    <h2>Mi Perfil</h2>

    <div class="card">
        <?php if ($foto): ?>
            <img src="<?php echo htmlspecialchars($foto); ?>" alt="Foto del personal bibliotecario" />
        <?php else: ?>
            <div class="default-icon" aria-label="Sin foto de perfil">
                <svg xmlns="http://www.w3.org/2000/svg" fill="white" viewBox="0 0 24 24" width="60" height="60">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
            </div>
        <?php endif; ?>

        <div class="card-info">
            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($personal['nombre'] . " " . $personal['apellido_paterno'] . " " . $personal['apellido_materno']); ?></p>
            <p><strong>Número de Control:</strong> <?php echo htmlspecialchars($personal['no_control']); ?></p>
            <p><strong>Correo:</strong> <?php echo htmlspecialchars($personal['correo']); ?></p>
            <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($personal['telefono']); ?></p>

            <a href="actualizarinfopersonalbiblioteca.php" class="update-btn" role="button">Actualizar información</a>
        </div>
    </div>

</div>

</body>
</html>
