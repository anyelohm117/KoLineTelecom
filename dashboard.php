<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | KoLine Telecom</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* Personalización para el toque "Neon" de KoLine */
        .neon-text {
            text-shadow: 0 0 10px rgba(6, 182, 212, 0.7);
        }
        .glass-panel {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 font-sans antialiased">

    <div class="flex h-screen overflow-hidden">

        <aside class="w-64 bg-slate-950 border-r border-slate-800 flex flex-col transition-all duration-300">
            <div class="h-16 flex items-center justify-center border-b border-slate-800">
                <i class="fa-solid fa-wifi text-cyan-400 text-2xl mr-2"></i>
                <h1 class="text-xl font-bold tracking-wider">KoLine <span class="text-cyan-400 neon-text">Telecom</span></h1>
            </div>

            <nav class="flex-1 overflow-y-auto py-4">
                <ul class="space-y-2 px-4">
                    <li>
                        <a href="#" class="flex items-center p-3 text-cyan-400 bg-slate-900 rounded-lg border border-slate-800 shadow-md">
                            <i class="fa-solid fa-gauge-high w-6"></i>
                            <span class="font-medium">Dashboard</span>
                        </a>
                    </li>
                    
                    <p class="text-xs text-slate-500 uppercase font-semibold mt-4 mb-2 pl-2">Gestión</p>
                    <li>
                        <a href="#" class="flex items-center p-3 text-slate-400 hover:text-cyan-300 hover:bg-slate-900 rounded-lg transition-colors">
                            <i class="fa-solid fa-users w-6"></i>
                            <span>Clientes</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center p-3 text-slate-400 hover:text-cyan-300 hover:bg-slate-900 rounded-lg transition-colors">
                            <i class="fa-solid fa-file-invoice-dollar w-6"></i>
                            <span>Facturación</span>
                        </a>
                    </li>

                    <p class="text-xs text-slate-500 uppercase font-semibold mt-4 mb-2 pl-2">Técnico</p>
                    <li>
                        <a href="#" class="flex items-center p-3 text-slate-400 hover:text-cyan-300 hover:bg-slate-900 rounded-lg transition-colors">
                            <i class="fa-solid fa-headset w-6"></i>
                            <span>Soporte / Tickets</span>
                            <span class="ml-auto bg-red-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">3</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center p-3 text-slate-400 hover:text-cyan-300 hover:bg-slate-900 rounded-lg transition-colors">
                            <i class="fa-solid fa-boxes-stacked w-6"></i>
                            <span>Almacén</span>
                        </a>
                    </li>

                    <p class="text-xs text-slate-500 uppercase font-semibold mt-4 mb-2 pl-2">Sistema</p>
                    <li>
                        <a href="#" class="flex items-center p-3 text-slate-400 hover:text-cyan-300 hover:bg-slate-900 rounded-lg transition-colors">
                            <i class="fa-solid fa-users-gear w-6"></i>
                            <span>Empleados</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="p-4 border-t border-slate-800">
                <div class="flex items-center gap-3">
                    <img class="h-10 w-10 rounded-full border-2 border-cyan-500" src="https://ui-avatars.com/api/?name=Admin+User&background=06b6d4&color=fff" alt="">
                    <div>
                        <p class="text-sm font-medium text-white">Administrador</p>
                        <p class="text-xs text-slate-500">admin@koline.com</p>
                    </div>
                    <button class="ml-auto text-slate-400 hover:text-red-400"><i class="fa-solid fa-right-from-bracket"></i></button>
                </div>
            </div>
        </aside>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-900 p-8">
            
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-white">Resumen General</h2>
                    <p class="text-slate-400">Bienvenido al panel de control de KoLine Telecom</p>
                </div>
                <div class="flex gap-4">
                    <button class="bg-cyan-600 hover:bg-cyan-500 text-white px-4 py-2 rounded-lg shadow-lg shadow-cyan-500/20 transition">
                        <i class="fa-solid fa-plus mr-2"></i> Nuevo Cliente
                    </button>
                    <button class="bg-slate-800 hover:bg-slate-700 text-white px-4 py-2 rounded-lg border border-slate-700 transition">
                        <i class="fa-solid fa-filter mr-2"></i> Filtrar
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                
                <div class="glass-panel p-6 rounded-xl border-l-4 border-emerald-500 shadow-lg">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-slate-400 text-sm font-medium uppercase">Ingresos (Hoy)</p>
                            <h3 class="text-2xl font-bold text-white mt-1">$ 12,450.00</h3>
                        </div>
                        <div class="p-3 bg-emerald-500/10 rounded-lg text-emerald-500">
                            <i class="fa-solid fa-money-bill-wave text-xl"></i>
                        </div>
                    </div>
                    <p class="text-emerald-400 text-xs mt-4 flex items-center">
                        <i class="fa-solid fa-arrow-up mr-1"></i> +15% vs ayer
                    </p>
                </div>

                <div class="glass-panel p-6 rounded-xl border-l-4 border-cyan-500 shadow-lg">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-slate-400 text-sm font-medium uppercase">Clientes Activos</p>
                            <h3 class="text-2xl font-bold text-white mt-1">842</h3>
                        </div>
                        <div class="p-3 bg-cyan-500/10 rounded-lg text-cyan-500">
                            <i class="fa-solid fa-users text-xl"></i>
                        </div>
                    </div>
                    <p class="text-cyan-400 text-xs mt-4 flex items-center">
                        <i class="fa-solid fa-wifi mr-1"></i> 5 instalaciones hoy
                    </p>
                </div>

                <div class="glass-panel p-6 rounded-xl border-l-4 border-orange-500 shadow-lg">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-slate-400 text-sm font-medium uppercase">Tickets Soporte</p>
                            <h3 class="text-2xl font-bold text-white mt-1">12</h3>
                        </div>
                        <div class="p-3 bg-orange-500/10 rounded-lg text-orange-500">
                            <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                        </div>
                    </div>
                    <p class="text-orange-400 text-xs mt-4 flex items-center">
                        <i class="fa-regular fa-clock mr-1"></i> 2 urgentes
                    </p>
                </div>

                <div class="glass-panel p-6 rounded-xl border-l-4 border-purple-500 shadow-lg">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-slate-400 text-sm font-medium uppercase">Stock Routers</p>
                            <h3 class="text-2xl font-bold text-white mt-1">45</h3>
                        </div>
                        <div class="p-3 bg-purple-500/10 rounded-lg text-purple-500">
                            <i class="fa-solid fa-box-open text-xl"></i>
                        </div>
                    </div>
                    <p class="text-red-400 text-xs mt-4 flex items-center">
                        <i class="fa-solid fa-circle-exclamation mr-1"></i> Stock bajo en ONUs
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-2 glass-panel p-6 rounded-xl">
                    <h3 class="text-lg font-bold text-white mb-4">Ingresos vs Gastos (Últimos 6 Meses)</h3>
                    <div class="relative h-64 w-full">
                        <canvas id="financeChart"></canvas>
                    </div>
                </div>

                <div class="glass-panel p-6 rounded-xl">
                    <h3 class="text-lg font-bold text-white mb-4">Pagos Recientes</h3>
                    <div class="overflow-y-auto h-64 pr-2">
                        <div class="flex items-center justify-between p-3 mb-3 bg-slate-800/50 rounded-lg border border-slate-700">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-green-900/50 flex items-center justify-center text-green-400">
                                    <i class="fa-solid fa-money-bill"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-white">Juan Pérez</p>
                                    <p class="text-xs text-slate-400">Hace 10 min</p>
                                </div>
                            </div>
                            <span class="text-emerald-400 font-bold text-sm">+$450.00</span>
                        </div>
                         <div class="flex items-center justify-between p-3 mb-3 bg-slate-800/50 rounded-lg border border-slate-700">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-900/50 flex items-center justify-center text-blue-400">
                                    <i class="fa-solid fa-credit-card"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-white">Maria Lopez</p>
                                    <p class="text-xs text-slate-400">Hace 35 min</p>
                                </div>
                            </div>
                            <span class="text-emerald-400 font-bold text-sm">+$600.00</span>
                        </div>
                         <div class="flex items-center justify-between p-3 mb-3 bg-slate-800/50 rounded-lg border border-slate-700">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-green-900/50 flex items-center justify-center text-green-400">
                                    <i class="fa-solid fa-money-bill"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-white">Carlos Ruiz</p>
                                    <p class="text-xs text-slate-400">Hace 1 hora</p>
                                </div>
                            </div>
                            <span class="text-emerald-400 font-bold text-sm">+$450.00</span>
                        </div>
                    </div>
                    <button class="w-full mt-4 py-2 text-sm text-cyan-400 border border-cyan-900 rounded-lg hover:bg-cyan-900/20 transition">
                        Ver todos los movimientos
                    </button>
                </div>
            </div>

            <div class="mt-8 glass-panel rounded-xl overflow-hidden">
                <div class="p-6 border-b border-slate-800 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white">Soporte Técnico - Pendientes</h3>
                    <span class="px-3 py-1 bg-orange-500/20 text-orange-400 text-xs rounded-full font-bold">Atención Requerida</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-400">
                        <thead class="bg-slate-800 text-slate-200 uppercase text-xs">
                            <tr>
                                <th class="px-6 py-3">ID Ticket</th>
                                <th class="px-6 py-3">Cliente</th>
                                <th class="px-6 py-3">Asunto</th>
                                <th class="px-6 py-3">Prioridad</th>
                                <th class="px-6 py-3">Estado</th>
                                <th class="px-6 py-3">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            <tr class="hover:bg-slate-800/50 transition">
                                <td class="px-6 py-4 font-medium text-white">#TK-802</td>
                                <td class="px-6 py-4">Roberto Gómez</td>
                                <td class="px-6 py-4">Sin conexión - Luz roja en router</td>
                                <td class="px-6 py-4"><span class="px-2 py-1 bg-red-500/10 text-red-500 rounded text-xs font-bold">Alta</span></td>
                                <td class="px-6 py-4 text-orange-400">Abierto</td>
                                <td class="px-6 py-4">
                                    <button class="text-cyan-400 hover:text-cyan-300">Ver</button>
                                </td>
                            </tr>
                            <tr class="hover:bg-slate-800/50 transition">
                                <td class="px-6 py-4 font-medium text-white">#TK-801</td>
                                <td class="px-6 py-4">Ana Martínez</td>
                                <td class="px-6 py-4">Cambio de contraseña Wifi</td>
                                <td class="px-6 py-4"><span class="px-2 py-1 bg-blue-500/10 text-blue-500 rounded text-xs font-bold">Baja</span></td>
                                <td class="px-6 py-4 text-yellow-400">En Proceso</td>
                                <td class="px-6 py-4">
                                    <button class="text-cyan-400 hover:text-cyan-300">Ver</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <script>
        const ctx = document.getElementById('financeChart').getContext('2d');
        const financeChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                datasets: [{
                    label: 'Ingresos',
                    data: [12000, 19000, 3000, 5000, 20000, 30000],
                    borderColor: '#06b6d4', // Cyan
                    backgroundColor: 'rgba(6, 182, 212, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Gastos',
                    data: [8000, 10000, 2500, 4000, 12000, 15000],
                    borderColor: '#ef4444', // Red
                    backgroundColor: 'rgba(239, 68, 68, 0)',
                    tension: 0.4,
                    borderDash: [5, 5]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: { color: '#94a3b8' }
                    }
                },
                scales: {
                    y: {
                        ticks: { color: '#94a3b8' },
                        grid: { color: '#334155' }
                    },
                    x: {
                        ticks: { color: '#94a3b8' },
                        grid: { display: false }
                    }
                }
            }
        });
    </script>
</body>
</html>
