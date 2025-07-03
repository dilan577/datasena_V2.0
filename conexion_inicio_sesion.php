<?php
session_start();

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "datasenn_db");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Obtener datos del formulario
$usuario = trim($_POST['usuario']);
$password = trim($_POST['password']);
$rol = $_POST['rol'];

// Validar que los campos no estén vacíos
if (empty($usuario) || empty($password) || empty($rol)) {
    echo "<script>alert('Todos los campos son obligatorios');window.history.back();</script>";
    exit();
}

// Determinar la tabla y redirección según el rol
switch ($rol) {
    case 'super':
        $tabla = "super_administrador";
        $redirect = "SU_admin/menu_SU_admin/";
        break;
    case 'admin':
        $tabla = "admin";
        $redirect = "admin/menu_admin/";
        break;
    case 'empresa':
        $tabla = "empresas";
        $redirect = "empresa/menu_empresa/";
        break;
    default:
        echo "<script>alert('Rol inválido');window.history.back();</script>";
        exit();
}

// Consulta para verificar usuario
$stmt = $conexion->prepare("SELECT * FROM $tabla WHERE usuario = ? AND contraseña = ?");
$stmt->bind_param("ss", $usuario, $password);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 1) {
    // Usuario encontrado
    $_SESSION['usuario'] = $usuario;
    $_SESSION['rol'] = $rol;
    header("Location: $redirect");
    exit();
} else {
    // Usuario o contraseña incorrectos
    echo "<script>alert('Usuario o contraseña incorrectos');window.history.back();</script>";
    exit();
}
?>
