<?php
session_start();

// Validar que la sesión exista y el rol sea "empresa"
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'empresa') {
    header("Location: ../inicio_sesion.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Empresa</title>
    <!-- Ícono de pestaña -->
    <link rel="icon" href="../img/Logotipo_Datasena.png" type="image/x-icon">
    <!-- Estilos CSS para el menú de empresa -->
    <link rel="stylesheet" href="empresa_menu.css">
    <!-- Ícono alternativo -->
    <link rel="icon" href="../../img/Logotipo_Datasena.png" type="image/x-icon">
    <!-- Adaptación a pantallas móviles -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<!-- Barra superior del portal GOV.CO -->
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
  <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>

<!-- Fondo oscuro que aparece cuando un menú está activo -->
<div id="overlay" class="overlay" onclick="closeAllDropdowns()"></div>

<!-- BARRA SUPERIOR DE NAVEGACIÓN -->
<div class="top-bar">
    <!-- Menú hamburguesa lateral -->
    <div class="dropdown">
        <button onclick="toggleDropdown('menuLateral')" class="dropdown-btn">&#9776;</button>
        <div id="menuLateral" class="dropdown-content">
            <button onclick="location.href='../inicio_sesion.html'">Cerrar sesi&oacute;n</button>
        </div>
    </div>

    <!-- Botones del menú principal centrados -->
    <div class="top-buttons">

        <!-- Menú Diagnóstico empresarial -->
        <div class="dropdown-container">
            <button class="top-btn" onclick="toggleDropdown('adminMenu')">Diagn&oacute;stico empresarial</button>
            <div id="adminMenu" class="submenu">
                <a href="../empresa/diagnostico/diagnostico_empresarial.php">Ingresar diagn&oacute;stico</a>
            </div>
        </div>

        <!-- Menú Programas de formación -->
        <div class="dropdown-container">
            <button class="top-btn" onclick="toggleDropdown('programaMenu')">Programas de formaci&oacute;n</button>
            <div id="programaMenu" class="submenu">
                <a href="../empresa/programas_formacion/listar_programa.php">Listar programa</a>
            </div>
        </div>
    </div>
</div>

<!-- SECCIÓN PRINCIPAL CON DECORADO -->
<div class="decorated-section">
  <div class="center-box">
      <h2>&iquest;Qui&eacute;nes somos?</h2>
      <p>Somos un sistema para la gesti&oacute;n de las relaciones corporativas del CDITI.</p>
  </div>
</div>

<!-- SCRIPTS DE FUNCIONALIDAD -->
<script>
    // Alterna la visibilidad de los menús desplegables
    function toggleDropdown(menuId) {
        closeAllDropdowns(menuId); // Cierra los otros menús
        const menu = document.getElementById(menuId);
        const overlay = document.getElementById("overlay");
        if (menu && !menu.classList.contains("show")) {
            menu.classList.add("show");
            overlay.style.display = "block"; // Muestra el fondo oscuro
        } else {
            menu.classList.remove("show");
            overlay.style.display = "none"; // Oculta el fondo oscuro
        }
    }

    // Cierra todos los menús desplegables excepto el indicado
    function closeAllDropdowns(exceptId = null) {
        const menus = document.querySelectorAll(".submenu, .dropdown-content");
        menus.forEach(menu => {
            if (menu.id !== exceptId) {
                menu.classList.remove("show");
            }
        });
        document.getElementById("overlay").style.display = "none";
    }

    // Cierra los menús si se hace clic fuera de ellos
    window.onclick = function(event) {
        if (!event.target.matches('.top-btn') && !event.target.matches('.dropdown-btn')) {
            closeAllDropdowns();
        }
    };
</script>

<!-- PIE DE PÁGINA -->
<footer>
    &copy; 2025 Todos los derechos reservados - Proyecto SENA
</footer>
</body>

<!-- Barra inferior del portal GOV.CO -->
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
  <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>
</html>
