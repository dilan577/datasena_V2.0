<?php
$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conexion = new mysqli("localhost", "root", " ", "datasena_db");
    if ($conexion->connect_error) {
        die("❌ Error de conexión: " . $conexion->connect_error);
    }

    $nombre_programa     = trim($_POST['nombre_programa'] ?? '');
    $numero_ficha        = trim($_POST['codigo_programa'] ?? '');
    $tipo_programa       = trim($_POST['nivel_formacion'] ?? '');
    $activacion          = trim($_POST['estado'] ?? '');

    if (empty($nombre_programa) || empty($numero_ficha) || empty($tipo_programa) || empty($activacion)) {
        $mensaje = "❌ Todos los campos son obligatorios.";
    } else {
        // Asignar duración según nivel
        switch ($tipo_programa) {
            case "Tecnico":
                $duracion_programa = "1.5 años";
                break;
            case "Tecnologo":
                $duracion_programa = "2 años";
                break;
            case "Operario":
                $duracion_programa = "6 meses";
                break;
            default:
                $duracion_programa = "No especificada";
        }

        // Validar duplicado
        $verificar_sql = "SELECT id FROM programas WHERE numero_ficha = ?";
        $verificar_stmt = $conexion->prepare($verificar_sql);
        $verificar_stmt->bind_param("s", $numero_ficha);
        $verificar_stmt->execute();
        $verificar_stmt->store_result();

        if ($verificar_stmt->num_rows > 0) {
            $mensaje = "❌ El número de ficha ya está registrado.";
        } else {
            $sql = "INSERT INTO programas (nombre_programa, tipo_programa, numero_ficha, duracion_programa, activacion)
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conexion->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("sssss", $nombre_programa, $tipo_programa, $numero_ficha, $duracion_programa, $activacion);
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
        $verificar_stmt->close();
    }

    $conexion->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Crear Programa</title>
  <link rel="shortcut icon" href="../../img/Logotipo_Datasena.png" type="image/x-icon">
  <link rel="stylesheet" href="../programas_formacion/crear_programa.css">
</head>
<body>
<div class="barra-gov">
  <img src="../../img/gov.png" alt="Gobierno de Colombia" class="gov-logo">
</div>

<h1>DATASENA</h1>
<img src="../../img/logo-sena.png" alt="Logo SENA" class="img">

<div class="forma-container">
  <h4>Crear Programa</h4>

  <?php if (!empty($mensaje)): ?>
    <div class="mensaje"><?= htmlspecialchars($mensaje) ?></div>
  <?php endif; ?>

  <form method="post">
    <div class="forma-grid">
      <div class="forma-row">
        <label for="nombre_programa">Nombre del Programa</label>
        <input type="text" id="nombre_programa" name="nombre_programa" placeholder="Ingrese el nombre del programa de formación" required>
      </div>

      <div class="forma-row">
        <label for="codigo_programa">Código delcPrograma</label>
        <input type="text" id="codigo_programa" name="codigo_programa" placeholder="Ingrese el código del programa" required>
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
        <button type="submit" class="registrar">✅ Crear</button>
        <button type="button" class="registrar" onclick="window.location.href='../super_menu.html'"> 
          ↩️ Regresar</button>
      </div>
    </div>
  </form>
</div>

<footer>
  <a>&copy; 2025 Todos los derechos reservados - Proyecto SENA</a>
</footer>

<div class="barra-gov">
  <img src="../../img/gov.png" alt="Gobierno de Colombia" class="gov-logo">
</div>
</body>
</html>
