<?php
session_start();
include("conexion.php");

// Verificar sesión activa y que sea personal
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== "personal") {
    header("Location: login.html");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "ID de autor inválido.";
    exit();
}

$id_autor = (int)$_GET['id'];

// Obtener nombre del autor para consultas
$sqlAutor = "SELECT autor FROM Autores WHERE id_autor = ?";
$stmtAutor = $conn->prepare($sqlAutor);
$stmtAutor->bind_param("i", $id_autor);
$stmtAutor->execute();
$resAutor = $stmtAutor->get_result();

if ($resAutor->num_rows === 0) {
    echo "Autor no encontrado.";
    exit();
}

$autor = $resAutor->fetch_assoc();
$nombreAutor = $autor['autor'];

// Iniciar transacción
$conn->begin_transaction();

try {
    // 1. Obtener los libros del autor
    $sqlLibros = "SELECT isbn, id_libro FROM Libros WHERE autor = ?";
    $stmtLibros = $conn->prepare($sqlLibros);
    $stmtLibros->bind_param("s", $nombreAutor);
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

            // 5. Eliminar libros del autor
            $sqlEliminarLibros = "DELETE FROM Libros WHERE autor = ?";
            $stmtEliminarLibros = $conn->prepare($sqlEliminarLibros);
            $stmtEliminarLibros->bind_param("s", $nombreAutor);
            if (!$stmtEliminarLibros->execute()) {
                throw new Exception("Error al eliminar libros del autor.");
            }
        }
    }

    // 6. Finalmente eliminar el autor
    $sqlEliminarAutor = "DELETE FROM Autores WHERE id_autor = ?";
    $stmtEliminarAutor = $conn->prepare($sqlEliminarAutor);
    $stmtEliminarAutor->bind_param("i", $id_autor);
    if (!$stmtEliminarAutor->execute()) {
        throw new Exception("Error al eliminar autor.");
    }

    // Confirmar transacción
    $conn->commit();

    // Redirigir con mensaje
    header("Location: autores.php?msg=autor_eliminado");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    echo "Error: " . $e->getMessage();
}
?>
