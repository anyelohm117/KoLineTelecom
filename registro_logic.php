<?php
// Configuración de base de datos
$host = "127.0.0.1";
$user = "root";
$pass = "";
$db   = "wisp_db";

// Crear conexión
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Error de conexión: " . $conn->connect_error);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Limpieza de datos (Seguridad básica)
    $nombre    = $conn->real_escape_string($_POST['nombre']);
    $ap_pat    = $conn->real_escape_string($_POST['apellido_paterno']);
    $ap_mat    = $conn->real_escape_string($_POST['apellido_materno']);
    $username  = $conn->real_escape_string($_POST['username']);
    $email     = $conn->real_escape_string($_POST['email']);
    $rol       = (int)$_POST['rol']; // Convertir a entero por seguridad
    $clave     = $_POST['clave'];
    $confirmar = $_POST['confirmar_clave'];

    // 2. Validación de contraseñas
    if ($clave !== $confirmar) {
        header("Location: registro.php?error=Las contraseñas no coinciden");
        exit();
    }

    // 3. Verificar duplicados (Email o Usuario ya existen)
    $check = "SELECT id_usuario FROM usuarios WHERE email = '$email' OR username = '$username'";
    $result = $conn->query($check);
    
    if ($result->num_rows > 0) {
        header("Location: registro.php?error=El correo o el usuario ya están registrados");
        exit();
    }

    // 4. Encriptar contraseña (Nunca guardar texto plano)
    $clave_hash = password_hash($clave, PASSWORD_BCRYPT);

    // 5. Insertar en Base de Datos
    // Nota: 'activo' = 1 para que puedan entrar de inmediato.
    // Si quisieras que un admin apruebe primero, pon activo = 0.
    $sql = "INSERT INTO usuarios (username, email, password_hash, nombres, apellido_paterno, apellido_materno, id_rol, activo) 
            VALUES ('$username', '$email', '$clave_hash', '$nombre', '$ap_pat', '$ap_mat', $rol, 1)";

    if ($conn->query($sql) === TRUE) {
        // Éxito: Usamos JS para avisar y redirigir al Login
        echo "<script>
                alert('¡Cuenta creada exitosamente! Por favor inicia sesión.');
                window.location.href = 'index.php';
              </script>";
    } else {
        // Error de BD
        header("Location: registro.php?error=Error del sistema: " . $conn->error);
    }
}
$conn->close();
?>
