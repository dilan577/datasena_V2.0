<?php
// Conexión
$conexion = new mysqli("localhost", "root", "", "datasena_db");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Variables
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
$todas_empresas = [];
$mensaje = "";
$numero_error = ""; // nuevo para mensajes de duplicado

// Actualizar empresa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && !empty($_POST['id'])) {
    $errores = [];
    $id = $_POST['id'];
    $tipo_documento = trim($_POST['tipo_documento']);
    $numero_identidad = trim($_POST['numero_identidad']);
    $nickname = trim($_POST['nickname']);
    $telefono = trim($_POST['telefono']);
    $correo = trim($_POST['correo']);
    $direccion = trim($_POST['direccion']);
    $actividad_economica = trim($_POST['actividad_economica']);

    // Validaciones
    $tipos_validos = ['NIT','Registro Mercantil','Registro Cámara de Comercio Extranjera','Pasaporte Empresarial','RUT','Licencia Municipal'];
    if (!in_array($tipo_documento, $tipos_validos)) {
        $errores[] = "Tipo de documento inválido.";
    }
    if (!preg_match('/^\d{8,12}$/', $numero_identidad)) {
        $errores[] = "Número de identidad debe tener entre 8 y 12 dígitos.";
    }
    if (!preg_match('/^[A-Za-zÁÉÍÓÚñáéíóú0-9 ]+$/u', $nickname)) {
        $errores[] = "El nombre solo puede contener letras, números y espacios.";
    }
    if (!preg_match('/^\d{10}$/', $telefono)) {
        $errores[] = "El teléfono debe tener exactamente 10 dígitos.";
    }
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "Correo electrónico inválido.";
    }
    if (empty($direccion) || strlen($direccion) < 5) {
        $errores[] = "La dirección debe tener al menos 5 caracteres.";
    }
    if (!preg_match('/^[A-Za-zÁÉÍÓÚñáéíóú0-9 ,.]+$/u', $actividad_economica)) {
        $errores[] = "Actividad económica con caracteres inválidos.";
    }

    if (empty($errores)) {
        // Verificar duplicado antes de actualizar
        $check = $conexion->prepare("SELECT id FROM empresas WHERE numero_identidad = ? AND id != ?");
        $check->bind_param("si", $numero_identidad, $id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $numero_error = "El número de identidad ya está en uso por otra empresa.";
            $mensaje = "❌ Ya existe otra empresa con ese número de identidad.";
            $check->close();
        } else {
            $check->close();
            $stmt = $conexion->prepare("UPDATE empresas SET tipo_documento=?, numero_identidad=?, nickname=?, telefono=?, correo=?, direccion=?, actividad_economica=? WHERE id=?");
            $stmt->bind_param("sssssssi", $tipo_documento, $numero_identidad, $nickname, $telefono, $correo, $direccion, $actividad_economica, $id);
            if ($stmt->execute()) {
                $mensaje = "✅ Empresa actualizada correctamente.";
            } else {
                $mensaje = "❌ Error al actualizar la empresa.";
            }
            $stmt->close();
        }
    } else {
        $mensaje = "❌ " . implode("<br>❌ ", $errores);
    }
}

// Buscar empresa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar'])) {
    $dato = trim($_POST['dato_busqueda']);
    $sql = "SELECT * FROM empresas WHERE numero_identidad = ? OR nickname = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ss", $dato, $dato);
    $stmt->execute();
    $resultado = $stmt->get_result();
    if ($resultado->num_rows > 0) {
        $empresas = $resultado->fetch_assoc();
    } else {
        $mensaje = "❌ No se encontró ninguna empresa.";
    }
    $stmt->close();
}

// Mostrar todas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mostrar_todos'])) {
    $sql = "SELECT * FROM empresas";
    $resultado = $conexion->query($sql);
    if ($resultado && $resultado->num_rows > 0) {
        while ($fila = $resultado->fetch_assoc()) {
            $todas_empresas[] = $fila;
        }
    } else {
        $mensaje = "❌ No hay empresas registradas.";
    }
}

$conexion->close();
?>




<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Visualizar / Actualizar Empresas</title>
    <link rel="shortcut icon" href="../../img/Logotipo_Datasena.png" type="image/x-icon">
    <link rel="stylesheet" href="../empresa/actualizar_empresa_su_v2.css">
</head>
<body>

<!--barra del gov superior-->
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
  <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>

<h1>DATASENA</h1>
<img src="../../img/logo-sena.png" alt="Logo SENA" class="img">

