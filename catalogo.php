<?php
session_start();
include("conexion.php");

// Si no hay sesión activa, redirigir al login
if (!isset($_SESSION['tipo_usuario'])) {
    header("Location: login.html");
    exit();
}

$tipo_usuario = $_SESSION['tipo_usuario'];
$no_control = $_SESSION['usuario'];

// Obtener nombre completo desde la base de datos
if ($tipo_usuario == "estudiante") {
    $sqlNombre = "SELECT nombre, apellido_paterno, apellido_materno FROM Alumnos WHERE no_control = ?";
} else {
    $sqlNombre = "SELECT nombre, apellido_paterno, apellido_materno FROM PersonalBiblioteca WHERE no_control = ?";
}

$stmt = $conn->prepare($sqlNombre);
$stmt->bind_param("s", $no_control);
$stmt->execute();
$resultado = $stmt->get_result();
$nombreCompleto = "Usuario desconocido";

if ($resultado->num_rows > 0) {
    $datos = $resultado->fetch_assoc();
    $nombreCompleto = $datos['nombre'] . " " . $datos['apellido_paterno'] . " " . $datos['apellido_materno'];
}

// Filtros de búsqueda
$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : "";
$generoFiltro = isset($_GET['genero']) ? $_GET['genero'] : "";

$sql = "SELECT id_libro, titulo, autor, imagen, editorial, anio_publicacion, genero, isbn FROM Libros WHERE 1";

if ($busqueda !== "") {
    $sql .= " AND (titulo LIKE ? OR autor LIKE ? OR editorial LIKE ? OR anio_publicacion LIKE ? OR genero LIKE ? OR isbn LIKE ?)";
}
if ($generoFiltro !== "" && $generoFiltro !== "Todos") {
    $sql .= " AND genero = ?";
}

$sql .= " ORDER BY titulo ASC";
$stmtLibros = $conn->prepare($sql);

if ($busqueda !== "" && $generoFiltro !== "" && $generoFiltro !== "Todos") {
    $like = "%$busqueda%";
    $stmtLibros->bind_param("sssssss", $like, $like, $like, $like, $like, $like, $generoFiltro);
} elseif ($busqueda !== "") {
    $like = "%$busqueda%";
    $stmtLibros->bind_param("ssssss", $like, $like, $like, $like, $like, $like);
} elseif ($generoFiltro !== "" && $generoFiltro !== "Todos") {
    $stmtLibros->bind_param("s", $generoFiltro);
}

