<?php
session_start();
include("conexion.php");

// Validar sesión
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== "personal") {
    header("Location: login.html");
    exit();
}

$no_control = $_SESSION['usuario'];
$sqlPersonal = "SELECT nombre, apellido_paterno, apellido_materno FROM PersonalBiblioteca WHERE no_control = ?";
$stmt = $conn->prepare($sqlPersonal);
$stmt->bind_param("s", $no_control);
$stmt->execute();
$res = $stmt->get_result();
$personal = $res->fetch_assoc();
$nombreCompleto = $personal ? $personal['nombre'] . " " . $personal['apellido_paterno'] . " " . $personal['apellido_materno'] : "Personal";


$busqueda_codigo = $_GET['busqueda_codigo'] ?? "";


if ($busqueda_codigo !== "") {
    $sql = "SELECT 
                m.id_multa,
                m.codigo, 
                m.no_control, 
                CONCAT(a.nombre, ' ', a.apellido_paterno, ' ', a.apellido_materno) AS alumno_nombre,
                l.titulo,
                l.isbn,
                p.fecha_prestamo,
                p.fecha_devolucion,
                p.fecha_real_devolucion,
                p.estado,
                p.observaciones,
                m.monto,
                m.motivo,
                m.pagada
            FROM Multas m
            JOIN Prestamos p ON m.codigo = p.codigo
            JOIN Alumnos a ON m.no_control = a.no_control
            JOIN Libros l ON p.isbn = l.isbn
            WHERE m.codigo LIKE ?
            ORDER BY p.fecha_prestamo DESC";
    $busqueda_param = "%".$busqueda_codigo."%";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $busqueda_param);
} else {
    $sql = "SELECT 
                m.id_multa,
                m.codigo, 
                m.no_control, 
                CONCAT(a.nombre, ' ', a.apellido_paterno, ' ', a.apellido_materno) AS alumno_nombre,
                l.titulo,
                l.isbn,
                p.fecha_prestamo,
                p.fecha_devolucion,
                p.fecha_real_devolucion,
                p.estado,
                p.observaciones,
                m.monto,
                m.motivo,
                m.pagada
            FROM Multas m
            JOIN Prestamos p ON m.codigo = p.codigo
            JOIN Alumnos a ON m.no_control = a.no_control
            JOIN Libros l ON p.isbn = l.isbn
            ORDER BY p.fecha_prestamo DESC";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Multas - LibraTec</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Segoe UI", sans-serif; }
    body { background-color: #eef3f7; }

    
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

    .main { padding: 90px 20px 20px; max-width: 1600px; margin: auto; }
    h1 { color: #002b80; margin-bottom: 20px; }

   
    .search-form {
        margin-bottom: 20px;
        display: flex;
        max-width: 400px;
        gap: 10px;
    }
    .search-form input[type="text"] {
        flex: 1;
        padding: 8px 12px;
        border-radius: 6px;
        border: 1px solid #ccc;
        font-size: 1rem;
    }
    .search-form button {
        padding: 8px 16px;
        background-color: #0040a0;
        color: white;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.25s;
    }
    .search-form button:hover {
        background-color: #002b80;
    }

   
    .table-wrapper {
        width: 100%; overflow-x: auto; background: white;
        border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    table { border-collapse: collapse; width: max-content; min-width: 1400px; }
    th, td {
        white-space: nowrap; padding: 12px 15px; text-align: center; border-bottom: 1px solid #ddd;
        vertical-align: middle;
    }
    th { background-color: #0040a0; color: white; font-weight: 600; }
    tr:hover { background-color: #f4f8ff; }

  
    .radio-group {
        display: flex; justify-content: center; gap: 1rem;
    }
    .radio-group label { cursor: pointer; user-select: none; }
    input[type="radio"] {
        appearance: none;
        -webkit-appearance: none;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: 2px solid #0040a0;
        outline: none;
        cursor: pointer;
        transition: background-color 0.2s ease-in-out;
        vertical-align: middle;
    }
    input[type="radio"]:checked { background-color: #0040a0; }

    @media (max-width: 768px) {
        th, td { font-size: 0.85rem; padding: 8px; }
        .table-wrapper { border-radius: 0; }
        h1 { font-size: 1.3rem; text-align: center; }
        table { min-width: 900px; }
    }
</style>
<script>
function pagarMulta(codigo, no_control) {
    if (confirm('¿Confirmas que deseas marcar esta multa como PAGADA?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'pagarmulta.php';

        const inputCodigo = document.createElement('input');
        inputCodigo.type = 'hidden';
        inputCodigo.name = 'codigo';
        inputCodigo.value = codigo;
        form.appendChild(inputCodigo);

        const inputNoControl = document.createElement('input');
        inputNoControl.type = 'hidden';
        inputNoControl.name = 'no_control';
        inputNoControl.value = no_control;
        form.appendChild(inputNoControl);

        document.body.appendChild(form);
        form.submit();
    } else {
        const radios = document.getElementsByName('pagada_' + codigo);
        radios.forEach(radio => { if (radio.checked) radio.checked = false; });
    }
}
</script>
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

<main class="main">
    <h1>Listado General de Multas</h1>

    <form class="search-form" method="GET" action="multas.php">
        <input 
          type="text" 
          name="busqueda_codigo" 
          placeholder="Buscar por código de multa..." 
          value="<?php echo htmlspecialchars($busqueda_codigo); ?>"
          autocomplete="off"
        />
        <button type="submit">Buscar</button>
    </form>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID Multa</th>
                    <th>Código Préstamo</th>
                    <th>No. Control</th>
                    <th>Alumno</th>
                    <th>Título</th>
                    <th>ISBN</th>
                    <th>Fecha Préstamo</th>
                    <th>Fecha Devolución</th>
                    <th>Fecha Real Devolución</th>
                    <th>Estado</th>
                    <th>Observaciones</th>
                    <th>Monto</th>
                    <th>Motivo</th>
                    <th>Pagada</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id_multa']; ?></td>
                            <td><?php echo htmlspecialchars($row['codigo']); ?></td>
                            <td><?php echo htmlspecialchars($row['no_control']); ?></td>
                            <td><?php echo htmlspecialchars($row['alumno_nombre']); ?></td>
                            <td><?php echo htmlspecialchars($row['titulo']); ?></td>
                            <td><?php echo htmlspecialchars($row['isbn']); ?></td>
                            <td><?php echo $row['fecha_prestamo']; ?></td>
                            <td><?php echo $row['fecha_devolucion']; ?></td>
                            <td><?php echo $row['fecha_real_devolucion'] ?: '-'; ?></td>
                            <td><?php echo ucfirst($row['estado']); ?></td>
                            <td><?php echo $row['observaciones'] ?: '-'; ?></td>
                            <td><?php echo number_format($row['monto'], 2); ?></td>
                            <td><?php echo htmlspecialchars($row['motivo']); ?></td>
                            <td>
                                <div class="radio-group">
                                    <label>
                                        <input type="radio" name="pagada_<?php echo $row['codigo']; ?>" value="no" <?php echo ($row['pagada'] === 'no') ? 'checked' : ''; ?> disabled> No
                                    </label>
                                    <label>
                                        <input type="radio" name="pagada_<?php echo $row['codigo']; ?>" value="si" <?php echo ($row['pagada'] === 'si') ? 'checked' : ''; ?> 
                                            onclick="pagarMulta('<?php echo $row['codigo']; ?>', '<?php echo $row['no_control']; ?>')"
                                            <?php echo ($row['pagada'] === 'si') ? 'disabled' : ''; ?>>
                                        Sí
                                    </label>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="14">No hay multas registradas.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>
