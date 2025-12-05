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
    // Obtenemos toda la info del servicio del cliente
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
    <title>Mi Conexión | KoLine</title>
    
    <link rel="icon" type="image/png" href="imagenes/logo.png">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'koline-primary': '#00eaff',
                        'koline-dark': '#020c1b',
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    boxShadow: {
                        'neon': '0 0 20px rgba(0, 234, 255, 0.4)',
                        'card': '0 8px 32px 0 rgba(0, 0, 0, 0.3)',
                    }
                }
            }
        }
    </script>
    <style>
        .bg-network {
            background-image: radial-gradient(circle, rgba(0, 234, 255, 0.05) 1px, transparent 1px);
            background-size: 40px 40px;
        }
        .glass-card {
            background: rgba(13, 25, 40, 0.6);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .text-glow { text-shadow: 0 0 10px rgba(0, 234, 255, 0.5); }
    </style>
</head>

<body class="bg-koline-dark text-white min-h-screen font-sans relative overflow-x-hidden">

    <div class="fixed inset-0 bg-gradient-to-br from-koline-dark via-[#051021] to-black z-0"></div>
    <div class="fixed inset-0 bg-network z-0 opacity-30"></div>
    <div class="fixed top-[-10%] left-[-10%] w-[500px] h-[500px] bg-koline-primary rounded-full blur-[180px] opacity-10 z-0"></div>

    <div class="relative z-10 max-w-5xl mx-auto p-6 md:p-10">

        <header class="flex flex-col md:flex-row justify-between items-center mb-12 gap-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-black/40 rounded-full flex items-center justify-center border border-koline-primary/30 shadow-neon">
                    <img src="imagenes/logo.png" alt="Logo" class="w-9 object-contain">
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">KoLine <span class="text-koline-primary">OS</span></h1>
                    <p class="text-xs text-gray-400 uppercase tracking-[0.2em]">Portal de Cliente</p>
                </div>
            </div>

            <div class="flex items-center gap-6">
                <div class="text-right hidden md:block">
                    <p class="text-sm font-bold text-white">Hola, <?= $datos['nombres'] ?></p>
                    <p class="text-xs text-green-400 flex justify-end items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span> Online
                    </p>
                </div>
                <a href="index.php" class="px-5 py-2 rounded-full bg-white/5 border border-white/10 hover:bg-red-500/20 hover:border-red-500/50 hover:text-red-300 transition text-sm font-medium">
                    Salir
                </a>
            </div>
        </header>

        <?php if ($datos): ?>
            
            <div class="mb-6 border-l-4 border-koline-primary pl-4">
                <h2 class="text-2xl font-bold text-white">Mi Conexión</h2>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <div class="glass-card rounded-3xl p-8 relative overflow-hidden flex flex-col justify-between min-h-[300px]">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-koline-primary/10 rounded-full blur-3xl"></div>

                    <div>
                        <div class="flex items-center gap-3 mb-6">
                            <svg class="w-5 h-5 text-koline-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            <h3 class="text-koline-primary font-bold uppercase tracking-wider text-sm">Estado del Servicio</h3>
                        </div>

                        <div class="flex justify-between items-end mb-4">
                            <div>
                                <p class="text-gray-400 text-sm mb-1">Plan Contratado</p>
                                <p class="text-xl font-bold text-white"><?= $datos['nombre_plan'] ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-3xl font-extrabold text-white text-glow"><?= $datos['velocidad_mbps'] ?> <span class="text-lg text-gray-400">Mbps</span></p>
                            </div>
                        </div>

                        <div class="w-full h-1.5 bg-white/10 rounded-full overflow-hidden mb-6">
                            <div class="h-full bg-gradient-to-r from-koline-primary to-blue-500 w-[85%] shadow-[0_0_10px_#00eaff]"></div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div class="p-3 bg-black/20 rounded-xl border border-white/5">
                                <p class="text-gray-500 text-xs mb-1">Dirección IP</p>
                                <p class="font-mono text-koline-primary"><?= $datos['ip_asignada'] ?></p>
                            </div>
                            <div class="p-3 bg-black/20 rounded-xl border border-white/5">
                                <p class="text-gray-500 text-xs mb-1">Instalación</p>
                                <p class="text-white"><?= date("d/m/Y", strtotime($datos['fecha_instalacion'])) ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-center">
                        <?php if($datos['activo'] == 1): ?>
                            <div class="inline-flex items-center gap-3 px-6 py-2 rounded-full bg-green-500/10 border border-green-500/30 text-green-400 font-bold tracking-wide">
                                <span class="relative flex h-3 w-3">
                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                  <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                                </span>
                                Servicio Activo
                            </div>
                        <?php else: ?>
                            <div class="inline-flex items-center gap-3 px-6 py-2 rounded-full bg-red-500/10 border border-red-500/30 text-red-400 font-bold tracking-wide">
                                <span class="w-3 h-3 rounded-full bg-red-500"></span>
                                Servicio Suspendido
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="glass-card rounded-3xl p-8 flex flex-col justify-center items-center text-center relative border border-white/10">
                    
                    <div class="w-16 h-16 bg-gradient-to-tr from-koline-primary to-blue-600 rounded-2xl flex items-center justify-center mb-6 shadow-neon transform rotate-3">
                        <svg class="w-8 h-8 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>

                    <h3 class="text-xl font-bold text-white mb-2">¿Problemas con tu internet?</h3>
                    <p class="text-gray-400 text-sm mb-8 max-w-xs">Puedes generar un reporte y un técnico revisará tu caso a la brevedad.</p>

                    <button onclick="alert('Sistema de tickets para clientes próximamente.')" class="w-full max-w-xs py-4 bg-gradient-to-r from-koline-primary to-cyan-500 hover:to-cyan-400 text-koline-dark font-bold rounded-xl shadow-neon transition transform hover:scale-[1.02] flex justify-center items-center gap-2">
                        <span>Crear Ticket de Soporte</span>
                    </button>

                    <a href="#" class="mt-4 text-sm text-koline-primary hover:text-white transition">Ver mis facturas</a>
                </div>

            </div>

        <?php else: ?>
            <div class="p-10 text-center glass-card rounded-3xl border-red-500/30">
                <h2 class="text-red-400 font-bold text-xl">No se encontró información</h2>
                <p class="text-gray-400">Contacta a soporte técnico.</p>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>
