<?php
$conn = new mysqli("localhost", "root", "123456", "datasena_db");
if ($conn->connect_error) {
    die("❌ Error de conexión: " . $conn->connect_error);
}

$mensaje_exito = false;
$recomendaciones = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $campos = [
        'empresa', 'nit', 'sector', 'tamano', 'ubicacion',
        'empleados', 'contrataciones', 'contrato_frecuente',
        'tiene_proceso', 'perfiles_definidos', 'publicacion',
        'aprendices', 'programa_apoyo', 'infraestructura',
        'apoyo_seleccion', 'beneficios'
    ];

    $valores = [];
    foreach ($campos as $campo) {
        $valores[$campo] = trim($_POST[$campo] ?? '');
    }

    $perfiles_seleccionados = $_POST['perfiles_necesarios'] ?? [];
    $texto_perfiles = implode(", ", $perfiles_seleccionados);

    $sql = "INSERT INTO diagnostico_empresarial (" . implode(", ", $campos) . ", perfiles_necesarios) 
            VALUES ('" . implode("','", array_map([$conn, 'real_escape_string'], $valores)) . "', '" . $conn->real_escape_string($texto_perfiles) . "')";
    
    if ($conn->query($sql) === TRUE) {
        $mensaje_exito = true;

        if (!empty($perfiles_seleccionados)) {
    $condiciones = [];

    // Diccionario de sinónimos por perfil
    $sinonimos = [
        'Textiles' => ['textil', 'moda', 'confección', 'costura', 'tejido'],
        'Programación' => ['programador', 'software', 'desarrollador', 'sistemas', 'aplicaciones', 'tecnología'],
        'Mecánica' => ['mecánica', 'mecánico', 'automotriz', 'motor', 'ingeniería mecánica'],
        // Puedes seguir agregando más perfiles y sinónimos
    ];

    foreach ($perfiles_seleccionados as $perfil) {
        $palabras = $sinonimos[$perfil] ?? [$perfil]; // Usa sinónimos si existen
        foreach ($palabras as $palabra) {
            $palabra = strtolower($conn->real_escape_string($palabra));
            $condiciones[] = "LOWER(nombre_programa) LIKE '%$palabra%'";
        }
    }

    $sql_recomendacion = "SELECT * FROM programas WHERE (" . implode(" OR ", $condiciones) . ") AND LOWER(activacion) = 'activo' LIMIT 5";
    $resultado = $conn->query($sql_recomendacion);


            while ($fila = $resultado->fetch_assoc()) {
                $recomendaciones[] = $fila;
            }
        }
    } else {
        echo "❌ Error al guardar: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Diagnóstico Empresarial</title>
    <link rel="stylesheet" href="diagnostico_empresarial.css">

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body>
    <div class="barra-gov">
        <img src="../../img/gov.png" alt="Gobierno de Colombia" class="gov-logo">
    </div>

    <header>DATASENA</header>
    <img src="../../img/logo-sena.png" class="logo-img" alt="Logo SENA">

    <main class="form-container">
        <h2>Formulario de Diagnóstico Empresarial</h2>

        <?php if ($mensaje_exito): ?>
            <div class="toast-success">✅ Diagnóstico guardado correctamente</div>
            <script>setTimeout(() => document.querySelector(".toast-success")?.remove(), 4000);</script>
        <?php endif; ?>

        <form method="POST" class="form-grid">
            <label>Nombre Empresa:</label>
            <input type="text" name="empresa" required>

            <label>NIT:</label>
            <input type="text" name="nit" required>

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
            <input type="text" name="ubicacion" required>

            <label>Total empleados:</label>
            <input type="number" name="empleados" required>

            <label>Contrataciones último año:</label>
            <input type="number" name="contrataciones" required>

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

            <label>Perfiles necesarios:</label>
            <select name="perfiles_necesarios[]" multiple required style="width: 100%;">
                <option value="Programación">Programación</option>
                <option value="Textiles">Textiles</option>
                <option value="Mecánica">Mecánica</option>
                <option value="Logística">Logística</option>
                <option value="Contabilidad">Contabilidad</option>
                <option value="Electricidad">Electricidad</option>
                <option value="Diseño gráfico">Diseño gráfico</option>
                <option value="Soporte técnico">Soporte técnico</option>
                <option value="Seguridad y salud en el trabajo">Seguridad y salud en el trabajo</option>
                <option value="Administración">Administración</option>
            </select>

            <label>¿Tiene infraestructura para formar?</label>
            <select name="infraestructura" required><option>Sí</option><option>No</option></select>

            <label>¿Requiere apoyo en selección?</label>
            <select name="apoyo_seleccion" required><option>Sí</option><option>No</option></select>

            <label>¿Desea orientación tributaria?</label>
            <select name="beneficios" required><option>Sí</option><option>No</option></select>

            <button type="submit" class="btn">Enviar Diagnóstico</button>
        </form>
    </main>

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
        <p class="mensaje info">ℹ️ No se encontraron programas que coincidan con los perfiles ingresados.</p>
    <?php endif; ?>

    <footer>
        <p>&copy; Todos los derechos reservados al SENA</p>
    </footer>

    <div class="barra-gov">
        <img src="../../img/gov.png" alt="Gobierno de Colombia" class="gov-logo">
    </div>

    <!-- jQuery y Select2 -->
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
