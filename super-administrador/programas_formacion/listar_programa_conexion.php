<?php
// Establecemos el tipo de contenido de la respuesta como JSON.
// Aunque no está explícito aquí, es una buena práctica incluirlo al devolver JSON.
// (Se añadirá más adelante en una nota).

try {
    // Conectamos a la base de datos MySQL usando PDO.
    // - Host: localhost (servidor local)
    // - Base de datos: datasenn_db (¡verifica si el nombre es correcto! ¿Quizás debería ser "datasena_db"?)
    // - Usuario: "root"
    // - Contraseña: vacía (común en entornos de desarrollo local)
    // Nota: No se especifica el charset; se recomienda añadir ";charset=utf8" para evitar problemas con tildes o caracteres especiales.
    $conexion = new PDO("mysql:host=localhost;dbname=datasenn_db", "root", "");
    
    // Configuramos PDO para que lance excepciones en caso de error (mejor manejo de errores).
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // Si falla la conexión, devolvemos un error genérico en formato JSON.
    // ⚠️ En producción, evita mostrar detalles del error (como $e->getMessage()) por seguridad.
    echo json_encode(['error' => 'Error de conexión']);
    exit; // Terminamos la ejecución inmediatamente.
}

// Verificamos que se haya proporcionado el parámetro 'id' en la URL.
if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'ID no proporcionado']);
    exit;
}

// Convertimos el ID a entero para mayor seguridad (evita inyecciones o valores no numéricos).
// Esto es una buena práctica incluso si usas consultas preparadas.
$id = (int) $_GET['id'];

// Validación adicional: aseguramos que el ID sea positivo (opcional, pero recomendable).
if ($id <= 0) {
    echo json_encode(['error' => 'ID inválido']);
    exit;
}

// Preparamos una consulta SQL para obtener los datos del programa con el ID dado.
// Seleccionamos solo los campos necesarios (buena práctica: evitar SELECT *).
// Usamos un marcador posicional (?) en lugar de uno nombrado (:id), lo cual es válido.
$stmt = $conexion->prepare("SELECT nombre_programa, tipo_programa, numero_ficha, duracion_programa, activacion FROM programas WHERE id = ?");

// Ejecutamos la consulta pasando el ID como parámetro (en un array).
// Esto protege contra inyecciones SQL gracias a las consultas preparadas.
$stmt->execute([$id]);

// Obtenemos una sola fila del resultado como un array asociativo (clave => valor).
$programa = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificamos si se encontró un programa con ese ID.
if ($programa) {
    // Si existe, lo devolvemos en formato JSON.
    // ✅ Recomendación: añadir header('Content-Type: application/json'); al inicio del script.
    echo json_encode($programa);
} else {
    // Si no se encontró, devolvemos un mensaje de error claro.
    echo json_encode(['error' => 'Programa no encontrado']);
}
?>