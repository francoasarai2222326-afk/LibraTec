<?php
session_start();
include("conexion.php");

// Si no hay sesión activa, redirigir al login
if (!isset($_SESSION['tipo_usuario'])) {
    header("Location: login.html");
    exit();
}

$tipo_usuario = $_SESSION['tipo_usuario'];
$no_control = $_SESSION['usuario'] ?? null;

// Obtener nombre completo para navbar (estudiante o personal)
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
$stmtNombre->close();

$idLibro = $_GET['id'] ?? null;
if (!$idLibro) {
    echo "Libro no encontrado.";
    exit();
}

// Obtener datos del libro
$stmt = $conn->prepare("SELECT * FROM Libros WHERE id_libro = ?");
$stmt->bind_param("i", $idLibro);
$stmt->execute();
$resLibro = $stmt->get_result();
$libro = $resLibro->fetch_assoc();
$stmt->close();

if (!$libro) {
    echo "Libro no encontrado.";
    exit();
}

// Función para generar código único de préstamo
function generarCodigoUnico($conn) {
    $codigo = '';
    $count = 1;
    while ($count > 0) {
        $letras = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 3);
        $numeros = substr(str_shuffle("0123456789"), 0, 5);
        $codigo = $letras . $numeros; // no hace falta mezclar más

        $chk = $conn->prepare("SELECT COUNT(*) FROM Prestamos WHERE codigo = ?");
        $chk->bind_param("s", $codigo);
        $chk->execute();
        $chk->bind_result($count);
        $chk->fetch();
        $chk->close();
    }
    return $codigo;
}

// Manejo del POST: pedir préstamo (solo para estudiantes)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pedir_prestamo'])) {
    // Solo estudiantes pueden pedir préstamo desde esta interfaz
    if ($tipo_usuario !== "estudiante") {
        echo "<script>alert('Solo estudiantes pueden pedir préstamos desde aquí.');</script>";
    } else {
        // Refrescar datos del libro (aún en BD) antes de decidir
        $stmt = $conn->prepare("SELECT cantidad_disponible FROM Libros WHERE id_libro = ?");
        $stmt->bind_param("i", $idLibro);
        $stmt->execute();
        $stmt->bind_result($cantidad_disponible_actual);
        $stmt->fetch();
        $stmt->close();

        if ($cantidad_disponible_actual <= 0) {
            // No disponible
            echo "<script>alert('No hay ejemplares disponibles de este libro.');</script>";
        } else {
            // Proceder con la transacción: insertar en Prestamos y actualizar Libros
            $conn->begin_transaction();

            try {
                $isbn = $libro['isbn'];
                $fecha_prestamo = date("Y-m-d");
                $fecha_devolucion = date("Y-m-d", strtotime("+30 days"));
                $codigo = generarCodigoUnico($conn);

                // Insertar préstamo (atendido_por queda NULL por ahora)
                $insert = $conn->prepare("INSERT INTO Prestamos (no_control, isbn, codigo, fecha_prestamo, fecha_devolucion, estado, observaciones, atendido_por) VALUES (?, ?, ?, ?, ?, 'activo', NULL, NULL)");
                $insert->bind_param("sssss", $no_control, $isbn, $codigo, $fecha_prestamo, $fecha_devolucion);
                if (!$insert->execute()) {
                    throw new Exception("Error al insertar préstamo: " . $insert->error);
                }
                $insert->close();

                // Actualizar cantidades en Libros
                $update = $conn->prepare("UPDATE Libros SET cantidad_disponible = cantidad_disponible - 1, cantidad_prestada = cantidad_prestada + 1, cantidad_pendiente_devolver = cantidad_pendiente_devolver + 1 WHERE id_libro = ? AND cantidad_disponible > 0");
                $update->bind_param("i", $idLibro);
                if (!$update->execute()) {
                    throw new Exception("Error al actualizar libro: " . $update->error);
                }
                if ($update->affected_rows === 0) {
                    // Algo raro: no se actualizó (posible condición de carrera)
                    throw new Exception("Error: no se pudo decrementar disponibilidad (otro proceso pudo modificarlo).");
                }
                $update->close();

                // Commit
                $conn->commit();

                // Redirigir a la página de la tarjetita con el código generado
                header("Location: prestamo.php?codigo=" . urlencode($codigo));
                exit();

            } catch (Exception $e) {
                $conn->rollback();
                // Mostrar error amigable y registrar en log si quieres
                error_log("Error al procesar préstamo: " . $e->getMessage());
                echo "<script>alert('Ocurrió un error al procesar el préstamo. Intenta de nuevo.');</script>";
            }
        }
    }
}

