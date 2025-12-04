<?php
session_start();
require 'db_con.php';

$mensaje = "";
$tipo_mensaje = ""; // success o error
$token_valido = false;
$id_usuario = null;

// 1. VALIDAR TOKEN AL ENTRAR
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Ajuste de zona horaria
    date_default_timezone_set('America/Mexico_City'); 
    $ahora = date("Y-m-d H:i:s");

    $stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE reset_token = ? AND token_expira > ?");
    $stmt->bind_param("ss", $token, $ahora);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $token_valido = true;
        $id_usuario = $res->fetch_assoc()['id_usuario'];
    } else {
        $mensaje = "El enlace ha expirado o no es válido.";
        $tipo_mensaje = "error";
    }
} else {
    header("Location: index.php");
    exit();
}

// 2. PROCESAR CAMBIO
if ($_SERVER["REQUEST_METHOD"] == "POST" && $token_valido) {
    $pass1 = $_POST['pass1'];
    $pass2 = $_POST['pass2'];

    if (strlen($pass1) < 6) {
        $mensaje = "La contraseña debe tener al menos 6 caracteres.";
        $tipo_mensaje = "error";
    } elseif ($pass1 === $pass2) {
        $new_hash = password_hash($pass1, PASSWORD_BCRYPT);
        
        $stmt = $conn->prepare("UPDATE usuarios SET password_hash = ?, reset_token = NULL, token_expira = NULL WHERE id_usuario = ?");
        $stmt->bind_param("si", $new_hash, $id_usuario);
        
        if ($stmt->execute()) {
            $mensaje = "¡Contraseña actualizada correctamente!";
            $tipo_mensaje = "success";
            $token_valido = false; // Ocultar formulario
        }
    } else {
        $mensaje = "Las contraseñas no coinciden.";
        $tipo_mensaje = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña - KoLine</title>
    
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
                <?php if ($tipo_mensaje == 'success'): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                <?php else: ?>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                <?php endif; ?>
            </div>

            <h1 class="text-2xl font-bold tracking-wide">
                <?= ($tipo_mensaje == 'success') ? '¡Listo!' : 'Nueva Contraseña' ?>
            </h1>
            
            <?php if (!$mensaje): ?>
                <p class="text-gray-400 text-sm mt-2">Ingresa tu nueva clave de acceso.</p>
            <?php endif; ?>
        </div>

        <?php if($mensaje): ?>
            <div class="mb-6 p-4 rounded-xl border flex items-center gap-3 text-sm font-medium
                <?php echo ($tipo_mensaje == 'error') ? 'bg-red-500/10 border-red-500/50 text-red-200' : 'bg-green-500/10 border-green-500/50 text-green-100'; ?>">
                <?= ($tipo_mensaje == 'error') ? '❌' : '✅'; ?>
                <?= $mensaje ?>
            </div>
        <?php endif; ?>

        <?php if ($token_valido): ?>
        <form method="POST" class="space-y-5">
            
            <div class="space-y-1">
                <label class="text-xs text-koline-primary ml-1 font-bold uppercase tracking-wider">Nueva Contraseña</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <input type="password" name="pass1" required placeholder="Mínimo 6 caracteres"
                        class="w-full pl-10 p-3 rounded-xl bg-[#0d213f]/80 border border-gray-700/50 focus:border-koline-primary focus:ring-1 focus:ring-koline-primary/50 outline-none transition-all duration-300 placeholder-gray-500 text-white text-sm">
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-xs text-koline-primary ml-1 font-bold uppercase tracking-wider">Confirmar Contraseña</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <input type="password" name="pass2" required placeholder="Repite la contraseña"
                        class="w-full pl-10 p-3 rounded-xl bg-[#0d213f]/80 border border-gray-700/50 focus:border-koline-primary focus:ring-1 focus:ring-koline-primary/50 outline-none transition-all duration-300 placeholder-gray-500 text-white text-sm">
                </div>
            </div>

            <button type="submit"
                class="w-full py-3.5 mt-2 font-bold rounded-xl bg-gradient-to-r from-koline-primary to-cyan-400 text-koline-dark shadow-neon hover:shadow-neon-lg hover:brightness-110 active:scale-[0.98] transition-all duration-300 uppercase tracking-wide text-sm flex justify-center items-center gap-2">
                <span>Guardar Cambios</span>
            </button>
        </form>
        <?php endif; ?>

        <?php if (!$token_valido): ?>
            <div class="mt-4 text-center">
                <a href="index.php" class="block w-full py-3 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl text-sm font-medium transition duration-200">
                    ← Volver al Inicio de Sesión
                </a>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>
