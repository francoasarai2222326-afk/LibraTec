<?php
session_start();
include("conexion.php");

if (!isset($_SESSION['tipo_usuario'])) {
    header("Location: login.html");
    exit();
}

$codigo = $_GET['codigo'] ?? null;

if (!$codigo) {
    die("Código de préstamo no especificado.");
}

// Obtener la información del préstamo, alumno y libro
$sql = "SELECT 
            p.codigo,
            p.fecha_prestamo,
            p.fecha_devolucion,
            a.no_control,
            CONCAT(a.nombre, ' ', a.apellido_paterno, ' ', a.apellido_materno) AS nombre_completo,
            l.titulo,
            l.autor,
            l.imagen
        FROM Prestamos p
        INNER JOIN Alumnos a ON p.no_control = a.no_control
        INNER JOIN Libros l ON p.isbn = l.isbn
        WHERE p.codigo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $codigo);
$stmt->execute();
$result = $stmt->get_result();
$prestamo = $result->fetch_assoc();

if (!$prestamo) {
    die("No se encontró el préstamo.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Comprobante de Préstamo</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<style>
body {
    background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('imagen/fondologin.jpg') no-repeat center center/cover;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100vh;
    margin: 0;
    font-family: Arial, sans-serif;
}

.card {
    background: white;
    padding: 30px;
    width: 600px;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.5);
    display: flex;
    gap: 20px;
}

.info-section {
    flex: 2;
}

.info-section h2, .info-section h3 { text-align: center; }

.info {
    margin-top: 20px;
    font-size: 1.1rem;
}

.info p { margin: 8px 0; }

.logo-box {
    width: 120px;
    height: 120px;
    margin: auto;
    margin-top: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.logo-box img {
    width: 200px;
    height: 200px;
    object-fit: contain;
}

.book-image {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}

.book-image img {
    width: 250px;
    height: 330px;
    object-fit: cover;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.3);
}

.button {
    display: inline-block;
    margin: 10px 5px 0 5px;
    padding: 10px 20px;
    background: #0047ff;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    text-decoration: none;
    text-align: center;
}

.button:hover {
    background: #0033cc;
}

.buttons-container {
    text-align: center;
    margin-top: 20px;
}

/* Ocultar botones al imprimir */
@media print {
    .buttons-container {
        display: none !important;
    }
}
</style>
</head>
<body>

<div class="card" id="tarjeta">
    <div class="info-section">
        <h3>Instituto Tecnológico de Pachuca</h3>
        <div class="logo-box">
            <img src="imagen/teclogo.jpg" alt="Logo Libratec" />
        </div>
        <h2>LIBRATEC</h2>

        <div class="info">
            <p><strong>Nombre del Estudiante:</strong> <?php echo htmlspecialchars($prestamo['nombre_completo']); ?></p>
            <p><strong>Número de Control:</strong> <?php echo htmlspecialchars($prestamo['no_control']); ?></p>
            <p><strong>Título del Libro:</strong> <?php echo htmlspecialchars($prestamo['titulo']); ?></p>
            <p><strong>Autor:</strong> <?php echo htmlspecialchars($prestamo['autor']); ?></p>
            <p><strong>Fecha de Préstamo:</strong> <?php echo $prestamo['fecha_prestamo']; ?></p>
            <p><strong>Fecha de Entrega:</strong> <?php echo $prestamo['fecha_devolucion']; ?></p>
            <p><strong>Código de Préstamo:</strong> <?php echo htmlspecialchars($prestamo['codigo']); ?></p>
        </div>

        <div class="buttons-container">
            <button class="button" onclick="window.print()">Imprimir</button>
            <button class="button" onclick="descargarPDF()">Descargar PDF</button>
        </div>
    </div>

    <div class="book-image">
        <img src="<?php echo htmlspecialchars($prestamo['imagen']); ?>" alt="Imagen del libro" />
    </div>
</div>

<script>
function descargarPDF() {
    const tarjeta = document.getElementById("tarjeta");
    const botones = document.querySelector('.buttons-container');
    botones.style.display = 'none';

    html2canvas(tarjeta, { scale: 2 }).then(canvas => {
        const imgData = canvas.toDataURL("image/png");
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('p', 'mm', 'a4');

        const pdfWidth = pdf.internal.pageSize.getWidth() - 20;
        const pdfHeight = (canvas.height * pdfWidth) / canvas.width;

        pdf.addImage(imgData, 'PNG', 10, 10, pdfWidth, pdfHeight);
        pdf.save("Comprobante_Prestamo_<?php echo htmlspecialchars($prestamo['no_control']); ?>.pdf");

        botones.style.display = 'block';
    }).catch(err => {
        console.error(err);
        alert("Ocurrió un error al generar el PDF");
        botones.style.display = 'block';
    });
}
</script>

</body>
</html>
