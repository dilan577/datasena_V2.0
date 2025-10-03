<?php
// Establece la codificación de caracteres en UTF-8 para garantizar el correcto manejo de tildes, ñ y otros caracteres especiales.
header('Content-Type: text/html; charset=utf-8');

// Intenta establecer una conexión segura con la base de datos MySQL usando PDO.
// Se especifica el host (localhost), la base de datos (datasena_db), el usuario (root) y contraseña vacía.
// Se incluye el parámetro charset=utf8 para evitar problemas de codificación en los datos.
try {
    $pdo = new PDO("mysql:host=localhost;dbname=datasena_db;charset=utf8", "root", "");
    // Configura PDO para que lance excepciones en caso de errores SQL, lo que facilita la depuración.
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Si la conexión falla, se termina la ejecución y se muestra un mensaje de error seguro.
    // Se usa htmlspecialchars() para prevenir posibles ataques XSS en el mensaje de error.
    die("Error de conexión: " . htmlspecialchars($e->getMessage()));
}

// Inicializa variables para manejar el estado del formulario y los mensajes al usuario.
$mensaje = "";
$lastId = null;

// Consulta los datos necesarios para poblar los menús desplegables del formulario:
// - Empresas: selecciona el id y el nickname como nombre visible.
// - Administradores: concatena nombres, apellidos y nickname para mostrar un formato legible.
// - Programas: selecciona el id y el nombre del programa.
$empresas = $pdo->query("SELECT id, nickname AS nombre FROM empresas")->fetchAll(PDO::FETCH_ASSOC);
$admin = $pdo->query("SELECT id, CONCAT(nombres, ' ', apellidos, ' (', nickname, ')') AS nombre FROM admin")->fetchAll(PDO::FETCH_ASSOC);
$programas = $pdo->query("SELECT id, nombre_programa AS nombre FROM programas")->fetchAll(PDO::FETCH_ASSOC);

// Verifica si el formulario fue enviado mediante el método POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtiene y limpia los valores enviados por el formulario.
    $tipo = $_POST['tipo_reporte'] ?? '';
    $id = filter_input(INPUT_POST, 'id_elemento', FILTER_VALIDATE_INT);
    $observacion = trim($_POST['observacion'] ?? '');

    // Valida que el tipo de reporte sea uno de los permitidos.
    if (!in_array($tipo, ['empresa', 'administrador', 'programa'])) {
        die("❌ Tipo de reporte no válido.");
    }

    // Valida que el ID sea un número entero positivo.
    if ($id === false || $id <= 0) {
        die("❌ ID inválido.");
    }

    // Valida que la observación no esté vacía y tenga al menos 5 caracteres.
    if (strlen($observacion) < 5) {
        die("❌ La observación debe tener al menos 5 caracteres.");
    }

    // Prepara y ejecuta una consulta SQL segura para insertar el reporte.
    // El uso de consultas preparadas evita inyecciones SQL.
    $stmt = $pdo->prepare("INSERT INTO reportes (tipo_reporte, id_referenciado, observacion) VALUES (?, ?, ?)");
    $stmt->execute([$tipo, $id, $observacion]);

    // Establece un mensaje de éxito y obtiene el ID del registro recién insertado.
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
    <!-- Enlaza el favicon del sistema -->
    <link rel="shortcut icon" href="../../img/Logotipo_Datasena.png" type="image/x-icon" />
    <!-- Enlaza la hoja de estilos personalizada -->
    <link rel="stylesheet" href="admin_reporte.css" />
</head>
<body>

<!-- Barra superior del Gobierno de Colombia (GOV.CO) -->
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
    <a href="https://www.gov.co/" target="_blank" rel="noopener" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>

<!-- Encabezado principal de la página -->
<header>
    <h1>DATASENA</h1>
    <img src="../../img/logo-sena.png" alt="Logo SENA" class="img" />
</header>

