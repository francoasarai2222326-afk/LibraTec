<?php
session_start();
include("conexion.php");

// Verificar sesión
if (!isset($_SESSION['tipo_usuario'])) {
    header("Location: login.html");
    exit();
}

$tipo_usuario = $_SESSION['tipo_usuario'];
$usuario_nc = $_SESSION['usuario'] ?? null;

// Obtener nombre del usuario logeado
if ($tipo_usuario == "estudiante") {
    $sqlNombre = "SELECT nombre, apellido_paterno, apellido_materno FROM Alumnos WHERE no_control = ?";
} else {
    $sqlNombre = "SELECT nombre, apellido_paterno, apellido_materno FROM PersonalBiblioteca WHERE no_control = ?";
}
$stmtNombre = $conn->prepare($sqlNombre);
$stmtNombre->bind_param("s", $usuario_nc);
$stmtNombre->execute();
$resNombre = $stmtNombre->get_result();
$nombreCompleto = "Usuario desconocido";

if ($resNombre->num_rows > 0) {
    $d = $resNombre->fetch_assoc();
    $nombreCompleto = $d['nombre']." ".$d['apellido_paterno']." ".$d['apellido_materno'];
}

// PROCESAR FORMULARIO
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // DATOS DEL ALUMNO
    $alumno_nc = $_POST['no_control'];
    $alumno_nombre = $_POST['nombre_alumno'];
    $alumno_ap = $_POST['apellido_paterno_alumno'];
    $alumno_am = $_POST['apellido_materno_alumno'];

    // VALIDAR QUE EL ALUMNO EXISTA
    $checkAlumno = $conn->prepare("SELECT * FROM Alumnos WHERE no_control = ?");
    $checkAlumno->bind_param("s", $alumno_nc);
    $checkAlumno->execute();
    if ($checkAlumno->get_result()->num_rows == 0) {
        echo "ERROR: El alumno no existe.";
        exit();
    }

    // DATOS DEL LIBRO
    $isbn = $_POST['isbn'];

    // VALIDAR QUE EL LIBRO EXISTA
    $checkLibro = $conn->prepare("SELECT * FROM Libros WHERE isbn = ?");
    $checkLibro->bind_param("s", $isbn);
    $checkLibro->execute();
    if ($checkLibro->get_result()->num_rows == 0) {
        echo "ERROR: El ISBN no existe.";
        exit();
    }

    // DATOS DEL PERSONAL
    $personal_nc = $_POST['no_control_personal'];

    // VALIDAR QUE EL PERSONAL EXISTA
    $checkPers = $conn->prepare("SELECT * FROM PersonalBiblioteca WHERE no_control = ?");
    $checkPers->bind_param("s", $personal_nc);
    $checkPers->execute();
    if ($checkPers->get_result()->num_rows == 0) {
        echo "ERROR: El personal no existe.";
        exit();
    }

    // GENERAR CÓDIGO ÚNICO
    $codigo = "PST-" . rand(100000, 999999);

    // FECHAS
    $fecha_prestamo = date("Y-m-d");
    $fecha_devolucion = date("Y-m-d", strtotime("+30 days"));

    // INSERT FINAL (SOLO COLUMNAS EXISTENTES)
    $sqlInsert = "INSERT INTO Prestamos 
        (no_control, isbn, codigo, fecha_prestamo, fecha_devolucion, atendido_por)
        VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sqlInsert);
    $stmt->bind_param("ssssss",
        $alumno_nc,
        $isbn,
        $codigo,
        $fecha_prestamo,
        $fecha_devolucion,
        $personal_nc
    );

    if ($stmt->execute()) {
        header("Location: prestamo.php?codigo=$codigo");
        exit();
    } else {
        echo "ERROR: ".$conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Añadir Préstamo</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
<style>
  *{margin:0;padding:0;box-sizing:border-box;font-family:"Segoe UI";}
  body{background:#eef3f7;}
  .navbar{
    position:fixed;top:0;left:0;right:0;
    background:#002b80;color:white;
    display:flex;align-items:center;
    padding:10px 20px;z-index:9999;
  }
  .navbar .logo{font-weight:700;font-size:1.4rem;}
  .nav-links{margin-left:auto;display:flex;gap:1rem;}
  .nav-links a{
    color:white;text-decoration:none;
    border:2px solid white;padding:6px 15px;border-radius:6px;
  }
  .main{padding:90px 20px;max-width:600px;margin:auto;}
  form{background:white;padding:2rem;border-radius:12px;}
  label{font-weight:600;margin-top:12px;display:block;}
  input{width:100%;padding:10px;margin-top:6px;border:1px solid #ccc;border-radius:6px;}
  button{margin-top:20px;width:100%;padding:12px;background:#007bff;border:none;color:white;font-weight:600;}
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
    <?php else: ?>
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
</nav>

<main class="main">
  <form method="POST">
    <h2>Añadir Préstamo</h2>

    <label>Nombre del Alumno</label>
    <input type="text" name="nombre_alumno" required>

    <label>Apellido Paterno</label>
    <input type="text" name="apellido_paterno_alumno" required>

    <label>Apellido Materno</label>
    <input type="text" name="apellido_materno_alumno" required>

    <label>No. Control del Alumno</label>
    <input type="text" name="no_control" required>

    <label>ISBN del Libro</label>
    <input type="text" name="isbn" required>

    <label>No. Control del Personal</label>
    <input type="text" name="no_control_personal" required>

    <button type="submit">Guardar Préstamo</button>
  </form>
</main>

</body>
</html>
