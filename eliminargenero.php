<?php
session_start();
include("conexion.php");

// Verificar sesión activa y que sea personal
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== "personal") {
    header("Location: login.html");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "ID de género inválido.";
    exit();
}

$id_genero = (int)$_GET['id'];

// Obtener nombre del género para consultas
$sqlGenero = "SELECT genero FROM Generos WHERE id_genero = ?";
$stmtGenero = $conn->prepare($sqlGenero);
$stmtGenero->bind_param("i", $id_genero);
$stmtGenero->execute();
$resGenero = $stmtGenero->get_result();

if ($resGenero->num_rows === 0) {
    echo "Género no encontrado.";
    exit();
}

$genero = $resGenero->fetch_assoc();
$nombreGenero = $genero['genero'];

// Iniciar transacción
$conn->begin_transaction();

try {
    // 1. Obtener los libros del género
    $sqlLibros = "SELECT isbn, id_libro FROM Libros WHERE genero = ?";
    $stmtLibros = $conn->prepare($sqlLibros);
    $stmtLibros->bind_param("s", $nombreGenero);
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

            // 5. Eliminar libros del género
            $sqlEliminarLibros = "DELETE FROM Libros WHERE genero = ?";
            $stmtEliminarLibros = $conn->prepare($sqlEliminarLibros);
            $stmtEliminarLibros->bind_param("s", $nombreGenero);
            if (!$stmtEliminarLibros->execute()) {
                throw new Exception("Error al eliminar libros del género.");
            }
        }
    }

    // 6. Finalmente eliminar el género
    $sqlEliminarGenero = "DELETE FROM Generos WHERE id_genero = ?";
    $stmtEliminarGenero = $conn->prepare($sqlEliminarGenero);
    $stmtEliminarGenero->bind_param("i", $id_genero);
    if (!$stmtEliminarGenero->execute()) {
        throw new Exception("Error al eliminar género.");
    }

    // Confirmar transacción
    $conn->commit();

    // Redirigir con mensaje
    header("Location: generos.php?msg=genero_eliminado");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    echo "Error: " . $e->getMessage();
}
?>
