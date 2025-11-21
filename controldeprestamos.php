<?php
session_start();
include("conexion.php");

// Validar sesión
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== "personal") {
    header("Location: login.html");
    exit();
}

// Validar libro
if (!isset($_GET['isbn'])) {
    echo "<p style='color:red; font-weight:bold;'>Libro no especificado.</p>";
    exit();
}

$isbn = $_GET['isbn'];

// Obtener el nombre del personal logueado
$no_control = $_SESSION['usuario'];
$sqlPersonal = "SELECT nombre, apellido_paterno, apellido_materno FROM PersonalBiblioteca WHERE no_control = ?";
$stmt = $conn->prepare($sqlPersonal);
$stmt->bind_param("s", $no_control);
$stmt->execute();
$res = $stmt->get_result();
$personal = $res->fetch_assoc();
$nombreCompleto = $personal ? $personal['nombre'] . " " . $personal['apellido_paterno'] . " " . $personal['apellido_materno'] : "Personal";

// Consultar préstamos del libro seleccionado
$sql = "SELECT 
            p.codigo, 
            p.no_control, 
            CONCAT(a.nombre, ' ', a.apellido_paterno, ' ', a.apellido_materno) AS alumno_nombre,
            l.titulo, 
            l.isbn, 
            p.fecha_prestamo, 
            p.fecha_devolucion, 
            p.fecha_real_devolucion, 
            p.estado, 
            p.observaciones,
            CONCAT(pb.nombre, ' ', pb.apellido_paterno, ' ', pb.apellido_materno) AS atendido_por
        FROM Prestamos p
        JOIN Alumnos a ON p.no_control = a.no_control
        JOIN Libros l ON p.isbn = l.isbn
LEFT JOIN PersonalBiblioteca pb ON p.atendido_por = pb.no_control
        WHERE p.isbn = ?
        ORDER BY p.fecha_prestamo DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $isbn);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Control de Préstamos - LibraTec</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Segoe UI", sans-serif; }
        body { background-color: #eef3f7; }

        /* Navbar */
        .navbar {
            position: fixed; top: 0; left: 0; right: 0;
            background: linear-gradient(90deg, #0040a0, #002b80);
            color: white; display: flex; align-items: center;
            padding: 10px 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 1000;
        }
        .navbar .logo { font-weight: 700; font-size: 1.4rem; letter-spacing: 2px; }
        .navbar .nav-links { margin-left: auto; display: flex; gap: 1rem; }
        .navbar .nav-links a {
            background: transparent; border: 2px solid white; border-radius: 6px;
            color: white; font-weight: 600; padding: 6px 15px; text-decoration: none;
            transition: background-color 0.25s, color 0.25s;
        }
        .navbar .nav-links a:hover { background-color: white; color: #0040a0; }
        .navbar .user-name { margin-left: 1.5rem; font-weight: 600; }

        .main { padding: 90px 20px 20px; max-width: 1600px; margin: auto; }
        h1 { color: #002b80; margin-bottom: 20px; }

        /* Tabla */
        .table-wrapper {
            width: 100%; overflow-x: auto; background: white;
            border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        table { border-collapse: collapse; width: max-content; min-width: 1200px; }
        th, td { white-space: nowrap; padding: 12px 15px; text-align: center; border-bottom: 1px solid #ddd; }
        th { background-color: #0040a0; color: white; font-weight: 600; }
        tr:hover { background-color: #f4f8ff; }

        /* Botones */
        .btn {
            padding: 6px 10px; border: none; border-radius: 6px; color: white;
            font-weight: 600; cursor: pointer; transition: 0.2s;
        }
        .btn-multa { background: #e43f3f; }
        .btn-devolver { background: #007bff; }
        .btn-multa:hover { background: #c70000; }
        .btn-devolver:hover { background: #0056b3; }

        @media (max-width: 768px) {
            th, td { font-size: 0.85rem; padding: 8px; }
            .table-wrapper { border-radius: 0; }
            h1 { font-size: 1.3rem; text-align: center; }
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
    <div class="user-name"><?php echo $nombreCompleto; ?></div>
</nav>

<main class="main">
    <h1>Control de Préstamos del Libro</h1>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>No. Control</th>
                    <th>Alumno</th>
                    <th>Título</th>
                    <th>ISBN</th>
                    <th>Fecha del Préstamo</th>
                    <th>Fecha de Devolución</th>
                    <th>Fecha Real de Devolución</th>
                    <th>Estado</th>
                    <th>Observaciones</th>
                    <th>Atendido Por</th>
                    <th> <th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['codigo']; ?></td>
                            <td><?php echo $row['no_control']; ?></td>
                            <td><?php echo $row['alumno_nombre']; ?></td>
                            <td><?php echo $row['titulo']; ?></td>
                            <td><?php echo $row['isbn']; ?></td>
                            <td><?php echo $row['fecha_prestamo']; ?></td>
                            <td><?php echo $row['fecha_devolucion']; ?></td>
                            <td><?php echo $row['fecha_real_devolucion'] ?: '-'; ?></td>
                            <td><?php echo ucfirst($row['estado']); ?></td>
                            <td><?php echo $row['observaciones'] ?: '-'; ?></td>
                            <td><?php echo $row['atendido_por']; ?></td>
                            <td>
                                <?php if ($row['estado'] === 'vencido'): ?>
                                    <form action="asignarmulta.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="codigo" value="<?php echo $row['codigo']; ?>">
                                        <input type="hidden" name="no_control" value="<?php echo $row['no_control']; ?>">
                                        <button type="submit" class="btn btn-multa">Asignar Multa</button>
                                    </form>
                                    <button class="btn btn-devolver" onclick="location.href='devolver.php?codigo=<?php echo $row['codigo']; ?>'">Devolver</button>
                                <?php elseif ($row['estado'] === 'activo'): ?>
                                    <button class="btn btn-devolver" onclick="location.href='devolver.php?codigo=<?php echo $row['codigo']; ?>'">Devolver</button>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="12">No hay préstamos registrados para este libro.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>

