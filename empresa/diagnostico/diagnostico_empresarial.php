<?php
// -------------------------
// Inicialización de variables
// -------------------------
$mensaje_exito = false;   // Bandera para mostrar si el diagnóstico fue guardado con éxito
$recomendaciones = [];    // Arreglo para almacenar recomendaciones de programas

// -------------------------
// Validación del método POST
// -------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Captura de datos enviados desde el formulario (operador ?? asegura valores por defecto si no se envían)
    $empresa          = $_POST['empresa'] ?? '';
    $nit              = $_POST['nit'] ?? '';
    $sector           = $_POST['sector'] ?? '';
    $tamano           = $_POST['tamano'] ?? '';
    $ubicacion        = $_POST['ubicacion'] ?? '';
    $empleados        = $_POST['empleados'] ?? 0;
    $contrataciones   = $_POST['contrataciones'] ?? 0;
    $contrato_frec    = $_POST['contrato_frecuente'] ?? '';
    $tiene_proceso    = $_POST['tiene_proceso'] ?? '';
    $perfiles_def     = $_POST['perfiles_definidos'] ?? '';
    $publicacion      = $_POST['publicacion'] ?? '';
    $aprendices       = $_POST['aprendices'] ?? '';
    $programa_apoyo   = $_POST['programa_apoyo'] ?? '';
    $perfiles_neces   = $_POST['perfiles_necesarios'] ?? [];
    $infraestructura  = $_POST['infraestructura'] ?? '';
    $apoyo_selec      = $_POST['apoyo_seleccion'] ?? '';
    $beneficios       = $_POST['beneficios'] ?? '';

    // -------------------------
    // Simulación de guardado en BD
    // -------------------------
    // Aquí debería ir la consulta SQL para guardar el diagnóstico en la base de datos
    // Ejemplo:
    /*
    $sql = "INSERT INTO diagnosticos (...) VALUES (...)";
    mysqli_query($conn, $sql);
    */

    // Marcamos como exitoso el registro
    $mensaje_exito = true;

    // -------------------------
    // Simulación de programas recomendados según los perfiles seleccionados
    // -------------------------
    if (!empty($perfiles_neces)) {
        foreach ($perfiles_neces as $perfil) {
            $recomendaciones[] = [
                'nombre_programa'   => "Programa de $perfil",
                'tipo_programa'     => "Técnico",
                'duracion_programa' => "12 meses"
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Diagnóstico Empresarial</title>
    <!-- Icono de pestaña -->
    <link rel="shortcut icon" href="../../img/Logotipo_Datasena.png" type="image/x-icon" />
    <!-- Archivo de estilos personalizados -->
    <link rel="stylesheet" href="diagnostico_empresarial.css" />

    <!-- Librería Select2 para selects avanzados -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body>

<!-- Barra superior GOV.CO -->
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
  <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>

    <!-- Encabezado principal -->
    <header>DATASENA</header>
    <img src="../../img/logo-sena.png" class="logo-img" alt="Logo SENA" />

    <main class="form-container">
        <h2>Formulario de Diagnóstico Empresarial</h2>

        <!-- Mensaje de éxito al guardar -->
        <?php if ($mensaje_exito): ?>
            <div class="toast-success">✅ Diagnóstico guardado correctamente</div>
            <!-- Script para ocultar el mensaje después de 4 segundos -->
            <script>setTimeout(() => document.querySelector(".toast-success")?.remove(), 4000);</script>
        <?php endif; ?>

        <!-- Formulario principal -->
        <form method="POST" class="form-grid">
            <!-- Campos del formulario -->
            <label>Nombre Empresa:</label>
            <input type="text" name="empresa" required />

            <label>NIT:</label>
            <input type="text" name="nit" required />

            <label>Sector:</label>
            <select name="sector" required>
                <option>Agroindustria</option>
                <option>Comercio</option>
                <option>Tecnología</option>
                <option>Servicios</option>
                <option>Otros</option>
            </select>

            <label>Tamaño:</label>
            <select name="tamano" required>
                <option>Microempresa (1-10)</option>
                <option>Pequeña empresa (11-50)</option>
                <option>Mediana empresa (51-200)</option>
                <option>Grande (>200)</option>
            </select>

            <label>Ubicación:</label>
            <input type="text" name="ubicacion" required />

            <label>Total empleados:</label>
            <input type="number" name="empleados" required />

            <label>Contrataciones último año:</label>
            <input type="number" name="contrataciones" required />

            <label>Tipo contrato frecuente:</label>
            <select name="contrato_frecuente" required>
                <option>Fijo</option>
                <option>Indefinido</option>
                <option>Prestación de servicios</option>
                <option>Aprendices SENA</option>
            </select>

            <label>¿Tiene proceso de selección formal?</label>
            <select name="tiene_proceso" required><option>Sí</option><option>No</option></select>

            <label>¿Perfiles definidos?</label>
            <select name="perfiles_definidos" required><option>Sí</option><option>No</option></select>

            <label>Medios publicación vacantes:</label>
            <select name="publicacion" required>
                <option>Redes sociales</option>
                <option>Servicio Público de Empleo</option>
                <option>Referidos</option>
                <option>Otras</option>
            </select>

            <label>¿Vincular aprendices?</label>
            <select name="aprendices" required><option>Sí</option><option>No</option></select>

            <label>¿Programa de apoyo?</label>
            <select name="programa_apoyo" required><option>Sí</option><option>No</option></select>

            <!-- Select con opción múltiple y Select2 -->
            <label>Perfiles necesarios:</label>
            <select name="perfiles_necesarios[]" multiple required style="width: 100%;">
                <!-- Opciones de perfiles -->
                <option value="Programación">Programación</option>
                <option value="Textiles">Textiles</option>
                <option value="Mecánica">Mecánica</option>
                <option value="Logística">Logística</option>
                <option value="Contabilidad">Contabilidad</option>
                <option value="Electricidad">Electricidad</option>
                <option value="Electrónica">Electrónica</option>
                <option value="Gestión empresarial">Gestión empresarial</option>
                <option value="Diseño gráfico">Diseño gráfico</option>
                <option value="Soporte técnico">Soporte técnico</option>
                <option value="Seguridad y salud en el trabajo">Seguridad y salud en el trabajo</option>
                <option value="Administración">Administración</option>
                <option value="Ambiental">Ambiental</option>
                <option value="Telecomunicaciones">Telecomunicaciones</option>
                <option value="Desarrollo de software">Desarrollo de software</option>
                <option value="Redes de datos">Redes de datos</option>
                <option value="Diseño de productos">Diseño de productos</option>
                <option value="Gestión documental">Gestión documental</option>
                <option value="Servicio al cliente">Servicio al cliente</option>
                <option value="Diseño industrial">Diseño industrial</option>
                <option value="Producción multimedia">Producción multimedia</option>
                <option value="Diseño web">Diseño web</option>
                <option value="Tecnología en automatización">Tecnología en automatización</option>
            </select>

            <label>¿Tiene infraestructura para formar?</label>
            <select name="infraestructura" required><option>Sí</option><option>No</option></select>

            <label>¿Requiere apoyo en selección?</label>
            <select name="apoyo_seleccion" required><option>Sí</option><option>No</option></select>

            <label>¿Desea orientación tributaria?</label>
            <select name="beneficios" required><option>Sí</option><option>No</option></select>

            <!-- Botones -->
            <button type="submit" class="btn">Enviar Diagnóstico</button>
            <button type="button" class="btn" onclick="window.location.href='../empresa_menu.html'">⬅ Regresar</button>
        </form>
    </main>

    <!-- Bloque de resultados -->
    <?php if (!empty($recomendaciones)): ?>
        <div class="form-container">
            <h3>🎓 Programas recomendados:</h3>
            <ul>
                <?php foreach ($recomendaciones as $prog): ?>
                    <li><strong><?= htmlspecialchars($prog['nombre_programa']) ?></strong> - 
                        <?= htmlspecialchars($prog['tipo_programa']) ?> 
                        (<?= htmlspecialchars($prog['duracion_programa']) ?>)
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php elseif ($mensaje_exito): ?>
        <!-- Si se guardó el diagnóstico pero no hubo coincidencias -->
        <p class="mensaje info">ℹ️ No se encontraron programas que coincidan con los perfiles ingresados.<p>
    <?php endif; ?>

    <!-- Pie de página -->
    <footer>
        <a>&copy; Todos los derechos reservados al SENA</a>
    </footer>

    <!-- Barra inferior GOV.CO -->
    <nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
        <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
    </nav>

    <!-- jQuery y Select2 para mejorar el select múltiple -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
      // Activación de Select2 en el campo de perfiles
      $(document).ready(function() {
        $('select[name="perfiles_necesarios[]"]').select2({
          placeholder: "Selecciona los perfiles necesarios",
          allowClear: true,
          width: '100%'
        });
      });
    </script>
</body>
</html>
