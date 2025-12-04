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
                        'koline-primary': '#00eaff',
                        'koline-dark': '#0a1d37',
                        'koline-card': '#112240',
                        'koline-error': '#ff3366',
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    boxShadow: {
                        'neon': '0 0 15px rgba(0, 234, 255, 0.4), 0 0 30px rgba(0, 234, 255, 0.2)', // Más brillo
                        'neon-lg': '0 0 30px rgba(0, 234, 255, 0.6), 0 0 60px rgba(0, 234, 255, 0.2)',
                        'card-glow': '0 0 50px rgba(0, 234, 255, 0.12)',
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
            background-size: 35px 35px; /* Patrón un poco más grande */
            opacity: 0.35;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.3; }
            50% { transform: scale(1.1); opacity: 0.5; }
        }
        .animation-delay-2000 { animation-delay: 2s; }
        
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
    
    <div class="absolute w-[500px] h-[500px] bg-koline-primary rounded-full blur-[140px] opacity-20 animate-pulse-slow top-0 left-1/2 transform -translate-x-1/2 z-0"></div>
    <div class="absolute w-[400px] h-[400px] bg-blue-600 rounded-full blur-[120px] opacity-20 animate-pulse-slow animation-delay-2000 bottom-0 right-10 z-0"></div>

    <div class="w-full max-w-md bg-white/10 backdrop-blur-xl border border-white/10 rounded-[2rem] p-10 shadow-card-glow z-10 relative overflow-hidden">
        
        <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-transparent via-koline-primary to-transparent opacity-60"></div>

        <div class="text-center mb-10">
            <div class="relative w-36 h-36 mx-auto mb-5 rounded-full flex items-center justify-center bg-black/40 shadow-neon border border-koline-primary/40 group transition-all duration-500 hover:scale-105">
                <img src="imagenes/logo.png" alt="Logo" class="w-full h-full object-contain rounded-full p-3 opacity-90 group-hover:opacity-100 transition duration-300">
            </div>

            <h1 class="text-3xl font-bold text-white tracking-wide">
                KoLine <span class="text-koline-primary drop-shadow-[0_0_10px_rgba(0,234,255,0.5)]">Telecom</span>
            </h1>
            <h2 class="text-sm font-semibold mt-2 text-gray-400 uppercase tracking-[0.2em]">Acceso a la Red</h2>
        </div>

        <?php if(isset($_GET['error'])): ?>
            <div class="mb-8 p-4 bg-red-500/10 border border-red-500/50 rounded-xl text-red-200 text-sm flex items-center justify-center gap-3 animate-pulse">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-medium">
                <?php 
                    if($_GET['error'] == 1) echo "La contraseña es incorrecta.";
                    elseif($_GET['error'] == 2) echo "El usuario no está registrado.";
                    elseif($_GET['error'] == 'rol_desconocido') echo "Error de rol de usuario.";
                    else echo "Error de credenciales.";
                ?>
                </span>
            </div>
        <?php endif; ?>

        <form action="login_logic.php" method="POST" class="space-y-6">
            
            <div class="space-y-2">
                <label class="text-xs font-bold text-koline-primary ml-1 uppercase tracking-wider">Correo electrónico</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-koline-primary transition-colors duration-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                        </svg>
                    </div>
                    <input type="email" name="correo_electronico" required
                        class="w-full pl-11 py-4 rounded-xl bg-[#0d213f]/80 border border-gray-700/50 focus:border-koline-primary focus:ring-1 focus:ring-koline-primary/50 focus:shadow-neon outline-none transition-all duration-300 placeholder-gray-500 text-white text-base"
                        placeholder="usuario@koline.com"
                    >
                </div>
            </div>
            
            <div class="space-y-2">
                <label class="text-xs font-bold text-koline-primary ml-1 uppercase tracking-wider">Contraseña</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-koline-primary transition-colors duration-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input type="password" name="clave" required
                        class="w-full pl-11 py-4 rounded-xl bg-[#0d213f]/80 border border-gray-700/50 focus:border-koline-primary focus:ring-1 focus:ring-koline-primary/50 focus:shadow-neon outline-none transition-all duration-300 placeholder-gray-500 text-white text-base tracking-widest"
                        placeholder="••••••••"
                    >
                </div>
            </div>

            <button type="submit"
                class="w-full py-4 mt-4 font-bold text-base rounded-xl bg-gradient-to-r from-koline-primary to-cyan-400 text-koline-dark shadow-neon hover:shadow-neon-lg hover:translate-y-[-2px] active:translate-y-[1px] transition-all duration-300 uppercase tracking-wide flex justify-center items-center gap-2 group"
            >
                <span>Iniciar sesión</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
        </form>

        <div class="mt-10 pt-6 border-t border-white/10 text-center space-y-4">
            <p class="text-sm text-gray-400">
                ¿No tienes cuenta?
                <a href="registro.php" class="font-bold text-koline-primary hover:text-white hover:underline transition duration-300 ml-1">
                    Regístrate aquí
                </a>
            </p>
            
            <div>
                <a href="recuperar.php" class="text-sm text-gray-500 hover:text-koline-primary transition duration-300 inline-flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Olvidé mi contraseña
                </a>
            </div>
        </div>

    </div>
</body>
</html>
