<?php
// Depuración: Verificar los datos recibidos
var_dump($_POST);

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "datasenn_db");

// Verificar si hubo error de conexión
if ($conexion->connect_error) {
    die("❌ Error de conexión: " . $conexion->connect_error);
}

// Recolección de datos del formulario (usando nombres reales de la tabla)
$nombre_programa     = trim($_POST['nombre_programa'] ?? '');
$numero_ficha        = trim($_POST['codigo_programa'] ?? ''); // este es el código único del programa
$tipo_programa       = trim($_POST['nivel_formacion'] ?? '');
$activacion          = trim($_POST['estado'] ?? '');

// Validación
if (empty($nombre_programa) || empty($numero_ficha) || empty($tipo_programa) || empty($activacion)) {
    echo "<script>alert('❌ Todos los campos son obligatorios.'); window.history.back();</script>";
    exit;
}

// Verificar si el número de ficha ya existe (porque es UNIQUE)
$verificar_sql = "SELECT id FROM programas WHERE numero_ficha = ?";
$verificar_stmt = $conexion->prepare($verificar_sql);
$verificar_stmt->bind_param("s", $numero_ficha);
$verificar_stmt->execute();
$verificar_stmt->store_result();

if ($verificar_stmt->num_rows > 0) {
    echo "<script>alert('❌ El número de ficha ya está registrado.'); window.history.back();</script>";
    exit;
}
$verificar_stmt->close();

// Insertar el programa
$sql = "INSERT INTO programas (nombre_programa, tipo_programa, numero_ficha, duracion_programa, activacion)
        VALUES (?, ?, ?, '2 años', ?)"; // duracion_programa puedes ajustarlo dinámicamente si lo deseas

$stmt = $conexion->prepare($sql);
if ($stmt === false) {
    die('❌ Error en la preparación de la consulta: ' . $conexion->error);
}

$stmt->bind_param("ssss", $nombre_programa, $tipo_programa, $numero_ficha, $activacion);

if ($stmt->execute()) {
    echo "<script>
        alert('✅ Programa registrado con éxito.');
        window.location.href = '/datasena_proyecto_1/SU_admin/menu_SU_admin/super_menu.html';
    </script>";
} else {
    echo "<script>
        alert('❌ Error al registrar el programa: " . addslashes($stmt->error) . "');
        window.history.back();
    </script>";
}

$stmt->close();
$conexion->close();
?>
