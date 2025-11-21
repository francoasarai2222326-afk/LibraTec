<?php
session_start();
include("conexion.php");

if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== "estudiante") {
    header("Location: login.html");
    exit();
}

$no_control = $_SESSION['usuario'];

$sqlAlumno = "SELECT nombre, apellido_paterno, apellido_materno FROM Alumnos WHERE no_control = ?";
$stmt = $conn->prepare($sqlAlumno);
$stmt->bind_param("s", $no_control);
$stmt->execute();
$res = $stmt->get_result();
$alumno = $res->fetch_assoc();
$nombreCompleto = $alumno ? $alumno['nombre'] . " " . $alumno['apellido_paterno'] . " " . $alumno['apellido_materno'] : "Alumno";

$sql = "SELECT p.codigo, l.titulo, p.fecha_prestamo, p.fecha_devolucion, p.fecha_real_devolucion, p.estado, IFNULL(p.observaciones, '-') AS observaciones
        FROM Prestamos p
        JOIN Libros l ON p.isbn = l.isbn
        WHERE p.no_control = ?
        ORDER BY p.fecha_prestamo DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $no_control);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Mis Préstamos - LIBRATEC</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Segoe UI", sans-serif; }
    body { background-color: #eef3f7; }
    .navbar {
        position: fixed; top: 0; left: 0; right: 0;
        background: linear-gradient(90deg, #0040a0, #002b80);
        color: white; display: flex; align-items: center;
        padding: 10px 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        z-index: 1000;
    }
    .logo { font-weight: 700; font-size: 1.4rem; letter-spacing: 2px; }
    .nav-links { margin-left: auto; display: flex; gap: 1rem; }
    .nav-links a {
        background: transparent; border: 2px solid white; border-radius: 6px;
        color: white; font-weight: 600; padding: 6px 15px; text-decoration: none;
        transition: background-color 0.25s, color 0.25s;
    }
    .nav-links a:hover { background-color: white; color: #0040a0; }
    .user-name { margin-left: 1.5rem; font-weight: 600; }
    .main { padding: 90px 20px 20px; max-width: 1600px; margin: auto; }
    h1 { color: #002b80; margin-bottom: 20px; }

    .table-wrapper {
        width: 100%;
        overflow-x: auto;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        /* padding removed as requested */
    }
    table {
        width: 100%;
        border-collapse: collapse;
        text-align: center;
    }
    th, td {
        white-space: nowrap;
        padding: 12px 15px;
        border-bottom: 1px solid #ddd;
    }
    th {
        background-color: #0040a0;
        color: white;
        font-weight: 600;
    }
    tr:hover { background-color: #f4f8ff; }
    tbody tr:last-child td { border-bottom: none; }
    @media (max-width: 768px) {
        th, td { font-size: 0.85rem; padding: 8px; }
        .table-wrapper { border-radius: 0; }
        h1 { font-size: 1.3rem; }
    }
</style>
</head>
<body>

<nav class="navbar">
  <div class="logo">LIBRATEC</div>
  <div class="nav-links">
    <a href="catalogo.php">Catálogo</a>
    <a href="misprestamos.php">Mis Préstamos</a>
    <a href="mismultas.php">Mis Multas</a>
    <a href="miperfilestudiante.php">Mi Perfil</a>
    <a href="home.php">Salir</a>
  </div>
  <div class="user-name"><?php echo htmlspecialchars($nombreCompleto); ?></div>
</nav>

<main class="main">
    <h1>Mis Préstamos</h1>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Título</th>
                    <th>Fecha Préstamo</th>
                    <th>Fecha Devolución</th>
                    <th>Fecha Real Devolución</th>
                    <th>Estado</th>
                    <th>Observaciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['codigo']); ?></td>
                            <td><?php echo htmlspecialchars($row['titulo']); ?></td>
                            <td><?php echo htmlspecialchars($row['fecha_prestamo']); ?></td>
                            <td><?php echo htmlspecialchars($row['fecha_devolucion']); ?></td>
                            <td><?php echo $row['fecha_real_devolucion'] ? htmlspecialchars($row['fecha_real_devolucion']) : '-'; ?></td>
                            <td><?php echo ucfirst(htmlspecialchars($row['estado'])); ?></td>
                            <td><?php echo htmlspecialchars($row['observaciones']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7">No tienes préstamos registrados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

</body>
</html>

