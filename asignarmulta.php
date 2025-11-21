<?php
session_start();
include("conexion.php");

// Verificar sesión activa
if (!isset($_SESSION['tipo_usuario'])) {
    header("Location: login.html");
    exit();
}

// Datos del personal que atiende
$no_control_personal = $_SESSION['usuario'];
$sqlNombre = "SELECT nombre, apellido_paterno, apellido_materno FROM PersonalBiblioteca WHERE no_control = ?";
$stmt = $conn->prepare($sqlNombre);
$stmt->bind_param("s", $no_control_personal);
$stmt->execute();
$result = $stmt->get_result();
$nombreCompleto = "Personal Desconocido";
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $nombreCompleto = $row['nombre'] . " " . $row['apellido_paterno'] . " " . $row['apellido_materno'];
}

// Obtener datos del préstamo
$codigo = $_POST['codigo'] ?? $_GET['codigo'] ?? null;
$no_control = $_POST['no_control'] ?? $_GET['no_control'] ?? null;

if (!$codigo || !$no_control) {
    die("<p style='color:red; font-weight:bold;'>Código o número de control no especificado.</p>");
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
    die("<p style='color:red; font-weight:bold;'>No se encontró el préstamo con ese código.</p>");
}

// Insertar multa al enviar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['monto'], $_POST['motivo'])) {
    $monto = $_POST['monto'];
    $motivo = $_POST['motivo'];

    $insert = $conn->prepare("INSERT INTO Multas (codigo, no_control, monto, motivo, pagada) VALUES (?, ?, ?, ?, 'no')");
    $insert->bind_param("ssds", $codigo, $no_control, $monto, $motivo);
    $insert->execute();

    if ($insert->affected_rows > 0) {
        echo "<script>alert('Multa asignada correctamente'); window.location.href='controldeprestamos.php?isbn={$prestamo['isbn']}';</script>";
        exit();
    } else {
        echo "<script>alert('Error al asignar la multa');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar Multa - LibraTec</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", sans-serif;
        }

        body {
            background-color: #eef3f7;
            color: #333;
        }

        /* Navbar */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(90deg, #0040a0, #002b80);
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 1000;
            flex-wrap: wrap;
        }

        .navbar .logo {
            font-weight: 700;
            font-size: 1.4rem;
            letter-spacing: 2px;
        }

        .nav-links {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }

        .nav-links a {
            background: transparent;
            border: 2px solid white;
            border-radius: 6px;
            color: white;
            font-weight: 600;
            padding: 6px 12px;
            text-decoration: none;
            transition: background-color 0.25s, color 0.25s;
        }

        .nav-links a:hover {
            background-color: white;
            color: #0040a0;
        }

        .user-name {
            font-weight: 600;
            margin-top: 8px;
        }

        .main {
            padding: 90px 20px 30px;
            max-width: 800px;
            margin: auto;
        }

        h1 {
            color: #002b80;
            margin-bottom: 20px;
            text-align: center;
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 20px;
        }

        .info {
            margin-bottom: 20px;
        }

        .info p {
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .info span {
            font-weight: bold;
            color: #002b80;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-weight: 600;
        }

        input[type="number"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            resize: vertical;
        }

        button {
            background: #0040a0;
            color: white;
            font-weight: 600;
            padding: 10px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: #002b80;
        }

        @media (max-width: 768px) {
            .navbar {
                justify-content: center;
            }

            .nav-links a {
                font-size: 0.85rem;
                padding: 5px 10px;
            }

            .main {
                padding: 100px 15px 20px;
            }

            .card {
                padding: 15px;
            }

            input, textarea, button {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .navbar .logo {
                font-size: 1.1rem;
            }

            .user-name {
                font-size: 0.9rem;
            }

            h1 {
                font-size: 1.2rem;
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
    <h1>Asignar Multa</h1>
    <div class="card">
        <div class="info">
            <p><span>Código del préstamo:</span> <?php echo $prestamo['codigo']; ?></p>
            <p><span>Alumno:</span> <?php echo $prestamo['nombre_alumno'] . " " . $prestamo['ap_pat'] . " " . $prestamo['ap_mat']; ?></p>
            <p><span>Libro:</span> <?php echo $prestamo['titulo']; ?></p>
            <p><span>Autor:</span> <?php echo $prestamo['autor']; ?></p>
            <p><span>Fecha de préstamo:</span> <?php echo $prestamo['fecha_prestamo']; ?></p>
            <p><span>Fecha de devolución:</span> <?php echo $prestamo['fecha_devolucion']; ?></p>
        </div>

        <form method="POST">
            <input type="hidden" name="codigo" value="<?php echo $codigo; ?>">
            <input type="hidden" name="no_control" value="<?php echo $no_control; ?>">

            <label for="monto">Monto de la multa (MXN):</label>
            <input type="number" name="monto" id="monto" step="0.01" min="1" required>

            <label for="motivo">Motivo:</label>
            <textarea name="motivo" id="motivo" rows="3" required></textarea>

            <button type="submit">Asignar Multa</button>
        </form>
    </div>
</main>

</body>
</html>



