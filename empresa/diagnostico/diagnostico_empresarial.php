<?php
session_start();

// Validar que esté logueado y que sea empresa
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'empresa') {
    header("Location: ../inicio_sesion.html");
    exit();
}

// -------------------------
// Inicialización de variables
// -------------------------
$empresa = $nit = $sector = $tamano = $ubicacion = "";
$empleados = $contrataciones = $contrato_frec = $tiene_proceso = "";
$perfiles_def = $publicacion = $aprendices = $programa_apoyo = "";
$perfiles_neces = [];
$infraestructura = $apoyo_selec = $beneficios = "";

$mensaje_exito = false;
$recomendaciones = [];
$errores = [];

// -------------------------
// Conexión a la base de datos (directa)
// -------------------------
try {
    $host = 'localhost';
    $dbname = 'datasena_db'; // ajusta según tu base de datos
    $username = 'root'; // usuario MySQL
    $password = ''; // contraseña MySQL
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

// -------------------------
// Procesar POST
// -------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Recoger datos del formulario
    $empresa          = trim($_POST['empresa'] ?? '');
    $nit              = trim($_POST['nit'] ?? '');
    $sector           = $_POST['sector'] ?? '';
    $tamano           = $_POST['tamano'] ?? '';
    $ubicacion        = trim($_POST['ubicacion'] ?? '');
    $empleados        = $_POST['empleados'] ?? '';
    $contrataciones   = $_POST['contrataciones'] ?? '';
    $contrato_frec    = $_POST['contrato_frecuente'] ?? '';
    $tiene_proceso    = $_POST['tiene_proceso'] ?? '';
    $perfiles_def     = $_POST['perfiles_definidos'] ?? '';
    $publicacion      = $_POST['publicacion'] ?? '';
    $aprendices       = $_POST['aprendices'] ?? '';
    $programa_apoyo   = $_POST['programa_apoyo'] ?? '';
    $perfiles_neces   = $_POST['perfiles_necesarios'] ?? [];
    $infraestructura  = $_POST['infraestructura'] ?? '';
    $apoyo_selec      = $_POST['apoyo_seleccion'] ?? '';
    $beneficios       = $_POST['beneficios'] ?? '';

    // Validaciones básicas
    if (empty($empresa)) $errores[] = "El nombre de la empresa es obligatorio";
    if (empty($nit)) $errores[] = "El NIT es obligatorio";
    if (empty($perfiles_neces)) $errores[] = "Debe seleccionar al menos un perfil necesario";

    // Si no hay errores, procesar el diagnóstico
    if (empty($errores)) {
        try {
            // Convertir array de perfiles a texto para guardar en la base de datos
            $perfiles_neces_texto = implode(', ', $perfiles_neces);
            
            // Guardar el diagnóstico en la base de datos
            $stmt = $pdo->prepare("INSERT INTO diagnostico_empresarial 
                (empresa, nit, sector, tamano, ubicacion, empleados, contrataciones, 
                 contrato_frecuente, tiene_proceso, perfiles_definidos, publicacion, 
                 aprendices, programa_apoyo, perfiles_necesarios, infraestructura, apoyo_seleccion, beneficios) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $empresa, $nit, $sector, $tamano, $ubicacion, $empleados, $contrataciones,
                $contrato_frec, $tiene_proceso, $perfiles_def, $publicacion, $aprendices,
                $programa_apoyo, $perfiles_neces_texto, $infraestructura, $apoyo_selec, $beneficios
            ]);

            // Buscar programas que coincidan con los perfiles seleccionados
            if (!empty($perfiles_neces)) {
                // Crear un array para mapear perfiles a términos de búsqueda
                $terminos_busqueda = [];
                foreach ($perfiles_neces as $perfil) {
                    // Mapear perfiles a términos relacionados con programas
                    switch ($perfil) {
                        case 'Programación':
                        case 'Desarrollo de software':
                            $terminos_busqueda[] = 'programación';
                            $terminos_busqueda[] = 'software';
                            $terminos_busqueda[] = 'desarrollo';
                            break;
                        case 'Textiles':
                            $terminos_busqueda[] = 'textil';
                            $terminos_busqueda[] = 'confección';
                            break;
                        case 'Mecánica':
                            $terminos_busqueda[] = 'mecánica';
                            $terminos_busqueda[] = 'mecánico';
                            break;
                        case 'Logística':
                            $terminos_busqueda[] = 'logística';
                            break;
                        case 'Contabilidad':
                            $terminos_busqueda[] = 'contabilidad';
                            $terminos_busqueda[] = 'contable';
                            break;
                        case 'Electricidad':
                            $terminos_busqueda[] = 'electricidad';
                            $terminos_busqueda[] = 'eléctrico';
                            break;
                        case 'Electrónica':
                            $terminos_busqueda[] = 'electrónica';
                            $terminos_busqueda[] = 'electrónico';
                            break;
                        case 'Gestión empresarial':
                            $terminos_busqueda[] = 'gestión';
                            $terminos_busqueda[] = 'empresarial';
                            $terminos_busqueda[] = 'administración';
                            break;
                        case 'Diseño gráfico':
                            $terminos_busqueda[] = 'diseño';
                            $terminos_busqueda[] = 'gráfico';
                            break;
                        case 'Soporte técnico':
                            $terminos_busqueda[] = 'soporte';
                            $terminos_busqueda[] = 'técnico';
                            break;
                        case 'Seguridad y salud en el trabajo':
                            $terminos_busqueda[] = 'seguridad';
                            $terminos_busqueda[] = 'salud';
                            $terminos_busqueda[] = 'trabajo';
                            break;
                        case 'Administración':
                            $terminos_busqueda[] = 'administración';
                            $terminos_busqueda[] = 'administrativo';
                            break;
                        case 'Ambiental':
                            $terminos_busqueda[] = 'ambiental';
                            $terminos_busqueda[] = 'medio ambiente';
                            break;
                        case 'Telecomunicaciones':
                            $terminos_busqueda[] = 'telecomunicaciones';
                            break;
                        case 'Redes de datos':
                            $terminos_busqueda[] = 'redes';
                            $terminos_busqueda[] = 'datos';
                            break;
                        case 'Diseño de productos':
                            $terminos_busqueda[] = 'diseño';
                            $terminos_busqueda[] = 'producto';
                            break;
                        case 'Gestión documental':
                            $terminos_busqueda[] = 'gestión';
                            $terminos_busqueda[] = 'documental';
                            break;
                        case 'Servicio al cliente':
                            $terminos_busqueda[] = 'servicio';
                            $terminos_busqueda[] = 'cliente';
                            break;
                        case 'Diseño industrial':
                            $terminos_busqueda[] = 'diseño';
                            $terminos_busqueda[] = 'industrial';
                            break;
                        case 'Producción multimedia':
                            $terminos_busqueda[] = 'multimedia';
                            $terminos_busqueda[] = 'producción';
                            break;
                        case 'Diseño web':
                            $terminos_busqueda[] = 'web';
                            $terminos_busqueda[] = 'diseño';
                            break;
                        case 'Tecnología en automatización':
                            $terminos_busqueda[] = 'automatización';
                            $terminos_busqueda[] = 'tecnología';
                            break;
                        default:
                            $terminos_busqueda[] = strtolower($perfil);
                    }
                }
                
                // Eliminar duplicados
                $terminos_busqueda = array_unique($terminos_busqueda);
                
                // Construir consulta LIKE para buscar programas
                $conditions = [];
                $params = [];
                
                foreach ($terminos_busqueda as $termino) {
                    $conditions[] = "nombre_programa LIKE ?";
                    $params[] = "%$termino%";
                }
                
                $sql_conditions = implode(" OR ", $conditions);
                $sql = "SELECT * FROM programas 
                        WHERE ($sql_conditions) 
                        AND activacion = 'activo'
                        ORDER BY nombre_programa";
                
                $stmt_programas = $pdo->prepare($sql);
                $stmt_programas->execute($params);
                $recomendaciones = $stmt_programas->fetchAll(PDO::FETCH_ASSOC);
            }

            $mensaje_exito = true;

        } catch (PDOException $e) {
            $errores[] = "Error al guardar el diagnóstico: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Diagnóstico Empresarial</title>
    <link rel="shortcut icon" href="../../img/Logotipo_Datasena.png" type="image/x-icon" />
    <link rel="stylesheet" href="diagnostico_empresarial.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body>

<!-- Barra superior GOV.CO -->
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
  <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>

<header>DATASENA    
    <img src="../../img/logo-sena.png" class="logo-img" alt="Logo SENA" />  
</header>

<main class="form-container">
    <h2>Formulario de Diagnóstico Empresarial</h2>

    <!-- Mostrar errores si existen -->
    <?php if (!empty($errores)): ?>
        <div class="toast-error">
            ❌ Se encontraron errores:
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Mensaje de éxito al guardar -->
    <?php if ($mensaje_exito): ?>
        <div class="toast-success">✅ Diagnóstico guardado correctamente</div>
        <script>setTimeout(() => document.querySelector(".toast-success")?.remove(), 4000);</script>
    <?php endif; ?>

    <!-- Formulario principal -->
    <form method="POST" class="form-grid">
        <label>Nombre Empresa:</label>
        <input type="text" name="empresa" value="<?= htmlspecialchars($empresa ?? '') ?>" required />

        <label>NIT:</label>
        <input type="text" name="nit" value="<?= htmlspecialchars($nit ?? '') ?>" required />

        <label>Sector:</label>
        <select name="sector" required>
            <option value="">Seleccione un sector</option>
            <option value="Agroindustria" <?= ($sector=="Agroindustria"?"selected":"") ?>>Agroindustria</option>
            <option value="Comercio" <?= ($sector=="Comercio"?"selected":"") ?>>Comercio</option>
            <option value="Tecnología" <?= ($sector=="Tecnología"?"selected":"") ?>>Tecnología</option>
            <option value="Servicios" <?= ($sector=="Servicios"?"selected":"") ?>>Servicios</option>
            <option value="Otros" <?= ($sector=="Otros"?"selected":"") ?>>Otros</option>
        </select>

        <label>Tamaño:</label>
        <select name="tamano" required>
            <option value="">Seleccione el tamaño</option>
            <option value="Microempresa (1-10)" <?= ($tamano=="Microempresa (1-10)"?"selected":"") ?>>Microempresa (1-10)</option>
            <option value="Pequeña empresa (11-50)" <?= ($tamano=="Pequeña empresa (11-50)"?"selected":"") ?>>Pequeña empresa (11-50)</option>
            <option value="Mediana empresa (51-200)" <?= ($tamano=="Mediana empresa (51-200)"?"selected":"") ?>>Mediana empresa (51-200)</option>
            <option value="Grande (>200)" <?= ($tamano=="Grande (>200)"?"selected":"") ?>>Grande (>200)</option>
        </select>

        <label>Ubicación:</label>
        <input type="text" name="ubicacion" value="<?= htmlspecialchars($ubicacion ?? '') ?>" required />

        <label>Total empleados:</label>
        <input type="number" name="empleados" value="<?= htmlspecialchars($empleados ?? '') ?>" required />

        <label>Contrataciones último año:</label>
        <input type="number" name="contrataciones" value="<?= htmlspecialchars($contrataciones ?? '') ?>" required />

        <label>Tipo contrato frecuente:</label>
        <select name="contrato_frecuente" required>
            <option value="">Seleccione tipo de contrato</option>
            <option value="Fijo" <?= ($contrato_frec=="Fijo"?"selected":"") ?>>Fijo</option>
            <option value="Indefinido" <?= ($contrato_frec=="Indefinido"?"selected":"") ?>>Indefinido</option>
            <option value="Prestación de servicios" <?= ($contrato_frec=="Prestación de servicios"?"selected":"") ?>>Prestación de servicios</option>
            <option value="Aprendices SENA" <?= ($contrato_frec=="Aprendices SENA"?"selected":"") ?>>Aprendices SENA</option>
        </select>

        <label>¿Tiene proceso de selección formal?</label>
        <select name="tiene_proceso" required>
            <option value="">Seleccione una opción</option>
            <option value="Sí" <?= ($tiene_proceso=="Sí"?"selected":"") ?>>Sí</option>
            <option value="No" <?= ($tiene_proceso=="No"?"selected":"") ?>>No</option>
        </select>

        <label>¿Perfiles definidos?</label>
        <select name="perfiles_definidos" required>
            <option value="">Seleccione una opción</option>
            <option value="Sí" <?= ($perfiles_def=="Sí"?"selected":"") ?>>Sí</option>
            <option value="No" <?= ($perfiles_def=="No"?"selected":"") ?>>No</option>
        </select>

        <label>Medios publicación vacantes:</label>
        <select name="publicacion" required>
            <option value="">Seleccione medio de publicación</option>
            <option value="Redes sociales" <?= ($publicacion=="Redes sociales"?"selected":"") ?>>Redes sociales</option>
            <option value="Servicio Público de Empleo" <?= ($publicacion=="Servicio Público de Empleo"?"selected":"") ?>>Servicio Público de Empleo</option>
            <option value="Referidos" <?= ($publicacion=="Referidos"?"selected":"") ?>>Referidos</option>
            <option value="Otras" <?= ($publicacion=="Otras"?"selected":"") ?>>Otras</option>
        </select>

        <label>¿Vincular aprendices?</label>
        <select name="aprendices" required>
            <option value="">Seleccione una opción</option>
            <option value="Sí" <?= ($aprendices=="Sí"?"selected":"") ?>>Sí</option>
            <option value="No" <?= ($aprendices=="No"?"selected":"") ?>>No</option>
        </select>

        <label>¿Programa de apoyo?</label>
        <select name="programa_apoyo" required>
            <option value="">Seleccione una opción</option>
            <option value="Sí" <?= ($programa_apoyo=="Sí"?"selected":"") ?>>Sí</option>
            <option value="No" <?= ($programa_apoyo=="No"?"selected":"") ?>>No</option>
        </select>

        <!-- Select múltiple con Select2 -->
        <label>Perfiles necesarios:</label>
        <select name="perfiles_necesarios[]" multiple required style="width: 100%;">
            <?php 
            $opciones = ["Programación","Textiles","Mecánica","Logística","Contabilidad","Electricidad",
                        "Electrónica","Gestión empresarial","Diseño gráfico","Soporte técnico",
                        "Seguridad y salud en el trabajo","Administración","Ambiental","Telecomunicaciones",
                        "Desarrollo de software","Redes de datos","Diseño de productos","Gestión documental",
                        "Servicio al cliente","Diseño industrial","Producción multimedia","Diseño web",
                        "Tecnología en automatización"];
            foreach ($opciones as $op): ?>
                <option value="<?= htmlspecialchars($op) ?>" <?= in_array($op, $perfiles_neces) ? "selected" : "" ?>>
                    <?= htmlspecialchars($op) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>¿Tiene infraestructura para formar?</label>
        <select name="infraestructura" required>
            <option value="">Seleccione una opción</option>
            <option value="Sí" <?= ($infraestructura=="Sí"?"selected":"") ?>>Sí</option>
            <option value="No" <?= ($infraestructura=="No"?"selected":"") ?>>No</option>
        </select>

        <label>¿Requiere apoyo en selección?</label>
        <select name="apoyo_seleccion" required>
            <option value="">Seleccione una opción</option>
            <option value="Sí" <?= ($apoyo_selec=="Sí"?"selected":"") ?>>Sí</option>
            <option value="No" <?= ($apoyo_selec=="No"?"selected":"") ?>>No</option>
        </select>

        <label>¿Desea orientación tributaria?</label>
        <select name="beneficios" required>
            <option value="">Seleccione una opción</option>
            <option value="Sí" <?= ($beneficios=="Sí"?"selected":"") ?>>Sí</option>
            <option value="No" <?= ($beneficios=="No"?"selected":"") ?>>No</option>
        </select>

        <!-- Botones -->
        <button type="submit" class="btn">Enviar Diagnóstico</button>
        <button type="button" class="btn" onclick="window.location.href='../empresa_menu.php'">↩️Regresar</button>
    </form>
</main>

<!-- Bloque de resultados -->
<?php if (!empty($recomendaciones)): ?>
    <div class="form-container">
        <h3>🎓 Programas SENA recomendados según sus perfiles:</h3>
        <p><strong>Perfiles seleccionados:</strong> <?= htmlspecialchars(implode(', ', $perfiles_neces)) ?></p>
        
        <div class="programas-grid">
            <?php foreach ($recomendaciones as $programa): ?>
                <div class="programa-card">
                    <h4><?= htmlspecialchars($programa['nombre_programa']) ?></h4>
                    <p><strong>Tipo:</strong> <?= htmlspecialchars($programa['tipo_programa']) ?></p>
                    <p><strong>Número de ficha:</strong> <?= htmlspecialchars($programa['numero_ficha']) ?></p>
                    <p><strong>Duración:</strong> <?= htmlspecialchars($programa['duracion_programa']) ?> horas</p>
                    <p><strong>Estado:</strong> <span class="estado-activo"><?= htmlspecialchars($programa['activacion']) ?></span></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php elseif ($mensaje_exito && empty($recomendaciones)): ?>
    <div class="form-container">
        <p class="mensaje info">ℹ️ No se encontraron programas activos que coincidan con los perfiles seleccionados.</p>
    </div>
<?php endif; ?>

<footer>
    <a>&copy; Todos los derechos reservados al SENA</a>
</footer>

<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
    <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
  $(document).ready(function() {
    $('select[name="perfiles_necesarios[]"]').select2({
      placeholder: "Selecciona los perfiles necesarios",
      allowClear: true,
      width: '100%'
    });
  });
</script>
</body>
</html>