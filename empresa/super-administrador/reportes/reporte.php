<?php
// Conexión a la base de datos
try {
    $pdo = new PDO("mysql:host=localhost;dbname=datasena_db", "root", "123456");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

$mensaje = "";

// Obtener empresas, admins y programas
$empresas = $pdo->query("SELECT id AS id, nickname AS nombre FROM empresas")->fetchAll(PDO::FETCH_ASSOC);
$admin = $pdo->query("SELECT id, CONCAT(nombres, ' ', apellidos, ' (', nickname, ')') AS nombre FROM admin")->fetchAll(PDO::FETCH_ASSOC);
$programas = $pdo->query("SELECT id, nombre_programa AS nombre FROM programas")->fetchAll(PDO::FETCH_ASSOC);

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo_reporte'];
    $id = $_POST['id_elemento'];
    $observacion = $_POST['observacion'];

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
    <div class="barra-gov">.gov.co</div>

    <div class="contenedor">
        <h1>📋 Reportar Empresa, Admin o Programa</h1>

        <form method="POST">
            <label>Tipo de reporte:</label>
            <select name="tipo_reporte" id="tipo_reporte" required onchange="mostrarOpciones()">
                <option value="">Seleccione</option>
                <option value="empresa">Empresa</option>
                <option value="admin">Admin</option>
                <option value="programa">Programa</option>
            </select>

            <label>Selecciona el elemento:</label>
            <select name="id_elemento" id="id_elemento" required>
                <option value="">Seleccione un tipo primero</option>
            </select>

            <label>Observación:</label>
            <textarea name="observacion" rows="5" required></textarea>

            <button type="submit">Guardar Reporte</button>
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
            <button onclick="window.location.href='../super_menu.html'">⬅️ Regresar</button>
        </div>
    </div>

    <div class="barra-gov">.gov.co</div>

    <script>
        const empresas = <?= json_encode($empresas) ?>;
        const admin = <?= json_encode($admin) ?>;
        const programas = <?= json_encode($programas) ?>;

        function mostrarOpciones() {
            const tipo = document.getElementById('tipo_reporte').value;
            const select = document.getElementById('id_elemento');
            select.innerHTML = '<option value="">Seleccione</option>';

            let datos = [];
            if (tipo === 'empresa') datos = empresas;
            else if (tipo === 'admin') datos = admin;
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
