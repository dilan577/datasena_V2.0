<?php
// ==================== CONEXIÓN A LA BASE DE DATOS ====================
// Establece conexión con la base de datos MySQL
$conexion = new mysqli("localhost", "root", "", "datasena_db");
// Verifica si hay errores en la conexión y termina el script si existe algún problema
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// ==================== INICIALIZACIÓN DE VARIABLES ====================
// Array para almacenar los datos de una empresa individual
$empresas = [
    'id' => '',
    'tipo_documento' => '',
    'numero_identidad' => '',
    'nickname' => '',
    'telefono' => '',
    'correo' => '',
    'direccion' => '',
    'actividad_economica' => '',
    'fecha_registro' => ''
];
// Array para almacenar múltiples empresas cuando se muestran todas
$todas_empresas = [];
// Variable para mostrar mensajes de éxito o error al usuario
$mensaje = "";
// Variable para mensajes específicos de error relacionados con duplicados de número de identidad
$numero_error = ""; // nuevo para mensajes de duplicado

// ==================== ACTUALIZAR EMPRESA ====================
// Procesa la actualización de una empresa cuando se envía el formulario con un ID
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && !empty($_POST['id'])) {
    // Array para acumular errores de validación
    $errores = [];
    
    // Captura y limpia los datos del formulario
    $id = $_POST['id'];
    $tipo_documento = trim($_POST['tipo_documento']);
    $numero_identidad = trim($_POST['numero_identidad']);
    $nickname = trim($_POST['nickname']);
    $telefono = trim($_POST['telefono']);
    $correo = trim($_POST['correo']);
    $direccion = trim($_POST['direccion']);
    $actividad_economica = trim($_POST['actividad_economica']);

    // ================= VALIDACIONES =================

    // Valida que el tipo de documento sea uno de los permitidos
    $tipos_validos = ['NIT','Registro Mercantil','Registro Cámara de Comercio Extranjera','Pasaporte Empresarial','RUT','Licencia Municipal'];
    if (!in_array($tipo_documento, $tipos_validos)) {
        $errores[] = "Tipo de documento inválido.";
    }

    // Valida que el número de identidad contenga solo dígitos y tenga entre 8 y 12 caracteres
    if (!preg_match('/^\d{8,12}$/', $numero_identidad)) {
        $errores[] = "Número de identidad debe tener entre 8 y 12 dígitos.";
    }
    // Valida que el número de identidad no sea negativo
    if ((int)$numero_identidad < 0) {
        $errores[] = "Número de identidad no puede ser negativo.";
    }

    // Valida que el nombre de la empresa solo contenga letras, números y espacios (incluye caracteres acentuados)
    if (!preg_match('/^[A-Za-zÁÉÍÓÚñáéíóú0-9 ]+$/u', $nickname)) {
        $errores[] = "El nombre de la empresa solo puede contener letras, números y espacios.";
    }

    // Valida que el teléfono tenga exactamente 10 dígitos
    if (!preg_match('/^\d{10}$/', $telefono)) {
        $errores[] = "El teléfono debe tener exactamente 10 dígitos.";
    }
    // Valida que el teléfono no sea negativo
    if ((int)$telefono < 0) {
        $errores[] = "El teléfono no puede ser negativo.";
    }

    // Valida que el correo electrónico tenga un formato válido
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "Correo electrónico inválido.";
    }

    // Valida que la dirección tenga al menos 5 caracteres
    if (empty($direccion) || strlen($direccion) < 5) {
        $errores[] = "La dirección debe tener al menos 5 caracteres.";
    }

    // Valida que la actividad económica solo contenga letras, números, espacios, comas y puntos
    if (!preg_match('/^[A-Za-zÁÉÍÓÚñáéíóú0-9 ,.]+$/u', $actividad_economica)) {
        $errores[] = "Actividad económica contiene caracteres inválidos.";
    }

    // ================= VALIDAR DUPLICADOS =================
    // Solo valida duplicados si no hay errores previos de formato
    if (empty($errores)) {
        // Verifica si el número de identidad ya existe en otra empresa (excluyendo la empresa actual)
        $check = $conexion->prepare("SELECT id FROM empresas WHERE numero_identidad = ? AND id != ?");
        $check->bind_param("si", $numero_identidad, $id);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $errores[] = "El número de identidad ya está en uso por otra empresa.";
        }
        $check->close();

        // Verifica si el nombre de la empresa ya existe en otra empresa (excluyendo la empresa actual)
        $check = $conexion->prepare("SELECT id FROM empresas WHERE nickname = ? AND id != ?");
        $check->bind_param("si", $nickname, $id);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $errores[] = "El nombre de la empresa ya está en uso.";
        }
        $check->close();

        // Verifica si el correo electrónico ya existe en otra empresa (excluyendo la empresa actual)
        $check = $conexion->prepare("SELECT id FROM empresas WHERE correo = ? AND id != ?");
        $check->bind_param("si", $correo, $id);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $errores[] = "El correo electrónico ya está en uso por otra empresa.";
        }
        $check->close();

        // Verifica si el número de teléfono ya existe en otra empresa (excluyendo la empresa actual)
        $check = $conexion->prepare("SELECT id FROM empresas WHERE telefono = ? AND id != ?");
        $check->bind_param("si", $telefono, $id);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $errores[] = "El número de teléfono ya está en uso por otra empresa.";
        }
        $check->close();
    }

    // ================= ACTUALIZAR SI NO HAY ERRORES =================
    // Si no hay errores de validación, procede a actualizar la empresa en la base de datos
    if (empty($errores)) {
        $stmt = $conexion->prepare("UPDATE empresas SET tipo_documento=?, numero_identidad=?, nickname=?, telefono=?, correo=?, direccion=?, actividad_economica=? WHERE id=?");
        $stmt->bind_param("sssssssi", $tipo_documento, $numero_identidad, $nickname, $telefono, $correo, $direccion, $actividad_economica, $id);
        if ($stmt->execute()) {
            $mensaje = "✅ Empresa actualizada correctamente.";
        } else {
            $mensaje = "❌ Error al actualizar la empresa.";
        }
        $stmt->close();
    } else {
        // Si hay errores, los concatena y los muestra al usuario
        $mensaje = "❌ " . implode("<br>❌ ", $errores);
    }
}

