<?php
session_start();
include("conexion.php");

// Verificar sesión
if (!isset($_SESSION['tipo_usuario'])) {
    header("Location: login.html");
    exit();
}

$tipo_usuario = $_SESSION['tipo_usuario'];
$no_control = $_SESSION['usuario'] ?? null;

// Obtener nombre del usuario
if ($tipo_usuario == "estudiante") {
    $sqlNombre = "SELECT nombre, apellido_paterno, apellido_materno FROM Alumnos WHERE no_control = ?";
} else {
    $sqlNombre = "SELECT nombre, apellido_paterno, apellido_materno FROM PersonalBiblioteca WHERE no_control = ?";
}
$stmtNombre = $conn->prepare($sqlNombre);
$stmtNombre->bind_param("s", $no_control);
$stmtNombre->execute();
$resultado = $stmtNombre->get_result();
$nombreCompleto = "Usuario desconocido";
if ($resultado->num_rows > 0) {
    $datos = $resultado->fetch_assoc();
    $nombreCompleto = $datos['nombre'] . " " . $datos['apellido_paterno'] . " " . $datos['apellido_materno'];
}

// Verificar que venga id_libro
if (!isset($_GET['id'])) {
    echo "Libro no especificado.";
    exit();
}

$idLibro = intval($_GET['id']);

// Obtener los datos del libro
$stmt = $conn->prepare("SELECT * FROM Libros WHERE id_libro = ?");
$stmt->bind_param("i", $idLibro);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Libro no encontrado.";
    exit();
}

$libro = $result->fetch_assoc();

// Procesar actualización
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $_POST['titulo'];
    $autor = $_POST['autor'];
    $editorial = $_POST['editorial'];
    $anio_publicacion = $_POST['anio_publicacion'];
    $genero = $_POST['genero'];
    $isbn = $_POST['isbn'];

    $sql_update = "UPDATE Libros 
                   SET titulo=?, autor=?, editorial=?, anio_publicacion=?, genero=?, isbn=?
                   WHERE id_libro=?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ssssssi", $titulo, $autor, $editorial, $anio_publicacion, $genero, $isbn, $idLibro);

    if ($stmt_update->execute()) {
        header("Location: detallelibro.php?id=" . urlencode($idLibro));
        exit;
    } else {
        echo "Error al actualizar: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Actualizar información - <?php echo htmlspecialchars($libro['titulo']); ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
<style>
  * {margin: 0; padding: 0; box-sizing: border-box; font-family: "Segoe UI", sans-serif;}
  body {background-color: #eef3f7;}
  .navbar {
    position: fixed; top: 0; left: 0; right: 0;
    background: linear-gradient(90deg, #0040a0, #002b80);
    color: white; display: flex; align-items: center;
    padding: 10px 20px; z-index: 9999; box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    flex-wrap: wrap;
  }
  .navbar .logo {font-weight: 700; font-size: 1.4rem; letter-spacing: 2px; cursor: default;}
  .navbar .nav-links {margin-left: auto; display: flex; gap: 1rem; flex-wrap: wrap;}
  .navbar .nav-links a {
    background: transparent; border: 2px solid white; border-radius: 6px;
    color: white; font-weight: 600; padding: 6px 15px; text-decoration: none;
    transition: background-color 0.25s, color 0.25s;
  }
  .navbar .nav-links a:hover {background-color: white; color: #0040a0;}
  .navbar .user-name {margin-left: 1.5rem; font-weight: 600; font-size: 0.95rem;}
  @media (max-width: 600px) {
    .navbar {flex-wrap: wrap;}
    .navbar .nav-links {width: 100%; justify-content: center; margin-top: 8px;}
    .navbar .user-name {width: 100%; text-align: center; margin: 6px 0 0 0;}
  }

  .main {
    padding: 90px 20px;
    max-width: 600px;
    margin: auto;
  }

  form {
    background: #fff;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 2px 7px rgba(0,0,0,0.15);
  }

  form h2 {
    text-align: center;
    color: #002b80;
    margin-bottom: 1.5rem;
  }

  label {
    font-weight: 600;
    margin-top: 10px;
    display: block;
  }

  input[type="text"], input[type="number"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 6px;
    margin-top: 5px;
  }

  button {
    margin-top: 20px;
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 8px;
    background: #007bff;
    color: white;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s;
  }

  button:hover {
    background: #0056b3;
  }

  @media (max-width: 600px) {
    form {padding: 1.5rem;}
  }
</style>
</head>
<body>
<nav class="navbar">
  <div class="logo">LIBRATEC</div>
  <div class="nav-links">
    <?php if ($tipo_usuario === "estudiante"): ?>
      <a href="catalogo.php">Catálogo</a>
      <a href="prestamos.php">Mis Préstamos</a>
      <a href="multas.php">Mis Multas</a>
      <a href="miperfilestudiante.php">Mi Perfil</a>
    <?php elseif ($tipo_usuario === "personal"): ?>
      <a href="catalogo.php">Catálogo</a>  
<a href="estudiantes.php">Estudiantes</a>
<a href="personalbiblioteca.php">Personal Bibliotecario</a>
      <a href="registros.php">Registros</a>
      <a href="prestamos.php">Préstamos</a>
      <a href="multas.php">Multas</a>
      <a href="miperfilpersonalbiblioteca.php">Mi Perfil</a>
    <?php endif; ?>
    <a href="home.php">Salir</a>
  </div>
  <div class="user-name"><?php echo htmlspecialchars($nombreCompleto); ?></div>
</nav>

<main class="main">
  <form method="POST">
    <h2>Actualizar Información del Libro</h2>

    <label for="titulo">Título</label>
    <input type="text" name="titulo" value="<?php echo htmlspecialchars($libro['titulo']); ?>" required>

    <label for="autor">Autor</label>
    <input type="text" name="autor" value="<?php echo htmlspecialchars($libro['autor']); ?>" required>

    <label for="editorial">Editorial</label>
    <input type="text" name="editorial" value="<?php echo htmlspecialchars($libro['editorial']); ?>">

    <label for="anio_publicacion">Año de publicación</label>
    <input type="number" name="anio_publicacion" value="<?php echo htmlspecialchars($libro['anio_publicacion']); ?>">

    <label for="genero">Género</label>
    <input type="text" name="genero" value="<?php echo htmlspecialchars($libro['genero']); ?>">

    <label for="isbn">ISBN</label>
    <input type="text" name="isbn" value="<?php echo htmlspecialchars($libro['isbn']); ?>">

    <button type="submit">Guardar Cambios</button>
  </form>
</main>

</body>
</html>
