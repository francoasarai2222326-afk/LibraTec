<?php
session_start();
include("conexion.php");

// Verificar sesión activa y que sea personal
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== "personal") {
    header("Location: login.html");
    exit();
}

// Obtener nombre completo del personal logueado para mostrar en la navbar
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

// Consulta de personal bibliotecario
if ($busqueda !== "") {
    $sql = "SELECT * FROM PersonalBiblioteca
            WHERE nombre LIKE ?
               OR apellido_paterno LIKE ?
               OR apellido_materno LIKE ?
               OR correo LIKE ?
               OR no_control LIKE ?
            ORDER BY nombre ASC";
    $busqueda_param = "%" . $busqueda . "%";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $busqueda_param, $busqueda_param, $busqueda_param, $busqueda_param, $busqueda_param);
} else {
    $sql = "SELECT * FROM PersonalBiblioteca ORDER BY nombre ASC";
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
<title>Personal Bibliotecario - LIBRATEC</title>
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
    .main { padding: 90px 20px 20px; max-width: 1200px; margin: auto; }
    .header-bar {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }
    h1 { color: #002b80; margin-bottom: 10px; }
    .search-section { display: flex; gap: 10px; flex-wrap: wrap; }
    .search-form { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
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
        width: 100%;
        min-width: 700px;
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
        <h1>Personal Bibliotecario Registrado</h1>

        <div class="search-section">
            <form class="search-form" method="GET" action="personalbiblioteca.php">
                <input 
                  type="text" 
                  name="busqueda" 
                  placeholder="Buscar por nombre, correo o no. control..."
                  value="<?php echo htmlspecialchars($busqueda); ?>"
                  autocomplete="off"
                />
                <button type="submit"><i class="fa fa-search"></i> Buscar</button>
            </form>
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
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['nombre'] . " " . $row['apellido_paterno'] . " " . $row['apellido_materno']); ?></td>
                            <td><?php echo htmlspecialchars($row['correo']); ?></td>
                            <td><?php echo htmlspecialchars($row['telefono']); ?></td>
                            <td><?php echo htmlspecialchars($row['no_control']); ?></td>
                            <td>
                                <button class="btn btn-info" onclick="location.href='detallepersonalbiblioteca.php?no_control=<?php echo urlencode($row['no_control']); ?>'">Ver detalles</button>
                                <button class="btn btn-danger" onclick="if(confirm('¿Seguro que deseas eliminar este personal?')) location.href='eliminarpersonalbiblioteca.php?no_control=<?php echo urlencode($row['no_control']); ?>'">Eliminar</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5">No se encontraron registros de personal bibliotecario.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

</body>
</html>
