<?php
// Conexión a la base de datos
try {
    $pdo = new PDO("mysql:host=localhost;dbname=datasena_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

$mensaje = "";

// Obtener empresas, administradores y programas
$empresas = $pdo->query("SELECT id, nickname AS nombre FROM empresas")->fetchAll(PDO::FETCH_ASSOC);
$administradores = $pdo->query("SELECT id, CONCAT(nombres, ' ', apellidos, ' (', nickname, ')') AS nombre FROM admin")->fetchAll(PDO::FETCH_ASSOC);
$programas = $pdo->query("SELECT id, nombre_programa AS nombre FROM programas")->fetchAll(PDO::FETCH_ASSOC);

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo_reporte'];
    $id = $_POST['id_elemento'];
    $observacion = $_POST['observacion'];

    // Insertar en la tabla reportes
    $stmt = $pdo->prepare("INSERT INTO reportes (tipo_reporte, id_referenciado, observacion) VALUES (?, ?, ?)");
    $stmt->execute([$tipo, $id, $observacion]);

    $mensaje = "✅ Reporte guardado exitosamente. Puedes descargarlo:";
    $lastId = $pdo->lastInsertId();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportar Elemento</title>
    <link rel="shortcut icon" href="../../img/Logotipo_Datasena.png" type="image/x-icon">
    <link rel="stylesheet" href="reporte.css">
</head>
<body>

    <!--barra del gov superior-->
    <nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
    <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
    </nav>
    
    <h1>DATASENA</h1>
    

    <div class="contenedor">
        <header>
            <img src="../../img/logo-sena.png" alt="Logo SENA" class="img">
            📋 Reportar Empresa, Administrador o Programa</header>

        <form method="POST">
            <label>Tipo de reporte:</label>
            <select name="tipo_reporte" id="tipo_reporte" required onchange="mostrarOpciones()">
                <option value="">Seleccione</option>
                <option value="empresa">Empresa</option>
                <option value="administrador">Administrador</option>
                <option value="programa">Programa</option>
            </select>

            <label>Selecciona una opción:</label>
            <select name="id_elemento" id="id_elemento" required>
                <option value="">Seleccione un tipo primero</option>
            </select>

            <label>Observación:</label>
            <textarea name="observacion" rows="5" required></textarea>

            <button type="submit">✅ Crear</button>
        </form>

        <?php if (isset($lastId)): ?>
            <div class="mensaje"><?= $mensaje ?></div>

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
            <button onclick="window.location.href='../super_menu.html'">↩️ Regresar</button>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 Todos los derechos reservados - Proyecto SENA</p>
    </footer>

    <!--barra del gov inferior-->
    <nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
    <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
    </nav>

    <script>
        const empresas = <?= json_encode($empresas) ?>;
        const administradores = <?= json_encode($administradores) ?>;
        const programas = <?= json_encode($programas) ?>;

        function mostrarOpciones() {
            const tipo = document.getElementById('tipo_reporte').value;
            const select = document.getElementById('id_elemento');
            select.innerHTML = '<option value="">Seleccione</option>';

            let datos = [];
            if (tipo === 'empresa') datos = empresas;
            else if (tipo === 'administrador') datos = administradores;
            else if (tipo === 'programa') datos = programas;

            datos.forEach(dato => {
                const option = document.createElement('option');
                option.value = dato.id;
                option.textContent = dato.nombre;
                select.appendChild(option);
            });
        }

        function descargar() {
            const id = <?= isset($lastId) ? $lastId : 'null' ?>;
            const formato = document.getElementById("formato").value;

            if (!id) {
                alert("❌ No hay reporte para descargar.");
                return;
            }

            const url = formato === "pdf" ? "descargas_pdf.php" : "descargas_xml.php";
            window.open(url + "?id=" + id, "_blank");
        }
    </script>
</body>
</html>
