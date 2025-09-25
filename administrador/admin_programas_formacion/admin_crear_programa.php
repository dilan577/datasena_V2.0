<?php
$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conexion = new mysqli("localhost", "root", "", "datasena_db");
    if ($conexion->connect_error) {
        die("❌ Error de conexión: " . $conexion->connect_error);
    }

    $nombre_programa = trim($_POST['nombre_programa'] ?? '');
    $numero_ficha    = trim($_POST['codigo_programa'] ?? '');
    $tipo_programa   = trim($_POST['nivel_formacion'] ?? '');
    $activacion      = trim($_POST['estado'] ?? '');

    // ✅ Validación campos vacíos
    if (empty($nombre_programa) || empty($numero_ficha) || empty($tipo_programa) || empty($activacion)) {
        $mensaje = "❌ Todos los campos son obligatorios.";
    }
    // ✅ Validación: nombre solo letras y espacios
    elseif (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u', $nombre_programa)) {
        $mensaje = "❌ El nombre del programa solo puede contener letras y espacios.";
    }
    // ✅ Validación: código solo números
    elseif (!preg_match('/^[0-9]+$/', $numero_ficha)) {
        $mensaje = "❌ El código del programa solo puede contener números.";
    }
    // ✅ Validación número de ficha mayor que 0
    elseif ((int)$numero_ficha <= 0) {
        $mensaje = "❌ El número de ficha debe ser mayor que 0.";
    }
    else {
        // ✅ Asignar duración en meses
        switch ($tipo_programa) {
            case "Tecnico":   $duracion_programa = 18; break;
            case "Tecnologo": $duracion_programa = 24; break;
            case "Operario":  $duracion_programa = 6;  break;
            default:          $duracion_programa = 0;
        }

        // ✅ Normalizar estado (solo "activo" o "inactivo")
        $activacion = strtolower($activacion) === "activo" ? "activo" : "inactivo";

        // ✅ Verificar duplicado por ficha
        $check_ficha = $conexion->prepare("SELECT id FROM programas WHERE numero_ficha = ?");
        $check_ficha->bind_param("s", $numero_ficha);
        $check_ficha->execute();
        $check_ficha->store_result();

        // ✅ Verificar duplicado por nombre
        $check_nombre = $conexion->prepare("SELECT id FROM programas WHERE nombre_programa = ?");
        $check_nombre->bind_param("s", $nombre_programa);
        $check_nombre->execute();
        $check_nombre->store_result();

        if ($check_ficha->num_rows > 0) {
            $mensaje = "❌ El número de ficha ya está registrado.";
        } elseif ($check_nombre->num_rows > 0) {
            $mensaje = "❌ El nombre del programa ya está registrado.";
        } else {
            // ✅ Insertar programa
            $sql = "INSERT INTO programas (nombre_programa, tipo_programa, numero_ficha, duracion_programa, activacion)
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conexion->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("sssis", $nombre_programa, $tipo_programa, $numero_ficha, $duracion_programa, $activacion);
                if ($stmt->execute()) {
                    $mensaje = "✅ Programa registrado con éxito.";
                } else {
                    $mensaje = "❌ Error al registrar el programa.";
                }
                $stmt->close();
            } else {
                $mensaje = "❌ Error al preparar la consulta.";
            }
        }

        $check_ficha->close();
        $check_nombre->close();
    }

    $conexion->close();
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Crear Programa</title>
  <link rel="shortcut icon" href="../../img/Logotipo_Datasena.png" type="image/x-icon" />
  <link rel="stylesheet" href="../../administrador/admin_programas_formacion/admin_crear_programa.css" />
</head>
<body>

<!--barra del gov superior-->
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
  <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>

<h1>DATASENA</h1>
<img src="../../img/logo-sena.png" alt="Logo SENA" class="img" />

<div class="forma-container">
  <h2>Crear Programa</h2>

  <?php if (!empty($mensaje)): ?>
    <div class="mensaje"><?= htmlspecialchars($mensaje) ?></div>
  <?php endif; ?>

  <form method="post">
    <div class="forma-grid">
      <div class="forma-row">
        <label for="nombre_programa">Nombre del Programa</label>
        <input type="text" id="nombre_programa" name="nombre_programa" placeholder="Ingrese el nombre del programa de formación" required />
      </div>

      <div class="forma-row">
        <label for="codigo_programa">Código del Programa</label>
        <input type="text" id="codigo_programa" name="codigo_programa" placeholder="Ingrese el código del programa" required />
      </div>

      <div class="forma-row">
        <label for="nivel_formacion">Nivel de Formación</label>
        <select id="nivel_formacion" name="nivel_formacion" required>
          <option value="Tecnico">Técnico</option>
          <option value="Tecnologo">Tecnólogo</option>
          <option value="Operario">Operario</option>
        </select>
      </div>

      <div class="forma-row">
        <label for="estado">Estado</label>
        <select id="estado" name="estado" required>
          <option value="activo">Activo</option>
          <option value="inactivo">Inactivo</option>
        </select>
      </div>

      <div class="buttons-container">
        <button type="submit" class="registrar">✅ Crear </button>
        <button type="button" class="registrar" onclick="window.location.href='../admin_menu.html'">↩️ Regresar</button>
      </div>
    </div>
  </form>
</div>

<footer>
  <a>&copy; 2025 Todos los derechos reservados - Proyecto SENA</a>
</footer>

<!--barra del gov inferior-->
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
  <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>
</body>
</html>