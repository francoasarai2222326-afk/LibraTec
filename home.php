<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Inicio - LIBRATEC</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
<style>
  /* Reset y tipografía */
  * {
    margin: 0; padding: 0; box-sizing: border-box;
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
  }
  body {
    background-color: #f0f4f9;
    color: #002b80;
  }
  a {
    text-decoration: none;
    color: inherit;
  }
  /* Navbar */
  .navbar {
    position: fixed;
    top: 0; left: 0; right: 0;
    background: linear-gradient(90deg, #0040a0, #002b80);
    color: white;
    display: flex;
    align-items: center;
    padding: 12px 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    z-index: 1000;
  }
  .logo-container {
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .logo-container img {
    height: 50px;
  }
  .logo-text {
    font-weight: 700;
    font-size: 1.5rem;
    letter-spacing: 2px;
  }
  .nav-links {
    margin-left: auto;
    display: flex;
    gap: 1.5rem;
  }
  .nav-links a {
    color: white;
    font-weight: 600;
    padding: 6px 12px;
    transition: color 0.25s;
  }
  .nav-links a:hover {
    color: #a8c4ff;
  }
  /* Banner principal */
  .banner {
    margin-top: 70px; /* navbar height + margin */
    position: relative;
    background: url('imagen/sobrenosotros.jpg') center/cover no-repeat;
    height: 320px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-shadow: 0 0 10px rgba(0,0,0,0.7);
  }
  .banner-overlay {
    position: absolute;
    top:0; left:0; right:0; bottom:0;
    background: rgba(0, 40, 120, 0.6);
  }
  .banner-content {
    position: relative;
    text-align: center;
    max-width: 900px;
    padding: 20px;
  }
  .banner-content h1 {
    font-size: 2.8rem;
    margin-bottom: 0.5rem;
  }
  .banner-content p {
    font-size: 1.3rem;
  }
  /* Sección servicios */
  .services {
    max-width: 1100px;
    margin: 40px auto 60px;
    display: flex;
    justify-content: space-around;
    flex-wrap: wrap;
    gap: 30px;
  }
  .service-item {
    flex: 1 1 200px;
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    text-align: center;
    color: #0040a0;
    transition: transform 0.2s;
  }
  .service-item:hover {
    transform: translateY(-6px);
  }
  .service-item i {
    font-size: 3rem;
    margin-bottom: 15px;
    color: #007bff;
  }
  .service-item h3 {
    font-size: 1.3rem;
    margin-bottom: 10px;
  }
  /* Noticias */
  .news-section {
    max-width: 900px;
    margin: 0 auto 60px;
    background: white;
    border-radius: 10px;
    padding: 25px 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  }
  .news-section h2 {
    margin-bottom: 20px;
    color: #0040a0;
  }
  .news-list {
    list-style: none;
  }
  .news-list li {
    padding: 12px 0;
    border-bottom: 1px solid #ccc;
  }
  .news-list li:last-child {
    border-bottom: none;
  }
  /* Sobre nosotros */
  .about-section {
    max-width: 1100px;
    margin: 0 auto 60px;
    display: flex;
    gap: 40px;
    align-items: center;
    padding: 20px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  }
  .about-text {
    flex: 1;
  }
  .about-text h2 {
    margin-bottom: 15px;
    color: #0040a0;
  }
  .about-text p {
    line-height: 1.5;
    font-size: 1.1rem;
  }
  .about-img {
    flex: 1;
  }
  .about-img img {
    width: 40%;
    border-radius: 10px;
    object-fit: cover;
  }
  /* Footer */
  footer {
    background: #002b80;
    color: white;
    text-align: center;
    padding: 20px 10px;
    font-size: 0.9rem;
  }
  /* Responsive */
  @media (max-width: 768px) {
    .services {
      flex-direction: column;
      gap: 20px;
      margin: 20px auto 40px;
    }
    .about-section {
      flex-direction: column;
      gap: 25px;
      padding: 15px;
    }
  }
</style>
</head>
<body>

<nav class="navbar">
  <div class="logo-container">
    <img src="imagen/teclogo.jpg" alt="Logo IT Pachuca" />
    <div class="logo-text">Instituto Tecnológico de Pachuca</div>
  </div>
  <div class="nav-links">
    <a href="login.php">Iniciar sesión</a>
    <a href="registrarse.php">Registrarse</a>
  </div>
</nav>

<section class="banner">
  <div class="banner-overlay"></div>
  <div class="banner-content">
    <h1>Bienvenido a LibraTec</h1>
    <p>Tu biblioteca digital en el Instituto Tecnológico de Pachuca</p>
  </div>
</section>

<section class="services">
  <div class="service-item">
    <i class="fas fa-book-reader"></i>
    <h3>Préstamos rápidos</h3>
    <p>Accede fácilmente a los libros que necesitas con un proceso sencillo y rápido.</p>
  </div>
  <div class="service-item">
    <i class="fas fa-search"></i>
    <h3>Catálogo digital</h3>
    <p>Explora nuestro catálogo completo y encuentra libros desde cualquier lugar.</p>
  </div>
  <div class="service-item">
    <i class="fas fa-money-bill-wave"></i>
    <h3>Multas y pagos</h3>
    <p>Consulta tus multas de manera digital y segura.</p>
  </div>
</section>

<section class="news-section">
  <h2>Noticias Recientes</h2>
  <ul class="news-list">
    <li><strong>15 Nov 2025:</strong> Se amplió el horario de la biblioteca hasta las 10pm.</li>
    <li><strong>01 Nov 2025:</strong> Nuevos títulos añadidos al catálogo digital.</li>
    <li><strong>20 Oct 2025:</strong> Curso de manejo de recursos digitales para estudiantes.</li>
  </ul>
</section>

<section class="about-section">
  <div class="about-text">
    <h2>Sobre Nosotros</h2>
    <p>El Instituto Tecnológico de Pachuca, a través de LibraTec, ofrece una plataforma moderna y eficiente para la gestión de recursos bibliográficos. Nuestro objetivo es facilitar el acceso al conocimiento y apoyar el desarrollo académico de nuestra comunidad estudiantil y docente.</p>
  </div>
  <div class="about-img">
    <img src="imagen/fondologin.jpg" alt="Instituto Tecnológico de Pachuca" />
  </div>
</section>

<footer>
  &copy; 2025 Instituto Tecnológico de Pachuca - LibraTec. Todos los derechos reservados.
</footer>

</body>
</html>
