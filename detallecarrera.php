<?php
session_start();
include("conexion.php");

// Verificar sesión activa y que sea personal
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

// Obtener id_carrera por GET y validar
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "ID de carrera inválido.";
    exit();
}
$id_carrera = (int)$_GET['id'];

// Procesar formulario de actualización de carrera
$errorMsg = "";
$successMsg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevo_nombre = trim($_POST['carrera']);
    if ($nuevo_nombre === '') {
        $errorMsg = "El nombre de la carrera no puede estar vacío.";
    } else {
        $sqlUpdate = "UPDATE Carreras SET carrera = ? WHERE id_carrera = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param("si", $nuevo_nombre, $id_carrera);
        if ($stmtUpdate->execute()) {
            $successMsg = "Carrera actualizada correctamente.";
            // Actualizar la variable $carrera para reflejar el cambio en la página sin recargar con GET
            $carrera['carrera'] = $nuevo_nombre;
        } else {
            $errorMsg = "Error al actualizar la carrera.";
        }
    }
}

// Obtener datos de la carrera
$sqlCarrera = "SELECT * FROM Carreras WHERE id_carrera = ?";
$stmtCarrera = $conn->prepare($sqlCarrera);
$stmtCarrera->bind_param("i", $id_carrera);
$stmtCarrera->execute();
$resCarrera = $stmtCarrera->get_result();

if ($resCarrera->num_rows === 0) {
    echo "Carrera no encontrada.";
    exit();
}
$carrera = $resCarrera->fetch_assoc();

// Obtener alumnos que estudian esa carrera
$sqlAlumnos = "SELECT * FROM Alumnos WHERE carrera = ? ORDER BY apellido_paterno, apellido_materno, nombre";
$stmtAlumnos = $conn->prepare($sqlAlumnos);
$stmtAlumnos->bind_param("s", $carrera['carrera']);
$stmtAlumnos->execute();
$resAlumnos = $stmtAlumnos->get_result();

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Detalle Carrera - LIBRATEC</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
<style>
    /* Usa estilos similares a detalleautor.php para coherencia */
    * {
        margin: 0; padding: 0; box-sizing: border-box; font-family: "Segoe UI", sans-serif;
    }
    body {
        background-color: #eef3f7;
    }
    .navbar {
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
    .logo {
        font-weight: 700;
        font-size: 1.4rem;
        letter-spacing: 2px;
    }
    .nav-links {
        margin-left: auto;
        display: flex;
        gap: 1rem;
    }
    .nav-links a {
        background: transparent;
        border: 2px solid white;
        border-radius: 6px;
        color: white;
        font-weight: 600;
        padding: 6px 15px;
        text-decoration: none;
        transition: background-color 0.25s, color 0.25s;
    }
    .nav-links a:hover {
        background-color: white;
        color: #0040a0;
    }
    .user-name {
        margin-left: 1.5rem;
        font-weight: 600;
    }

    main.main {
        padding: 90px 20px 20px;
        max-width: 1100px;
        margin: auto;
    }

    h1, h2 {
        color: #002b80;
        margin-bottom: 20px;
    }

    form.update-carrera {
        margin-bottom: 30px;
        max-width: 500px;
    }

    form.update-carrera input[type="text"] {
        width: 100%;
        padding: 10px 12px;
        font-size: 1.1rem;
        border-radius: 6px;
        border: 1px solid #ccc;
        margin-bottom: 10px;
    }

    form.update-carrera button {
        padding: 10px 16px;
        background-color: #0040a0;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.25s;
    }

    form.update-carrera button:hover {
        background-color: #002b80;
    }

    .error-message {
        color: #e43f3f;
        margin-bottom: 15px;
        font-weight: 600;
    }

    .success-message {
        color: #007b3f;
        margin-bottom: 15px;
        font-weight: 600;
    }

    .table-wrapper {
        width: 100%;
        overflow-x: auto;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }

    table {
        border-collapse: collapse;
        width: 100%;
        min-width: 700px;
    }

    th, td {
        white-space: nowrap;
        padding: 12px 15px;
        text-align: center;
        border-bottom: 1px solid #ddd;
    }

    th {
        background-color: #0040a0;
        color: white;
        font-weight: 600;
    }

    tr:hover {
        background-color: #f4f8ff;
    }

    .btn {
        padding: 6px 12px;
        border: none;
        border-radius: 6px;
        color: white;
        font-weight: 600;
        cursor: pointer;
        transition: 0.2s;
        margin: 0 4px;
    }
    .btn-info {
        background: #007bff;
    }
    .btn-info:hover {
        background: #0056b3;
    }

    @media (max-width: 768px) {
        main.main {
            padding: 80px 10px;
        }
        h1, h2 {
            font-size: 1.3rem;
            text-align: center;
        }
        table {
            font-size: 0.9rem;
        }
        th, td {
            padding: 8px;
        }
        .btn {
            padding: 5px 8px;
            font-size: 0.85rem;
        }
        form.update-carrera input[type="text"] {
            font-size: 1rem;
        }
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

<main class="main">

    <h1>Detalle de la Carrera: <?php echo htmlspecialchars($carrera['carrera']); ?></h1>

    <?php if ($successMsg): ?>
        <p class="success-message"><?php echo htmlspecialchars($successMsg); ?></p>
    <?php endif; ?>
    <?php if ($errorMsg): ?>
        <p class="error-message"><?php echo htmlspecialchars($errorMsg); ?></p>
    <?php endif; ?>

    <form method="POST" class="update-carrera" action="detallecarrera.php?id=<?php echo $id_carrera; ?>">
        <label for="carrera"><strong>Editar nombre de la carrera:</strong></label>
        <input type="text" id="carrera" name="carrera" value="<?php echo htmlspecialchars($carrera['carrera']); ?>" required />
        <button type="submit">Actualizar Carrera</button>
    </form>

    <h2>Alumnos inscritos en esta carrera</h2>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>No. Control</th>
                    <th>Nombre Completo</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Semestre</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resAlumnos->num_rows > 0): ?>
                    <?php while ($alumno = $resAlumnos->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($alumno['no_control']); ?></td>
                            <td><?php echo htmlspecialchars($alumno['apellido_paterno'] . " " . $alumno['apellido_materno'] . ", " . $alumno['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($alumno['correo']); ?></td>
                            <td><?php echo htmlspecialchars($alumno['telefono']); ?></td>
                            <td><?php echo htmlspecialchars($alumno['semestre']); ?></td>
                            <td>
                                <button class="btn btn-info" onclick="location.href='detalleestudiante.php?id_alumno=<?php echo $alumno['id_alumno']; ?>'">Ver detalle</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6">No hay alumnos registrados en esta carrera.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>

</body>
</html>
