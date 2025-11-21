<?php
session_start();
include("conexion.php");

if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== "personal") {
    header("Location: login.html");
    exit();
}

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $_POST["titulo"];
    $autor = $_POST["autor"];
    $editorial = $_POST["editorial"];
    $anio_publicacion = $_POST["anio_publicacion"];
    $genero = $_POST["genero"];
    $isbn = $_POST["isbn"];
    $cantidad_total = $_POST["cantidad_total"];

    $cantidad_disponible = $cantidad_total;
    $cantidad_prestada = 0;
    $cantidad_pendiente_devolver = 0;

    $directorio = __DIR__ . "/imagen/";
    $nombreArchivo = basename($_FILES["imagen"]["name"]);
    $rutaBD = "imagen/" . $nombreArchivo;
    $rutaDestino = $directorio . $nombreArchivo;
    $extension = strtolower(pathinfo($rutaDestino, PATHINFO_EXTENSION));

    if ($extension != "jpg") {
        $mensaje = "Solo se permiten imágenes JPG.";
    } else {
        if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $rutaDestino)) {

            $sql = "INSERT INTO Libros (titulo, autor, editorial, anio_publicacion, genero, isbn, imagen, cantidad_total, cantidad_disponible, cantidad_prestada, cantidad_pendiente_devolver)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssiiii", 
                $titulo, $autor, $editorial, $anio_publicacion, 
                $genero, $isbn, $rutaBD, $cantidad_total, 
                $cantidad_disponible, $cantidad_prestada, 
                $cantidad_pendiente_devolver
            );

            if ($stmt->execute()) {
                header("Location: registros.php");
                exit();
            } else {
                $mensaje = "Error al guardar en la base de datos.";
            }
        } else {
            $mensaje = "Error al subir la imagen. Verifica permisos.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Agregar Libro - LIBRATEC</title>
<style>
    body {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        background-color: #eef3f7;
        margin: 0; padding: 20px;
    }

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
        transition: 0.25s;
    }
    .nav-links a:hover { background-color: white; color: #0040a0; }
    .user-name { margin-left: 1.5rem; font-weight: 600; }

    .container {
        max-width: 600px; margin: 100px auto 0;
        background: white; padding: 30px;
        border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    h2 {
        color: #002b80; margin-bottom: 25px; text-align: center;
    }
    label {
        display: block; margin-top: 15px; font-weight: 600;
    }
    input[type="text"], input[type="number"], input[type="file"], textarea {
        width: 100%; padding: 10px;
        border: 1px solid #ccc; border-radius: 6px;
        margin-top: 5px; font-size: 1rem;
    }

    .btn {
        margin-top: 25px; width: 100%; padding: 12px;
        background-color: #0040a0; color: white; font-size: 1.1rem;
        border: none; border-radius: 8px; cursor: pointer;
        font-weight: 700; transition: 0.3s;
    }
    .btn:hover { background-color: #002b5c; }

    .error {
        margin-top: 10px; color: red; font-weight: 600; text-align: center;
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
    <div class="user-name"><?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Personal'); ?></div>
</nav>

<div class="container">
    <h2>Agregar Nuevo Libro</h2>

    <?php if ($mensaje): ?>
        <div class="error"><?= htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>

    <form method="POST" action="agregarlibro.php" enctype="multipart/form-data">

        <label>Título del Libro</label>
        <input type="text" name="titulo" required>

        <label>Autor</label>
        <input type="text" name="autor" required>

        <label>Editorial</label>
        <input type="text" name="editorial" required>

        <label>Año de Publicación</label>
        <input type="number" name="anio_publicacion" required>

        <label>Género</label>
        <input type="text" name="genero" required>

        <label>ISBN</label>
        <input type="text" name="isbn" required>

        <label>Cantidad Total</label>
        <input type="number" name="cantidad_total" min="1" required>

        <label>Imagen del Libro (JPG)</label>
        <input type="file" name="imagen" accept=".jpg" required>

        <button type="submit" class="btn">Guardar Libro</button>
    </form>
</div>

</body>
</html>