<div class="form-container">
    <h2>Visualizar / Actualizar Empresas</h2>

    <?php if (!empty($mensaje)): ?>
        <p style="color:<?= str_contains($mensaje, '❌') ? 'red' : 'green' ?>; font-weight:bold;"><?= $mensaje ?></p>
    <?php endif; ?>

    <!-- Buscar y mostrar -->
    <form id="form-busqueda" action="actualizar_empresa_su.php" method="post" style="display:flex; flex-wrap: wrap; gap: 10px; align-items: center;">
        <label for="buscar_dato">Buscar por número de identidad o nickname:</label>
        <input type="text" id="buscar_dato" name="dato_busqueda" placeholder="Ingrese número de identidad o nombre" required>
        <button class="logout-btn" type="submit" name="buscar">🔍 Buscar</button>
        <button class="logout-btn" type="submit" name="mostrar_todos" id="btn-todos">📋 Mostrar Todos</button>
        <button class="logout-btn" onclick="window.location.href='../super_menu.html'">↩️ Regresar</button>
    </form>
    <div style="height: 0.1cm;"></div>
    <hr style="border: 0.01px solid #ccc; width: 100%;">
    <script>
        document.getElementById('btn-todos').addEventListener('click', function () {
            document.getElementById('buscar_dato').removeAttribute('required');
        });
    </script>

    <!-- Formulario de edición -->
    <?php if (!empty($empresas['id'])): ?>
        <form class="form-grid" action="actualizar_empresa_su.php" method="post">
            <input type="hidden" name="id" value="<?= htmlspecialchars($empresas['id']) ?>">

            <div class="form-row">
                <label>Tipo de documento:</label>
                <select name="tipo_documento" required>
                    <option value="">Seleccione</option>
                    <?php
                    $tipos = ['NIT','Registro Mercantil','Registro Cámara de Comercio Extranjera','Pasaporte Empresarial','RUT','Licencia Municipal'];
                    foreach ($tipos as $tipo) {
                        $sel = ($empresas['tipo_documento'] == $tipo) ? 'selected' : '';
                        echo "<option value='$tipo' $sel>$tipo</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-row">
                <label>Número de identidad:</label>
                <input type="text" id="numero_identidad" name="numero_identidad"
                       value="<?= htmlspecialchars($empresas['numero_identidad']) ?>"
                       required pattern="\d{8,12}">
                <output id="numero_output" for="numero_identidad" style="display:block; color:red; font-weight:bold; margin-top:4px;">
                    <?= isset($numero_error) && $numero_error !== "" ? htmlspecialchars($numero_error) : '' ?>
                </output>
            </div>

            <div class="form-row"><label>Nombre de la empresa:</label><input type="text" name="nickname" value="<?= htmlspecialchars($empresas['nickname']) ?>" required></div>
            <div class="form-row"><label>Teléfono:</label><input type="text" name="telefono" value="<?= htmlspecialchars($empresas['telefono']) ?>" pattern="\d{10}" required></div>
            <div class="form-row"><label>Correo:</label><input type="email" name="correo" value="<?= htmlspecialchars($empresas['correo']) ?>" required></div>
            <div class="form-row"><label>Dirección:</label><input type="text" name="direccion" value="<?= htmlspecialchars($empresas['direccion']) ?>" required></div>
            <div class="form-row"><label>Actividad económica:</label><input type="text" name="actividad_economica" value="<?= htmlspecialchars($empresas['actividad_economica']) ?>" required></div>
            <div class="form-row">
                <label>Fecha de Registro:</label>
                <input type="text" value="<?= htmlspecialchars($empresas['fecha_registro']) ?>" readonly>
            </div>

            <div class="form-row botones-finales">
                <button class="logout-btn" type="submit">Actualizar</button>
                <button class="logout-btn" type="button" onclick="window.location.href='../super_menu.html'">Regresar</button>
            </div>
        </form>
    <?php endif; ?>

    <!-- Tabla de todas las empresas -->
    <?php if (!empty($todas_empresas)): ?>
        <h3>📋 Empresas registradas</h3>
        <div style="overflow-x: auto;">
            <table border="1" cellpadding="6" cellspacing="0" style="width:100%; border-collapse: collapse; background: #fff;">
                <thead style="background-color: #f5f5f5ff; color: black;">
                    <tr>
                        <th>Tipo Doc</th>
                        <th>Identidad</th>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Dirección</th>
                        <th>Actividad Económica</th>
                        <th>Estado</th>
                        <th>Fecha Registro</th>
                    </tr>
                </thead>
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
                            <td><?= htmlspecialchars($e['estado']) ?></td>
                            <td><?= htmlspecialchars($e['fecha_registro']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<footer>
    <a>&copy; 2025 Todos los derechos reservados - Proyecto SENA</a>
</footer>

<!--barra del gov inferior-->
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
  <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>

<script>
(function(){
    const numInput = document.getElementById('numero_identidad');
    const output = document.getElementById('numero_output');

    if (numInput && output && output.textContent.trim() !== "") {
        numInput.setCustomValidity(output.textContent.trim());
        if (typeof numInput.reportValidity === 'function') {
            numInput.reportValidity();
        }
    }

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