<?php
include("conexion.php");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Biblioteca - Alumnos</title>
    <link rel="stylesheet" href="estilos.css"> <!-- opcional -->
</head>
<body>
    <h1>Lista de Alumnos</h1>
    <table border="1" cellpadding="10">
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Apellido Paterno</th>
            <th>Apellido Materno</th>
            <th>Correo</th>
            <th>Teléfono</th>
            <th>No. de Control</th>
            <th>Semestre</th>
            <th>Carrera</th>
        </tr>
        <?php
        $resultado = $conn->query("SELECT * FROM Alumnos");

        while($fila = $resultado->fetch_assoc()){
            echo "<tr>
                    <td>{$fila['id_alumno']}</td>
                    <td>{$fila['nombre']}</td>
                    <td>{$fila['apellido_paterno']}</td>
                    <td>{$fila['apellido_materno']}</td>
                    <td>{$fila['correo']}</td>
                    <td>{$fila['telefono']}</td>
                    <td>{$fila['no_control']}</td>
                    <td>{$fila['semestre']}</td>
                    <td>{$fila['carrera']}</td>
                  </tr>";
        }
        ?>
    </table>
    <script src="script.js"></script> <!-- opcional -->
</body>
</html>
