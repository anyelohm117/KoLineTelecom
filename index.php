<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - KoLine Telecom</title>

    <link rel="icon" type="image/png" href="imagenes/logo.png?v=5">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'koline-primary': '#00eaff', // Cian Neón
                        'koline-dark': '#0a1d37',    // Azul oscuro profundo
                        'koline-card': '#112240',    // Fondo de la tarjeta un poco más claro
                        'koline-error': '#ff3366',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    boxShadow: {
                        'neon': '0 0 10px rgba(0, 234, 255, 0.5), 0 0 20px rgba(0, 234, 255, 0.3)',
                        'neon-lg': '0 0 25px rgba(0, 234, 255, 0.5), 0 0 50px rgba(0, 234, 255, 0.1)',
                        'neon-red': '0 0 15px rgba(255, 51, 102, 0.6)',
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
        /* Patrón de fondo estilo red */
        .bg-network-pattern {
            background-image: radial-gradient(circle, rgba(0, 234, 255, 0.1) 1px, transparent 1px);
            background-size: 30px 30px;
            opacity: 0.3;
        }

        /* Animación personalizada */
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.3; }
            50% { transform: scale(1.1); opacity: 0.5; }
        }

        /* Clase utilitaria para delay de animación que faltaba */
        .animation-delay-2000 {
            animation-delay: 2s;
        }
        
        /* Autofill fix para que no se ponga blanco el input en Chrome */
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus, 
        input:-webkit-autofill:active{
            -webkit-box-shadow: 0 0 0 30px #0d213f inset !important;
            -webkit-text-fill-color: white !important;
            transition: background-color 5000s ease-in-out 0s;
        }
    </style>
</head>

<body class="bg-koline-dark text-white min-h-screen flex justify-center items-center p-4 relative overflow-hidden font-sans">

    <div class="absolute inset-0 bg-gradient-to-br from-koline-dark via-[#051021] to-black z-0"></div>
    <div class="absolute inset-0 bg-network-pattern z-0"></div>
    
    <div class="absolute w-96 h-96 bg-koline-primary rounded-full blur-[120px] opacity-20 animate-pulse-slow top-0 left-1/2 transform -translate-x-1/2 z-0"></div>
    <div class="absolute w-80 h-80 bg-blue-600 rounded-full blur-[100px] opacity-20 animate-pulse-slow animation-delay-2000 bottom-0 right-10 z-0"></div>

    <div class="w-full max-w-sm bg-white/10 backdrop-blur-md border border-white/10 rounded-3xl p-8 shadow-card-glow z-10 relative overflow-hidden">
        
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-koline-primary to-transparent opacity-50"></div>

        <div class="text-center mb-8">
            <div class="relative w-32 h-32 mx-auto mb-4 rounded-full flex items-center justify-center bg-black/40 shadow-neon border border-koline-primary/30">
                <img src="imagenes/logo.png" alt="Logo" class="w-full h-full object-contain rounded-full p-2 opacity-90 hover:opacity-100 transition duration-300">
            </div>

            <h1 class="text-2xl font-bold text-white tracking-wide">
                KoLine <span class="text-koline-primary">Telecom</span>
            </h1>
            <h2 class="text-sm font-medium mt-1 text-gray-400 uppercase tracking-widest">Acceso a la Red</h2>
        </div>

        <?php if(isset($_GET['error'])): ?>
            <div class="mb-6 p-3 bg-red-500/10 border border-red-500/50 rounded-lg text-red-200 text-xs flex items-center justify-center gap-2 animate-pulse">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>
                <?php 
                    if($_GET['error'] == 1) echo "Contraseña incorrecta.";
                    elseif($_GET['error'] == 2) echo "El usuario no existe.";
                    else echo "Error de credenciales.";
                ?>
                </span>
            </div>
        <?php endif; ?>

        <form action="login_logic.php" method="POST" class="space-y-5">
            
            <div class="space-y-1">
                <label class="text-xs text-koline-primary ml-1">Correo electrónico</label>
                <input type="email" name="correo_electronico" required
                    class="w-full p-3 rounded-xl bg-[#0d213f]/80 border border-gray-700/50 focus:border-koline-primary focus:ring-1 focus:ring-koline-primary/50 focus:shadow-neon outline-none transition-all duration-300 placeholder-gray-500 text-white text-sm"
                    placeholder="usuario@koline.com"
                >
            </div>
            
            <div class="space-y-1">
                <label class="text-xs text-koline-primary ml-1">Contraseña</label>
                <input type="password" name="clave" required
                    class="w-full p-3 rounded-xl bg-[#0d213f]/80 border border-gray-700/50 focus:border-koline-primary focus:ring-1 focus:ring-koline-primary/50 focus:shadow-neon outline-none transition-all duration-300 placeholder-gray-500 text-white text-sm"
                    placeholder="••••••••"
                >
            </div>

            <button type="submit"
                class="w-full py-3.5 mt-2 font-bold rounded-xl bg-gradient-to-r from-koline-primary to-cyan-400 text-koline-dark shadow-neon hover:shadow-neon-lg hover:brightness-110 active:scale-[0.98] transition-all duration-300 uppercase tracking-wide text-sm"
            >
                Iniciar sesión
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-white/5">
            <p class="text-xs text-center text-gray-400">
                ¿No tienes cuenta?
                <a href="registro.php" class="font-semibold text-koline-primary hover:text-white transition duration-200 ml-1">
                    Regístrate aquí
                </a>
            </p>
            
            <div class="text-center mt-3">
                <a href="recuperar.php" class="text-xs text-gray-500 hover:text-koline-primary transition duration-200">
                    Olvidé mi contraseña
                </a>
            </div>
        </div>

    </div>
</body>
</html>
