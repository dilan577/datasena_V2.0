<?php
$conexion = new mysqli("localhost", "root", "123456", "datasena_db");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$admin = [
    'id' => '',
    'tipo_documento' => '',
    'numero_documento' => '',
    'nombres' => '',
    'apellidos' => '',
    'nickname' => '',
    'correo_electronico' => '',
    'contrasena' => '',
    'rol_id' => '',
    'estado_habilitacion' => '',
    'fecha_creacion' => ''
];
$todos_admins = [];
$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && !empty($_POST['id'])) {
    $id = $_POST['id'];
    $tipo_documento     = $_POST['tipo_documento']      ?? null;
    $numero_documento   = $_POST['numero_documento']    ?? null;
    $nombres            = $_POST['nombres']             ?? null;
    $apellidos          = $_POST['apellidos']           ?? null;
    $nickname           = $_POST['nickname']            ?? null;
    $correo_electronico = $_POST['correo_electronico']  ?? null;
    $contrasena         = $_POST['contrasena']          ?? null;
    $rol_id             = $_POST['rol_id']              ?? null;

    $campos = [];
    $tipos = "";
    $parametros = [];

    if ($tipo_documento !== null) {
        $campos[] = "tipo_documento=?";
        $tipos .= "s";
        $parametros[] = $tipo_documento;
    }
    if ($numero_documento !== null) {
        $campos[] = "numero_documento=?";
        $tipos .= "s";
        $parametros[] = $numero_documento;
    }
    if ($nombres !== null) {
        $campos[] = "nombres=?";
        $tipos .= "s";
        $parametros[] = $nombres;
    }
    if ($apellidos !== null) {
        $campos[] = "apellidos=?";
        $tipos .= "s";
        $parametros[] = $apellidos;
    }
    if ($nickname !== null) {
        $campos[] = "nickname=?";
        $tipos .= "s";
        $parametros[] = $nickname;
    }
    if ($correo_electronico !== null) {
        $campos[] = "correo_electronico=?";
        $tipos .= "s";
        $parametros[] = $correo_electronico;
    }
    if (!empty($contrasena)) {
        $campos[] = "contrasena=?";
        $tipos .= "s";
        $parametros[] = password_hash($contrasena, PASSWORD_DEFAULT);
    }

    if (!empty($campos)) {
        $tipos .= "i";
        $parametros[] = $id;

        $sql = "UPDATE admin SET " . implode(", ", $campos) . " WHERE id=?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param($tipos, ...$parametros);

        $mensaje = $stmt->execute() ? "✅ Administrador actualizado correctamente." : "❌ Error al actualizar el administrador.";
        $stmt->close();
    } else {
        $mensaje = "⚠️ No se enviaron datos para actualizar.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar'])) {
    $numero_documento = trim($_POST['numero_documento']);
    $stmt = $conexion->prepare("SELECT * FROM admin WHERE numero_documento = ?");
    $stmt->bind_param("s", $numero_documento);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $admin = $resultado->num_rows > 0 ? $resultado->fetch_assoc() : [];
    if (empty($admin)) $mensaje = "❌ No se encontró ningún administrador.";
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mostrar_todos'])) {
    $sql = "SELECT * FROM admin";
    $resultado = $conexion->query($sql);
    if ($resultado && $resultado->num_rows > 0) {
        while ($fila = $resultado->fetch_assoc()) {
            $todos_admins[] = $fila;
        }
    } else {
        $mensaje = "❌ No hay administradores registrados.";
    }
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Visualizar / Actualizar Administrador</title>
    <link rel="stylesheet" href="../../super-administrador/administrador/actualizar_admin_su_v2.css">
    <link rel="shortcut icon" href="../../img/Logotipo_Datasena.png" type="image/x-icon">
</head>
<body>
<div class="barra-gov">
    <img src="../../img/gov.png" alt="Gobierno de Colombia" class="gov-logo">
</div>

<h1>DATASENA</h1>
<img src="../../img/logo-sena.png" alt="Logo SENA" class="img">

<div class="form-container">
    <h2>Visualizar / Actualizar Administrador</h2>

    <?php if (!empty($mensaje)): ?>
        <p style="color:<?= str_contains($mensaje, '❌') ? 'red' : 'green' ?>; font-weight:bold;"><?= $mensaje ?></p>
    <?php endif; ?>

    <form action="actualizar_admin_su.php" method="post" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
        <label for="numero_documento">Buscar por Documento:</label>
        <input type="text" name="numero_documento" id="numero_documento" required>
        <button class="logout-btn" type="submit" name="buscar">Buscar</button>
        <button class="logout-btn" type="submit" name="mostrar_todos" id="btn-todos">Mostrar Todos</button>
        <button class="logout-btn" onclick="window.location.href='../super_menu.html'">↩️ Regresar</button>
    </form>

    <script>
        document.getElementById('btn-todos').addEventListener('click', function () {
            document.getElementById('numero_documento').removeAttribute('required');
        });
    </script>

    <hr>

    <?php if (!empty($admin['id'])): ?>
        <form class="form-grid" action="actualizar_admin_su.php" method="post">
            <input type="hidden" name="id" value="<?= htmlspecialchars($admin['id']) ?>">

            <div class="form-row">
                <label>Tipo de documento:</label>
                <select name="tipo_documento" required>
                    <option value="">Seleccione</option>
                    <option value="CC" <?= $admin['tipo_documento'] == 'CC' ? 'selected' : '' ?>>Cédula</option>
                    <option value="TI" <?= $admin['tipo_documento'] == 'TI' ? 'selected' : '' ?>>Tarjeta</option>
                    <option value="CE" <?= $admin['tipo_documento'] == 'CE' ? 'selected' : '' ?>>Cédula extranjera</option>
                    <option value="Otro" <?= $admin['tipo_documento'] == 'Otro' ? 'selected' : '' ?>>Otro</option>
                </select>
            </div>

            <div class="form-row"><label>Documento:</label>
                <input type="text" name="numero_documento" pattern="[0-9]+" title="Solo números" value="<?= htmlspecialchars($admin['numero_documento']) ?>" required>
            </div>

            <div class="form-row"><label>Nombres:</label>
                <input type="text" name="nombres" pattern="[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+" title="Solo letras" value="<?= htmlspecialchars($admin['nombres']) ?>" required>
            </div>

            <div class="form-row"><label>Apellidos:</label>
                <input type="text" name="apellidos" pattern="[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+" title="Solo letras" value="<?= htmlspecialchars($admin['apellidos']) ?>" required>
            </div>

            <div class="form-row"><label>Nickname:</label>
                <input type="text" name="nickname" value="<?= htmlspecialchars($admin['nickname']) ?>" required>
            </div>

            <div class="form-row"><label>Correo:</label>
                <input type="email" name="correo_electronico" value="<?= htmlspecialchars($admin['correo_electronico']) ?>" required>
            </div>

            <div class="form-row">
                <label>Contraseña:</label>
                <input type="password" name="contrasena" placeholder="Ingrese nueva contraseña" class="input-estandar">
            </div>
            
            <div class="form-row">
                <label>Rol:</label>
                <input type="text" value="<?= $admin['rol_id'] == 1 ? 'Administrador' : 'Usuario' ?>" readonly>
                <input type="hidden" name="rol_id" value="<?= $admin['rol_id'] ?>">
            </div>

            <div class="form-row"><label>Fecha de Creación:</label>
                <input type="text" value="<?= htmlspecialchars($admin['fecha_creacion']) ?>" readonly>
            </div>

            <div class="form-row botones-finales">
                <button class="logout-btn" type="submit">Actualizar</button>
            </div>
        </form>
    <?php endif; ?>

    <?php if (!empty($todos_admins)): ?>
        <h3>📋 Administradores registrados</h3>
        <div style="overflow-x:auto;">
            <table border="1" cellpadding="6" cellspacing="0" style="width:100%; border-collapse:collapse; background:#fff;">
                <thead style="background-color:#0078c0; color:white;">
                    <tr>
                        <th>ID</th>
                        <th>Tipo Doc</th>
                        <th>Documento</th>
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th>Nickname</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Habilitación</th>
                        <th>Creación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($todos_admins as $a): ?>
                        <tr>
                            <td><?= htmlspecialchars($a['id']) ?></td>
                            <td><?= htmlspecialchars($a['tipo_documento']) ?></td>
                            <td><?= htmlspecialchars($a['numero_documento']) ?></td>
                            <td><?= htmlspecialchars($a['nombres']) ?></td>
                            <td><?= htmlspecialchars($a['apellidos']) ?></td>
                            <td><?= htmlspecialchars($a['nickname']) ?></td>
                            <td><?= htmlspecialchars($a['correo_electronico']) ?></td>
                            <td><?= htmlspecialchars($a['rol_id']) ?></td>
                            <td><?= htmlspecialchars($a['estado_habilitacion']) ?></td>
                            <td><?= htmlspecialchars($a['fecha_creacion']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<footer>
    <a>&copy; Todos los derechos reservados al SENA</a>
</footer>

<div class="barra-gov">
    <img src="../../img/gov.png" alt="Gobierno de Colombia" class="gov-logo">
</div>

<script>
document.querySelector("form.form-grid")?.addEventListener("submit", function(e) {
    const letras = /^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+$/;
    const nombres = document.querySelector("[name='nombres']");
    const apellidos = document.querySelector("[name='apellidos']");

    if (!letras.test(nombres.value) || !letras.test(apellidos.value)) {
        alert("Nombres y apellidos solo deben contener letras.");
        e.preventDefault();
    }
});
</script>
</body>
</html>
