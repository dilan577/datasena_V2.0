<?php
// Conexión a la base de datos
$conn = new mysqli("localhost", "root", "", "datasena_db");
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
        'aprendices', 'programa_apoyo', 'perfiles_necesarios',
        'infraestructura', 'apoyo_seleccion', 'beneficios'
    ];

    $valores = [];
    foreach ($campos as $campo) {
        $valores[$campo] = trim($_POST[$campo] ?? '');
    }

    $sql = "INSERT INTO diagnostico_empresarial (" . implode(",", $campos) . ") VALUES ('" . implode("','", $valores) . "')";
    if ($conn->query($sql) === TRUE) {
        $mensaje_exito = true;

        // Buscar programas basados en las palabras ingresadas
        $perfiles_raw = strtolower($valores['perfiles_necesarios']);
        $palabras_usuario = preg_split("/[\s,;]+/", $perfiles_raw);

        if (!empty($palabras_usuario)) {
            $condiciones = [];
            foreach ($palabras_usuario as $palabra) {
                $condiciones[] = "LOWER(nombre_programa) LIKE '%" . $conn->real_escape_string($palabra) . "%'";
            }

            $sql_recomendacion = "SELECT * FROM programas WHERE (" . implode(" OR ", $condiciones) . ") AND LOWER(activacion) = 'activo' LIMIT 5";
            $resultado = $conn->query($sql_recomendacion);

            while ($fila = $resultado->fetch_assoc()) {
                $recomendaciones[] = $fila;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Diagnóstico Empresarial</title>
    <link rel="stylesheet" href="diagnostico_empresarial.css">
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
            <script>
                setTimeout(() => document.querySelector(".toast-success")?.remove(), 4000);
            </script>
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
                <option>Grande (&gt;200)</option>
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
            <input type="text" name="perfiles_necesarios" required>

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
                    <li><strong><?= htmlspecialchars($prog['nombre_programa']) ?></strong> - <?= htmlspecialchars($prog['tipo_programa']) ?> (<?= htmlspecialchars($prog['duracion_programa']) ?>)</li>
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
</body>
</html>
