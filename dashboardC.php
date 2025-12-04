<?php
session_start();
require 'db_con.php';

/* ============================================
   🔒 SEGURIDAD: SOLO CLIENTES (Rol 2)
============================================ */
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 2) {
    header("Location: index.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$datos = null;

try {
    // Obtenemos info detallada del servicio
    $sql = "SELECT u.nombres, u.apellido_paterno, u.activo, 
            c.direccion_instalacion, c.ip_asignada, c.fecha_instalacion,
            p.nombre_plan, p.velocidad_mbps, p.precio_mensual
            FROM usuarios u
            JOIN clientes c ON u.id_usuario = c.id_usuario
            JOIN planes_internet p ON c.id_plan = p.id_plan
            WHERE u.id_usuario = $id_usuario";
            
    $res = $conn->query($sql);
    $datos = $res->fetch_assoc();

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Servicio | KoLine</title>
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
                        'neon': '0 0 15px rgba(0, 234, 255, 0.4)',
                        'card-glow': '0 0 40px rgba(0, 234, 255, 0.1)',
                    }
                }
            }
        }
    </script>
    <style>
        .bg-network-pattern {
            background-image: radial-gradient(circle, rgba(0, 234, 255, 0.1) 1px, transparent 1px);
            background-size: 30px 30px; opacity: 0.3;
        }
        /* Efecto Glass */
        .glass-panel {
            background: rgba(17, 34, 64, 0.6);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
    </style>
</head>

<body class="bg-koline-dark text-white min-h-screen font-sans relative overflow-x-hidden">

    <div class="fixed inset-0 bg-gradient-to-br from-koline-dark via-[#051021] to-black z-0"></div>
    <div class="fixed inset-0 bg-network-pattern z-0"></div>
    <div class="fixed w-[600px] h-[600px] bg-koline-primary rounded-full blur-[150px] opacity-10 top-[-100px] left-[-100px] z-0"></div>

    <div class="relative z-10 max-w-6xl mx-auto p-6">

        <header class="flex flex-col md:flex-row justify-between items-center mb-10 gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-black/40 rounded-full flex items-center justify-center border border-koline-primary/30 shadow-neon">
                    <img src="imagenes/logo.png" alt="Logo" class="w-8">
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-wide">KoLine <span class="text-koline-primary">OS</span></h1>
                    <p class="text-xs text-gray-400 uppercase tracking-widest">Portal de Cliente</p>
                </div>
            </div>

            <div class="flex items-center gap-4 bg-white/5 px-5 py-2 rounded-full border border-white/10 backdrop-blur-md">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-bold text-white"><?= $datos['nombres'] ?></p>
                    <p class="text-xs text-koline-primary">Cliente Verificado</p>
                </div>
                <a href="index.php" class="text-xs bg-red-500/10 text-red-400 border border-red-500/30 px-3 py-1.5 rounded-full hover:bg-red-500 hover:text-white transition">
                    Cerrar Sesión
                </a>
            </div>
        </header>

        <?php if ($datos): ?>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <div class="lg:col-span-2 glass-panel rounded-3xl p-8 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-koline-primary/10 rounded-full blur-3xl -mr-16 -mt-16 group-hover:bg-koline-primary/20 transition duration-700"></div>
                    
                    <div class="flex flex-col sm:flex-row items-center sm:items-start justify-between gap-6">
                        <div>
                            <h2 class="text-gray-400 text-sm uppercase tracking-wider mb-1">Estado del Servicio</h2>
                            
                            <?php if($datos['activo'] == 1): ?>
                                <div class="flex items-center gap-3">
                                    <span class="relative flex h-6 w-6">
                                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                      <span class="relative inline-flex rounded-full h-6 w-6 bg-green-500 shadow-[0_0_15px_rgba(34,197,94,0.6)]"></span>
                                    </span>
                                    <span class="text-3xl font-bold text-white text-shadow-sm">Conectado</span>
                                </div>
                                <p class="text-green-400 text-sm mt-2">Tu servicio opera con normalidad.</p>
                            <?php else: ?>
                                <div class="flex items-center gap-3">
                                    <span class="relative flex h-6 w-6">
                                      <span class="relative inline-flex rounded-full h-6 w-6 bg-red-500 shadow-[0_0_15px_rgba(239,68,68,0.6)]"></span>
                                    </span>
                                    <span class="text-3xl font-bold text-white">Suspendido</span>
                                </div>
                                <p class="text-red-400 text-sm mt-2">Contacta a soporte para reactivación.</p>
                            <?php endif; ?>
                        </div>

                        <div class="text-right space-y-1 hidden sm:block">
                            <p class="text-xs text-gray-500">IP Asignada</p>
                            <p class="font-mono text-koline-primary bg-koline-primary/10 px-2 py-1 rounded text-sm"><?= $datos['ip_asignada'] ?></p>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-white/10 grid grid-cols-2 sm:grid-cols-3 gap-6">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Plan Contratado</p>
                            <p class="font-bold text-lg text-white"><?= $datos['nombre_plan'] ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Velocidad</p>
                            <p class="font-bold text-lg text-white flex items-center gap-1">
                                <svg class="w-4 h-4 text-koline-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                <?= $datos['velocidad_mbps'] ?> Mbps
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Mensualidad</p>
                            <p class="font-bold text-lg text-white">$<?= number_format($datos['precio_mensual'], 2) ?></p>
                        </div>
                    </div>
                </div>

                <div class="glass-panel rounded-3xl p-6 flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-koline-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            ¿Problemas?
                        </h3>
                        <p class="text-sm text-gray-400 mb-6">Si experimentas lentitud o cortes, genera un reporte y un técnico te atenderá.</p>
                    </div>
                    
                    <button onclick="alert('Funcionalidad próxima: Crear ticket')" class="w-full py-4 bg-gradient-to-r from-koline-primary to-cyan-600 text-koline-dark font-bold rounded-xl shadow-neon hover:scale-[1.02] transition-transform flex justify-center items-center gap-2">
                        <span>Generar Ticket de Soporte</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                    </button>
                </div>

                <div class="lg:col-span-3 glass-panel rounded-2xl p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-white/5 rounded-full text-gray-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Dirección de Instalación</p>
                            <p class="text-sm text-white max-w-xl"><?= $datos['direccion_instalacion'] ?></p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-white/5 rounded-full text-gray-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-500">Fecha Instalación</p>
                            <p class="text-sm text-white"><?= date("d/m/Y", strtotime($datos['fecha_instalacion'])) ?></p>
                        </div>
                    </div>
                </div>

            </div>

        <?php else: ?>
            <div class="text-center p-10 glass-panel rounded-3xl">
                <h2 class="text-red-400 text-xl font-bold">Error de Datos</h2>
                <p class="text-gray-400">No se pudo cargar la información de tu servicio.</p>
            </div>
        <?php endif; ?>

        <footer class="mt-10 text-center text-xs text-gray-600">
            <p>&copy; <?= date('Y') ?> KoLine Telecom. Todos los derechos reservados.</p>
        </footer>

    </div>

</body>
</html>
