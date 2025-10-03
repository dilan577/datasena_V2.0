<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: inicio_sesion.html");
    exit();
}
// Establecemos la cabecera de respuesta como JSON.
// Esto indica al cliente (navegador, app, etc.) que el contenido es un objeto JSON.
header('Content-Type: application/json');

try {
    // Conectamos a la base de datos MySQL usando PDO.
    // - Host: localhost (servidor local)
    // - Base de datos: datasenn_db → ⚠️ ¿Es correcto? En otros scripts usas "datasena_db". Verifica consistencia.
    // - Usuario: "root"
    // - Contraseña: vacía (común en entornos de desarrollo)
    // 🔒 Recomendación: Añadir ";charset=utf8" para evitar problemas con tildes, ñ, etc.
    $conexion = new PDO("mysql:host=localhost;dbname=datasenn_db;charset=utf8", "root", "");
    
    // Configuramos PDO para que lance excepciones en caso de error (mejor manejo de errores).
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Preparamos una consulta SQL para obtener todos los programas.
    // Seleccionamos solo los campos necesarios (buena práctica: evitar SELECT *).
    // Campos: id, nombre_programa, codigo_programa, nivel_formacion, estado.
    $stmt = $conexion->prepare("SELECT id, nombre_programa, codigo_programa, nivel_formacion, estado FROM programas");
    
    // Ejecutamos la consulta (sin parámetros, ya que es una selección completa).
    $stmt->execute();
    
    // Obtenemos todos los resultados como un array asociativo (clave => valor).
    $programas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Devolvemos los datos en formato JSON.
    // Si no hay programas, devolverá un array vacío [] (lo cual es válido y útil para el frontend).
    echo json_encode($programas);

} catch (PDOException $e) {
    // Si ocurre un error (conexión fallida, tabla no existe, etc.),
    // capturamos la excepción y devolvemos un mensaje de error en JSON.
    // ⚠️ En producción, evita exponer $e->getMessage() por seguridad.
    // Mejor usar: ['error' => 'Error interno del servidor']
    echo json_encode(['error' => 'Error de conexión o consulta: ' . $e->getMessage()]);
}
?>