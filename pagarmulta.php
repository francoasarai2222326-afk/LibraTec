<?php
session_start();
include("conexion.php");

if (!isset($_SESSION['tipo_usuario'])) {
    header("Location: login.html");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = $_POST['codigo'] ?? '';
    $no_control = $_POST['no_control'] ?? '';
    $monto = $_POST['monto'] ?? 0;
    $motivo = $_POST['motivo'] ?? '';
    $metodo_pago = $_POST['metodo_pago'] ?? '';

    if (empty($codigo) || empty($no_control) || empty($monto) || empty($metodo_pago)) {
        $error = "Por favor llena todos los campos requeridos.";
    } else {
        
        $stmt_check = $conn->prepare("SELECT id_multa FROM Multas WHERE codigo = ? AND no_control = ? AND pagada = 'no'");
        $stmt_check->bind_param("ss", $codigo, $no_control);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            
            $update = $conn->prepare("UPDATE Multas SET monto = ?, motivo = ?, pagada = 'si' WHERE codigo = ? AND no_control = ? AND pagada = 'no'");
            $update->bind_param("dsss", $monto, $motivo, $codigo, $no_control);
            if ($update->execute()) {
                
                $stmtAlumno = $conn->prepare("SELECT nombre, apellido_paterno, apellido_materno FROM Alumnos WHERE no_control = ?");
                $stmtAlumno->bind_param("s", $no_control);
                $stmtAlumno->execute();
                $resultAlumno = $stmtAlumno->get_result();
                $alumno = $resultAlumno->fetch_assoc();
                $stmtAlumno->close();

                
                $stmtPrestamo = $conn->prepare("SELECT isbn FROM Prestamos WHERE codigo = ?");
                $stmtPrestamo->bind_param("s", $codigo);
                $stmtPrestamo->execute();
                $resultPrestamo = $stmtPrestamo->get_result();
                $prestamo = $resultPrestamo->fetch_assoc();
                $stmtPrestamo->close();

                $titulo = "";
                if ($prestamo) {
                    $isbn = $prestamo['isbn'];
                    $stmtLibro = $conn->prepare("SELECT titulo FROM Libros WHERE isbn = ?");
                    $stmtLibro->bind_param("s", $isbn);
                    $stmtLibro->execute();
                    $resultLibro = $stmtLibro->get_result();
                    $libro = $resultLibro->fetch_assoc();
                    $titulo = $libro['titulo'] ?? '';
                    $stmtLibro->close();
                }

                
                $_SESSION['ticket_pago'] = [
                    'codigo' => $codigo,
                    'no_control' => $no_control,
                    'monto' => $monto,
                    'motivo' => $motivo,
                    'metodo_pago' => $metodo_pago,
                    'nombre' => $alumno['nombre'] ?? '',
                    'apellido_paterno' => $alumno['apellido_paterno'] ?? '',
                    'apellido_materno' => $alumno['apellido_materno'] ?? '',
                    'titulo' => $titulo
                ];

                header("Location: ticketpago.php");
                exit();
            } else {
                $error = "Error al actualizar la multa.";
            }
        } else {
            $error = "No existe multa pendiente para ese préstamo y alumno.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Pagar Multa - LibraTec</title>
<link rel="stylesheet" href="estilos.css">
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
    .navbar .logo { font-weight: 700; font-size: 1.4rem; letter-spacing: 2px; }
    .navbar .nav-links { margin-left: auto; display: flex; gap: 1rem; }
    .navbar .nav-links a {
        background: transparent; border: 2px solid white; border-radius: 6px;
        color: white; font-weight: 600; padding: 6px 15px; text-decoration: none;
        transition: background-color 0.25s, color 0.25s;
    }
    .navbar .nav-links a:hover { background-color: white; color: #0040a0; }
    .navbar .user-name { margin-left: 1.5rem; font-weight: 600; }

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
    input[type="text"], input[type="number"], textarea {
        width: 100%; padding: 10px; border: 1px solid #ccc;
        border-radius: 6px; margin-top: 5px;
        font-size: 1rem;
        box-sizing: border-box;
    }
    textarea { resize: vertical; min-height: 80px; }

    .radio-group {
        margin-top: 15px;
        display: flex; gap: 20px; align-items: center;
    }
    .radio-group input[type="radio"] {
        margin-right: 8px;
    }

    .btn {
        margin-top: 25px; width: 100%; padding: 12px;
        background-color: #0040a0; color: white; font-size: 1.1rem;
        border: none; border-radius: 8px; cursor: pointer;
        font-weight: 700;
        transition: background-color 0.3s ease;
    }
    .btn:hover {
        background-color: #002b5c;
    }
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
    <h2>Pagar Multa</h2>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="pagarmulta.php">
        <label for="codigo">Código del Préstamo</label>
        <input type="text" name="codigo" id="codigo" required>

        <label for="no_control">No. Control del Alumno</label>
        <input type="text" name="no_control" id="no_control" required>

        <label for="motivo">Motivo de la Multa</label>
        <textarea name="motivo" id="motivo" required></textarea>

        <label for="monto">Monto a Pagar</label>
        <input type="number" step="0.01" name="monto" id="monto" min="0" required>

        <label>Método de Pago</label>
        <div class="radio-group">
            <label><input type="radio" name="metodo_pago" value="Efectivo" required> Efectivo</label>
            <label><input type="radio" name="metodo_pago" value="Transferencia"> Transferencia</label>
        </div>

        <button type="submit" class="btn">Registrar Pago y Generar Ticket</button>
    </form>
</div>

</body>
</html>
