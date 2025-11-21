<?php
session_start();
include("conexion.php");

// Verificar sesión y que sea personal bibliotecario
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== "personal") {
    header("Location: login.html");
    exit();
}

// Obtener nombre completo del personal logueado para navbar
$no_control_personal = $_SESSION['usuario'];
$sqlPersonal = "SELECT nombre, apellido_paterno, apellido_materno FROM PersonalBiblioteca WHERE no_control = ?";
$stmtPersonal = $conn->prepare($sqlPersonal);
$stmtPersonal->bind_param("s", $no_control_personal);
$stmtPersonal->execute();
$resPersonal = $stmtPersonal->get_result();
$personal = $resPersonal->fetch_assoc();
$nombreCompleto = $personal ? $personal['nombre'] . " " . $personal['apellido_paterno'] . " " . $personal['apellido_materno'] : "Personal";

// Obtener no_control del personal a mostrar
if (!isset($_GET['no_control'])) {
    echo "Error: No se recibió el identificador del personal.";
    exit();
}

$no_control = $_GET['no_control'];

// Consultar datos del personal bibliotecario
$sql = "SELECT * FROM PersonalBiblioteca WHERE no_control = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $no_control);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Personal bibliotecario no encontrado.";
    exit();
}

$personalDetalle = $result->fetch_assoc();

// Si tienes foto o avatar para personal, aquí puedes agregar lógica para mostrarla
// Por ejemplo, si guardas imagen, ponerla en $foto, sino mostrar icono genérico
$foto = null; // O ruta a foto si tienes campo para eso
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Detalle Personal Bibliotecario - LIBRATEC</title>
<style>
    /* Copia el mismo CSS que usaste para alumnos para mantener estilo */
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
        max-width: 600px;
        margin: 100px auto 40px;
        background: white;
        border-radius: 12px;
        padding: 20px 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    h2 {
        margin-bottom: 20px;
        font-weight: 700;
        text-align: center;
        color: #002b80;
    }
    .card {
        display: flex;
        align-items: center;
        gap: 25px;
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
        margin-bottom: 12px;
        font-size: 1.2rem;
        font-weight: 600;
        color: #0040a0;
    }
</style>
</head>
<body>

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

<div class="container">
    <h2>Detalle del Personal Bibliotecario</h2>

    <div class="card">
        <?php if ($foto): ?>
            <img src="<?php echo htmlspecialchars($foto); ?>" alt="Foto del personal" />
        <?php else: ?>
            <div class="default-icon" aria-label="Sin foto de perfil">
                <svg xmlns="http://www.w3.org/2000/svg" fill="white" viewBox="0 0 24 24" width="60" height="60">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
            </div>
        <?php endif; ?>

        <div class="card-info">
            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($personalDetalle['nombre'] . " " . $personalDetalle['apellido_paterno'] . " " . $personalDetalle['apellido_materno']); ?></p>
            <p><strong>Número de Control:</strong> <?php echo htmlspecialchars($personalDetalle['no_control']); ?></p>
            <p><strong>Correo:</strong> <?php echo htmlspecialchars($personalDetalle['correo']); ?></p>
            <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($personalDetalle['telefono']); ?></p>
        </div>
    </div>

</div>

</body>
</html>
