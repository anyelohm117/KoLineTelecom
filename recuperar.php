<?php
session_start();
require 'db_con.php';

$mensaje = "";
$tipo_mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // 1. Verificar si el correo existe
    $stmt = $conn->prepare("SELECT id_usuario, nombres FROM usuarios WHERE email = ? AND activo = 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $usuario = $res->fetch_assoc();
        
        // 2. Generar Token
        $token = bin2hex(random_bytes(50));
        date_default_timezone_set('America/Mexico_City');
        $expira = date("Y-m-d H:i:s", strtotime('+1 hour'));

        // 3. Guardar en BD
        $update = $conn->prepare("UPDATE usuarios SET reset_token = ?, token_expira = ? WHERE email = ?");
        $update->bind_param("sss", $token, $expira, $email);
        
        if ($update->execute()) {
            $link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/cambiar_password.php?token=" . $token;
            
            // Mensaje estilizado para el diseño nuevo
            $mensaje = "
                <div class='flex flex-col gap-2'>
                    <span class='font-bold text-lg'>¡Enlace Generado!</span>
                    <span class='text-sm opacity-80'>Hola <b>{$usuario['nombres']}</b>, usa este enlace para recuperar tu cuenta:</span>
                    <div class='bg-black/30 p-3 rounded border border-white/10 mt-2 break-all text-xs font-mono text-koline-primary select-all'>
                        $link
                    </div>
                    <span class='text-xs text-center mt-1 text-green-300 animate-pulse'>Copia y pega el enlace en tu navegador</span>
                </div>
            ";
            $tipo_mensaje = "success";
        }
    } else {
        $mensaje = "❌ No encontramos una cuenta activa con ese correo electrónico.";
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
    
    <link rel="icon" type="image/png" href="imagenes/logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'koline-primary': '#00eaff',
                        'koline-dark': '#0a1d37',
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    boxShadow: {
                        'neon': '0 0 10px rgba(0, 234, 255, 0.5), 0 0 20px rgba(0, 234, 255, 0.3)',
                        'card-glow': '0 0 40px rgba(0, 234, 255, 0.15)',
                    },
                    animation: {
                        'pulse-slow': 'pulse 6s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    }
                }
            }
        }
    </script>

    <style>
        .bg-network-pattern {
            background-image: radial-gradient(circle, rgba(0, 234, 255, 0.1) 1px, transparent 1px);
            background-size: 30px 30px;
            opacity: 0.3;
        }
    </style>
</head>

<body class="bg-koline-dark text-white min-h-screen flex justify-center items-center p-4 relative overflow-hidden">

    <div class="absolute inset-0 bg-gradient-to-br from-koline-dark via-[#051021] to-black z-0"></div>
    <div class="absolute inset-0 bg-network-pattern z-0"></div>
    <div class="absolute w-96 h-96 bg-koline-primary rounded-full blur-[120px] opacity-20 animate-pulse-slow top-0 left-1/2 transform -translate-x-1/2 z-0"></div>
    <div class="absolute w-80 h-80 bg-blue-600 rounded-full blur-[100px] opacity-20 animate-pulse-slow bottom-0 right-10 z-0"></div>

    <div class="w-full max-w-md bg-white/10 backdrop-blur-md border border-white/10 rounded-3xl p-8 shadow-card-glow z-10 relative overflow-hidden">
        
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-koline-primary to-transparent opacity-50"></div>

        <div class="text-center mb-6">
            <div class="w-16 h-16 mx-auto mb-4 bg-koline-primary/10 rounded-full flex items-center justify-center border border-koline-primary/30 shadow-neon text-koline-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>

            <h1 class="text-2xl font-bold tracking-wide">Recuperar Acceso</h1>
            <p class="text-gray-400 text-sm mt-2">Introduce tu correo y te ayudaremos a restablecer tu contraseña.</p>
        </div>

        <?php if($mensaje): ?>
            <div class="mb-6 p-4 rounded-xl border flex items-start gap-3 text-sm
                <?php echo ($tipo_mensaje == 'error') ? 'bg-red-500/10 border-red-500/50 text-red-200' : 'bg-green-500/10 border-green-500/50 text-green-100'; ?>">
                
                <div class="mt-0.5 text-lg">
                    <?php echo ($tipo_mensaje == 'error') ? '⚠️' : '✅'; ?>
                </div>
                <div class="w-full">
                    <?= $mensaje ?>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <div class="space-y-1">
                <label class="text-xs text-koline-primary ml-1 font-bold uppercase tracking-wider">Correo Electrónico</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                        </svg>
                    </div>
                    <input type="email" name="email" required placeholder="usuario@koline.com"
                        class="w-full pl-10 p-3 rounded-xl bg-[#0d213f]/80 border border-gray-700/50 focus:border-koline-primary focus:ring-1 focus:ring-koline-primary/50 outline-none transition-all duration-300 placeholder-gray-500 text-white text-sm">
                </div>
            </div>

            <button type="submit"
                class="w-full py-3.5 mt-2 font-bold rounded-xl bg-gradient-to-r from-koline-primary to-cyan-400 text-koline-dark shadow-neon hover:shadow-neon-lg hover:brightness-110 active:scale-[0.98] transition-all duration-300 uppercase tracking-wide text-sm flex justify-center items-center gap-2">
                <span>Enviar Instrucciones</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-white/5 text-center">
            <a href="index.php" class="inline-flex items-center text-xs text-gray-400 hover:text-white transition duration-200 group">
                <span class="w-6 h-6 rounded-full bg-white/5 flex items-center justify-center mr-2 group-hover:bg-koline-primary group-hover:text-koline-dark transition">
                    ←
                </span>
                Volver al inicio de sesión
            </a>
        </div>

    </div>
</body>
</html>
