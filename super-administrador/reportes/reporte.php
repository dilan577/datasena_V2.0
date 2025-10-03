<?php
// Establecer codificación
header('Content-Type: text/html; charset=utf-8');

// Conexión segura a la base de datos con UTF-8
try {
    $pdo = new PDO("mysql:host=localhost;dbname=datasena_db;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . htmlspecialchars($e->getMessage()));
}

$mensaje = "";
$lastId = null;

// Obtener listas para el formulario
$empresas = $pdo->query("SELECT id, nickname AS nombre FROM empresas")->fetchAll(PDO::FETCH_ASSOC);
$admin = $pdo->query("SELECT id, CONCAT(nombres, ' ', apellidos, ' (', nickname, ')') AS nombre FROM admin")->fetchAll(PDO::FETCH_ASSOC);
$programas = $pdo->query("SELECT id, nombre_programa AS nombre FROM programas")->fetchAll(PDO::FETCH_ASSOC);

// Procesar formulario solo si es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar tipo de reporte
    $tiposValidos = ['empresa', 'administrador', 'programa'];
    $tipo = $_POST['tipo_reporte'] ?? '';
    if (!in_array($tipo, $tiposValidos)) {
        die("❌ Tipo de reporte no válido.");
    }

    // Validar ID como entero positivo
    $id = filter_input(INPUT_POST, 'id_elemento', FILTER_VALIDATE_INT);
    if ($id === false || $id <= 0) {
        die("❌ ID inválido.");
    }

    // Validar observación
    $observacion = trim($_POST['observacion'] ?? '');
    if (strlen($observacion) < 5) {
        die("❌ La observación debe tener al menos 5 caracteres.");
    }

    // Insertar en base de datos
    $stmt = $pdo->prepare("INSERT INTO reportes (tipo_reporte, id_referenciado, observacion) VALUES (?, ?, ?)");
    $stmt->execute([$tipo, $id, $observacion]);

    $mensaje = "✅ Reporte guardado exitosamente. Puedes descargarlo:";
    $lastId = $pdo->lastInsertId();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportar Elemento</title>
    <link rel="shortcut icon" href="../../img/Logotipo_Datasena.png" type="image/x-icon" />
    <link rel="stylesheet" href="reporte.css" />
</head>
<body>

<!-- Barra superior del Gobierno de Colombia -->
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
  <a href="https://www.gov.co/" target="_blank" rel="noopener" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>

<header>
    <h1>DATASENA</h1>
    <img src="../../img/logo-sena.png" alt="Logo SENA" class="img" />
</header>

<div class="contenedor">
    <h2>📋 Reportar Empresa, Admin o Programa</h2>

    <form method="POST">
        <label for="tipo_reporte">Tipo de reporte:</label>
        <select name="tipo_reporte" id="tipo_reporte" required onchange="mostrarOpciones()">
            <option value="">Seleccione</option>
            <option value="empresa">Empresa</option>
            <option value="administrador">Administrador</option>
            <option value="programa">Programa</option>
        </select>

        <label for="id_elemento">Selecciona el elemento:</label>
        <select name="id_elemento" id="id_elemento" required>
            <option value="">Seleccione un tipo primero</option>
        </select>

        <label for="observacion">Observación:</label>
        <textarea name="observacion" id="observacion" rows="5" required></textarea>

        <button type="submit">✅ Crear</button>
    </form>

    <?php if (isset($lastId)): ?>
        <div class="mensaje"><?= htmlspecialchars($mensaje) ?></div>

        <div class="descargas">
            <label for="formato">📥 Descargar como:</label>
            <select id="formato">
                <option value="pdf">PDF</option>
                <option value="xml">XML</option>
            </select>
            <button type="button" onclick="descargar()">Descargar</button>
        </div>
    <?php endif; ?>

    <div class="acciones-extra">
        <button type="button" onclick="window.location.href='../super_menu.html'">↩️ Regresar</button>
    </div>
</div>

<footer>
    <p>&copy; 2025 Todos los derechos reservados - Proyecto SENA</p>
</footer>

<!-- Barra inferior del Gobierno de Colombia -->
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
    <a href="https://www.gov.co/" target="_blank" rel="noopener" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>

<script>
    // Datos pre-cargados desde PHP (seguro con json_encode)
    const empresas = <?= json_encode($empresas, JSON_UNESCAPED_UNICODE) ?>;
    const admin = <?= json_encode($admin, JSON_UNESCAPED_UNICODE) ?>;
    const programas = <?= json_encode($programas, JSON_UNESCAPED_UNICODE) ?>;

    function mostrarOpciones() {
        const tipo = document.getElementById('tipo_reporte').value;
        const select = document.getElementById('id_elemento');
        select.innerHTML = '<option value="">Seleccione</option>';

        let datos = [];
        if (tipo === 'empresa') datos = empresas;
        else if (tipo === 'administrador') datos = admin;
        else if (tipo === 'programa') datos = programas;

        datos.forEach(dato => {
            const option = document.createElement('option');
            option.value = dato.id;
            option.textContent = dato.nombre;
            select.appendChild(option);
        });
    }

    function descargar() {
        const id = <?= json_encode($lastId) ?>; // null si no existe
        if (!id) {
            alert("❌ No hay reporte para descargar.");
            return;
        }
        const formato = document.getElementById("formato").value;
        const url = formato === "pdf" ? "descargas_pdf.php" : "descargas_xml.php";
        window.open(url + "?id=" + encodeURIComponent(id), "_blank");
    }
</script>
</body>
</html>