<?php
session_start();
include("conexion.php");

// Verificar sesión y que sea estudiante
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== "estudiante") {
    header("Location: login.php");
    exit();
}

$no_control = $_SESSION['usuario'];

// Consultar datos del alumno
$sqlAlumno = "SELECT * FROM Alumnos WHERE no_control = ? LIMIT 1";
$stmtAlumno = $conn->prepare($sqlAlumno);
$stmtAlumno->bind_param("s", $no_control);
$stmtAlumno->execute();
$resultAlumno = $stmtAlumno->get_result();

if ($resultAlumno->num_rows == 0) {
    echo "Alumno no encontrado.";
    exit();
}

$alumno = $resultAlumno->fetch_assoc();
$foto = (!empty($alumno['foto'])) ? $alumno['foto'] : null;

// Consultar préstamos del alumno
$sqlPrestamos = "
    SELECT p.codigo, l.titulo, p.fecha_prestamo, p.fecha_devolucion, 
           p.fecha_real_devolucion, p.estado, p.observaciones
    FROM prestamos p
    INNER JOIN libros l ON p.isbn = l.isbn
    WHERE p.no_control = ?
";
$stmtPrestamos = $conn->prepare($sqlPrestamos);
$stmtPrestamos->bind_param("s", $no_control);
$stmtPrestamos->execute();
$resPrestamos = $stmtPrestamos->get_result();

// Consultar multas del alumno
$sqlMultas = "
    SELECT 
        m.codigo, l.titulo, l.isbn,
        p.fecha_prestamo, p.fecha_devolucion, p.fecha_real_devolucion, 
        p.estado AS estado_prestamo, p.observaciones AS observaciones_prestamo,
        m.monto, m.motivo, m.pagada
    FROM multas m
    INNER JOIN prestamos p ON m.codigo = p.codigo
    INNER JOIN libros l ON p.isbn = l.isbn
    WHERE m.no_control = ?
";
$stmtMultas = $conn->prepare($sqlMultas);
$stmtMultas->bind_param("s", $no_control);
$stmtMultas->execute();
$resMultas = $stmtMultas->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Mi Perfil - LIBRATEC</title>

<style>
<?php
// COPIAMOS TODO EL CSS DEL ARCHIVO ORIGINAL SIN CAMBIAR ABSOLUTAMENTE NADA
?>

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
h2, h3 {
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

/* Contenedor para scroll horizontal */
.table-wrapper {
    overflow-x: auto;
    width: 100%;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    background: white;
    margin-bottom: 30px;
}

table {
    border-collapse: collapse;
    width: 100%;
    min-width: 900px;
    table-layout: fixed;
    font-size: 0.95rem;
}
th, td {
    border: 1px solid #ddd;
    padding: 12px 15px;
    text-align: left;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 140px;
}
th {
    background-color: #0040a0;
    color: white;
    font-weight: 700;
}
tr:nth-child(even) {
    background-color: #f4f8ff;
}
tr:hover {
    background-color: #dbe9ff;
}

@media (max-width: 768px) {
    .card {
        flex-direction: column;
        text-align: center;
    }
    .card img, .card .default-icon {
        margin-bottom: 15px;
    }
    th, td {
        font-size: 0.85rem;
        padding: 8px 10px;
        max-width: none;
        white-space: normal;
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
    table {
        min-width: 100%;
    }
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
  <div class="user-name">
    <?php echo htmlspecialchars($alumno['nombre'] . " " . $alumno['apellido_paterno'] . " " . $alumno['apellido_materno']); ?>
  </div>
</nav>

<div class="container">
    <h2>Mi Perfil</h2>

    <div class="card">
        <?php if ($foto): ?>
            <img src="<?php echo htmlspecialchars($foto); ?>" alt="Foto del alumno">
        <?php else: ?>
            <div class="default-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="white" viewBox="0 0 24 24" width="60" height="60">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
            </div>
        <?php endif; ?>

        <div class="card-info">
            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($alumno['nombre'] . " " . $alumno['apellido_paterno'] . " " . $alumno['apellido_materno']); ?></p>
            <p><strong>Número de Control:</strong> <?php echo htmlspecialchars($alumno['no_control']); ?></p>
            <p><strong>Correo:</strong> <?php echo htmlspecialchars($alumno['correo']); ?></p>
            <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($alumno['telefono']); ?></p>
            <p><strong>Semestre:</strong> <?php echo htmlspecialchars($alumno['semestre']); ?></p>
            <p><strong>Carrera:</strong> <?php echo htmlspecialchars($alumno['carrera']); ?></p>

            <!-- BOTÓN AGREGADO: actualizar información -->
            <a href="actualizarinfoestudiante.php" class="update-btn" role="button">Actualizar información</a>
        </div>
    </div>

    <hr>

    <h3>Préstamos</h3>
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
                <?php if ($resPrestamos->num_rows > 0): ?>
                    <?php while ($row = $resPrestamos->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['codigo']); ?></td>
                            <td><?php echo htmlspecialchars($row['titulo']); ?></td>
                            <td><?php echo htmlspecialchars($row['fecha_prestamo']); ?></td>
                            <td><?php echo htmlspecialchars($row['fecha_devolucion']); ?></td>
                            <td><?php echo htmlspecialchars($row['fecha_real_devolucion'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($row['estado']); ?></td>
                            <td><?php echo htmlspecialchars($row['observaciones']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7">No tiene préstamos</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <hr>

    <h3>Multas</h3>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Título</th>
                    <th>ISBN</th>
                    <th>Fecha Préstamo</th>
                    <th>Fecha Devolución</th>
                    <th>Fecha Real Devolución</th>
                    <th>Estado Préstamo</th>
                    <th>Observaciones Préstamo</th>
                    <th>Monto</th>
                    <th>Motivo</th>
                    <th>Pagada</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resMultas->num_rows > 0): ?>
                    <?php while ($row = $resMultas->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['codigo']); ?></td>
                            <td><?php echo htmlspecialchars($row['titulo']); ?></td>
                            <td><?php echo htmlspecialchars($row['isbn']); ?></td>
                            <td><?php echo htmlspecialchars($row['fecha_prestamo']); ?></td>
                            <td><?php echo htmlspecialchars($row['fecha_devolucion']); ?></td>
                            <td><?php echo htmlspecialchars($row['fecha_real_devolucion'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($row['estado_prestamo']); ?></td>
                            <td><?php echo htmlspecialchars($row['observaciones_prestamo']); ?></td>
                            <td>$<?php echo htmlspecialchars($row['monto']); ?></td>
                            <td><?php echo htmlspecialchars($row['motivo']); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($row['pagada'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="11">Sin multas</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>
