<?php
//  Establecemos la codificación de caracteres para evitar problemas con tildes, ñ, etc.
header('Content-Type: text/html; charset=utf-8');

//  Conexión segura a la base de datos usando PDO (más moderno y seguro que MySQLi)
// - Host: localhost (servidor local)
// - Base de datos: datasena_db
// - Usuario: root (sin contraseña, común en desarrollo)
// - Charset: utf8 → esencial para soportar caracteres especiales
try {
    $pdo = new PDO("mysql:host=localhost;dbname=datasena_db;charset=utf8", "root", "");
    // Configuramos PDO para que lance excepciones en caso de error (mejor manejo de errores)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Si falla la conexión, mostramos un mensaje de error seguro (sin exponer detalles técnicos)
    die("Error de conexión: " . htmlspecialchars($e->getMessage()));
}

//  Variables para manejar el estado del formulario y resultados
$mensaje = "";      // Mensaje de éxito o error
$lastId = null;     // ID del último reporte insertado (para descarga)

//  Consultamos las listas necesarias para el formulario dinámico
// - Empresas: usamos 'nickname' como nombre visible
// - Administradores: concatenamos nombres, apellidos y nickname para mostrar "Nombre Apellido (usuario)"
// - Programas: usamos 'nombre_programa'
$empresas = $pdo->query("SELECT id, nickname AS nombre FROM empresas")->fetchAll(PDO::FETCH_ASSOC);
$admin = $pdo->query("SELECT id, CONCAT(nombres, ' ', apellidos, ' (', nickname, ')') AS nombre FROM admin")->fetchAll(PDO::FETCH_ASSOC);
$programas = $pdo->query("SELECT id, nombre_programa AS nombre FROM programas")->fetchAll(PDO::FETCH_ASSOC);

//  Procesamos el formulario SOLO si se envió por método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //  Validamos que el tipo de reporte sea uno de los permitidos
    $tiposValidos = ['empresa', 'administrador', 'programa'];
    $tipo = $_POST['tipo_reporte'] ?? '';
    if (!in_array($tipo, $tiposValidos)) {
        die("❌ Tipo de reporte no válido.");
    }

    //  Validamos que el ID sea un entero positivo (evita inyecciones o valores inválidos)
    $id = filter_input(INPUT_POST, 'id_elemento', FILTER_VALIDATE_INT);
    if ($id === false || $id <= 0) {
        die("❌ ID inválido.");
    }

    //  Validamos la observación: no vacía y al menos 5 caracteres
    $observacion = trim($_POST['observacion'] ?? '');
    if (strlen($observacion) < 5) {
        die("❌ La observación debe tener al menos 5 caracteres.");
    }

    //  Insertamos el reporte en la base de datos usando consulta preparada (seguro contra SQL Injection)
    $stmt = $pdo->prepare("INSERT INTO reportes (tipo_reporte, id_referenciado, observacion) VALUES (?, ?, ?)");
    $stmt->execute([$tipo, $id, $observacion]);

    //  Mensaje de éxito y obtenemos el ID del nuevo registro
    $mensaje = "✅ Reporte guardado exitosamente. Puedes descargarlo:";
    $lastId = $pdo->lastInsertId();
}
?>

<!--  Estructura HTML5 estándar con soporte multilenguaje y responsive -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportar Elemento</title>
    <!--  Favicon personalizado del sistema -->
    <link rel="shortcut icon" href="../../img/Logotipo_Datasena.png" type="image/x-icon" />
    <!--  Estilos CSS personalizados -->
    <link rel="stylesheet" href="reporte.css" />
</head>
<body>

<!-- 🇨🇴 Barra superior del Gobierno de Colombia (GOV.CO) -->
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
  <!-- Enlace al portal oficial del Estado colombiano -->
  <a href="https://www.gov.co/" target="_blank" rel="noopener" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>

<!--  Encabezado del sistema DATASENA -->
<header>
    <h1>DATASENA</h1>
    <img src="../../img/logo-sena.png" alt="Logo SENA" class="img" />
</header>

