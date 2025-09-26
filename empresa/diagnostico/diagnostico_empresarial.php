<?php
// -------------------------
// Inicialización de variables
// -------------------------
$mensaje_exito = false;   // Bandera para mostrar si el diagnóstico fue guardado con éxito
$recomendaciones = [];    // Arreglo para almacenar recomendaciones de programas
$errores = [];            // Lista de errores de validación

// -------------------------
// Validación del método POST
// -------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Captura de datos enviados desde el formulario
    $empresa          = trim($_POST['empresa'] ?? '');
    $nit              = trim($_POST['nit'] ?? '');
    $sector           = $_POST['sector'] ?? '';
    $tamano           = $_POST['tamano'] ?? '';
    $ubicacion        = trim($_POST['ubicacion'] ?? '');
    $empleados        = $_POST['empleados'] ?? '';
    $contrataciones   = $_POST['contrataciones'] ?? '';
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
    // Validaciones
    // -------------------------
    if ($empresa === '') $errores[] = "El nombre de la empresa es obligatorio.";
    if ($nit === '' || !preg_match("/^[0-9]+$/", $nit)) $errores[] = "El NIT es obligatorio y debe ser numérico.";
    if ($ubicacion === '') $errores[] = "La ubicación es obligatoria.";
    if ($empleados === '' || !filter_var($empleados, FILTER_VALIDATE_INT) || $empleados < 0) 
        $errores[] = "El número de empleados debe ser un entero positivo.";
    if ($contrataciones === '' || !filter_var($contrataciones, FILTER_VALIDATE_INT) || $contrataciones < 0) 
        $errores[] = "El número de contrataciones debe ser un entero positivo.";
    if (empty($perfiles_neces)) $errores[] = "Debe seleccionar al menos un perfil necesario.";

    // -------------------------
    // Si no hay errores, simulamos guardado
    // -------------------------
    if (empty($errores)) {
        // Aquí debería ir la consulta SQL para guardar el diagnóstico en la base de datos
        // Ejemplo:
        /*
        $sql = "INSERT INTO diagnosticos (...) VALUES (...)";
        mysqli_query($conn, $sql);
        */

        $mensaje_exito = true;

        // Simulación de programas recomendados según perfiles
        foreach ($perfiles_neces as $perfil) {
            $recomendaciones[] = [
                'nombre_programa'   => "Programa de " . htmlspecialchars($perfil),
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
    <link rel="shortcut icon" href="../../img/Logotipo_Datasena.png" type="image/x-icon" />
    <link rel="stylesheet" href="diagnostico_empresarial.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body>

<!-- Barra superior GOV.CO -->
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
  <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>

<header>DATASENA</header>
<img src="../../img/logo-sena.png" class="logo-img" alt="Logo SENA" />

<main class="form-container">
    <h2>Formulario de Diagnóstico Empresarial</h2>

    <!-- Mostrar errores si existen -->
    <?php if (!empty($errores)): ?>
        <div class="toast-error">
            ❌ Se encontraron errores:
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Mensaje de éxito al guardar -->
    <?php if ($mensaje_exito): ?>
        <div class="toast-success">✅ Diagnóstico guardado correctamente</div>
        <script>setTimeout(() => document.querySelector(".toast-success")?.remove(), 4000);</script>
    <?php endif; ?>

    <!-- Formulario principal -->
    <form method="POST" class="form-grid">
        <label>Nombre Empresa:</label>
        <input type="text" name="empresa" value="<?= htmlspecialchars($empresa ?? '') ?>" required />

        <label>NIT:</label>
        <input type="text" name="nit" value="<?= htmlspecialchars($nit ?? '') ?>" required />

        <label>Sector:</label>
        <select name="sector" required>
            <option <?= ($sector=="Agroindustria"?"selected":"") ?>>Agroindustria</option>
            <option <?= ($sector=="Comercio"?"selected":"") ?>>Comercio</option>
            <option <?= ($sector=="Tecnología"?"selected":"") ?>>Tecnología</option>
            <option <?= ($sector=="Servicios"?"selected":"") ?>>Servicios</option>
            <option <?= ($sector=="Otros"?"selected":"") ?>>Otros</option>
        </select>

        <label>Tamaño:</label>
        <select name="tamano" required>
            <option <?= ($tamano=="Microempresa (1-10)"?"selected":"") ?>>Microempresa (1-10)</option>
            <option <?= ($tamano=="Pequeña empresa (11-50)"?"selected":"") ?>>Pequeña empresa (11-50)</option>
            <option <?= ($tamano=="Mediana empresa (51-200)"?"selected":"") ?>>Mediana empresa (51-200)</option>
            <option <?= ($tamano=="Grande (>200)"?"selected":"") ?>>Grande (>200)</option>
        </select>

        <label>Ubicación:</label>
        <input type="text" name="ubicacion" value="<?= htmlspecialchars($ubicacion ?? '') ?>" required />

        <label>Total empleados:</label>
        <input type="number" name="empleados" value="<?= htmlspecialchars($empleados ?? '') ?>" required />

        <label>Contrataciones último año:</label>
        <input type="number" name="contrataciones" value="<?= htmlspecialchars($contrataciones ?? '') ?>" required />

        <label>Tipo contrato frecuente:</label>
        <select name="contrato_frecuente" required>
            <option <?= ($contrato_frec=="Fijo"?"selected":"") ?>>Fijo</option>
            <option <?= ($contrato_frec=="Indefinido"?"selected":"") ?>>Indefinido</option>
            <option <?= ($contrato_frec=="Prestación de servicios"?"selected":"") ?>>Prestación de servicios</option>
            <option <?= ($contrato_frec=="Aprendices SENA"?"selected":"") ?>>Aprendices SENA</option>
        </select>

        <label>¿Tiene proceso de selección formal?</label>
        <select name="tiene_proceso" required>
            <option <?= ($tiene_proceso=="Sí"?"selected":"") ?>>Sí</option>
            <option <?= ($tiene_proceso=="No"?"selected":"") ?>>No</option>
        </select>

        <label>¿Perfiles definidos?</label>
        <select name="perfiles_definidos" required>
            <option <?= ($perfiles_def=="Sí"?"selected":"") ?>>Sí</option>
            <option <?= ($perfiles_def=="No"?"selected":"") ?>>No</option>
        </select>

        <label>Medios publicación vacantes:</label>
        <select name="publicacion" required>
            <option <?= ($publicacion=="Redes sociales"?"selected":"") ?>>Redes sociales</option>
            <option <?= ($publicacion=="Servicio Público de Empleo"?"selected":"") ?>>Servicio Público de Empleo</option>
            <option <?= ($publicacion=="Referidos"?"selected":"") ?>>Referidos</option>
            <option <?= ($publicacion=="Otras"?"selected":"") ?>>Otras</option>
        </select>

        <label>¿Vincular aprendices?</label>
        <select name="aprendices" required>
            <option <?= ($aprendices=="Sí"?"selected":"") ?>>Sí</option>
            <option <?= ($aprendices=="No"?"selected":"") ?>>No</option>
        </select>

        <label>¿Programa de apoyo?</label>
        <select name="programa_apoyo" required>
            <option <?= ($programa_apoyo=="Sí"?"selected":"") ?>>Sí</option>
            <option <?= ($programa_apoyo=="No"?"selected":"") ?>>No</option>
        </select>

        <!-- Select múltiple con Select2 -->
        <label>Perfiles necesarios:</label>
        <select name="perfiles_necesarios[]" multiple required style="width: 100%;">
            <?php 
            $opciones = ["Programación","Textiles","Mecánica","Logística","Contabilidad","Electricidad",
                        "Electrónica","Gestión empresarial","Diseño gráfico","Soporte técnico",
                        "Seguridad y salud en el trabajo","Administración","Ambiental","Telecomunicaciones",
                        "Desarrollo de software","Redes de datos","Diseño de productos","Gestión documental",
                        "Servicio al cliente","Diseño industrial","Producción multimedia","Diseño web",
                        "Tecnología en automatización"];
            foreach ($opciones as $op): ?>
                <option value="<?= htmlspecialchars($op) ?>" <?= in_array($op,$perfiles_neces)?"selected":"" ?>>
                    <?= htmlspecialchars($op) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>¿Tiene infraestructura para formar?</label>
        <select name="infraestructura" required>
            <option <?= ($infraestructura=="Sí"?"selected":"") ?>>Sí</option>
            <option <?= ($infraestructura=="No"?"selected":"") ?>>No</option>
        </select>

        <label>¿Requiere apoyo en selección?</label>
        <select name="apoyo_seleccion" required>
            <option <?= ($apoyo_selec=="Sí"?"selected":"") ?>>Sí</option>
            <option <?= ($apoyo_selec=="No"?"selected":"") ?>>No</option>
        </select>

        <label>¿Desea orientación tributaria?</label>
        <select name="beneficios" required>
            <option <?= ($beneficios=="Sí"?"selected":"") ?>>Sí</option>
            <option <?= ($beneficios=="No"?"selected":"") ?>>No</option>
        </select>

        <!-- Botones -->
        <button type="submit" class="btn">Enviar Diagnóstico</button>
        <button type="button" class="btn" onclick="window.location.href='../empresa_menu.html'">↩️Regresar</button>
    </form>
</main>

<!-- Bloque de resultados -->
<?php if (!empty($recomendaciones)): ?>
    <div class="form-container">
        <h3>🎓 Programas recomendados:</h3>
        <ul>
            <?php foreach ($recomendaciones as $prog): ?>
                <li><strong><?= $prog['nombre_programa'] ?></strong> - 
                    <?= $prog['tipo_programa'] ?> 
                    (<?= $prog['duracion_programa'] ?>)
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php elseif ($mensaje_exito): ?>
    <p class="mensaje info">ℹ️ No se encontraron programas que coincidan con los perfiles ingresados.<p>
<?php endif; ?>

<footer>
    <a>&copy; Todos los derechos reservados al SENA</a>
</footer>

<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
    <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
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
