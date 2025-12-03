<?php
session_start();
require 'db_con.php';

// --- SEGURIDAD: CANDADO ---
// Solo entra Rol 2 (Cliente)
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 2) {
    header("Location: index.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

// Variables por defecto (por si es un cliente nuevo sin datos técnicos aún)
$cliente_data = [
    'plan' => 'Sin plan asignado',
    'velocidad' => '0 Mbps',
    'direccion' => 'Sin dirección registrada',
    'ip' => 'Pendiente',
    'fecha_instalacion' => date('d/m/Y')
];

// 1. OBTENER DATOS TÉCNICOS DEL CLIENTE
// Hacemos JOIN con la tabla de planes para saber el nombre del plan
$sql = "SELECT c.*, p.nombre_plan, p.velocidad_mbps 
        FROM clientes c 
        LEFT JOIN planes_internet p ON c.id_plan = p.id_plan 
        WHERE c.id_usuario = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    $cliente_data['plan'] = $row['nombre_plan'] ?? 'Plan no definido';
    $cliente_data['velocidad'] = ($row['velocidad_mbps'] ?? 0) . ' Mbps';
    $cliente_data['direccion'] = $row['direccion_instalacion'];
    $cliente_data['ip'] = $row['ip_asignada'] ?? 'Asignando IP...';
    $cliente_data['fecha_instalacion'] = $row['fecha_instalacion'] ? date('d/m/Y', strtotime($row['fecha_instalacion'])) : 'Pendiente';
}

// 2. PREPARAR DATOS PARA JS
$js_data = [
    'nombre' => $_SESSION['nombre_usuario'],
    'servicio' => $cliente_data
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mi KoLine - Cliente</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
    body { background-color: #0a1d37; color: white; font-family: sans-serif; }
    .neon-text { text-shadow: 0 0 10px rgba(0, 234, 255, 0.5); }
</style>
</head>
<body class="min-h-screen flex flex-col">

<nav class="bg-gray-900 border-b border-cyan-500/30 p-4 flex justify-between items-center">
    <div class="text-2xl font-bold text-cyan-400 neon-text">KoLine OS</div>
    <div class="flex items-center gap-4">
        <span class="text-sm text-gray-300">Hola, <b class="text-white"><?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?></b></span>
        <a href="index.php" class="bg-red-600 hover:bg-red-700 text-white text-xs px-3 py-2 rounded transition">Salir</a>
    </div>
</nav>

<div class="max-w-5xl mx-auto p-6 w-full">
    <h1 class="text-3xl font-bold mb-6 border-l-4 border-cyan-500 pl-4">Mi Conexión</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <div class="bg-gray-800/50 border border-cyan-500/20 rounded-xl p-6 shadow-lg">
            <h2 class="text-xl text-cyan-400 mb-4 font-bold flex items-center">
                📡 Estado del Servicio
            </h2>
            <div class="space-y-4">
                <div class="flex justify-between border-b border-gray-700 pb-2">
                    <span class="text-gray-400">Plan Contratado</span>
                    <span class="text-white font-semibold"><?php echo htmlspecialchars($cliente_data['plan']); ?></span>
                </div>
                <div class="flex justify-between border-b border-gray-700 pb-2">
                    <span class="text-gray-400">Velocidad</span>
                    <span class="text-white font-semibold"><?php echo htmlspecialchars($cliente_data['velocidad']); ?></span>
                </div>
                <div class="flex justify-between border-b border-gray-700 pb-2">
                    <span class="text-gray-400">Dirección IP</span>
                    <span class="font-mono text-cyan-300"><?php echo htmlspecialchars($cliente_data['ip']); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Instalación</span>
                    <span class="text-white"><?php echo htmlspecialchars($cliente_data['fecha_instalacion']); ?></span>
                </div>
            </div>
            
            <div class="mt-6 text-center">
                <span class="inline-block px-4 py-1 rounded-full bg-green-500/20 text-green-400 border border-green-500/50 text-sm font-bold">
                    ● Servicio Activo
                </span>
            </div>
        </div>

        <div class="bg-gray-800/50 border border-gray-600 rounded-xl p-6 shadow-lg flex flex-col justify-center items-center text-center">
            <h3 class="text-lg text-white mb-2">¿Problemas con tu internet?</h3>
            <p class="text-gray-400 text-sm mb-6">Puedes generar un reporte y un técnico revisará tu caso.</p>
            <button class="bg-cyan-600 hover:bg-cyan-500 text-white py-2 px-6 rounded-lg font-bold transition w-full max-w-xs shadow-[0_0_15px_rgba(0,200,255,0.3)]">
                Crear Ticket de Soporte
            </button>
            <button class="mt-3 text-cyan-400 text-sm hover:underline">Ver mis facturas</button>
        </div>

    </div>
</div>

</body>
</html>
