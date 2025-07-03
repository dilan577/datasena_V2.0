<?php
header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['error' => 'ID no válido o no proporcionado.']);
    exit;
}

$id = $_GET['id'];

try {
    $conexion = new PDO("mysql:host=localhost;dbname=datasenn_db;charset=utf8", "root", "");
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT tipo_documento, numero_identidad, nickname,
                   telefono, correo, direccion,
                   actividad_economica, estado
            FROM empresas
            WHERE id = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $empresa = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($empresa) {
        echo json_encode($empresa);
    } else {
        echo json_encode(['error' => 'Empresa no encontrada.']);
    }

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error de conexión o consulta: ' . $e->getMessage()]);
}