<!-- Contenedor principal del formulario -->
<div class="contenedor">
    <h2>📋 Reportar Empresa, Administrador o Programa</h2>

    <!-- Formulario para crear un nuevo reporte -->
    <form method="POST">
        <!-- Selector para elegir el tipo de reporte -->
        <label for="tipo_reporte">Tipo de reporte:</label>
        <select name="tipo_reporte" id="tipo_reporte" required onchange="mostrarOpciones()">
            <option value="">Seleccione</option>
            <option value="empresa">Empresa</option>
            <option value="administrador">Administrador</option>
            <option value="programa">Programa</option>
        </select>

        <!-- Selector dinámico que se llena con JavaScript según la opción elegida -->
        <label for="id_elemento">Selecciona el elemento:</label>
        <select name="id_elemento" id="id_elemento" required>
            <option value="">Seleccione un tipo primero</option>
        </select>

        <!-- Campo de texto para la observación -->
        <label for="observacion">Observación:</label>
        <textarea name="observacion" id="observacion" rows="5" required></textarea>

        <!-- Botón para enviar el formulario -->
        <button type="submit">✅ Crear</button>
    </form>

    <!-- Muestra un mensaje de éxito si se ha guardado un reporte -->
    <?php if (isset($lastId)): ?>
        <div class="mensaje"><?= htmlspecialchars($mensaje) ?></div>

        <!-- Sección para descargar el reporte en formato PDF o XML -->
        <div class="descargas">
            <label for="formato">📥 Descargar como:</label>
            <select id="formato">
                <option value="pdf">PDF</option>
                <option value="xml">XML</option>
            </select>
            <button type="button" onclick="descargar()">Descargar</button>
        </div>
    <?php endif; ?>

    <!-- Botón para regresar al menú de administración -->
    <div class="acciones-extra">
        <button type="button" onclick="window.location.href='../admin_menu.html'">↩️ Regresar</button>
    </div>
</div>

<!-- Pie de página con información de derechos de autor -->
<footer>
    <p>&copy; 2025 Todos los derechos reservados - Proyecto SENA</p>
</footer>

<!-- Barra inferior del Gobierno de Colombia (GOV.CO) -->
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
    <a href="https://www.gov.co/" target="_blank" rel="noopener" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>

<!-- Script para la lógica interactiva del formulario -->
<script>
    // Carga los datos obtenidos desde PHP en variables JavaScript.
    // Se usa JSON_UNESCAPED_UNICODE para preservar caracteres como tildes y ñ.
    const empresas = <?= json_encode($empresas, JSON_UNESCAPED_UNICODE) ?>;
    const admin = <?= json_encode($admin, JSON_UNESCAPED_UNICODE) ?>;
    const programas = <?= json_encode($programas, JSON_UNESCAPED_UNICODE) ?>;

    // Función que actualiza dinámicamente el segundo menú desplegable
    // según la opción seleccionada en el primer menú.
    function mostrarOpciones() {
        const tipo = document.getElementById('tipo_reporte').value;
        const select = document.getElementById('id_elemento');
        // Reinicia las opciones del segundo menú.
        select.innerHTML = '<option value="">Seleccione</option>';

        let datos = [];
        if (tipo === 'empresa') {
            datos = empresas;
        } else if (tipo === 'administrador') {
            datos = admin;
        } else if (tipo === 'programa') {
            datos = programas;
        }

        // Recorre los datos y crea una opción por cada elemento.
        datos.forEach(dato => {
            // Verifica que el dato tenga la estructura esperada antes de usarlo.
            if (dato && dato.id !== undefined && dato.nombre !== undefined) {
                const option = document.createElement('option');
                option.value = dato.id;
                option.textContent = dato.nombre;
                select.appendChild(option);
            }
        });
    }

    // Función para abrir la página de descarga del reporte en una nueva pestaña.
    function descargar() {
        // Obtiene el ID del último reporte guardado (puede ser null).
        const id = <?= json_encode($lastId) ?>;
        if (!id) {
            alert("❌ No hay reporte para descargar.");
            return;
        }
        const formato = document.getElementById("formato").value;
        // Define la URL de descarga según el formato seleccionado.
        const url = formato === "pdf" ? "admin_descargas_pdf.php" : "admin_descargas_xml.php";
        // Abre la URL en una nueva pestaña, codificando el ID para seguridad.
        window.open(url + "?id=" + encodeURIComponent(id), "_blank");
    }
</script>
</body>
</html>