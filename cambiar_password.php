<?php
session_start();
require 'db_con.php';

$mensaje = "";
$token_valido = false;
$id_usuario = null;

// 1. VALIDAR TOKEN AL ENTRAR (Capturamos lo que viene en el enlace verde)
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Ajuste de zona horaria (importante para que no expire antes de tiempo)
    date_default_timezone_set('America/Mexico_City'); 
    $ahora = date("Y-m-d H:i:s");

    // Buscamos un usuario que tenga ese token y que NO haya expirado
    $stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE reset_token = ? AND token_expira > ?");
    $stmt->bind_param("ss", $token, $ahora);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        // ¡Token encontrado y válido!
        $token_valido = true;
        $id_usuario = $res->fetch_assoc()['id_usuario'];
    } else {
        $mensaje = "<div class='text-red-400 bg-red-900/20 p-4 rounded-xl border border-red-500/50 text-center'>
                        ⚠️ <b>Enlace no válido</b><br>
                        El token ha expirado o ya fue utilizado. <a href='recuperar.php' class='underline'>Solicita uno nuevo aquí</a>.
                    </div>";
    }
} else {
    // Si alguien intenta entrar directo sin token, lo mandamos al inicio
    header("Location: index.php");
    exit();
}

// 2. PROCESAR EL CAMBIO CUANDO LE DAN A "GUARDAR"
if ($_SERVER["REQUEST_METHOD"] == "POST" && $token_valido) {
    $pass1 = $_POST['pass1'];
    $pass2 = $_POST['pass2'];

    if ($pass1 === $pass2) {
        // Encriptamos la nueva clave
        $new_hash = password_hash($pass1, PASSWORD_BCRYPT);
        
        // Actualizamos contraseña y BORRAMOS el token (para que no se pueda reusar)
        $stmt = $conn->prepare("UPDATE usuarios SET password_hash = ?, reset_token = NULL, token_expira = NULL WHERE id_usuario = ?");
        $stmt->bind_param("si", $new_hash, $id_usuario);
        
        if ($stmt->execute()) {
            $mensaje = "<div class='text-green-400 bg-green-900/20 p-4 rounded-xl border border-green-500/50 text-center'>
                            ✅ <b>¡Contraseña Restablecida!</b><br>
                            Ya puedes <a href='index.php' class='font-bold underline text-white'>Iniciar Sesión</a> con tu nueva clave.
                        </div>";
            $token_valido = false; // Ocultamos el formulario
        }
    } else {
        $mensaje = "<div class='text-red-400 bg-red-900/20 p-3 rounded border border-red-500/50'>❌ Las contraseñas no coinciden.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña - KoLine</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-[#020c1b] text-white min-h-screen flex justify-center items-center p-4 relative">

    <div class="absolute inset-0 bg-gradient-to-br from-[#020c1b] to-black z-0"></div>

    <div class="w-full max-w-md bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-8 shadow-2xl z-10 relative">
        
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold mb-2 text-[#00eaff]">Nueva Contraseña</h1>
            <p class="text-gray-400 text-sm">Ingresa tu nueva clave de acceso.</p>
        </div>

        <?= $mensaje ?>

        <?php if ($token_valido): ?>
        <form method="POST" class="space-y-6 mt-4">
            <div>
                <label class="text-xs text-[#00eaff] ml-1 uppercase font-bold tracking-wider">Nueva Contraseña</label>
                <input type="password" name="pass1" required placeholder="Mínimo 6 caracteres"
                    class="w-full p-3 mt-1 rounded-xl bg-[#0d213f] border border-gray-700 focus:border-[#00eaff] focus:ring-1 focus:ring-[#00eaff] outline-none text-white transition placeholder-gray-600">
            </div>

            <div>
                <label class="text-xs text-[#00eaff] ml-1 uppercase font-bold tracking-wider">Confirmar Contraseña</label>
                <input type="password" name="pass2" required placeholder="Repite la contraseña"
                    class="w-full p-3 mt-1 rounded-xl bg-[#0d213f] border border-gray-700 focus:border-[#00eaff] focus:ring-1 focus:ring-[#00eaff] outline-none text-white transition placeholder-gray-600">
            </div>

            <button type="submit" 
                class="w-full py-3.5 font-bold rounded-xl bg-gradient-to-r from-[#00eaff] to-cyan-500 text-[#020c1b] hover:shadow-[0_0_20px_rgba(0,234,255,0.4)] transition transform hover:scale-[1.02] uppercase tracking-wide">
                Guardar Cambios
            </button>
        </form>
        <?php endif; ?>

        <?php if (!$token_valido): ?>
            <div class="mt-8 text-center border-t border-white/10 pt-4">
                <a href="index.php" class="text-sm text-gray-400 hover:text-white transition flex items-center justify-center gap-2">
                    <span>←</span> Volver al Login
                </a>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>
