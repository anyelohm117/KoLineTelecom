<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Crear Cuenta - KoLine Telecom</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    'koline-primary': '#00eaff',
                    'koline-dark': '#0a1d37',
                    'koline-error': '#ff3366',
                },
                fontFamily: {
                    sans: ['Inter', 'sans-serif'],
                },
                boxShadow: {
                    'neon': '0 0 15px rgba(0, 234, 255, 0.7)',
                    'neon-lg': '0 0 30px rgba(0, 234, 255, 0.9)',
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
/* Truco visual: Los options del select a veces se ven blancos en algunos navegadores, esto fuerza el oscuro */
select option {
    background-color: #0a1d37;
    color: white;
}
</style>
</head>

<body class="bg-koline-dark text-white min-h-screen flex justify-center items-center p-4 relative overflow-hidden font-sans">

<div class="absolute inset-0 bg-network-pattern"></div>
<div class="absolute w-96 h-96 bg-koline-primary rounded-full blur-[100px] opacity-20 animate-pulse-slow top-10 left-10"></div>
<div class="absolute w-72 h-72 bg-purple-600 rounded-full blur-[120px] opacity-20 animate-pulse-slow bottom-10 right-10"></div>

<div class="w-full max-w-lg bg-white/5 backdrop-blur-xl border border-koline-primary/30 rounded-2xl p-8 shadow-neon-lg z-10 relative transition-all hover:shadow-neon-lg/90">

    <div class="text-center mb-6">
        <h1 class="text-3xl font-bold text-koline-primary tracking-wider shadow-neon/50">Únete a la Red</h1>
        <p class="text-sm text-gray-300 mt-1">Crea tu credencial de acceso</p>
    </div>

    <?php if(isset($_GET['error'])): ?>
        <div class="mb-4 p-2 bg-red-900/60 border border-koline-error rounded-lg text-center text-sm text-red-100 animate-pulse">
            <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <form action="registro_logic.php" method="POST" class="space-y-4">
        
        <div class="grid grid-cols-2 gap-4">
            <input type="text" name="nombre" placeholder="Nombre(s)" required
                class="w-full p-3 rounded-xl bg-koline-dark/50 border border-gray-600 focus:border-koline-primary focus:outline-none transition text-white placeholder-gray-400">
            
            <input type="text" name="apellido_paterno" placeholder="Apellido Paterno" required
                class="w-full p-3 rounded-xl bg-koline-dark/50 border border-gray-600 focus:border-koline-primary focus:outline-none transition text-white placeholder-gray-400">
        </div>

        <input type="text" name="apellido_materno" placeholder="Apellido Materno (Opcional)"
            class="w-full p-3 rounded-xl bg-koline-dark/50 border border-gray-600 focus:border-koline-primary focus:outline-none transition text-white placeholder-gray-400">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
             <input type="text" name="username" placeholder="Usuario (Nickname)" required
                class="w-full p-3 rounded-xl bg-koline-dark/50 border border-gray-600 focus:border-koline-primary focus:outline-none transition text-white placeholder-gray-400">
             
             <input type="email" name="email" placeholder="Correo Electrónico" required
                class="w-full p-3 rounded-xl bg-koline-dark/50 border border-gray-600 focus:border-koline-primary focus:outline-none transition text-white placeholder-gray-400">
        </div>

        <div class="relative">
            <select name="rol" required class="w-full p-3 rounded-xl bg-koline-dark/50 border border-gray-600 focus:border-koline-primary focus:outline-none text-white appearance-none cursor-pointer">
                <option value="" disabled selected>Selecciona tu tipo de cuenta</option>
                <option value="2">👤 Cliente (Ver mi consumo)</option>
                <option value="1">🛠 Administrador / Staff</option>
                <option value="3">🔧 Técnico de Soporte</option>
            </select>
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-koline-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <input type="password" name="clave" placeholder="Contraseña" required
                class="w-full p-3 rounded-xl bg-koline-dark/50 border border-gray-600 focus:border-koline-primary focus:outline-none transition text-white placeholder-gray-400">
            
            <input type="password" name="confirmar_clave" placeholder="Confirmar" required
                class="w-full p-3 rounded-xl bg-koline-dark/50 border border-gray-600 focus:border-koline-primary focus:outline-none transition text-white placeholder-gray-400">
        </div>

        <button type="submit"
            class="w-full p-3 mt-4 font-bold rounded-xl bg-koline-primary text-koline-dark shadow-neon hover:shadow-neon-lg hover:scale-[1.02] transition-transform duration-200">
            REGISTRARME
        </button>
    </form>

    <div class="mt-6 text-center text-sm text-gray-400">
        ¿Ya tienes cuenta? 
        <a href="index.php" class="text-koline-primary hover:underline hover:shadow-neon font-semibold transition-all">
            Inicia sesión aquí
        </a>
    </div>

</div>
</body>
</html>
