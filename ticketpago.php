<?php
session_start();
include("conexion.php");

if (!isset($_SESSION['tipo_usuario'])) {
    header("Location: login.html");
    exit();
}

if (!isset($_SESSION['ticket_pago'])) {
    header("Location: controldemultas.php");
    exit();
}

$ticket = $_SESSION['ticket_pago'];
$codigo = isset($ticket['codigo']) && !empty($ticket['codigo']) ? $ticket['codigo'] : 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Ticket de Pago - LibraTec</title>
<style>
body {
  background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('imagen/fondologin.jpg') no-repeat center/cover;
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  font-family: 'Courier New', monospace;
  margin: 0;
}
.btn-back {
  position: fixed;
  top: 20px;
  left: 20px;
  background: #0040a0;
  color: white;
  border: none;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  font-size: 2rem;
  cursor: pointer;
  box-shadow: 0 2px 5px rgba(0,0,0,0.4);
}
.btn-back:hover { background: #002b80; }
.ticket {
  background: white;
  padding: 25px;
  width: 360px;
  border-radius: 12px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.5);
  display: flex;
  flex-direction: column;
  align-items: center;
}
.ticket h2 { margin: 10px 0; }
.ticket p { width: 100%; margin: 5px 0; border-bottom: 1px dashed #ccc; padding-bottom: 3px; }
.btn-print {
  margin-top: 20px;
  background: #0040a0;
  color: white;
  border: none;
  padding: 10px 18px;
  border-radius: 8px;
  cursor: pointer;
}
.btn-print:hover { background: #002b80; }
@media print {
  .btn-back, .btn-print { display: none !important; }
  body { background: white !important; }
}
</style>
</head>
<body>

<!-- 🔹 Botón de regreso -->
<button class="btn-back" title="Regresar a Control de Multas" 
onclick="window.location.href='controldemultas.php?codigo=<?= urlencode($codigo) ?>'">
&#8592;
</button>

<div class="ticket">
  <h3>Instituto Tecnológico de Pachuca</h3>
  <img src="imagen/teclogo.jpg" width="100" alt="Logo">
  <h2>LIBRATEC</h2>
  
  <p><strong>Nombre del Alumno:</strong> <?= htmlspecialchars($ticket['nombre'] . ' ' . $ticket['apellido_paterno'] . ' ' . $ticket['apellido_materno']) ?></p>
  <p><strong>No. de Control:</strong> <?= htmlspecialchars($ticket['no_control']) ?></p>
  <p><strong>Título del Libro:</strong> <?= htmlspecialchars($ticket['titulo']) ?></p>
  <p><strong>Código del Préstamo:</strong> <?= htmlspecialchars($ticket['codigo']) ?></p>
  <p><strong>Motivo de la Multa:</strong> <?= htmlspecialchars($ticket['motivo']) ?></p>
  <p><strong>Monto Pagado:</strong> $<?= number_format($ticket['monto'], 2) ?></p>
  <p><strong>Método de Pago:</strong> <?= ucfirst(htmlspecialchars($ticket['metodo_pago'])) ?></p>

  <button class="btn-print" onclick="window.print()">Imprimir Ticket</button>
</div>

</body>
</html>