// ==================== BUSCAR EMPRESA ====================
// Procesa la búsqueda de una empresa específica por número de identidad o nombre
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar'])) {
    // Captura y limpia el dato de búsqueda
    $dato = trim($_POST['dato_busqueda']);
    // Consulta que busca por número de identidad o nombre de empresa
    $sql = "SELECT * FROM empresas WHERE numero_identidad = ? OR nickname = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ss", $dato, $dato);
    $stmt->execute();
    $resultado = $stmt->get_result();
    // Si encuentra resultados, carga los datos en el array $empresas
    if ($resultado->num_rows > 0) {
        $empresas = $resultado->fetch_assoc();
    } else {
        $mensaje = "❌ No se encontró ninguna empresa.";
    }
    $stmt->close();
}

// ==================== MOSTRAR TODAS LAS EMPRESAS ====================
// Procesa la solicitud de mostrar todas las empresas registradas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mostrar_todos'])) {
    // Consulta todas las empresas sin filtros
    $sql = "SELECT * FROM empresas";
    $resultado = $conexion->query($sql);
    // Si hay resultados, los carga en el array $todas_empresas
    if ($resultado && $resultado->num_rows > 0) {
        while ($fila = $resultado->fetch_assoc()) {
            $todas_empresas[] = $fila;
        }
    } else {
        $mensaje = "❌ No hay empresas registradas.";
    }
}

// Cierra la conexión a la base de datos
$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Visualizar / Actualizar Empresas</title>
    <!-- Favicon del sitio -->
    <link rel="shortcut icon" href="../../img/Logotipo_Datasena.png" type="image/x-icon">
    <!-- Hoja de estilos CSS -->
    <link rel="stylesheet" href="../empresa/actualizar_empresa_su_v2.css">
</head>
<body>
<!-- ==================== BARRA SUPERIOR GOV.CO ==================== -->
<!-- Barra de navegación superior con enlace al portal del gobierno colombiano -->
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
  <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>

<!-- ==================== ENCABEZADO ==================== -->
<!-- Título principal de la aplicación -->
<h1>DATASENA</h1>
<!-- Logo del SENA -->
<img src="../../img/logo-sena.png" alt="Logo SENA" class="img">

