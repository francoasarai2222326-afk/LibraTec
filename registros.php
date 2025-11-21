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

// Consulta para mostrar libros o filtrar
if ($busqueda !== "") {
    $sql = "SELECT * FROM Libros 
            WHERE titulo LIKE ? 
               OR isbn LIKE ? 
               OR autor LIKE ? 
               OR genero LIKE ? 
               OR editorial LIKE ?
            ORDER BY titulo ASC";
    $busqueda_param = "%" . $busqueda . "%";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $busqueda_param, $busqueda_param, $busqueda_param, $busqueda_param, $busqueda_param);
} else {
    $sql = "SELECT * FROM Libros ORDER BY titulo ASC";
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
<title>Registros de Libros - LIBRATEC</title>
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

    .main {
        padding: 90px 20px 20px;
        max-width: 1600px;
        margin: auto;
    }

    .header-bar {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    h1 {
        color: #002b80;
        margin-bottom: 10px;
    }

    .search-section {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .search-form {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .search-form input[type="text"] {
        flex: 1;
        padding: 10px 12px;
        border-radius: 6px;
        border: 1px solid #ccc;
        font-size: 1rem;
        min-width: 220px;
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
    }
    .search-form button:hover {
        background-color: #002b80;
    }

    .add-book-btn {
        padding: 10px 18px;
        background-color: #007b3f;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.25s;
        white-space: nowrap;
    }
    .add-book-btn:hover {
        background-color: #005f2e;
    }

    /* Dropdown Configuración Avanzada */
    #btn-config {
        padding: 10px 16px;
        background-color: #ffc107;
        border: none;
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
        transition: background-color 0.25s;
        margin-top: 20px;
        white-space: nowrap;
    }
    #btn-config:hover {
        background-color: #ffb000;
    }
    #dropdown-config {
        display: none;
        margin-top: 8px;
        background: #0040a0;
        border-radius: 8px;
        width: max-content;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    }
    #dropdown-config a {
        display: block;
        padding: 10px 20px;
        color: white;
        text-decoration: none;
        font-weight: 600;
        border-bottom: 1px solid #002b80;
    }
    #dropdown-config a:last-child {
        border-bottom: none;
    }
    #dropdown-config a:hover {
        background-color: #002b80;
    }

    .table-wrapper {
        width: 100%;
        overflow-x: auto; /* Scroll horizontal */
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }

    table {
        border-collapse: collapse;
        width: max-content; /* Ajusta según contenido */
        min-width: 1200px; /* Forzar scroll si es menos ancho */
    }

    th, td {
        white-space: nowrap; /* Evitar saltos de línea */
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
        .main {
            padding: 80px 10px;
        }
        h1 {
            font-size: 1.3rem;
            text-align: center;
        }
        .header-bar {
            flex-direction: column;
            align-items: stretch;
            gap: 10px;
        }
        .search-section {
            flex-direction: column;
            align-items: stretch;
        }
        .add-book-btn {
            width: 100%;
        }
        .table-wrapper {
            border-radius: 0;
        }
        th, td {
            font-size: 0.85rem;
            padding: 8px;
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
    <div class="header-bar">
        <h1>Registros de Libros</h1>

        <div class="search-section">
            <form class="search-form" method="GET" action="registros.php">
                <input 
                  type="text" 
                  name="busqueda" 
                  placeholder="Buscar por título, ISBN, autor, género o editorial..." 
                  value="<?php echo htmlspecialchars($busqueda); ?>"
                  autocomplete="off"
                />
                <button type="submit"><i class="fa fa-search"></i> Buscar</button>
            </form>

            <button class="add-book-btn" onclick="location.href='agregarlibro.php'">
                <i class="fa fa-plus"></i> Añadir Libro
            </button>
        </div>

        <!-- Dropdown Configuración Avanzada -->
        <button id="btn-config">Configuración Avanzada ▼</button>
        <div id="dropdown-config">
            <a href="autores.php">Autores</a>
            <a href="editoriales.php">Editoriales</a>
            <a href="generos.php">Géneros</a>
        </div>

    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Autor</th>
                    <th>Editorial</th>
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
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['titulo']); ?></td>
                            <td><?php echo htmlspecialchars($row['autor']); ?></td>
                            <td><?php echo htmlspecialchars($row['editorial']); ?></td>
                            <td><?php echo htmlspecialchars($row['anio_publicacion']); ?></td>
                            <td><?php echo htmlspecialchars($row['genero']); ?></td>
                            <td><?php echo htmlspecialchars($row['isbn']); ?></td>
                            <td><?php echo htmlspecialchars($row['cantidad_total']); ?></td>
                            <td><?php echo htmlspecialchars($row['cantidad_disponible']); ?></td>
                            <td><?php echo htmlspecialchars($row['cantidad_prestada']); ?></td>
                            <td><?php echo htmlspecialchars($row['cantidad_pendiente_devolver']); ?></td>
                            <td>
                                <button class="btn btn-info" onclick="location.href='detallelibro.php?id=<?php echo $row['id_libro']; ?>'">Ver detalles</button>
                                <button class="btn btn-danger" onclick="if(confirm('¿Seguro que deseas eliminar este libro?')) location.href='eliminarlibro.php?id_libro=<?php echo $row['id_libro']; ?>'">Eliminar</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="11">No se encontraron libros.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
  const btnConfig = document.getElementById('btn-config');
  const dropdown = document.getElementById('dropdown-config');
  btnConfig.addEventListener('click', () => {
    if (dropdown.style.display === 'none' || dropdown.style.display === '') {
      dropdown.style.display = 'block';
      btnConfig.textContent = 'Configuración Avanzada ▲';
    } else {
      dropdown.style.display = 'none';
      btnConfig.textContent = 'Configuración Avanzada ▼';
    }
  });
</script>

</body>
</html>

