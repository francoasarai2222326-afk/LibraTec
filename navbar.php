<?php
$tipo_usuario = $_SESSION['tipo_usuario'];
$nombreCompleto = $_SESSION['nombre'];
?>

<style>
.navbar {
width: 100%;
background: #003b95;
color: white;
padding: 12px 25px;
display: flex;
align-items: center;
justify-content: space-between;
flex-wrap: wrap;
font-family: "Segoe UI", sans-serif;
}

.nav-left {
font-size: 20px;
font-weight: 700;
}

.nav-links a {
color: white;
margin-left: 20px;
text-decoration: none;
font-weight: 500;
}

.nav-links a:hover {
text-decoration: underline;
}

.user-info {
margin-left: 20px;
opacity: 0.8;
}

.logout {
margin-left: 20px;
color: #ffebee;
cursor: pointer;
font-weight: 600;
}
</style>

<div class="navbar">
<div class="nav-left">LIBRATEC</div>

<div class="nav-links">
<a href="catalogo.php">Catálogo</a>

<?php if ($tipo_usuario == "estudiante"): ?>
<a href="misprestamos.php">Mis Préstamos</a>
<a href="mismultas.php">Mis Multas</a>
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
</div>




<div class="user-info"><?php echo $nombreCompleto; ?></div>
<div class="logout" onclick="window.location='login.php'">Salir</div>
</div>

