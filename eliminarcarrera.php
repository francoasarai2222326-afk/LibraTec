<?php
session_start();
include("conexion.php");

// Verificar sesión activa y que sea personal
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== "personal") {
    header("Location: login.html");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "ID de carrera inválido.";
    exit();
}

$id_carrera = (int)$_GET['id'];

// Obtener nombre de la carrera para consultas
$sqlCarrera = "SELECT carrera FROM Carreras WHERE id_carrera = ?";
$stmtCarrera = $conn->prepare($sqlCarrera);
$stmtCarrera->bind_param("i", $id_carrera);
$stmtCarrera->execute();
$resCarrera = $stmtCarrera->get_result();

if ($resCarrera->num_rows === 0) {
    echo "Carrera no encontrada.";
    exit();
}

$carrera = $resCarrera->fetch_assoc();
$nombreCarrera = $carrera['carrera'];

// Iniciar transacción
$conn->begin_transaction();

try {
    // 1. Obtener alumnos que estudian esa carrera
    $sqlAlumnos = "SELECT no_control FROM Alumnos WHERE carrera = ?";
    $stmtAlumnos = $conn->prepare($sqlAlumnos);
    $stmtAlumnos->bind_param("s", $nombreCarrera);
    $stmtAlumnos->execute();
    $resAlumnos = $stmtAlumnos->get_result();

    if ($resAlumnos->num_rows > 0) {
        $no_controls = [];
        while ($alumno = $resAlumnos->fetch_assoc()) {
            $no_controls[] = $alumno['no_control'];
        }

        $no_control_list = "'" . implode("','", $no_controls) . "'";

        // 2. Obtener códigos de préstamos de esos alumnos
        $sqlPrestamos = "SELECT codigo FROM Prestamos WHERE no_control IN ($no_control_list)";
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

            // 4. Eliminar préstamos de esos alumnos
            $sqlEliminarPrestamos = "DELETE FROM Prestamos WHERE codigo IN ($codigos_list)";
            if (!$conn->query($sqlEliminarPrestamos)) {
                throw new Exception("Error al eliminar préstamos relacionados.");
            }
        }

        // 5. Eliminar alumnos de la carrera
        $sqlEliminarAlumnos = "DELETE FROM Alumnos WHERE no_control IN ($no_control_list)";
        if (!$conn->query($sqlEliminarAlumnos)) {
            throw new Exception("Error al eliminar alumnos de la carrera.");
        }
    }

    // 6. Finalmente eliminar la carrera
    $sqlEliminarCarrera = "DELETE FROM Carreras WHERE id_carrera = ?";
    $stmtEliminarCarrera = $conn->prepare($sqlEliminarCarrera);
    $stmtEliminarCarrera->bind_param("i", $id_carrera);
    if (!$stmtEliminarCarrera->execute()) {
        throw new Exception("Error al eliminar la carrera.");
    }

    // Confirmar transacción
    $conn->commit();

    // Redirigir con mensaje
    header("Location: carreras.php?msg=carrera_eliminada");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    echo "Error: " . $e->getMessage();
}
?>
