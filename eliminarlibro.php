<?php
include("conexion.php");
session_start();

if (!isset($_GET['id_libro'])) {
    echo "Libro no especificado.";
    exit;
}

$id_libro = $_GET['id_libro'];

$sql_check = "SELECT * FROM Libros WHERE id_libro = ?";
$stmt = $conn->prepare($sql_check);
$stmt->bind_param("i", $id_libro);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "El libro no existe o ya fue eliminado.";
    exit;
}

$sql_delete = "DELETE FROM Libros WHERE id_libro = ?";
$stmt = $conn->prepare($sql_delete);
$stmt->bind_param("i", $id_libro);

if ($stmt->execute()) {
    header("Location: registros.php");
    exit;
} else {
    echo "Error al eliminar el libro: " . $conn->error;
}
?>
