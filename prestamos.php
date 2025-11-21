<?php
session_start();
include("conexion.php");

if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== "personal") {
    header("Location: login.html");
    exit();
}

$no_control = $_SESSION['usuario'];
$sqlPersonal = "SELECT nombre, apellido_paterno, apellido_materno FROM PersonalBiblioteca WHERE no_control = ?";
$stmt = $conn->prepare($sqlPersonal);
$stmt->bind_param("s", $no_control);
$stmt->execute();
$res = $stmt->get_result();
$personal = $res->fetch_assoc();
$nombreCompleto = $personal ? $personal['nombre'] . " " . $personal['apellido_paterno'] . " " . $personal['apellido_materno'] : "Personal";

$busqueda = $_GET['busqueda'] ?? "";

// Consulta con múltiples campos para búsqueda
if ($busqueda !== "") {
    $sql = "SELECT p.codigo, a.no_control AS alumno_no_control, 
                   CONCAT(a.nombre, ' ', a.apellido_paterno, ' ', a.apellido_materno) AS alumno_nombre, 
                   l.titulo, l.isbn, p.fecha_prestamo, p.fecha_devolucion, 
                   p.fecha_real_devolucion, p.estado, p.observaciones, 
                   CONCAT(pb.nombre, ' ', pb.apellido_paterno, ' ', pb.apellido_materno) AS atendido_por 
            FROM Prestamos p 
            JOIN Alumnos a ON p.no_control = a.no_control 
            JOIN Libros l ON p.isbn = l.isbn 
            LEFT JOIN PersonalBiblioteca pb ON p.atendido_por = pb.no_control 
            WHERE p.estado = 'activo' AND (
                p.codigo LIKE ? OR 
                a.no_control LIKE ? OR 
                CONCAT(a.nombre, ' ', a.apellido_paterno, ' ', a.apellido_materno) LIKE ? OR 
                l.titulo LIKE ? OR 
                l.isbn LIKE ? OR 
                p.estado LIKE ?
            )
            ORDER BY p.fecha_prestamo DESC";

    $busqueda_param = "%" . $busqueda . "%";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $busqueda_param, $busqueda_param, $busqueda_param, $busqueda_param, $busqueda_param, $busqueda_param);
} else {
    $sql = "SELECT p.codigo, a.no_control AS alumno_no_control, 
                   CONCAT(a.nombre, ' ', a.apellido_paterno, ' ', a.apellido_materno) AS alumno_nombre, 
                   l.titulo, l.isbn, p.fecha_prestamo, p.fecha_devolucion, 
                   p.fecha_real_devolucion, p.estado, p.observaciones, 
                   CONCAT(pb.nombre, ' ', pb.apellido_paterno, ' ', pb.apellido_materno) AS atendido_por 
            FROM Prestamos p 
            JOIN Alumnos a ON p.no_control = a.no_control 
            JOIN Libros l ON p.isbn = l.isbn 
            LEFT JOIN PersonalBiblioteca pb ON p.atendido_por = pb.no_control 
            WHERE p.estado = 'activo'
            ORDER BY p.fecha_prestamo DESC";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Préstamos Activos - LIBRATEC</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
<style>
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
        flex-wrap: wrap;
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
        flex-wrap: wrap;
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

    .main {
        padding: 90px 20px 20px;
        max-width: 1600px;
        margin: auto;
    }

    .top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: 20px;
        gap: 10px;
    }

    h1 {
        color: #002b80;
        margin: 0;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .search-add {
        display: flex;
        gap: 10px;
        flex-wrap: nowrap;
        align-items: center;
        min-width: 250px;
        max-width: 600px;
        flex: 1;
        justify-content: flex-end;
    }

    .search-form {
        display: flex;
        gap: 10px;
        flex: 1;
        min-width: 250px;
    }

    .search-form input[type="text"] {
        flex: 1;
        padding: 10px 12px;
        border-radius: 6px;
        border: 1px solid #ccc;
        font-size: 1rem;
        min-width: 0;
    }

    .search-form button {
        padding: 10px 16px;
        background-color: #0040a0;
        color: white;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.25s;
        white-space: nowrap;
    }

    .search-form button:hover {
        background-color: #002b80;
    }

    .add-loan-btn {
        padding: 10px 18px;
        background-color: #007b3f;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
        transition: background-color 0.25s;
    }

    .add-loan-btn:hover {
        background-color: #005f2e;
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
        width: max-content;
        min-width: 1200px;
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
        padding: 6px 10px;
        border: none;
        border-radius: 6px;
        color: white;
        font-weight: 600;
        cursor: pointer;
        transition: 0.2s;
    }
    .btn-multa {
        background: #e43f3f;
    }
    .btn-devolver {
        background: #007bff;
    }
    .btn-multa:hover {
        background: #c70000;
    }
    .btn-devolver:hover {
        background: #0056b3;
    }

    @media (max-width: 768px) {
        th, td {
            font-size: 0.85rem;
            padding: 8px;
        }
        .table-wrapper {
            border-radius: 0;
        }
        .top-bar {
            flex-direction: column;
            align-items: stretch;
        }
        h1 {
            text-align: center;
            margin-bottom: 10px;
        }
        .search-add {
            justify-content: center;
            max-width: 100%;
            flex-wrap: wrap;
        }
        .search-form {
            flex-direction: column;
            width: 100%;
        }
        .search-form input[type="text"],
        .search-form button {
            width: 100%;
            margin-bottom: 8px;
        }
        .add-loan-btn {
            width: 100%;
            margin: 0;
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
  <div class="top-bar">
    <h1>Préstamos Activos</h1>

    <div class="search-add">
      <form class="search-form" method="GET" action="prestamos.php">
          <input 
            type="text" 
            name="busqueda" 
            placeholder="Buscar por código, no_control, alumno, título, ISBN o estado..." 
            value="<?php echo htmlspecialchars($busqueda); ?>"
            autocomplete="off"
          />
          <button type="submit"><i class="fa fa-search"></i> Buscar</button>
      </form>

      <button class="add-loan-btn" onclick="location.href='añadirprestamo.php'">
        <i class="fa fa-plus"></i> Añadir Préstamo
      </button>
    </div>
  </div>

  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Código</th>
          <th>No. Control</th>
          <th>Alumno</th>
          <th>Título</th>
          <th>ISBN</th>
          <th>Fecha Préstamo</th>
          <th>Fecha Devolución</th>
          <th>Fecha Real Devolución</th>
          <th>Estado</th>
          <th>Observaciones</th>
          <th>Atendido Por</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?php echo htmlspecialchars($row['codigo']); ?></td>
              <td><?php echo htmlspecialchars($row['alumno_no_control']); ?></td>
              <td><?php echo htmlspecialchars($row['alumno_nombre']); ?></td>
              <td><?php echo htmlspecialchars($row['titulo']); ?></td>
              <td><?php echo htmlspecialchars($row['isbn']); ?></td>
              <td><?php echo htmlspecialchars($row['fecha_prestamo']); ?></td>
              <td><?php echo htmlspecialchars($row['fecha_devolucion']); ?></td>
              <td><?php echo $row['fecha_real_devolucion'] ? htmlspecialchars($row['fecha_real_devolucion']) : '-'; ?></td>
              <td><?php echo ucfirst(htmlspecialchars($row['estado'])); ?></td>
              <td><?php echo $row['observaciones'] ? htmlspecialchars($row['observaciones']) : '-'; ?></td>
              <td><?php echo htmlspecialchars($row['atendido_por']); ?></td>
              <td>
                <button class="btn btn-devolver" onclick="location.href='devolver.php?codigo=<?php echo urlencode($row['codigo']); ?>'">Devolver</button>
                <form action="asignarmulta.php" method="POST" style="display:inline;">
                  <input type="hidden" name="codigo" value="<?php echo htmlspecialchars($row['codigo']); ?>">
                  <input type="hidden" name="no_control" value="<?php echo htmlspecialchars($row['alumno_no_control']); ?>">
                  <button type="submit" class="btn btn-multa">Asignar Multa</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="12">No hay préstamos activos registrados.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>

</body>
</html>
