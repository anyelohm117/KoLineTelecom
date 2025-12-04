<?php
// --- MODO DEBUG ACTIVADO ---
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Verificamos si el archivo de conexión existe y lo cargamos
if (!file_exists('db_con.php')) {
    die("<h1 style='color:red'>❌ ERROR FATAL: No encuentro el archivo db_con.php</h1><p>Asegúrate de que recuperar.php y db_con.php estén en la misma carpeta.</p>");
}
require 'db_con.php';

// Verificamos la conexión
if ($conn->connect_error) {
    die("<h1 style='color:red'>❌ Error de Conexión BD: " . $conn->connect_error . "</h1>");
}

$mensaje = "";
$tipo_mensaje = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // 1. Diagnóstico: Verificar si las columnas existen en la BD
    $check_cols = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'reset_token'");
    if ($check_cols->num_rows == 0) {
        die("<div style='background:red; color:white; padding:20px;'>❌ ERROR DE BASE DE DATOS: <br>Faltan las columnas 'reset_token' y 'token_expira'. <br>Por favor ejecuta el comando SQL proporcionado en el chat.</div>");
    }

    // 2. Verificar si el correo existe
    $stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE email = ? AND activo = 1");
    if (!$stmt) { die("Error en consulta SELECT: " . $conn->error); }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $token = bin2hex(random_bytes(50));
        // Ajuste de zona horaria para evitar que expire inmediatamente
        date_default_timezone_set('America/Mexico_City'); 
        $expira = date("Y-m-d H:i:s", strtotime('+1 hour'));

        $update = $conn->prepare("UPDATE usuarios SET reset_token = ?, token_expira = ? WHERE email = ?");
        if (!$update) { die("Error en consulta UPDATE: " . $conn->error); }

        $update->bind_param("sss", $token, $expira, $email);
        
        if ($update->execute()) {
            $link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/cambiar_password.php?token=" . $token;
            
            $mensaje = "✅ <b>¡ÉXITO! Copia este enlace:</b><br><br><a href='$link' style='color:#00eaff; font-size:18px;'>$link</a>";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "❌ Error al guardar en BD: " . $update->error;
            $tipo_mensaje = "error";
        }
    } else {
        $mensaje = "❌ El correo <b>$email</b> no existe o el usuario está inactivo.";
        $tipo_mensaje = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña (Debug)</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white min-h-screen flex flex-col justify-center items-center p-4">
    
    <div class="w-full max-w-md bg-gray-800 p-8 rounded-xl shadow-lg border border-gray-700">
        <h1 class="text-2xl font-bold mb-4 text-center text-cyan-400">Prueba de Recuperación</h1>
        
        <?php if($mensaje): ?>
            <div class="mb-5 p-4 rounded border text-sm break-all <?php echo ($tipo_mensaje == 'error') ? 'bg-red-900 border-red-500 text-red-200' : 'bg-green-900 border-green-500 text-green-200'; ?>">
                <?= $mensaje ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <input type="email" name="email" required placeholder="Ingresa el correo" class="w-full p-3 bg-gray-900 border border-gray-600 rounded text-white">
            <button type="submit" class="w-full p-3 bg-cyan-500 hover:bg-cyan-600 text-black font-bold rounded">PROBAR AHORA</button>
        </form>
        <a href="index.php" class="block text-center mt-4 text-gray-400">Volver</a>
    </div>

</body>
</html>
