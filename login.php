<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>LibraTec - Iniciar Sesión</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      /* Fondo con imagen + overlay desvanecido */
      background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('imagen/fondologin.jpg') no-repeat center center/cover;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }

    .container {
      background: #fff;
      width: 90%;
      max-width: 400px;
      border-radius: 20px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
      padding: 40px 30px;
      text-align: center;
      position: relative;
    }

    .container img.top-image {
      width: 80px;
      margin-bottom: 15px;
    }

    .container img.icon {
      width: 50px;
      margin-bottom: 10px;
    }

    h1 {
      font-size: 1.8rem;
      font-weight: 700;
      color: #000;
    }

    p {
      color: #555;
      margin-bottom: 25px;
      font-size: 0.9rem;
    }

    .user-type {
      display: flex;
      justify-content: space-between;
      margin-bottom: 25px;
    }

    .user-type button {
      width: 48%;
      padding: 10px 0;
      font-size: 0.95rem;
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

    label {
      display: block;
      text-align: left;
      font-weight: 600;
      font-size: 0.9rem;
      margin-bottom: 5px;
      color: #333;
    }

    input[type="text"], input[type="password"] {
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

    .btn-login {
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

    .btn-login:hover {
      background: linear-gradient(90deg, #0033cc, #0060ff);
    }

    @media (max-width: 480px) {
      .container {
        padding: 30px 20px;
      }
    }
  </style>
</head>
<body>
  <div class="container">

    <!-- Icono dentro del recuadro -->
    <img src="imagen/teclogo.jpg" alt="Icono usuario" class="icon">

    <h1>LibraTec</h1>
    <p>Selecciona tu usuario para continuar</p>

    <form action="validarlogin.php" method="POST">
      <!-- Radios ocultos -->
      <input type="radio" name="tipo_usuario" value="estudiante" id="radio-estudiante" checked hidden>
      <input type="radio" name="tipo_usuario" value="personal" id="radio-personal" hidden>

      <!-- Botones visuales -->
      <div class="user-type">
        <button type="button" class="active" id="btn-estudiante">Estudiante</button>
        <button type="button" id="btn-personal">Personal</button>
      </div>

      <!-- Datos de login -->
      <label for="control">Número de Control</label>
      <input type="text" name="control" id="control" placeholder="Ingresa tu numero de control" required>

      <label for="password">Contraseña</label>
      <input type="password" name="password" id="password" placeholder="Ingresa tu contraseña" required>

      <button type="submit" class="btn-login">Iniciar Sesión</button>
    </form>
  </div>

  <script>
    const btnEstudiante = document.getElementById("btn-estudiante");
    const btnPersonal = document.getElementById("btn-personal");
    const radioEstudiante = document.getElementById("radio-estudiante");
    const radioPersonal = document.getElementById("radio-personal");

    btnEstudiante.addEventListener("click", () => {
      btnEstudiante.classList.add("active");
      btnPersonal.classList.remove("active");
      radioEstudiante.checked = true;
    });

    btnPersonal.addEventListener("click", () => {
      btnPersonal.classList.add("active");
      btnEstudiante.classList.remove("active");
      radioPersonal.checked = true;
    });
  </script>
</body>
</html>

