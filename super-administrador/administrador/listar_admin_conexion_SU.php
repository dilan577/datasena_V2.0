<?php
// get_admin.php
try {
    $conexion = new PDO("mysql:host=localhost;dbname=datasenn_db", "root", "123456");
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

$stmt = $conexion->prepare("SELECT a.tipo_documento, a.nombres, a.apellidos, a.correo_electronico, r.nombre_rol as rol FROM admin a LEFT JOIN rol r ON a.rol_id = r.id WHERE a.id = ?");
$stmt->execute([$id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin) {
    echo json_encode($admin);
} else {
    echo json_encode(['error' => 'Administrador no encontrado']);
}
