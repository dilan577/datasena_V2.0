<?php
// ====================================================================
// PROPÓSITO: Obtener datos de un administrador específico por su ID
// RETORNA: JSON con los datos del administrador o mensaje de error
// ====================================================================

// ====================================================================
// CONEXIÓN A LA BASE DE DATOS USANDO PDO
// ====================================================================
try {
    // Crea una nueva conexión PDO a MySQL
    // Parámetros: DSN (Data Source Name), usuario, contraseña
    $conexion = new PDO("mysql:host=localhost;dbname=datasenn_db", "root", "");
    
    // Configura PDO para que lance excepciones en caso de errores
    // Esto permite capturar errores con try-catch de manera más elegante
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    // Si hay error en la conexión, devuelve un JSON con el mensaje de error
    echo json_encode(['error' => 'Error de conexión']);
    
    // Detiene la ejecución del script
    exit;
}

// ====================================================================
// VALIDACIÓN DEL PARÁMETRO ID
// ====================================================================
// Verifica que se haya enviado el parámetro 'id' por GET
if (!isset($_GET['id'])) {
    // Si no existe el parámetro, retorna error en formato JSON
    echo json_encode(['error' => 'ID no proporcionado']);
    
    // Detiene la ejecución
    exit;
}

// ====================================================================
// CAPTURA Y SANITIZACIÓN DEL ID
// ====================================================================
// Convierte el ID a entero para prevenir inyección SQL
// (int) asegura que solo sea un número, eliminando cualquier otro carácter
$id = (int) $_GET['id'];

// ====================================================================
// CONSULTA A LA BASE DE DATOS
// ====================================================================
// Prepara la consulta SQL con JOIN para obtener el nombre del rol
// LEFT JOIN asegura que se obtenga el admin incluso si no tiene rol asignado
// El ? es un placeholder que será reemplazado de forma segura
$stmt = $conexion->prepare("
    SELECT 
        a.tipo_documento,           -- Tipo de documento del admin (CC, TI, CE, Otro)
        a.nombres,                  -- Nombres del administrador
        a.apellidos,                -- Apellidos del administrador
        a.correo_electronico,       -- Email del administrador
        r.nombre_rol as rol         -- Nombre del rol (Administrador, Usuario, etc.)
    FROM admin a                    -- Tabla principal: admin (alias 'a')
    LEFT JOIN rol r                 -- Unión con tabla rol (alias 'r')
        ON a.rol_id = r.id          -- Condición: el rol_id del admin debe coincidir con el id del rol
    WHERE a.id = ?                  -- Filtro: solo el admin con el ID especificado
");

// Ejecuta la consulta preparada, reemplazando el ? con el valor de $id
// Los corchetes [$id] crean un array con los parámetros a vincular
$stmt->execute([$id]);

// Obtiene el resultado como un array asociativo
// PDO::FETCH_ASSOC hace que las claves del array sean los nombres de las columnas
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// ====================================================================
// RESPUESTA AL CLIENTE
// ====================================================================
// Verifica si se encontró el administrador
if ($admin) {
    // Si existe, convierte el array a JSON y lo envía al cliente
    // json_encode transforma el array PHP en formato JSON
    echo json_encode($admin);
    
    /* Ejemplo de respuesta exitosa:
    {
        "tipo_documento": "CC",
        "nombres": "Juan Carlos",
        "apellidos": "Pérez González",
        "correo_electronico": "juan.perez@sena.edu.co",
        "rol": "Administrador"
    }
    */
    
} else {
    // Si no se encontró el administrador, retorna error en JSON
    echo json_encode(['error' => 'Administrador no encontrado']);
    
    /* Ejemplo de respuesta con error:
    {
        "error": "Administrador no encontrado"
    }
    */
}

// ====================================================================
// NOTAS IMPORTANTES
// ====================================================================
/*
1. SEGURIDAD:
   - Usa prepared statements para prevenir inyección SQL
   - Convierte el ID a entero (int) como capa extra de seguridad
   - No expone información sensible como contraseñas

2. PDO vs MySQLi:
   - PDO es más moderno y portable (funciona con varios tipos de BD)
   - MySQLi solo funciona con MySQL
   - PDO usa ? o :nombre para placeholders

3. JSON Response:
   - Este archivo está diseñado para ser consumido por JavaScript (AJAX)
   - Siempre retorna JSON válido
   - El cliente puede procesar fácilmente la respuesta

4. LEFT JOIN:
   - Se usa LEFT JOIN en lugar de INNER JOIN para obtener el admin
     incluso si no tiene un rol asignado (rol_id es NULL)
   - Si no hay rol, el campo 'rol' será NULL en el JSON

5. MEJORAS POSIBLES:
   - Agregar header('Content-Type: application/json') al inicio
   - Validar que el ID sea mayor a 0
   - Agregar logs de errores
   - Implementar autenticación/autorización
*/
?>