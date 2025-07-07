    <?php
    // Conexión a la base de datos
    $conexion = new mysqli("localhost", "root", "123456", "datasenn_db");

    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }

    // Recolectar los datos del formulario
    $tipo_programa = $_POST['tipo_programa'] ?? '';
    $nombre_programa = $_POST['nombre_programa'] ?? '';
    $duracion_programa = $_POST['duracion_programa'] ?? '';
    $estado = $_POST['activacion'] ?? '';

    // Validación básica
    if (empty($tipo_programa) || empty($nombre_programa) || empty($duracion_programa) || empty($estado)) {
        echo "<script>
            alert('❌ Todos los campos son obligatorios.');
            window.history.back();
        </script>";
        exit;
    }

    // Verificar si ya existe un programa con ese nombre
    $consulta = $conexion->prepare("SELECT id FROM programa_formacion WHERE nombre_programa = ?");
    $consulta->bind_param("s", $nombre_programa);
    $consulta->execute();
    $consulta->store_result();

    if ($consulta->num_rows > 0) {
        echo "<script>
            alert('⚠️ El programa ya existe. Por favor, ingresa uno diferente.');
            window.history.back();
        </script>";
        $consulta->close();
        $conexion->close();
        exit;
    }
    $consulta->close();

    // Insertar nuevo programa
    $sql = "INSERT INTO programa_formacion (tipo_programa, nombre_programa, duracion_programa, estado)
            VALUES (?, ?, ?, ?)";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssss", $tipo_programa, $nombre_programa, $duracion_programa, $estado);

    if ($stmt->execute()) { 
        echo "<script>
            alert('✅ Programa creado con éxito.');
            window.location.href = 'http://localhost/datasenn_proyecto/SU_admin/Crud%20programas/menu_programas.html';
        </script>";
    } else {
        echo "<script>
            alert('❌ Error al crear el programa: " . addslashes($stmt->error) . "');
            window.history.back();
        </script>";
    }

    $stmt->close();
    $conexion->close();
    ?>