<!--  Contenedor principal del formulario -->
<div class="contenedor">
    <h2>📋 Reportar Empresa, Admin o Programa</h2>

    <!--  Formulario para crear un nuevo reporte -->
    <form method="POST">
        <!-- Selector de tipo de reporte (dispara la actualización dinámica) -->
        <label for="tipo_reporte">Tipo de reporte:</label>
        <select name="tipo_reporte" id="tipo_reporte" required onchange="mostrarOpciones()">
            <option value="">Seleccione</option>
            <option value="empresa">Empresa</option>
            <option value="administrador">Administrador</option>
            <option value="programa">Programa</option>
        </select>

        <!-- Selector dinámico que se llena con JavaScript según el tipo elegido -->
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

    <!--  Mensaje de éxito si se guardó el reporte -->
    <?php if (isset($lastId)): ?>
        <div class="mensaje"><?= htmlspecialchars($mensaje) ?></div>

        <!--  Opciones de descarga (PDF o XML) -->
        <div class="descargas">
            <label for="formato">📥 Descargar como:</label>
            <select id="formato">
                <option value="pdf">PDF</option>
                <option value="xml">XML</option>
            </select>
            <button type="button" onclick="descargar()">Descargar</button>
        </div>
    <?php endif; ?>

    <!-- Botón para regresar al menú principal -->
    <div class="acciones-extra">
        <button type="button" onclick="window.location.href='../super_menu.html'">↩️ Regresar</button>
    </div>
</div>

<!-- Pie de página con derechos de autor -->
<footer>
    <p>&copy; 2025 Todos los derechos reservados - Proyecto SENA</p>
</footer>

<!-- 🇨🇴 Barra inferior del Gobierno de Colombia (GOV.CO) -->
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
    <a href="https://www.gov.co/" target="_blank" rel="noopener" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>

<!-- Lógica JavaScript para interactividad dinámica -->
<script>
    //  Cargamos los datos desde PHP al JavaScript (con soporte para tildes y ñ)
    const empresas = <?= json_encode($empresas, JSON_UNESCAPED_UNICODE) ?>;
    const admin = <?= json_encode($admin, JSON_UNESCAPED_UNICODE) ?>;
    const programas = <?= json_encode($programas, JSON_UNESCAPED_UNICODE) ?>;

    // Función que actualiza el segundo select según la opción elegida
    function mostrarOpciones() {
        const tipo = document.getElementById('tipo_reporte').value;
        const select = document.getElementById('id_elemento');
        // Mostramos un estado de "cargando" brevemente
        select.innerHTML = '<option value="">Cargando...</option>';

        let datos = [];
        if (tipo === 'empresa') {
            datos = empresas;
        } else if (tipo === 'administrador') {
            datos = admin;
        } else if (tipo === 'programa') {
            datos = programas;
        }

        // Reiniciamos el select
        select.innerHTML = '<option value="">Seleccione</option>';

        // Si no hay datos, mostramos un mensaje y deshabilitamos el campo
        if (datos.length === 0) {
            select.innerHTML = '<option value="">No hay elementos disponibles</option>';
            select.disabled = true;
        } else {
            select.disabled = false;
            // Rellenamos las opciones dinámicamente
            datos.forEach(dato => {
                // Validamos que el dato tenga estructura correcta
                if (dato && dato.id !== undefined && dato.nombre !== undefined) {
                    const option = document.createElement('option');
                    option.value = dato.id;
                    option.textContent = dato.nombre;
                    select.appendChild(option);
                }
            });
        }
    }

    // Función para abrir la descarga en una nueva pestaña
    function descargar() {
        const id = <?= json_encode($lastId) ?>; // null si no hay reporte
        if (!id) {
            alert("❌ No hay reporte para descargar.");
            return;
        }
        const formato = document.getElementById("formato").value;
        const url = formato === "pdf" ? "descargas_pdf.php" : "descargas_xml.php";
        // Abrimos en nueva pestaña con ID codificado (seguro para URLs)
        window.open(url + "?id=" + encodeURIComponent(id), "_blank");
    }
</script>
</body>
</html>