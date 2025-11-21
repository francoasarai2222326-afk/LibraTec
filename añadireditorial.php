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

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $editorial = trim($_POST['editorial'] ?? '');

    if ($editorial === '') {
        $mensaje = "El nombre de la editorial no puede estar vacío.";
    } else {
        // Insertar editorial
        $sqlInsert = "INSERT INTO Editoriales (editorial) VALUES (?)";
        $stmtInsert = $conn->prepare($sqlInsert);
        $stmtInsert->bind_param("s", $editorial);

        if ($stmtInsert->execute()) {
            // Redirigir a editoriales.php después de agregar
            header("Location: editoriales.php");
            exit();
        } else {
            $mensaje = "Error al agregar la editorial. Inténtalo de nuevo.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Añadir Editorial - LIBRATEC</title>
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
        max-width: 600px;
        margin: auto;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }

    h1 {
        color: #002b80;
        margin-bottom: 20px;
        font-weight: 700;
        font-size: 1.8rem;
        text-align: center;
    }

    form {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    label {
        font-weight: 600;
        color: #0040a0;
        font-size: 1.1rem;
    }

    input[type="text"] {
        padding: 12px 15px;
        border-radius: 8px;
        border: 1px solid #ccc;
        font-size: 1rem;
        transition: border-color 0.25s;
    }
    input[type="text"]:focus {
        outline: none;
        border-color: #0040a0;
        box-shadow: 0 0 6px rgba(0,64,160,0.4);
    }

    .btn-submit {
        padding: 12px 20px;
        background-color: #28a745;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 700;
        font-size: 1.1rem;
        cursor: pointer;
        transition: background-color 0.25s;
    }
    .btn-submit:hover {
        background-color: #1e7e34;
    }

    .btn-cancel {
        padding: 12px 20px;
        background-color: #6c757d;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 700;
        font-size: 1.1rem;
        cursor: pointer;
        transition: background-color 0.25s;
        margin-top: 10px;
        text-align: center;
        display: block;
        text-decoration: none;
    }
    .btn-cancel:hover {
        background-color: #5a6268;
    }

    .message {
        color: red;
        font-weight: 600;
        text-align: center;
    }

    @media (max-width: 480px) {
        main.main {
            padding: 70px 15px 20px;
            width: 90%;
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
    <h1>Añadir Editorial</h1>

    <?php if ($mensaje !== ""): ?>
        <p class="message"><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>

    <form method="POST" action="añadireditorial.php" autocomplete="off">
        <label for="editorial">Nombre de la editorial:</label>
        <input 
            type="text" 
            id="editorial" 
            name="editorial" 
            required
            maxlength="100"
            value="<?php echo isset($_POST['editorial']) ? htmlspecialchars($_POST['editorial']) : ''; ?>"
        />

        <button type="submit" class="btn-submit"><i class="fa fa-save"></i> Guardar Editorial</button>
    </form>

    <a href="editoriales.php" class="btn-cancel">Cancelar</a>
</main>

</body>
</html>
