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

// Consulta editoriales con filtro búsqueda
if ($busqueda !== "") {
    $sqlEditoriales = "SELECT * FROM Editoriales WHERE editorial LIKE ? ORDER BY editorial ASC";
    $stmtEditoriales = $conn->prepare($sqlEditoriales);
    $likeBusqueda = "%" . $busqueda . "%";
    $stmtEditoriales->bind_param("s", $likeBusqueda);
    $stmtEditoriales->execute();
    $result = $stmtEditoriales->get_result();
} else {
    $sqlEditoriales = "SELECT * FROM Editoriales ORDER BY editorial ASC";
    $result = $conn->query($sqlEditoriales);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Editoriales - LIBRATEC</title>
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

    main.main {
        padding: 90px 20px 20px;
        max-width: 900px;
        margin: auto;
    }

    .header-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        margin-bottom: 20px;
        gap: 10px;
    }

    h1 {
        color: #002b80;
        font-weight: 700;
        font-size: 1.8rem;
        white-space: nowrap;
    }

    form.search-form {
        display: flex;
        gap: 8px;
        flex-wrap: nowrap;
        flex-shrink: 0;
    }
    form.search-form input[type="text"] {
        padding: 8px 12px;
        border-radius: 6px;
        border: 1px solid #ccc;
        font-size: 1rem;
        width: 220px;
        transition: border-color 0.25s;
    }
    form.search-form input[type="text"]:focus {
        outline: none;
        border-color: #0040a0;
        box-shadow: 0 0 5px rgba(0,64,160,0.4);
    }
    form.search-form button {
        background-color: #0040a0;
        border: none;
        color: white;
        padding: 8px 14px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.25s;
    }
    form.search-form button:hover {
        background-color: #002b80;
    }

    .btn-add-editorial {
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        background-color: #28a745;
        color: white;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 1rem;
        white-space: nowrap;
        transition: background-color 0.25s;
        flex-shrink: 0;
    }
    .btn-add-editorial:hover {
        background-color: #1e7e34;
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
        min-width: 400px;
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
        .header-bar {
            flex-direction: column;
            align-items: stretch;
            gap: 10px;
        }
        h1 {
            font-size: 1.5rem;
            text-align: center;
            white-space: normal;
        }
        form.search-form input[type="text"] {
            width: 100%;
        }
        form.search-form {
            justify-content: center;
        }
        .btn-add-editorial {
            width: 100%;
            justify-content: center;
        }
        table {
            font-size: 0.9rem;
        }
        th, td {
            padding: 8px;
        }
        .btn, .btn-info, .btn-danger {
            padding: 5px 8px;
            font-size: 0.85rem;
            margin: 2px 0;
            width: 100%;
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
        <h1>Editoriales</h1>

        <form class="search-form" method="GET" action="editoriales.php">
            <input 
                type="text" 
                name="busqueda" 
                placeholder="Buscar editorial..." 
                value="<?php echo htmlspecialchars($busqueda); ?>" 
                autocomplete="off"
            />
            <button type="submit"><i class="fa fa-search"></i> Buscar</button>
        </form>

        <button class="btn-add-editorial" onclick="location.href='añadireditorial.php'">
            <i class="fa fa-plus"></i> Añadir Editorial
        </button>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Editorial</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['editorial']); ?></td>
                            <td>
                                <button class="btn btn-info" onclick="location.href='detalleeditorial.php?id=<?php echo $row['id_editorial']; ?>'">
                                    Ver detalles
                                </button>
                                <button class="btn btn-danger" onclick="if(confirm('¿Seguro que deseas eliminar esta editorial?')) location.href='eliminareditorial.php?id=<?php echo $row['id_editorial']; ?>'">
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="2">No se encontraron editoriales.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

</body>
</html>
