<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $conexion = new PDO("mysql:host=localhost;dbname=datasena_db", "root", "");
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $rol = $_POST['rol'] ?? '';
        $usuario = $_POST['usuario'] ?? '';
        $password = $_POST['password'] ?? '';

        if ($rol === 'super') {
            $stmt = $conexion->prepare("SELECT * FROM inicio_super_admin WHERE usuario = :usuario LIMIT 1");
            $stmt->bindParam(':usuario', $usuario);
            $stmt->execute();
            $superadmin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($superadmin && $password === $superadmin['contrasena']) {
                $_SESSION['rol'] = 'super';
                $_SESSION['usuario'] = $usuario;
                header("Location: super-administrador/super_menu.html");
                exit;
            } else {
                echo "<script>alert('❌ Usuario o contraseña incorrectos (superadmin)'); window.history.back();</script>";
                exit;
            }

        } elseif ($rol === 'admin') {
            $stmt = $conexion->prepare("SELECT * FROM admin WHERE nickname = :nickname LIMIT 1");
            $stmt->bindParam(':nickname', $usuario);
            $stmt->execute();
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin && $password === $admin['contrasena']) {
                $_SESSION['rol'] = 'admin';
                $_SESSION['usuario_id'] = $admin['id'];
                $_SESSION['nickname'] = $admin['nickname'];
                header("Location: administrador/admin_menu.html");
                exit;
            } else {
                echo "<script>alert('❌ Usuario o contraseña incorrectos (admin)'); window.history.back();</script>";
                exit;
            }

        } elseif ($rol === 'empresa') {
            $stmt = $conexion->prepare("SELECT * FROM empresas WHERE nickname = :nickname LIMIT 1");
            $stmt->bindParam(':nickname', $usuario);
            $stmt->execute();
            $empresa = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($empresa && $password === $empresa['contrasena']) {
                $_SESSION['rol'] = 'empresa';
                $_SESSION['usuario_id'] = $empresa['id'];
                $_SESSION['nickname'] = $empresa['nickname'];
                header("Location: empresa/empresa_menu.html");
                exit;
            } else {
                echo "<script>alert('❌ Usuario o contraseña incorrectos (empresa)'); window.history.back();</script>";
                exit;
            }

        } else {
            echo "<script>alert('⚠️ Rol no válido'); window.history.back();</script>";
            exit;
        }

    } catch (PDOException $e) {
        echo "❌ Error de conexión: " . $e->getMessage();
        exit;
    }
}
?>
