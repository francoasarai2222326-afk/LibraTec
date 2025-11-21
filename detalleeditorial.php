<?php
session_start();
include("conexion.php");

// Verificar sesión activa y que sea personal
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== "personal") {
    header("Location: login.html");
    exit();
}

// Obtener nombre completo del personal logueado para navbar
$no_control = $_SESSION['usuario'];
$sqlPersonal = "SELECT nombre, apellido_paterno, apellido_materno FROM PersonalBiblioteca WHERE no_control = ?";
$stmtPersonal = $conn->prepare($sqlPersonal);
$stmtPersonal->bind_param("s", $no_control);
$stmtPersonal->execute();
$resPersonal = $stmtPersonal->get_result();
$personal = $resPersonal->fetch_assoc();
$nombreCompleto = $personal ? $personal['nombre'] . " " . $personal['apellido_paterno'] . " " . $personal['apellido_materno'] : "Personal";

// Obtener id de la editorial por GET
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "ID de editorial inválido.";
    exit();
}
$id_editorial = (int)$_GET['id'];

// Procesar formulario de actualización de editorial
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevo_nombre = trim($_POST['editorial']);
    if ($nuevo_nombre !== '') {
        $sqlUpdate = "UPDATE Editoriales SET editorial = ? WHERE id_editorial = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param("si", $nuevo_nombre, $id_editorial);
        if ($stmtUpdate->execute()) {
            // Recargar para mostrar cambios
            header("Location: detalleeditorial.php?id=" . $id_editorial . "&msg=actualizado");
            exit();
        } else {
            $errorMsg = "Error al actualizar la editorial.";
        }
    } else {
        $errorMsg = "El nombre de la editorial no puede estar vacío.";
    }
}

// Obtener datos de la editorial
$sqlEditorial = "SELECT * FROM Editoriales WHERE id_editorial = ?";
$stmtEditorial = $conn->prepare($sqlEditorial);
$stmtEditorial->bind_param("i", $id_editorial);
$stmtEditorial->execute();
$resEditorial = $stmtEditorial->get_result();

if ($resEditorial->num_rows === 0) {
    echo "Editorial no encontrada.";
    exit();
}

$editorial = $resEditorial->fetch_assoc();

// Obtener libros de la editorial
$sqlLibros = "SELECT * FROM Libros WHERE editorial = ? ORDER BY titulo ASC";
$stmtLibros = $conn->prepare($sqlLibros);
$stmtLibros->bind_param("s", $editorial['editorial']);
$stmtLibros->execute();
$resLibros = $stmtLibros->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Detalle Editorial - LIBRATEC</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
<style>
    /* Copia los estilos del navbar y tabla del detalleautor.php */
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
        max-width: 1200px;
        margin: auto;
    }

    h1, h2 {
        color: #002b80;
        margin-bottom: 20px;
    }

    form.update-editorial {
        margin-bottom: 30px;
        max-width: 500px;
    }

    form.update-editorial input[type="text"] {
        width: 100%;
        padding: 10px 12px;
        font-size: 1.1rem;
        border-radius: 6px;
        border: 1px solid #ccc;
        margin-bottom: 10px;
    }

    form.update-editorial button {
        padding: 10px 16px;
        background-color: #0040a0;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.25s;
    }

    form.update-editorial button:hover {
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
    .btn-danger {
        background: #e43f3f;
    }
    .btn-info:hover {
        background: #0056b3;
    }
    .btn-danger:hover {
        background: #c70000;
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
        form.update-editorial input[type="text"] {
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

    <h1>Detalle de la Editorial</h1>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'actualizado'): ?>
        <p class="success-message">Editorial actualizada correctamente.</p>
    <?php endif; ?>

    <?php if (!empty($errorMsg)): ?>
        <p class="error-message"><?php echo htmlspecialchars($errorMsg); ?></p>
    <?php endif; ?>

    <form method="POST" class="update-editorial" action="detalleeditorial.php?id=<?php echo $id_editorial; ?>">
        <label for="editorial"><strong>Nombre de la Editorial:</strong></label>
        <input type="text" id="editorial" name="editorial" value="<?php echo htmlspecialchars($editorial['editorial']); ?>" required />
        <button type="submit">Actualizar Editorial</button>
    </form>

    <h2>Libros de la Editorial "<?php echo htmlspecialchars($editorial['editorial']); ?>"</h2>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Autor</th>
                    <th>Año</th>
                    <th>Género</th>
                    <th>ISBN</th>
                    <th>Total</th>
                    <th>Disponibles</th>
                    <th>Prestados</th>
                    <th>Pendientes</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resLibros && $resLibros->num_rows > 0): ?>
                    <?php while ($libro = $resLibros->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($libro['titulo']); ?></td>
                            <td><?php echo htmlspecialchars($libro['autor']); ?></td>
                            <td><?php echo htmlspecialchars($libro['anio_publicacion']); ?></td>
                            <td><?php echo htmlspecialchars($libro['genero']); ?></td>
                            <td><?php echo htmlspecialchars($libro['isbn']); ?></td>
                            <td><?php echo htmlspecialchars($libro['cantidad_total']); ?></td>
                            <td><?php echo htmlspecialchars($libro['cantidad_disponible']); ?></td>
                            <td><?php echo htmlspecialchars($libro['cantidad_prestada']); ?></td>
                            <td><?php echo htmlspecialchars($libro['cantidad_pendiente_devolver']); ?></td>
                            <td>
                                <button class="btn btn-info" onclick="location.href='detallelibro.php?id=<?php echo $libro['id_libro']; ?>'">Ver detalles</button>
                                <button class="btn btn-danger" onclick="if(confirm('¿Seguro que deseas eliminar este libro?')) location.href='eliminarlibro.php?id_libro=<?php echo $libro['id_libro']; ?>'">Eliminar</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="10">Esta editorial no tiene libros registrados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>

</body>
</html>
