<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "123456", "datasenn_db");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
$mensaje = "";

// Inicializar datos del programa vacíos
$programa = [
    'id' => '',
    'nombre_programa' => '',
    'tipo_programa' => '',
    'numero_ficha' => '',
    'duracion_programa' => '',
    'activacion' => ''
];

$mensaje = "";

// Actualizar datos del programa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && !empty($_POST['id'])) {
    $id = $_POST['id'];
    $nombre_programa = $_POST['nombre_programa'];
    $tipo_programa = $_POST['tipo_programa'];
    $numero_ficha = $_POST['numero_ficha'];
    $duracion_programa = $_POST['duracion_programa'];
    $activacion = $_POST['activacion'];

    $stmt = $conexion->prepare("UPDATE programas SET nombre_programa=?, tipo_programa=?, numero_ficha=?, duracion_programa=?, activacion=? WHERE id=?");
    $stmt->bind_param("sssssi", $nombre_programa, $tipo_programa, $numero_ficha, $duracion_programa, $activacion, $id);
    
    if ($stmt->execute()) {
        $mensaje = "Programa actualizado correctamente.";
    } else {
        $mensaje = "Error al actualizar el programa.";
    }

    $stmt->close();
}

// Buscar programa por ID
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['programa_id']) && !empty($_GET['programa_id'])) {
    $programa_id = $_GET['programa_id'];

    $stmt = $conexion->prepare("SELECT * FROM programas WHERE id = ?");
    $stmt->bind_param("i", $programa_id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $programa = $resultado->fetch_assoc();
    } else {
        $mensaje = "No se encontró ningún programa con ese ID.";
    }

    $stmt->close();
}

$conexion->close();
?>