$stmtLibros->execute();
$libros = $stmtLibros->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Catálogo de Libros - LibraTec</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Segoe UI", sans-serif; }
  body { background-color: #eef3f7; }

  .navbar {
    position: fixed; top: 0; left: 0; right: 0;
    background: linear-gradient(90deg, #0040a0, #002b80);
    color: white; display: flex; align-items: center;
    padding: 10px 20px; z-index: 9999;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
  }
  .logo { font-weight: 700; font-size: 1.4rem; letter-spacing: 2px; }
  .nav-links { margin-left: auto; display: flex; gap: 1rem; }
  .nav-links a {
    background: transparent; border: 2px solid white;
    border-radius: 6px; color: white; font-weight: 600;
    padding: 6px 15px; text-decoration: none;
    transition: background-color 0.25s, color 0.25s;
  }
  .nav-links a:hover { background-color: white; color: #0040a0; }
  .user-name { margin-left: 1.5rem; font-weight: 600; font-size: 0.95rem; }

  @media (max-width: 600px) {
    .navbar { flex-wrap: wrap; padding: 10px; }
    .nav-links { width: 100%; justify-content: center; margin-top: 8px; }
    .user-name { width: 100%; text-align: center; margin-top: 6px; }
  }

  .main {
    padding: 90px 20px 20px 20px;
    max-width: 1200px; margin: auto;
  }

  .header-bar {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    margin-bottom: 20px;
  }

  .header-bar h1 {
    font-size: 1.8rem;
    color: #002b80;
    margin-bottom: 10px;
  }

  .search-container {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .search-container input,
  .search-container select {
    padding: 7px 10px;
    border: 2px solid #0040a0;
    border-radius: 8px;
    font-size: 0.95rem;
    outline: none;
  }

  .search-container button {
    background-color: #0040a0;
    color: white;
    border: none;
    padding: 8px 14px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: background-color 0.25s;
  }
  .search-container button:hover { background-color: #002b80; }

  .books {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 1rem;
  }
  .book {
    background: #fff; border-radius: 15px; overflow: hidden;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
    cursor: pointer; transition: 0.2s;
  }
  .book:hover { transform: scale(1.05); }
  .book img { width: 100%; height: 200px; object-fit: cover; }
  .book-info { padding: 10px; text-align: center; }

  @media (max-width: 600px) {
    .main { padding-top: 120px; }
    .book img { height: 150px; }
    .header-bar { flex-direction: column; align-items: stretch; }
    .search-container { justify-content: flex-start; flex-wrap: wrap; }
  }
</style>
</head>
<body>

<nav class="navbar">
  <div class="logo">LIBRATEC</div>
  <div class="nav-links">
    <?php if ($tipo_usuario === "estudiante"): ?>
      <a href="catalogo.php">Catálogo</a>
      <a href="misprestamos.php">Mis Préstamos</a>
      <a href="mismultas.php">Mis Multas</a>
      <a href="miperfilestudiante.php">Mi Perfil</a>
      <a href="home.php">Salir</a>
    <?php elseif ($tipo_usuario === "personal"): ?>
      <a href="catalogo.php">Catálogo</a>
      <a href="estudiantes.php">Estudiantes</a>
      <a href="personalbiblioteca.php">Personal Bibliotecario</a>
      <a href="registros.php">Registros</a>
      <a href="prestamos.php">Préstamos</a>
      <a href="multas.php">Multas</a>
      <a href="miperfilpersonalbiblioteca.php">Mi Perfil</a>
      <a href="home.php">Salir</a>
    <?php endif; ?>
  </div>
  <div class="user-name"><?php echo htmlspecialchars($nombreCompleto); ?></div>
</nav>

<div class="main">
  <div class="header-bar">
    <h1>Catálogo de Libros</h1>

    <form method="GET" class="search-container">
      <input type="text" name="buscar" placeholder="Buscar libro..." value="<?php echo htmlspecialchars($busqueda); ?>">
      <select name="genero">
        <option value="Todos">Todos los géneros</option>
        <?php
        $generos = ["Novela", "Distopía", "Infantil", "Fantasía", "Misterio", "Terror", "Romance", "Ficción", "Aventura", "Novela histórica", "Ciencia ficción", "Ficción histórica", "Realismo mágico", "Suspense", "Cuentos", "Poesía", "Teatro", "Filosofía", "Crónica", "Autobiografía", "Realismo"];
        foreach ($generos as $g) {
            $selected = ($g == $generoFiltro) ? "selected" : "";
            echo "<option value='$g' $selected>$g</option>";
        }
        ?>
      </select>
      <button type="submit"><i class="fa fa-search"></i> Buscar</button>
    </form>
  </div>

  <div class="books">
    <?php while ($libro = $libros->fetch_assoc()): ?>
      <div class="book" onclick="window.location='detallelibro.php?id=<?php echo $libro['id_libro']; ?>'">
        <img src="<?php echo htmlspecialchars($libro['imagen']); ?>" alt="Imagen de <?php echo htmlspecialchars($libro['titulo']); ?>" />
        <div class="book-info">
          <strong><?php echo htmlspecialchars($libro['titulo']); ?></strong><br />
          <small><?php echo htmlspecialchars($libro['autor']); ?></small>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</div>

</body>
</html>

