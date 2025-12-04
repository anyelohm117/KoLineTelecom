<?php
session_start();
require 'db_con.php';

$mensaje = "";
$tipo_mensaje = ""; // success o error

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // 1. Verificar si el correo existe
    $stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE email = ? AND activo = 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        // 2. Generar Token Único y Fecha de Expiración (1 hora)
        $token = bin2hex(random_bytes(50));
        $expira = date("Y-m-d H:i:s", strtotime('+1 hour'));

        // 3. Guardar en la BD
        $update = $conn->prepare("UPDATE usuarios SET reset_token = ?, token_expira = ? WHERE email = ?");
        $update->bind_param("sss", $token, $expira, $email);
        
        if ($update->execute()) {
            // EN UN SISTEMA REAL: Aquí enviarías el email con PHPMailer.
            // MODO PRUEBA: Generamos el link para que lo copies.
            $link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/cambiar_password.php?token=" . $token;
            
            $mensaje = "✅ <b>Enlace generado (Simulación de Email):</b><br><a href='$link' class='underline text-koline-primary'>$link</a>";
            $tipo_mensaje = "success";
        }
    } else {
        $mensaje = "❌ Ese correo no está registrado o el usuario está inactivo.";
        $tipo_mensaje = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - KoLine</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { 'koline-primary': '#00eaff', 'koline-dark': '#0a1d37' },
                    fontFamily: { sans: ['Inter', 'sans-serif'] }
                }
            }
        }
    </script>
</head>

<body class="bg-[#020c1b] text-white min-h-screen flex justify-center items-center p-4 relative overflow-hidden">
    
    <div class="absolute inset-0 bg-gradient-to-br from-[#020c1b] to-black z-0"></div>
    <div class="absolute w-96 h-96 bg-[#00eaff] rounded-full blur-[150px] opacity-10 top-0 left-1/2 -translate-x-1/2 z-0"></div>

    <div class="w-full max-w-md bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-8 shadow-2xl z-10 relative">
        
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold mb-2">Recuperar Acceso</h1>
            <p class="text-gray-400 text-sm">Ingresa tu correo electrónico asociado.</p>
        </div>

        <?php if($mensaje): ?>
            <div class="mb-5 p-3 rounded-lg text-sm border <?php echo ($tipo_mensaje == 'error') ? 'bg-red-500/10 border-red-500/50 text-red-200' : 'bg-green-500/10 border-green-500/50 text-green-200'; ?>">
                <?= $mensaje ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <div>
                <label class="text-xs text-[#00eaff] ml-1 uppercase font-bold">Correo Electrónico</label>
                <input type="email" name="email" required 
                    class="w-full p-3 mt-1 rounded-xl bg-[#0d213f] border border-gray-700 focus:border-[#00eaff] focus:ring-1 focus:ring-[#00eaff] outline-none transition text-white">
            </div>

            <button type="submit" 
                class="w-full py-3 font-bold rounded-xl bg-[#00eaff] text-[#020c1b] hover:bg-cyan-400 transition uppercase tracking-wide">
                Enviar Enlace
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="index.php" class="text-sm text-gray-500 hover:text-[#00eaff] transition">← Volver al Login</a>
        </div>
    </div>
</body>
</html>
