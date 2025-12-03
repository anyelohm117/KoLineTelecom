<?php
// registro_logic.php
session_start();

// 1. Usamos el archivo de conexión centralizado (No repetir credenciales)
require 'db_con.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Recibir datos
    $nombre    = $_POST['nombre'];
    $ap_pat    = $_POST['apellido_paterno'];
    $ap_mat    = $_POST['apellido_materno'];
    $username  = $_POST['username'];
    $email     = $_POST['email'];
    $clave     = $_POST['clave'];
    $confirmar = $_POST['confirmar_clave'];
    
    // --- SEGURIDAD: FORZAR ROL DE CLIENTE ---
    // Ignoramos lo que envíe el formulario y asignamos 2 (Cliente)
    // Si quieres crear un admin, hazlo directamente en la BD
    $rol = 2; 

    // 2. Validaciones básicas
    if ($clave !== $confirmar) {
        header("Location: registro.php?error=Las contraseñas no coinciden");
        exit();
    }

    // 3. Verificar duplicados (Usando Prepared Statements)
    $stmt_check = $conn->prepare("SELECT id_usuario FROM usuarios WHERE email = ? OR username = ?");
    $stmt_check->bind_param("ss", $email, $username);
    $stmt_check->execute();
    $stmt_check->store_result();
    
    if ($stmt_check->num_rows > 0) {
        header("Location: registro.php?error=El correo o el usuario ya existen");
        exit();
    }
    $stmt_check->close();

    // 4. Encriptar contraseña
    $clave_hash = password_hash($clave, PASSWORD_BCRYPT);

    // 5. Insertar usuario (Sentencia Preparada - Nivel CIS de seguridad)
    $sql = "INSERT INTO usuarios (username, email, password_hash, nombres, apellido_paterno, apellido_materno, id_rol, activo) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 1)";
            
    $stmt = $conn->prepare($sql);
    // "ssssssi" significa: String, String, String, String, String, String, Integer
    $stmt->bind_param("ssssssi", $username, $email, $clave_hash, $nombre, $ap_pat, $ap_mat, $rol);

    if ($stmt->execute()) {
        // Éxito
        echo "<script>
                alert('¡Cuenta creada exitosamente! Bienvenido a KoLine Telecom.');
                window.location.href = 'index.php'; // Redirigir al login (que ahora sabemos que es index o login.php)
              </script>";
    } else {
        header("Location: registro.php?error=Error del sistema al registrar");
    }
    
    $stmt->close();
} else {
    header("Location: registro.php");
}
$conn->close();
?>
