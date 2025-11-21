<?php
session_start();
include("conexion.php");

// Verificar sesión activa
if (!isset($_SESSION['tipo_usuario'])) {
    header("Location: login.html");
    exit();
}

// Obtener el no_control del personal logueado para mostrar su nombre completo
$no_control = $_SESSION['usuario'];
$sqlPersonal = "SELECT nombre, apellido_paterno, apellido_materno FROM PersonalBiblioteca WHERE no_control = ?";
$stmt = $conn->prepare($sqlPersonal);
$stmt->bind_param("s", $no_control);
$stmt->execute();
$res = $stmt->get_result();
$personal = $res->fetch_assoc();
$nombreCompleto = $personal ? $personal['nombre'] . " " . $personal['apellido_paterno'] . " " . $personal['apellido_materno'] : "Personal";

// Obtener datos del préstamo
$codigo = $_GET['codigo'] ?? null;

if (!$codigo) {
    die("Código no especificado.");
}

$sql = "SELECT P.*, L.titulo, L.autor, A.nombre AS nombre_alumno, A.apellido_paterno AS ap_pat, A.apellido_materno AS ap_mat
        FROM Prestamos P
        JOIN Libros L ON P.isbn = L.isbn
        JOIN Alumnos A ON P.no_control = A.no_control
        WHERE P.codigo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $codigo);
$stmt->execute();
$prestamo = $stmt->get_result()->fetch_assoc();

if (!$prestamo) {
    die("No se encontró el préstamo con ese código.");
}

// Guardar devolución
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha_real = $_POST['fecha_real_devolucion'];

    if (!$fecha_real) {
        echo "<script>alert('Por favor ingresa la fecha de devolución real');</script>";
    } else {
        // Actualizar préstamo
        $update = $conn->prepare("UPDATE Prestamos SET fecha_real_devolucion = ?, estado = 'devuelto' WHERE codigo = ?");
        $update->bind_param("ss", $fecha_real, $codigo);
        $update->execute();

        // Actualizar cantidad disponible del libro
        $update_libro = $conn->prepare("UPDATE Libros SET cantidad_disponible = cantidad_disponible + 1 WHERE isbn = ?");
        $update_libro->bind_param("s", $prestamo['isbn']);
        $update_libro->execute();

        echo "<script>alert('Devolución registrada correctamente'); window.location.href='controldeprestamos.php?isbn={$prestamo['isbn']}';</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Registrar Devolución - LIBRATEC</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
    <style>
        /* Navbar */
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
            z-index: 999;
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
        /* Container */
        .container {
            max-width: 700px;
            margin: 110px auto 40px;
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        h2 {
            text-align: center;
            color: #002b80;
            margin-bottom: 25px;
        }
        label {
            display: block;
            font-weight: 600;
            margin-top: 15px;
            color: #002b80;
        }
        input[type="text"],
        input[type="date"] {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border: 1.5px solid #ccc;
            border-radius: 8px;
            font-size: 1rem;
            background-color: #f9f9f9;
        }
        input[readonly] {
            background-color: #f2f2f2;
            cursor: not-allowed;
        }
        button {
            margin-top: 25px;
            width: 100%;
            padding: 14px;
            background: #0040a0;
            color: white;
            font-size: 1.1rem;
            font-weight: 600;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        button:hover {
            background: #002b80;
        }
        /* Responsive */
        @media (max-width: 768px) {
            .container {
                margin: 120px 15px 40px;
                padding: 20px;
            }
            h2 {
                font-size: 1.5rem;
            }
            label {
                font-size: 1rem;
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
  <div class="user-name"><?php echo $nombreCompleto; ?></div>
</nav>

<div class="container">
    <h2>Registrar Devolución</h2>
    <form method="POST" action="">
        <label>Código del préstamo:</label>
        <input type="text" name="codigo" value="<?php echo htmlspecialchars($prestamo['codigo']); ?>" readonly>

        <label>Alumno:</label>
        <input type="text" value="<?php echo htmlspecialchars($prestamo['nombre_alumno'] . ' ' . $prestamo['ap_pat'] . ' ' . $prestamo['ap_mat']); ?>" readonly>

        <label>Título del libro:</label>
        <input type="text" value="<?php echo htmlspecialchars($prestamo['titulo']); ?>" readonly>

        <label>Autor:</label>
        <input type="text" value="<?php echo htmlspecialchars($prestamo['autor']); ?>" readonly>

        <label>Fecha de préstamo:</label>
        <input type="text" value="<?php echo htmlspecialchars($prestamo['fecha_prestamo']); ?>" readonly>

        <label>Fecha de devolución programada:</label>
        <input type="text" value="<?php echo htmlspecialchars($prestamo['fecha_devolucion']); ?>" readonly>

        <label for="fecha_real_devolucion">Fecha real de devolución:</label>
        <input type="date" name="fecha_real_devolucion" id="fecha_real_devolucion" required>

        <button type="submit">Registrar devolución</button>
    </form>
</div>

</body>
</html>
