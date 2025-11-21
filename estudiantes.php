<?php
session_start();
include("conexion.php");

// Verificar sesión activa y que sea personal
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== "personal") {
    header("Location: login.html");
    exit();
}

// Obtener nombre completo del personal logueado
$no_control = $_SESSION['usuario'];
$sqlPersonal = "SELECT nombre, apellido_paterno, apellido_materno FROM PersonalBiblioteca WHERE no_control = ?";
$stmt = $conn->prepare($sqlPersonal);
$stmt->bind_param("s", $no_control);
$stmt->execute();
$res = $stmt->get_result();
$personal = $res->fetch_assoc();
$nombreCompleto = $personal ? $personal['nombre'] . " " . $personal['apellido_paterno'] . " " . $personal['apellido_materno'] : "Personal";

// Obtener término de búsqueda
$busqueda = $_GET['busqueda'] ?? "";

// Consulta de alumnos
if ($busqueda !== "") {
    $sql = "SELECT * FROM Alumnos
            WHERE nombre LIKE ?
               OR apellido_paterno LIKE ?
               OR apellido_materno LIKE ?
               OR correo LIKE ?
               OR no_control LIKE ?
               OR carrera LIKE ?
            ORDER BY nombre ASC";
    $busqueda_param = "%" . $busqueda . "%";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $busqueda_param, $busqueda_param, $busqueda_param, $busqueda_param, $busqueda_param, $busqueda_param);
} else {
    $sql = "SELECT * FROM Alumnos ORDER BY nombre ASC";
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
<title>Estudiantes - LIBRATEC</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Segoe UI", sans-serif; }
    body { background-color: #eef3f7; }
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
    .logo { font-weight: 700; font-size: 1.4rem; letter-spacing: 2px; }
    .nav-links { margin-left: auto; display: flex; gap: 1rem; }
    .nav-links a {
        background: transparent;
        border: 2px solid white;
        border-radius: 6px;
        color: white;
        font-weight: 600;
        padding: 6px 15px;
        text-decoration: none;
        transition: .25s;
    }
    .nav-links a:hover { background-color: white; color: #0040a0; }
    .user-name { margin-left: 1.5rem; font-weight: 600; }
    .main { padding: 90px 20px 20px; max-width: 1600px; margin: auto; }
    .header-bar {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }
    h1 { color: #002b80; margin-bottom: 10px; }
    .search-section {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
    }
    .search-form { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; flex: 1; }
    .search-form input[type="text"] {
        flex: 1; padding: 10px 12px; border-radius: 6px;
        border: 1px solid #ccc; font-size: 1rem; min-width: 220px;
    }
    .search-form button {
        padding: 10px 16px; background-color: #0040a0; color: white;
        border: none; border-radius: 6px; font-weight: 600;
        cursor: pointer; transition: .25s;
    }
    .search-form button:hover { background-color: #002b80; }
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
    th { background-color: #0040a0; color: white; font-weight: 600; }
    tr:hover { background-color: #f4f8ff; }
    .btn {
        padding: 6px 10px; border: none; border-radius: 6px;
        color: white; font-weight: 600; cursor: pointer; transition: .2s;
    }
    .btn-info { background: #007bff; }
    .btn-danger { background: #e43f3f; }
    .btn-info:hover { background: #0056b3; }
    .btn-danger:hover { background: #c70000; }
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
    <div class="header-bar">
        <h1>Estudiantes Registrados</h1>

        <div class="search-section">
            <form class="search-form" method="GET" action="estudiantes.php">
                <input 
                  type="text" 
                  name="busqueda" 
                  placeholder="Buscar por nombre, correo, no. control o carrera..."
                  value="<?php echo htmlspecialchars($busqueda); ?>"
                  autocomplete="off"
                />
                <button type="submit"><i class="fa fa-search"></i> Buscar</button>
            </form>

            <!-- Botón Configuración Avanzada -->
            <div style="position: relative;">
                <button id="btnConfigAvanzada" class="btn btn-info" type="button">
                    Configuración Avanzada <i class="fa fa-caret-down"></i>
                </button>
                <div id="menuConfigAvanzada" style="display:none; position: absolute; top: 100%; right: 0; background: white; box-shadow: 0 2px 6px rgba(0,0,0,0.2); border-radius: 6px; min-width: 140px; z-index: 10;">
                    <button class="btn btn-info" style="width: 100%; border-radius: 0 0 6px 6px;" onclick="location.href='carreras.php'">Carreras</button>
                </div>
            </div>
        </div>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Nombre Completo</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>No. Control</th>
                    <th>Semestre</th>
                    <th>Carrera</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['nombre']." ".$row['apellido_paterno']." ".$row['apellido_materno']); ?></td>
                            <td><?php echo htmlspecialchars($row['correo']); ?></td>
                            <td><?php echo htmlspecialchars($row['telefono']); ?></td>
                            <td><?php echo htmlspecialchars($row['no_control']); ?></td>
                            <td><?php echo htmlspecialchars($row['semestre']); ?></td>
                            <td><?php echo htmlspecialchars($row['carrera']); ?></td>
                            <td>
                                <button class="btn btn-info" onclick="location.href='detalleestudiante.php?id_alumno=<?php echo $row['id_alumno']; ?>'">Ver detalles</button>
                                <button class="btn btn-danger" onclick="if(confirm('¿Seguro que deseas eliminar este estudiante?')) location.href='eliminarestudiante.php?id_alumno=<?php echo $row['no_control']; ?>'">Eliminar</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7">No se encontraron estudiantes.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
    const btnConfig = document.getElementById('btnConfigAvanzada');
    const menuConfig = document.getElementById('menuConfigAvanzada');

    btnConfig.addEventListener('click', () => {
        if (menuConfig.style.display === 'none' || menuConfig.style.display === '') {
            menuConfig.style.display = 'block';
        } else {
            menuConfig.style.display = 'none';
        }
    });

    // Cerrar menú si se hace clic fuera
    document.addEventListener('click', (e) => {
        if (!btnConfig.contains(e.target) && !menuConfig.contains(e.target)) {
            menuConfig.style.display = 'none';
        }
    });
</script>

</body>
</html>
