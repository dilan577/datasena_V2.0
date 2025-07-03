<?php
// Conexión con PDO
try {
    $conexion = new PDO("mysql:host=localhost;dbname=datasena_db", "root", "123456");
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

$programas = [];
$mensaje = "";
$searchQuery = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $searchQuery = trim($_POST['search']);

    $sql = "SELECT nombre_programa, numero_ficha, tipo_programa, duracion_programa, activacion 
            FROM programas 
            WHERE nombre_programa LIKE :search OR tipo_programa LIKE :search";
    $stmt = $conexion->prepare($sql);
    $stmt->bindValue(':search', "%$searchQuery%");
    $stmt->execute();
    $programas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($programas)) {
        $mensaje = "⚠️ No se encontraron programas con ese criterio.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listar Programas</title>
    <link rel="stylesheet" href="listar_programa.css">
    <link rel="icon" href="../../img/Logotipo_Datasena.png" type="image/x-icon">
</head>
<body>
    <div class="barra-gov">
        <img src="../../img/gov.png" alt="Gobierno de Colombia" class="gov-logo">
    </div>

    <header>DATASENA</header>
    <img src="../../img/logo-sena.png" alt="Logo SENA" class="img">

    <div class="form-container">
        <h2>Listar Programas</h2>

        <?php if (!empty($mensaje)): ?>
            <p class="mensaje-error"><?= htmlspecialchars($mensaje) ?></p>
        <?php endif; ?>

        <form action="listar_programa.php" method="post">
            <label for="search">Buscar programa:</label>
            <input type="text" id="search" name="search" placeholder="Nombre o tipo de programa" value="<?= htmlspecialchars($searchQuery) ?>" required>
            <button class="logout-btn" type="submit">🔍 Buscar</button>
        </form>

        <hr>

        <?php if (!empty($programas)): ?>
            <?php foreach ($programas as $p): ?>
                <div class="empresa-card">
                    <p><strong>Nombre del Programa:</strong> <?= htmlspecialchars($p['nombre_programa']) ?></p>
                    <p><strong>Número de Ficha:</strong> <?= htmlspecialchars($p['numero_ficha']) ?></p>
                    <p><strong>Tipo de Programa:</strong> <?= htmlspecialchars($p['tipo_programa']) ?></p>
                    <p><strong>Duración:</strong> <?= htmlspecialchars($p['duracion_programa']) ?> meses</p>
                    <p><strong>Activación:</strong> <?= htmlspecialchars($p['activacion']) ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="back_visual" style="margin-top: 20px;">
            <button class="logout-btn" onclick="window.location.href='../super_menu.html'">⬅ Regresar</button>
        </div>
    </div>

    <footer>
        <a>&copy; Todos los derechos reservados al SENA</a>
    </footer>

    <div class="barra-gov">
        <img src="../../img/gov.png" alt="Gobierno de Colombia" class="gov-logo">
    </div>
</body>
</html>