<!-- ==================== CONTENEDOR PRINCIPAL ==================== -->
<div class="form-container">
    <h2>Visualizar / Actualizar Empresas</h2>
    
    <!-- ==================== MENSAJES AL USUARIO ==================== -->
    <!-- Muestra mensajes de éxito o error si existen -->
    <?php if (!empty($mensaje)): ?>
        <p style="color:<?= str_contains($mensaje, '❌') ? 'red' : 'green' ?>; font-weight:bold;"><?= $mensaje ?></p>
    <?php endif; ?>
    
    <!-- ==================== FORMULARIO DE BÚSQUEDA ==================== -->
    <!-- Formulario para buscar empresas o mostrar todas -->
    <form id="form-busqueda" action="actualizar_empresa_su.php" method="post" style="display:flex; flex-wrap: wrap; gap: 10px; align-items: center;">
        <label for="buscar_dato">Buscar por número de identidad o nombre de la empresa:</label>
        <input type="text" id="buscar_dato" name="dato_busqueda" placeholder="Ingrese número de identidad o nombre" required>
        <!-- Botón para ejecutar la búsqueda -->
        <button class="logout-btn" type="submit" name="buscar">🔍 Buscar</button>
        <!-- Botón para mostrar todas las empresas -->
        <button class="logout-btn" type="submit" name="mostrar_todos" id="btn-todos">📋 Mostrar Todas</button>
        <!-- Botón para regresar al menú principal -->
        <button class="logout-btn" onclick="window.location.href='../super_menu.html'">↩️ Regresar</button>
    </form>
    
    <!-- Espaciador visual -->
    <div style="height: 0.1cm;"></div>
    <hr style="border: 0.01px solid #ccc; width: 100%;">
    
    <!-- ==================== SCRIPT: REMOVER VALIDACIÓN REQUERIDA ==================== -->
    <!-- Script que remueve el atributo "required" del campo de búsqueda cuando se presiona "Mostrar Todas" -->
    <script>
        document.getElementById('btn-todos').addEventListener('click', function () {
            document.getElementById('buscar_dato').removeAttribute('required');
        });
    </script>
    
    <!-- ==================== FORMULARIO DE EDICIÓN ==================== -->
    <!-- Muestra el formulario de edición solo si se encontró una empresa -->
    <?php if (!empty($empresas['id'])): ?>
        <form class="form-grid" action="actualizar_empresa_su.php" method="post">
            <!-- Campo oculto con el ID de la empresa -->
            <input type="hidden" name="id" value="<?= htmlspecialchars($empresas['id']) ?>">
            
            <!-- Campo: Tipo de documento -->
            <div class="form-row">
                <label>Tipo de documento:</label>
                <select name="tipo_documento" required>
                    <option value="">Seleccione...</option>
                    <?php
                    // Genera las opciones del select con los tipos de documento válidos
                    $tipos = ['NIT','Registro Mercantil','Registro Cámara de Comercio Extranjera','Pasaporte Empresarial','RUT','Licencia Municipal'];
                    foreach ($tipos as $tipo) {
                        // Marca como seleccionada la opción que coincide con el tipo actual
                        $sel = ($empresas['tipo_documento'] == $tipo) ? 'selected' : '';
                        echo "<option value='$tipo' $sel>$tipo</option>";
                    }
                    ?>
                </select>
            </div>
            
            <!-- Campo: Número de identidad con validación de patrón -->
            <div class="form-row">
                <label>Número de identidad:</label>
                <input type="text" id="numero_identidad" name="numero_identidad"
                       value="<?= htmlspecialchars($empresas['numero_identidad']) ?>"
                       required pattern="\d{8,12}">
                <!-- Elemento de salida para mostrar errores de duplicado -->
                <output id="numero_output" for="numero_identidad" style="display:block; color:red; font-weight:bold; margin-top:4px;">
                    <?= isset($numero_error) && $numero_error !== "" ? htmlspecialchars($numero_error) : '' ?>
                </output>
            </div>
            
            <!-- Campo: Nombre de la empresa -->
            <div class="form-row"><label>Nombre de la empresa:</label><input type="text" name="nickname" value="<?= htmlspecialchars($empresas['nickname']) ?>" required></div>
            
            <!-- Campo: Teléfono con validación de patrón (10 dígitos) -->
            <div class="form-row"><label>Teléfono:</label><input type="text" name="telefono" value="<?= htmlspecialchars($empresas['telefono']) ?>" pattern="\d{10}" required></div>
            
            <!-- Campo: Correo electrónico -->
            <div class="form-row"><label>Correo electrónico:</label><input type="email" name="correo" value="<?= htmlspecialchars($empresas['correo']) ?>" required></div>
            
            <!-- Campo: Dirección -->
            <div class="form-row"><label>Dirección:</label><input type="text" name="direccion" value="<?= htmlspecialchars($empresas['direccion']) ?>" required></div>
            
            <!-- Campo: Actividad económica -->
            <div class="form-row"><label>Actividad económica:</label><input type="text" name="actividad_economica" value="<?= htmlspecialchars($empresas['actividad_economica']) ?>" required></div>
            
            <!-- Campo: Fecha de registro (solo lectura) -->
            <div class="form-row">
                <label>Fecha de registro:</label>
                <input type="text" value="<?= htmlspecialchars($empresas['fecha_registro']) ?>" readonly>
            </div>
            
            <!-- Botones de acción del formulario -->
            <div class="form-row botones-finales">
                <!-- Botón para enviar el formulario y actualizar la empresa -->
                <button class="logout-btn" type="submit">Actualizar</button>
                <!-- Botón para regresar al menú principal -->
                <button class="logout-btn" type="button" onclick="window.location.href='../super_menu.html'">Regresar</button>
            </div>
        </form>
    <?php endif; ?>
    
    <!-- ==================== TABLA DE TODAS LAS EMPRESAS ==================== -->
    <!-- Muestra una tabla con todas las empresas solo si hay datos en el array -->
    <?php if (!empty($todas_empresas)): ?>
        <h3>📋 Lista de Empresas Registradas</h3>
        <div style="overflow-x: auto;">
            <table border="1" cellpadding="6" cellspacing="0" style="width:100%; border-collapse: collapse; background: #fff;">
                <!-- Encabezados de la tabla -->
                <thead style="background-color: #0078c0; color: white;">
                    <tr>
                        <th>Tipo de Documento</th>
                        <th>Número de Identidad</th>
                        <th>Nombre de la Empresa</th>
                        <th>Teléfono</th>
                        <th>Correo Electrónico</th>
                        <th>Dirección</th>
                        <th>Actividad Económica</th>
                        <th>Estado</th>
                        <th>Fecha de Registro</th>
                    </tr>
                </thead>
                <!-- Cuerpo de la tabla con los datos de cada empresa -->
                <tbody>
                    <?php foreach ($todas_empresas as $e): ?>
                        <tr>
                            <td><?= htmlspecialchars($e['tipo_documento']) ?></td>
                            <td><?= htmlspecialchars($e['numero_identidad']) ?></td>
                            <td><?= htmlspecialchars($e['nickname']) ?></td>
                            <td><?= htmlspecialchars($e['telefono']) ?></td>
                            <td><?= htmlspecialchars($e['correo']) ?></td>
                            <td><?= htmlspecialchars($e['direccion']) ?></td>
                            <td><?= htmlspecialchars($e['actividad_economica']) ?></td>
                            <!-- Muestra "Activo" o "Inactivo" según el valor del campo estado -->
                            <td><?= $e['estado'] == 1 ? 'Activo' : 'Inactivo' ?></td>
                            <td><?= htmlspecialchars($e['fecha_registro']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- ==================== PIE DE PÁGINA ==================== -->
<footer>
    <a>&copy; 2025 Todos los derechos reservados - Proyecto SENA</a>
</footer>

<!-- ==================== BARRA INFERIOR GOV.CO ==================== -->
<!-- Barra de navegación inferior con enlace al portal del gobierno colombiano -->
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
  <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>

<!-- ==================== SCRIPT: VALIDACIÓN PERSONALIZADA ==================== -->
<!-- Script que maneja la validación personalizada del campo número de identidad -->
<script>
(function(){
    // Obtiene referencias a los elementos del DOM
    const numInput = document.getElementById('numero_identidad');
    const output = document.getElementById('numero_output');
    
    // Si existe un mensaje de error, establece la validación personalizada
    if (numInput && output && output.textContent.trim() !== "") {
        numInput.setCustomValidity(output.textContent.trim());
        // Reporta la validez del campo (muestra el mensaje de error)
        if (typeof numInput.reportValidity === 'function') {
            numInput.reportValidity();
        }
    }
    
    // Agrega un evento que limpia el mensaje de error cuando el usuario comienza a escribir
    if (numInput) {
        numInput.addEventListener('input', function() {
            this.setCustomValidity('');
            if (output) output.textContent = '';
        });
    }
})();
</script>
</body>
</html>