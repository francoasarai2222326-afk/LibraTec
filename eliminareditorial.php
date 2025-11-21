<?php
session_start();
include("conexion.php");

// Verificar sesión activa y que sea personal
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== "personal") {
    header("Location: login.html");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "ID de editorial inválido.";
    exit();
}

$id_editorial = (int)$_GET['id'];

// Obtener nombre de la editorial para consultas
$sqlEditorial = "SELECT editorial FROM Editoriales WHERE id_editorial = ?";
$stmtEditorial = $conn->prepare($sqlEditorial);
$stmtEditorial->bind_param("i", $id_editorial);
$stmtEditorial->execute();
$resEditorial = $stmtEditorial->get_result();

if ($resEditorial->num_rows === 0) {
    echo "Editorial no encontrada.";
    exit();
}

$editorial = $resEditorial->fetch_assoc();
$nombreEditorial = $editorial['editorial'];

// Iniciar transacción
$conn->begin_transaction();

try {
    // 1. Obtener los libros de la editorial
    $sqlLibros = "SELECT isbn, id_libro FROM Libros WHERE editorial = ?";
    $stmtLibros = $conn->prepare($sqlLibros);
    $stmtLibros->bind_param("s", $nombreEditorial);
    $stmtLibros->execute();
    $resLibros = $stmtLibros->get_result();

    if ($resLibros->num_rows > 0) {
        $isbns = [];
        while ($libro = $resLibros->fetch_assoc()) {
            $isbns[] = $libro['isbn'];
        }

        // Si hay libros
        if (count($isbns) > 0) {
            $isbn_list = "'" . implode("','", $isbns) . "'";

            // 2. Obtener códigos de préstamos de esos libros
            $sqlPrestamos = "SELECT codigo FROM Prestamos WHERE isbn IN ($isbn_list)";
            $resPrestamos = $conn->query($sqlPrestamos);

            $codigos = [];
            if ($resPrestamos->num_rows > 0) {
                while ($fila = $resPrestamos->fetch_assoc()) {
                    $codigos[] = $fila['codigo'];
                }
            }

            if (count($codigos) > 0) {
                $codigos_list = "'" . implode("','", $codigos) . "'";

                // 3. Eliminar multas relacionadas a esos préstamos
                $sqlEliminarMultas = "DELETE FROM Multas WHERE codigo IN ($codigos_list)";
                if (!$conn->query($sqlEliminarMultas)) {
                    throw new Exception("Error al eliminar multas relacionadas.");
                }

                // 4. Eliminar préstamos de esos libros
                $sqlEliminarPrestamos = "DELETE FROM Prestamos WHERE codigo IN ($codigos_list)";
                if (!$conn->query($sqlEliminarPrestamos)) {
                    throw new Exception("Error al eliminar préstamos relacionados.");
                }
            }

            // 5. Eliminar libros de la editorial
            $sqlEliminarLibros = "DELETE FROM Libros WHERE editorial = ?";
            $stmtEliminarLibros = $conn->prepare($sqlEliminarLibros);
            $stmtEliminarLibros->bind_param("s", $nombreEditorial);
            if (!$stmtEliminarLibros->execute()) {
                throw new Exception("Error al eliminar libros de la editorial.");
            }
        }
    }

    // 6. Finalmente eliminar la editorial
    $sqlEliminarEditorial = "DELETE FROM Editoriales WHERE id_editorial = ?";
    $stmtEliminarEditorial = $conn->prepare($sqlEliminarEditorial);
    $stmtEliminarEditorial->bind_param("i", $id_editorial);
    if (!$stmtEliminarEditorial->execute()) {
        throw new Exception("Error al eliminar editorial.");
    }

    // Confirmar transacción
    $conn->commit();

    // Redirigir con mensaje
    header("Location: editoriales.php?msg=editorial_eliminada");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    echo "Error: " . $e->getMessage();
}
?>
