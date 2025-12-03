<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Iniciar Sesión - KoLine Telecom</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    'koline-primary': '#00eaff',
                    'koline-dark': '#0a1d37',
                    'koline-error': '#ff3366', // Color extra para errores
                },
                fontFamily: {
                    sans: ['Inter', 'sans-serif'],
                },
                boxShadow: {
                    'neon': '0 0 15px rgba(0, 234, 255, 0.7)',
                    'neon-lg': '0 0 30px rgba(0, 234, 255, 0.9)',
                    'neon-red': '0 0 15px rgba(255, 51, 102, 0.6)', // Sombra roja para error
                },
                animation: {
                    'pulse-slow': 'pulse 8s cubic-bezier(0.4, 0, 0.6, 1) infinite alternate',
                }
            }
        }
    }
</script>

<style>
.bg-network-pattern {
    background-image: radial-gradient(circle, rgba(0, 234, 255, 0.1) 1px, transparent 1px);
    background-size: 40px 40px;
    opacity: 0.2;
}
@keyframes pulse {
  0% { transform: scale(1); opacity: 0.2; }
  100% { transform: scale(1.1); opacity: 0.35; }
}
</style>

</head>

<body class="bg-koline-dark text-white min-h-screen flex justify-center items-center p-4 relative overflow-hidden font-sans">

<div class="absolute inset-0 bg-network-pattern"></div>
<div class="absolute w-96 h-96 bg-koline-primary rounded-full blur-[100px] opacity-20 animate-pulse-slow top-1/4 left-3/4"></div>
<div class="absolute w-72 h-72 bg-blue-500 rounded-full blur-[120px] opacity-15 animate-pulse-slow bottom-1/4 left-1/4 animation-delay-2000"></div>


<div class="w-full max-w-sm bg-white/5 backdrop-blur-xl border border-koline-primary/30 rounded-2xl p-8 shadow-neon-lg z-10 transition-all duration-300 hover:shadow-neon-lg/90">

    <div class="text-center mb-8">
        <svg class="w-12 h-12 mx-auto mb-2 text-koline-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
        </svg>

        <h1 class="text-3xl font-bold text-koline-primary tracking-wider shadow-neon/50">KoLine Telecom</h1>
        <h2 class="text-xl font-medium mt-3 text-white">Acceso a la Red</h2>
    </div>

    <?php if(isset($_GET['error'])): ?>
        <div class="mb-4 p-3 bg-red-900/40 border border-koline-error rounded-xl text-red-200 text-sm text-center shadow-neon-red backdrop-blur-md animate-pulse">
            <?php 
                if($_GET['error'] == 1) echo "⚠ Contraseña incorrecta.";
                elseif($_GET['error'] == 2) echo "⚠ El usuario no existe.";
                else echo "⚠ Error de acceso.";
            ?>
        </div>
    <?php endif; ?>

    <form action="login_logic.php" method="POST" class="space-y-4">
        
        <input type="email" name="correo_electronico" placeholder="Correo electrónico" required
            class="w-full p-3 rounded-xl bg-koline-dark/50 border border-gray-600 focus:border-koline-primary focus:ring-1 focus:ring-koline-primary transition duration-200 placeholder-gray-400 text-white"
        >
        
        <input type="password" name="clave" placeholder="Contraseña" required
            class="w-full p-3 rounded-xl bg-koline-dark/50 border border-gray-600 focus:border-koline-primary focus:ring-1 focus:ring-koline-primary transition duration-200 placeholder-gray-400 text-white"
        >

        <button type="submit"
            class="w-full p-3 mt-6 font-semibold rounded-xl bg-koline-primary text-koline-dark shadow-neon hover:shadow-neon-lg hover:bg-opacity-90 transition duration-300 transform hover:scale-[1.01]"
        >
            Iniciar sesión
        </button>
    </form>

    <p class="text-sm text-center text-gray-400 mt-6">
        ¿No tienes cuenta?
        <a href="registro.php" class="font-medium text-koline-primary hover:text-koline-primary/80 transition duration-200 hover:shadow-neon/50">
            Regístrate aquí
        </a>
    </p>

    <div class="text-center mt-3">
        <a href="#" class="text-xs text-gray-500 hover:text-koline-primary transition duration-200">
            Olvidé mi contraseña
        </a>
    </div>
</div>
</body>
</html>
