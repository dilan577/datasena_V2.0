<?php
header('Content-Type: application/json');

try {
    // Conexión a la base de datos
    $conexion = new PDO("mysql:host=localhost;dbname=datasenn_db", "root", "");
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consulta para obtener todos los programas
    $stmt = $conexion->prepare("SELECT id, nombre_programa, codigo_programa, nivel_formacion, estado FROM programas");
    $stmt->execute();
    $programas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($programas);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error de conexión o consulta: ' . $e->getMessage()]);
}
?>
