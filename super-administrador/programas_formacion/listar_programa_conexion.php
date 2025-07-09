<?php
try {
    $conexion = new PDO("mysql:host=localhost;dbname=datasenn_db", "root", "");
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'ID no proporcionado']);
    exit;
}

$id = (int) $_GET['id'];

$stmt = $conexion->prepare("SELECT nombre_programa, tipo_programa, numero_ficha, duracion_programa, activacion FROM programas WHERE id = ?");
$stmt->execute([$id]);
$programa = $stmt->fetch(PDO::FETCH_ASSOC);

if ($programa) {
    echo json_encode($programa);
} else {
    echo json_encode(['error' => 'Programa no encontrado']);
}