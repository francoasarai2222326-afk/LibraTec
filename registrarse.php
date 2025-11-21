<?php
// registrarse.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>LibraTec - Registrarse</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }
    body {
      background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('imagen/fondologin.jpg') no-repeat center center/cover;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 20px;
      color: #000;
    }
    .container {
      background: #fff;
      width: 100%;
      max-width: 450px;
      border-radius: 20px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
      padding: 40px 30px;
      text-align: center;
      position: relative;
    }
    .container img.icon {
      width: 80px;
      margin-bottom: 15px;
    }
    h1 {
      font-size: 1.8rem;
      font-weight: 700;
      margin-bottom: 10px;
    }
    p {
      color: #555;
      margin-bottom: 25px;
      font-size: 0.9rem;
    }
    .user-type {
      display: flex;
      justify-content: space-between;
      margin-bottom: 30px;
    }
    .user-type button {
      width: 48%;
      padding: 12px 0;
      font-size: 1rem;
      font-weight: 600;
      border: 1.5px solid #ddd;
      border-radius: 10px;
      background: #fff;
      color: #333;
      cursor: pointer;
      transition: 0.3s;
    }
    .user-type button.active {
      border-color: #0047ff;
      background: #eaf0ff;
      color: #0047ff;
    }
    form {
      text-align: left;
    }
    label {
      display: block;
      font-weight: 600;
      font-size: 0.9rem;
      margin-bottom: 6px;
      color: #333;
    }
    input[type="text"], input[type="email"], input[type="password"], input[type="number"] {
      width: 100%;
      padding: 10px;
      border-radius: 10px;
      border: 1.5px solid #ddd;
      outline: none;
      font-size: 0.9rem;
      margin-bottom: 20px;
      transition: border-color 0.3s;
    }
    input:focus {
      border-color: #0047ff;
    }
    /* Estilo para el select carrera */
    select#carrera {
      width: 100%;
      padding: 10px 15px;
      border-radius: 10px;
      border: 1.5px solid #ddd;
      outline: none;
      font-size: 0.9rem;
      margin-bottom: 20px;
      font-family: 'Poppins', sans-serif;
      color: #333;
      background: white url('data:image/svg+xml;utf8,<svg fill="%23777777" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>') no-repeat right 12px center;
      background-size: 18px 18px;
      -webkit-appearance: none;
      -moz-appearance: none;
      appearance: none;
      cursor: pointer;
      transition: border-color 0.3s;
    }
    select#carrera:focus {
      border-color: #0047ff;
    }
    .btn-register {
      width: 100%;
      padding: 12px;
      font-size: 1rem;
      font-weight: 600;
      border: none;
      border-radius: 10px;
      background: linear-gradient(90deg, #0047ff, #007aff);
      color: #fff;
      cursor: pointer;
      transition: 0.3s;
    }
    .btn-register:hover {
      background: linear-gradient(90deg, #0033cc, #0060ff);
    }
    /* Ocultar todos los formularios por defecto */
    form.formulario {
      display: none;
    }
    /* Mostrar formulario activo */
    form.formulario.active {
      display: block;
    }
  </style>
</head>
<body>
  <div class="container">
    <img src="imagen/teclogo.jpg" alt="Icono IT Pachuca" class="icon">
    <h1>LibraTec</h1>
    <p>Selecciona tu tipo de usuario y completa el formulario</p>

    <div class="user-type">
      <button type="button" id="btn-estudiante" class="active">Estudiante</button>
      <button type="button" id="btn-personal">Personal</button>
    </div>

    <!-- Formulario Estudiante -->
    <form class="formulario active" id="form-estudiante" action="registrarsea.php" method="POST" autocomplete="off">
      <label for="nombre">Nombre(s)</label>
      <input type="text" name="nombre" id="nombre" required>

      <label for="apellido_paterno">Apellido Paterno</label>
      <input type="text" name="apellido_paterno" id="apellido_paterno" required>

      <label for="apellido_materno">Apellido Materno</label>
      <input type="text" name="apellido_materno" id="apellido_materno" required>

      <label for="correo">Correo</label>
      <input type="email" name="correo" id="correo" required>

      <label for="telefono">Teléfono</label>
      <input type="text" name="telefono" id="telefono" placeholder="Opcional">

      <label for="no_control">Número de Control</label>
      <input type="text" name="no_control" id="no_control" required>

      <label for="semestre">Semestre</label>
      <input type="number" name="semestre" id="semestre" min="1" max="15" required>

      <label for="carrera">Carrera</label>
      <select name="carrera" id="carrera" required>
        <option value="" disabled selected>Selecciona tu carrera</option>
        <option value="Licenciatura en Administración">Licenciatura en Administración</option>
        <option value="Arquitectura">Arquitectura</option>
        <option value="Ingeniería Civil">Ingeniería Civil</option>
        <option value="Ingeniería en Diseño Industrial">Ingeniería en Diseño Industrial</option>
        <option value="Ingeniería Eléctrica">Ingeniería Eléctrica</option>
        <option value="Ingeniería Ferroviaria">Ingeniería Ferroviaria</option>
        <option value="Ingeniería en Gestión Empresarial">Ingeniería en Gestión Empresarial</option>
        <option value="Ingeniería Industrial">Ingeniería Industrial</option>
        <option value="Ingeniería en Tecnologías de la Información y Comunicaciones">Ingeniería en Tecnologías de la Información y Comunicaciones</option>
        <option value="Ingeniería Mecánica">Ingeniería Mecánica</option>
        <option value="Ingeniería Química">Ingeniería Química</option>
        <option value="Ingeniería en Sistemas Computacionales">Ingeniería en Sistemas Computacionales</option>
      </select>

      <label for="contrasena">Contraseña</label>
      <input type="password" name="contrasena" id="contrasena" required>

      <button type="submit" class="btn-register">Registrarse</button>
    </form>

    <!-- Formulario Personal -->
    <form class="formulario" id="form-personal" action="registrarsep.php" method="POST" autocomplete="off">
      <label for="nombre_p">Nombre(s)</label>
      <input type="text" name="nombre" id="nombre_p" required>

      <label for="apellido_paterno_p">Apellido Paterno</label>
      <input type="text" name="apellido_paterno" id="apellido_paterno_p" required>

      <label for="apellido_materno_p">Apellido Materno</label>
      <input type="text" name="apellido_materno" id="apellido_materno_p" required>

      <label for="correo_p">Correo</label>
      <input type="email" name="correo" id="correo_p" required>

      <label for="telefono_p">Teléfono</label>
      <input type="text" name="telefono" id="telefono_p" placeholder="Opcional">

      <label for="no_control_p">Número de Control</label>
      <input type="text" name="no_control" id="no_control_p" required>

      <label for="contrasena_p">Contraseña</label>
      <input type="password" name="contrasena" id="contrasena_p" required>

      <button type="submit" class="btn-register">Registrarse</button>
    </form>
  </div>

  <script>
    const btnEstudiante = document.getElementById('btn-estudiante');
    const btnPersonal = document.getElementById('btn-personal');
    const formEstudiante = document.getElementById('form-estudiante');
    const formPersonal = document.getElementById('form-personal');

    btnEstudiante.addEventListener('click', () => {
      btnEstudiante.classList.add('active');
      btnPersonal.classList.remove('active');
      formEstudiante.classList.add('active');
      formPersonal.classList.remove('active');
    });

    btnPersonal.addEventListener('click', () => {
      btnPersonal.classList.add('active');
      btnEstudiante.classList.remove('active');
      formPersonal.classList.add('active');
      formEstudiante.classList.remove('active');
    });
  </script>
</body>
</html>