// Libros recomendados (mismo género)
$recom = $conn->prepare("SELECT id_libro, titulo, autor, imagen FROM Libros WHERE genero = ? AND id_libro != ? LIMIT 4");
$recom->bind_param("si", $libro['genero'], $idLibro);
$recom->execute();
$recomendados = $recom->get_result();
$recom->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?php echo htmlspecialchars($libro['titulo']); ?> - Detalles</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
<style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Segoe UI", sans-serif;
  }
  body {
    background-color: #eef3f7;
  }

  /* Navbar general */
  .navbar {
    position: fixed;
    top: 0; left: 0; right: 0;
    background: linear-gradient(90deg, #0040a0, #002b80);
    color: white;
    display: flex;
    align-items: center;
    padding: 10px 20px;
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    z-index: 9999;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    flex-wrap: wrap;
  }
  .navbar .logo {
    font-weight: 700;
    font-size: 1.4rem;
    letter-spacing: 2px;
    cursor: default;
    user-select: none;
  }
  .navbar .nav-links {
    margin-left: auto;
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
  }
  .navbar .nav-links button,
  .navbar .nav-links a {
    background: transparent;
    border: 2px solid white;
    border-radius: 6px;
    color: white;
    font-weight: 600;
    padding: 6px 15px;
    cursor: pointer;
    text-decoration: none;
    transition: background-color 0.25s, color 0.25s;
  }
  .navbar .nav-links button:hover,
  .navbar .nav-links a:hover {
    background-color: white;
    color: #0040a0;
  }
  .navbar .user-name {
    margin-left: 1.5rem;
    font-weight: 600;
    font-size: 0.95rem;
    white-space: nowrap;
    user-select: none;
  }
  @media (max-width: 600px) {
    .navbar {
      flex-wrap: wrap;
      padding: 10px;
    }
    .navbar .nav-links {
      width: 100%;
      justify-content: center;
      margin-top: 8px;
      gap: 0.5rem;
    }
    .navbar .user-name {
      width: 100%;
      text-align: center;
      margin: 6px 0 0 0;
    }
  }

  .main {
    padding: 90px 20px 20px 20px;
    max-width: 1200px;
    margin: auto;
  }

  .content-container {
    display: flex;
    gap: 2rem;
    align-items: flex-start;
    flex-wrap: wrap;
  }

  .book-detail {
    flex: 1;
    display: flex;
    gap: 2rem;
    background: #fff;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 2px 7px rgba(0,0,0,0.15);
    min-width: 320px;
  }

  .book-detail img {
    width: 260px;
    height: 360px;
    border-radius: 10px;
    object-fit: cover;
  }

  .book-info h2 {
    margin-bottom: 0.5rem;
  }

  .book-info p {
    margin: 4px 0;
    font-size: 0.95rem;
  }

  .action-btn {
    margin-top: 1rem;
    padding: 10px 15px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    color: white;
    user-select: none;
    width: 100%;
    max-width: 220px;
    display: block;
  }

  .estudiante {
    background: #007bff;
  }

  .personal {
    background: #ffde21;
    margin-bottom: 10px;
  }

  .reco-section {
    width: 320px;
    background: #fff;
    border-radius: 15px;
    padding: 1rem;
    box-shadow: 0 2px 7px rgba(0,0,0,0.15);
    min-width: 280px;
  }

  .reco-section h3 {
    margin-bottom: 1rem;
    font-weight: 700;
    font-size: 1.1rem;
    color: #002b80;
  }

  .reco-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    grid-template-rows: repeat(2, auto);
    gap: 1rem;
  }

  .reco-item {
    background: #fefefe;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 1px 4px rgba(0,0,0,0.1);
    cursor: pointer;
    padding: 5px;
    transition: transform 0.2s;
  }

  .reco-item:hover {
    transform: scale(1.05);
  }

  .reco-item img {
    width: 100%;
    height: 130px;
    object-fit: cover;
    border-radius: 8px;
  }

  .reco-item p {
    margin-top: 6px;
    font-size: 0.9rem;
    font-weight: 600;
    color: #333;
  }

  @media (max-width: 900px) {
    .content-container {
      flex-direction: column;
    }
    .reco-section {
      width: 100%;
      min-width: auto;
      margin-top: 1.5rem;
    }
    .book-detail {
      width: 100%;
      flex-wrap: wrap;
      justify-content: center;
    }
    .book-detail img {
      width: 200px;
      height: 280px;
    }
    .reco-item img {
      height: 100px;
    }
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
  <div class="content-container">
    <div class="book-detail">
      <img src="<?php echo htmlspecialchars($libro['imagen']); ?>" alt="Portada del libro" />
      <div class="book-info">
        <h2><?php echo htmlspecialchars($libro['titulo']); ?></h2>
        <p><strong>Autor:</strong> <?php echo htmlspecialchars($libro['autor']); ?></p>
        <p><strong>Editorial:</strong> <?php echo htmlspecialchars($libro['editorial']); ?></p>
        <p><strong>Año:</strong> <?php echo htmlspecialchars($libro['anio_publicacion']); ?></p>
        <p><strong>Género:</strong> <?php echo htmlspecialchars($libro['genero']); ?></p>
        <p><strong>ISBN:</strong> <?php echo htmlspecialchars($libro['isbn']); ?></p>
        <p><strong>Disponibles:</strong> <?php echo (int)$libro['cantidad_disponible']; ?></p>

        <?php if ($tipo_usuario == "estudiante"): ?>
          <form method="POST" style="display:inline;">
            <button type="submit" name="pedir_prestamo" class="action-btn estudiante">Pedir préstamo</button>
          </form>
        <?php else: ?>
          <button class="action-btn personal" onclick="location.href='controldeprestamos.php?isbn=<?php echo urlencode($libro['isbn']); ?>'">Control de Préstamos</button>
          <button class="action-btn personal" onclick="location.href='controldemultas.php?isbn=<?php echo urlencode($libro['isbn']); ?>'">Control de Multas</button>
          <button class="action-btn personal" onclick="location.href='actualizarinformacion.php?id=<?php echo $libro['id_libro']; ?>'">Actualizar información</button>
        <?php endif; ?>

      </div>
    </div>

    <div class="reco-section">
      <h3>Libros que podrían interesarte</h3>
      <div class="reco-grid">
        <?php while ($r = $recomendados->fetch_assoc()): ?>
          <div class="reco-item" onclick="location.href='detallelibro.php?id=<?php echo $r['id_libro']; ?>'">
            <img src="<?php echo htmlspecialchars($r['imagen']); ?>" alt="Portada libro recomendado" />
            <p><?php echo htmlspecialchars($r['titulo']); ?></p>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
  </div>
</main>

</body>
</html>
