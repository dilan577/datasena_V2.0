<?php
// Establecemos el tipo de contenido de la respuesta como JSON.
// Esto es esencial para que el cliente (por ejemplo, JavaScript) sepa cómo interpretar la respuesta.
header('Content-Type: application/json');

// Validamos que se haya proporcionado un parámetro 'id' en la URL y que sea un número válido.
// Esto evita inyecciones o errores al intentar buscar con un ID no numérico.
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Si no hay ID o no es numérico, devolvemos un error en formato JSON y terminamos la ejecución.
    echo json_encode(['error' => 'ID no válido o no proporcionado.']);
    exit; // Terminamos el script inmediatamente.
}

// Asignamos el ID a una variable, asegurándonos de que sea entero (aunque ya validamos con is_numeric).
// Nota: is_numeric permite cadenas como "123", pero bindParam con PDO::PARAM_INT lo convertirá a entero.
$id = $_GET['id'];

try {
    // Establecemos conexión con la base de datos usando PDO.
    // - Host: localhost
    // - Base de datos: datasenn_db (¡nota el posible error tipográfico: ¿no debería ser "datasena_db"?)
    // - Usuario: root (sin contraseña, común en entornos locales)
    // - Charset: utf8 para soportar caracteres especiales (tildes, ñ, etc.)
    $conexion = new PDO("mysql:host=localhost;dbname=datasenn_db;charset=utf8", "root", "");
    
    // Configuramos PDO para que lance excepciones en caso de error (mejor manejo de errores).
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consulta SQL para obtener los datos de la empresa cuyo ID coincide con el proporcionado.
    // Seleccionamos solo los campos necesarios (evitamos SELECT * por buenas prácticas).
    $sql = "SELECT tipo_documento, numero_identidad, nickname,
                   telefono, correo, direccion,
                   actividad_economica, estado
            FROM empresas
            WHERE id = :id";

    // Preparamos la consulta para evitar inyecciones SQL.
    $stmt = $conexion->prepare($sql);
    
    // Vinculamos el parámetro :id con el valor de $id, especificando que es un entero.
    // Esto refuerza la seguridad y asegura el tipo de dato.
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
    // Ejecutamos la consulta.
    $stmt->execute();

    // Obtenemos una sola fila de resultado como un array asociativo (clave => valor).
    $empresa = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificamos si se encontró una empresa con ese ID.
    if ($empresa) {
        // Si existe, la devolvemos en formato JSON.
        echo json_encode($empresa);
    } else {
        // Si no se encontró, devolvemos un mensaje de error indicando que no existe.
        echo json_encode(['error' => 'Empresa no encontrada.']);
    }

} catch (PDOException $e) {
    // Si ocurre cualquier error en la conexión o en la consulta (por ejemplo, tabla no existe),
    // capturamos la excepción y devolvemos un mensaje de error genérico (en producción, evita mostrar $e->getMessage() por seguridad).
    // NOTA: En entornos de producción, es mejor no exponer detalles del error (como el mensaje de la excepción).
    echo json_encode(['error' => 'Error de conexión o consulta: ' . $e->getMessage()]);
}
?>