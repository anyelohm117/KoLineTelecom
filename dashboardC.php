<?php
// 1. INICIAR LA SESIÓN Y CONECTAR LA BD
session_start();
require 'db_con.php'; 

// 2. OBTENER ID DEL CLIENTE
// ¡¡IMPORTANTE!! Asumimos que el ID del cliente se guarda en la sesión después del login.
// Como no tenemos login, usaremos el ID 1 (Juan Pérez de nuestros datos de prueba)
$cliente_id = $_SESSION['cliente_id'] ?? 1; 

// 3. OBTENER DATOS REALES DE LA BASE DE DATOS
$cliente_db = null;
$tickets_db = [];
$inventario_db = [];

// --- Obtener datos del Cliente ---
$stmt_cliente = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt_cliente->bind_param("i", $cliente_id);
$stmt_cliente->execute();
$cliente_db = $stmt_cliente->get_result()->fetch_assoc();

if (!$cliente_db) {
    die("Error: No se pudo encontrar al cliente con ID $cliente_id.");
}

// --- Obtener Tickets del Cliente ---
$stmt_tickets = $conn->prepare("SELECT * FROM tikets WHERE id_cliente = ? ORDER BY fecha_creacion DESC");
$stmt_tickets->bind_param("i", $cliente_id);
$stmt_tickets->execute();
$tickets_db = $stmt_tickets->get_result()->fetch_all(MYSQLI_ASSOC);

// --- Obtener Inventario Asignado al Cliente ---
$stmt_inv = $conn->prepare("SELECT * FROM inventario WHERE id_cliente_asignado = ? AND estado = 'Asignado'");
$stmt_inv->bind_param("i", $cliente_id);
$stmt_inv->execute();
$inventario_db = $stmt_inv->get_result()->fetch_all(MYSQLI_ASSOC);


// 4. TRADUCIR DATOS DE LA BD AL FORMATO QUE EL JAVASCRIPT ESPERA

// --- Datos de Sesión (Reales) ---
$userSession_php = [
    'name' => htmlspecialchars($cliente_db['nombre'] . ' ' . $cliente_db['apellido_paterno']),
    'currentRole' => 'Cliente', // El rol viene de tu tabla de login (usuarios)
    'userId' => $cliente_db['id']
];

// --- Datos del Cliente (Reales + Simulados) ---
$db_fecha_registro = new DateTime($cliente_db['fecha_registro']);
$equipo_principal = !empty($inventario_db) ? $inventario_db[0]['nombre_item'] . ' (' . $inventario_db[0]['modelo'] . ')' : 'No asignado';

$CLIENT_DATA_php = [
    'service' => [
        'plan' => $equipo_principal, // ¡DATO REAL! (Mostramos el equipo)
        'speed' => '500 Mbps Simétricos', // Simulado (Aún no está en la BD)
        'status' => 'Conexión Estable', // Simulado (Esto vendría de un sistema de monitoreo)
        'ip' => '189.145.33.12', // Simulado (Esto es dinámico)
        'since' => $db_fecha_registro->format('d/m/Y') // ¡DATO REAL!
    ],
    'invoice' => [ // Simulado (Aún no tenemos tabla de facturas)
        'id' => 'FCT-202511-001',
        'date' => date('01 M, Y'),
        'amount' => '$550.00',
        'status' => 'Pendiente de Pago',
        'dueDate' => date('20 M, Y')
    ],
    'usage' => [ // Simulado (Esto vendría de un sistema de monitoreo)
        'currentMonthGB' => 240,
        'averageGB' => 220,
        'maxGB' => 500,
    ]
];

// --- Datos de Facturas (Simulados para la lista) ---
$invoices_php = [
    ['id' => 'FCT-202511-001', 'date' => '01 Nov', 'amount' => '$550.00', 'status' => 'Pendiente', 'due' => '20 Nov', 'color' => 'status-red'],
    ['id' => 'FCT-202510-001', 'date' => '01 Oct', 'amount' => '$550.00', 'status' => 'Pagada', 'due' => '20 Oct', 'color' => 'status-green'],
];

// 5. CERRAR CONEXIÓN
$conn->close();

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard de Cliente - KoLine Telecom</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/lucide-icons@latest/umd/lucide.js"></script>

<script>
    // Configuración de Tailwind para colores temáticos KoLine (Neon/Futurista)
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    'koline-primary': '#00eaff', // Cian brillante
                    'koline-dark': '#0a1d37', // Azul oscuro casi negro (Fondo)
                    'koline-medium': '#143154', // Azul medio (Cards y Sidebar)
                    'status-green': '#10b981', 
                    'status-red': '#ef4444',   
                    'status-orange': '#f97316', 
                },
                fontFamily: {
                    sans: ['Inter', 'sans-serif'],
                },
                boxShadow: {
                    'neon': '0 0 10px rgba(0, 234, 255, 0.5)',
                },
            }
        }
    }
</script>

<style>
/* Estilo para el fondo (simulación de red/datos) */
.bg-network-pattern {
    background-image: radial-gradient(circle, rgba(0, 234, 255, 0.08) 1px, transparent 1px);
    background-size: 30px 30px;
}
/* Estilo para items del sidebar */
.sidebar-item {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    margin-bottom: 8px;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s ease;
    border-left: 4px solid transparent;
}
.sidebar-item:hover {
    background-color: rgba(0, 234, 255, 0.1); /* Efecto hover neon */
}
.sidebar-item.active {
    background-color: rgba(0, 234, 255, 0.2);
    border-left-color: #00eaff;
    font-weight: 600;
}
/* Estilo para las tarjetas de información */
.info-card {
    transition: all 0.3s ease;
}
.info-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0, 234, 255, 0.1);
}
</style>
</head>

<body class="bg-koline-dark text-white min-h-screen font-sans">

<header class="bg-koline-medium/50 border-b border-koline-primary/30 shadow-neon/10 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex justify-between items-center">
        <div class="flex items-center space-x-2">
            <svg class="w-6 h-6 text-koline-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9h-3M6 12a3 3 0 11-6 0 3 3 0 016 0zM12 21V3"/></svg>
            <h1 class="text-xl font-extrabold text-koline-primary tracking-widest">KoLine OS</h1>
        </div>
        
        <div class="flex items-center space-x-3 text-sm">
            <span class="hidden sm:inline text-gray-400">Rol Activo: <span id="user-role" class="font-semibold text-koline-primary"></span></span>
            <span id="user-display-name" class="font-semibold text-white bg-koline-primary/10 px-3 py-1 rounded-full border border-koline-primary/50"></span>
            <button id="logout-button" class="text-red-400 hover:text-red-500 p-1 rounded-full hover:bg-white/10" title="Cerrar Sesión">
                <i data-lucide="log-out" class="w-5 h-5"></i>
            </button>
        </div>
    </div>
</header>

<div class="bg-network-pattern min-h-[calc(100vh-64px)] flex">
    
    <aside class="w-64 bg-koline-medium/50 p-4 border-r border-koline-primary/30 hidden lg:block flex-shrink-0">
        <nav id="sidebar-menu" class="mt-4">
            </nav>
        <div class="mt-8 text-center pt-4 border-t border-gray-700">
            <p class="text-xs text-gray-500">Versión 1.2.0</p>
        </div>
    </aside>

    <main class="flex-1 p-4 sm:p-8 overflow-y-auto">
        <h2 class="text-3xl font-bold mb-8 text-white" id="main-title"></h2>
        <div id="content-area">
            </div>
    </main>
</div>


<script>
// ==================================================================
// --- 1. INYECCIÓN DE DATOS DESDE PHP ---
// (Estas variables son creadas por el bloque PHP de arriba)
// ==================================================================
const userSession = <?php echo json_encode($userSession_php); ?>;
const CLIENT_DATA = <?php echo json_encode($CLIENT_DATA_php); ?>;
const REAL_INVOICES = <?php echo json_encode($invoices_php); ?>;
const REAL_TICKETS = <?php echo json_encode($tickets_db); ?>;


    // --- 2. CONFIGURACIÓN DE ROLES Y PERMISOS (Igual que antes) ---
    const ROLES = {
        ADMIN: 'Administrador',
        CLIENTE: 'Cliente', 
        SOPORTE: 'Soporte', 
        INVITADO: 'Invitado'
    };

    // Define qué opciones de menú ve cada rol
    const MENU_OPTIONS = {
        [ROLES.CLIENTE]: [
            // Menú específico para el rol de CLIENTE
            { id: 'dashboard', name: 'Mi Conexión', icon: 'zap', view: 'renderClientDashboard' },
            { id: 'billing', name: 'Facturación', icon: 'credit-card', view: 'renderClientBilling' },
            { id: 'support', name: 'Soporte', icon: 'life-buoy', view: 'renderClientSupport' },
            { id: 'reports', name: 'Reportes de Uso', icon: 'bar-chart', view: 'renderGenericPlaceholder' }
        ],
        // El resto de roles no tienen menú visible si no son CLIENTE
    };

    // --- (YA NO NECESITAMOS LA SIMULACIÓN DE DATOS, FUE BORRADA) ---
    
    // --- 3. FUNCIONES DE RENDERIZADO (Usan los datos inyectados) ---

    function renderAccessDenied() {
        // (Esta función queda igual que en tu código original)
        document.getElementById('main-title').textContent = "Acceso Denegado";
        const html = `
            <div class="bg-status-red/20 border border-status-red p-12 rounded-xl shadow-neon/20 text-center mx-auto max-w-lg mt-16">
                <i data-lucide="shield-off" class="w-16 h-16 mx-auto text-status-red mb-4"></i>
                <h3 class="text-3xl font-bold text-status-red mb-2">Acceso Restringido</h3>
                <p class="text-lg text-gray-300 mb-6">
                    Tu rol actual (<span class="font-bold text-white">${userSession.currentRole}</span>) no tiene permisos para acceder al Dashboard de Clientes.
                </p>
                <button onclick="alertMessage('Soporte', 'Contactando a soporte...', 'info')" class="mt-6 p-3 font-semibold rounded-xl bg-status-red text-white transition duration-300 hover:bg-red-700">
                    Contactar
                </button>
            </div>
        `;
        document.getElementById('content-area').innerHTML = html;
        document.getElementById('sidebar-menu').innerHTML = '';
        lucide.createIcons();
    }


    // Vista 1: Dashboard Principal (Mi Conexión)
    function renderClientDashboard() {
        document.getElementById('main-title').textContent = "Mi Conexión y Resumen";
        
        // ¡Estos datos ahora vienen de CLIENT_DATA (que fue llenado por PHP)!
        const serviceStatusColor = CLIENT_DATA.service.status.includes('Estable') ? 'text-status-green' : 'text-status-red';
        const invoiceStatusBg = CLIENT_DATA.invoice.status.includes('Pendiente') ? 'bg-status-red' : 'bg-status-green';

        const html = `
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                
                <div class="info-card bg-koline-medium p-6 rounded-xl border-l-4 border-koline-primary shadow-neon/20 lg:col-span-2">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-xl font-bold text-white flex items-center">
                            <i data-lucide="zap" class="w-6 h-6 mr-2 text-koline-primary"></i> Estado de la Conexión
                        </h4>
                        <span class="px-3 py-1 text-sm font-semibold rounded-full ${serviceStatusColor} bg-white/10 border border-current">${CLIENT_DATA.service.status}</span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 border-t border-gray-700 pt-4">
                        <div>
                            <p class="text-sm text-gray-400">Equipo Asignado</p>
                            <p class="text-lg font-semibold text-koline-primary">${CLIENT_DATA.service.plan}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Velocidad Máxima</p>
                            <p class="text-lg font-semibold text-white">${CLIENT_DATA.service.speed}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">IP (Simulada)</p>
                            <p class="text-lg font-semibold text-white">${CLIENT_DATA.service.ip}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Cliente Desde</p>
                            <p class="text-lg font-semibold text-white">${CLIENT_DATA.service.since}</p>
                        </div>
                    </div>
                    <button class="mt-5 w-full p-3 font-semibold rounded-xl bg-koline-primary/10 text-koline-primary border border-koline-primary/50 transition duration-300 hover:bg-koline-primary/20">
                        <i data-lucide="monitor-dot" class="w-5 h-5 inline mr-2"></i> Ejecutar Test de Velocidad
                    </button>
                </div>

                <div class="info-card bg-koline-medium p-6 rounded-xl border-t-4 border-status-orange shadow-neon/20">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-xl font-bold text-white flex items-center">
                            <i data-lucide="file-text" class="w-6 h-6 mr-2 text-status-orange"></i> Última Factura
                        </h4>
                        <span class="px-3 py-1 text-xs font-bold rounded-full text-white ${invoiceStatusBg}">${CLIENT_DATA.invoice.status.split(' ')[0]}</span>
                    </div>
                    
                    <p class="text-4xl font-extrabold text-white">${CLIENT_DATA.invoice.amount}</p>
                    <p class="text-sm text-gray-400 mt-1">Vence: <span class="text-status-red font-semibold">${CLIENT_DATA.invoice.dueDate}</span></p>

                    <button class="mt-5 w-full p-3 font-semibold rounded-xl bg-status-red text-white transition duration-300 hover:bg-red-700 shadow-lg">
                        <i data-lucide="wallet" class="w-5 h-5 inline mr-2"></i> Pagar Ahora
                    </button>
                    <a href="#" onclick="navigate('billing', 'renderClientBilling'); return false;" class="mt-2 text-center block text-sm text-koline-primary hover:underline">Ver detalles y historial</a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <div class="info-card bg-koline-medium p-6 rounded-xl border-t-4 border-gray-600 shadow-neon/20 col-span-1 lg:col-span-2">
                    <h4 class="text-xl font-bold text-white mb-4 flex items-center">
                        <i data-lucide="trending-up" class="w-6 h-6 mr-2 text-status-green"></i> Consumo de Datos (Últimos 30 días)
                    </h4>
                    
                    <div class="bg-koline-dark/70 p-4 rounded-lg h-40 flex items-end justify-around border border-gray-700">
                        <div class="text-center"><div class="w-6 bg-koline-primary rounded-t-lg" style="height: 80%;"></div><span class="text-xs text-gray-500 mt-1 block">Sem 1</span></div>
                        <div class="text-center"><div class="w-6 bg-koline-primary rounded-t-lg" style="height: 60%;"></div><span class="text-xs text-gray-500 mt-1 block">Sem 2</span></div>
                        <div class="text-center"><div class="w-6 bg-koline-primary rounded-t-lg" style="height: 90%;"></div><span class="text-xs text-gray-500 mt-1 block">Sem 3</span></div>
                        <div class="text-center"><div class="w-6 bg-koline-primary rounded-t-lg" style="height: 50%;"></div><span class="text-xs text-gray-500 mt-1 block">Sem 4</span></div>
                    </div>
                    <div class="text-center mt-3 text-lg font-semibold text-white">
                        Consumo Total: <span class="text-koline-primary">${CLIENT_DATA.usage.currentMonthGB} GB</span>
                    </div>
                </div>

                <div class="info-card bg-koline-medium p-6 rounded-xl border-t-4 border-status-red shadow-neon/20">
                    <h4 class="text-xl font-bold text-white mb-4 flex items-center">
                        <i data-lucide="life-buoy" class="w-6 h-6 mr-2 text-status-red"></i> ¿Necesitas Ayuda?
                    </h4>
                    <p class="text-sm text-gray-300 mb-4">Si tienes problemas de conexión o facturación, crea un ticket de soporte.</p>
                    <button onclick="navigate('support', 'renderClientSupport');" class="w-full p-3 font-semibold rounded-xl bg-status-red text-white transition duration-300 hover:bg-red-700 shadow-lg flex items-center justify-center">
                        <i data-lucide="ticket" class="w-5 h-5 inline mr-2"></i> Crear Nuevo Ticket
                    </button>
                </div>
            </div>
        `;
        document.getElementById('content-area').innerHTML = html;
        lucide.createIcons();
    }
    
    // Vista 2: Facturación
    function renderClientBilling() {
        document.getElementById('main-title').textContent = "Facturación y Pagos";

        // ¡MODIFICADO! Usa la variable inyectada desde PHP
        const invoices = REAL_INVOICES; 
        
        const invoiceList = invoices.map(inv => `
            <div class="flex justify-between items-center p-4 bg-koline-dark/50 rounded-lg mb-2 border-l-4 border-${inv.color}">
                <div class="text-sm">
                    <p class="font-semibold text-white">Factura ${inv.id}</p>
                    <p class="text-gray-400">Emitida: ${inv.date} | Vencimiento: ${inv.due}</p>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-lg font-bold text-koline-primary">${inv.amount}</span>
                    <span class="px-3 py-1 text-xs font-bold rounded-full text-white bg-${inv.color}">${inv.status}</span>
                    <button class="text-koline-primary hover:text-white p-1 rounded-full hover:bg-koline-primary/10 transition duration-200" title="Descargar PDF">
                        <i data-lucide="download" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
        `).join('');

        const html = `
            <div class="bg-koline-medium p-6 rounded-xl shadow-neon/20">
                <h3 class="text-xl font-bold text-koline-primary mb-4 border-b border-gray-600 pb-3">Historial de Pagos</h3>
                ${invoiceList}
                <div class="mt-6 text-center">
                    <button class="p-3 font-semibold rounded-xl bg-koline-primary text-koline-dark transition duration-300 hover:opacity-90 shadow-neon">
                        <i data-lucide="file-check" class="w-5 h-5 inline mr-2"></i> Ver Métodos de Pago
                    </button>
                </div>
            </div>
        `;
        document.getElementById('content-area').innerHTML = html;
        lucide.createIcons();
    }
    
    // Vista 3: Soporte y Reporte de Problemas
    function renderClientSupport() {
        document.getElementById('main-title').textContent = "Soporte Técnico";

        // ¡MODIFICADO! Esta función ahora construye la lista de tickets reales
        const ticketsListHtml = buildTicketList();

        const html = `
            <div class="bg-koline-medium p-6 rounded-xl shadow-neon/20">
                <h3 class="text-xl font-bold text-koline-primary mb-4 border-b border-gray-600 pb-3">Reportar un Problema</h3>
                
                <div class="space-y-4">
                    <div>
                        <label for="support-category" class="block text-sm font-medium text-gray-300 mb-1">Categoría del Problema</label>
                        <select id="support-category" class="w-full p-3 rounded-xl bg-koline-dark border border-gray-600 text-white focus:ring-koline-primary focus:border-koline-primary">
                            <option value="connection">Problemas de Conexión / Internet Lento</option>
                            <option value="billing">Dudas o Problemas de Facturación</option>
                            <option value="equipment">Fallo de Equipo (Router/ONT)</option>
                            <option value="other">Otro</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="support-details" class="block text-sm font-medium text-gray-300 mb-1">Detalles del Problema</label>
                        <textarea id="support-details" rows="4" placeholder="Describa el problema lo más detallado posible (ej: la luz PON está roja)."
                            class="w-full p-3 rounded-xl bg-koline-dark border border-gray-600 text-white focus:ring-koline-primary focus:border-koline-primary placeholder-gray-400"></textarea>
                    </div>
                    
                    <button id="submit-ticket-button" class="w-full p-3 font-semibold rounded-xl bg-koline-primary text-koline-dark transition duration-300 hover:opacity-90 shadow-neon">
                        <i data-lucide="send" class="w-5 h-5 inline mr-2"></i> Enviar Ticket de Soporte
                    </button>
                </div>

                <div class="mt-8 border-t border-gray-700 pt-6">
                    <h3 class="text-xl font-bold text-white mb-3 flex items-center"><i data-lucide="ticket" class="w-6 h-6 mr-2 text-koline-primary"></i> Mis Tickets Abiertos</h3>
                    ${ticketsListHtml}
                </div>
            </div>
        `;
        document.getElementById('content-area').innerHTML = html;
        lucide.createIcons();

        // Simulación de envío de ticket (Esto debería llamar a un PHP con fetch)
        document.getElementById('submit-ticket-button').addEventListener('click', () => {
            alertMessage("Ticket Enviado", "Tu ticket ha sido creado (ID: KLT-4502). Un agente te atenderá pronto.", "success");
        });
    }

    // --- 4. NUEVA FUNCIÓN AUXILIAR (Para mostrar tickets) ---
    function buildTicketList() {
        if (REAL_TICKETS.length === 0) {
            return '<p class="text-sm text-gray-400">No hay tickets abiertos actualmente.</p>';
        }
        
        // Usamos .map() para convertir cada objeto de ticket (de la BD) en HTML
        return REAL_TICKETS.map(ticket => {
            const statusColor = (ticket.estado === 'Abierto' || ticket.estado === 'En Proceso') ? 'text-koline-primary' : 'text-status-green';
            // Formatear la fecha (ej: 15/11/2025)
            const fecha = new Date(ticket.fecha_creacion).toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
            
            return `
            <div class="flex justify-between items-center p-3 bg-koline-dark/50 rounded-lg mb-2 border-l-4 border-koline-primary">
                <div class="text-sm">
                    <p class="font-semibold text-white">${ticket.titulo}</p>
                    <p class="text-gray-400">ID: KLT-${ticket.id} | Estado: <span class="font-bold ${statusColor}">${ticket.estado}</span></p>
                </div>
                <span class="text-xs text-gray-500">${fecha}</span>
            </div>
            `;
        }).join('');
    }

    // --- (El resto de funciones de tu script van aquí, sin cambios) ---

    // Función de renderizado genérica (Placeholder)
    function renderGenericPlaceholder(title = "Vista Placeholder", content = "Esta es una vista genérica.") {
        document.getElementById('main-title').textContent = title;
        const html = `
            <div class="bg-koline-medium p-8 rounded-xl shadow-neon/20">
                <p class="text-lg text-gray-300">${content}</p>
            </div>
        `;
        document.getElementById('content-area').innerHTML = html;
        lucide.createIcons();
    }

    // --- LÓGICA DE NAVEGACIÓN Y PERMISOS ---
    
    function loadSidebar() {
        const sidebar = document.getElementById('sidebar-menu');
        sidebar.innerHTML = '';
        const roleMenu = MENU_OPTIONS[userSession.currentRole];
        
        if (!roleMenu) {
            renderGenericPlaceholder("Error de Acceso", "Tu rol no tiene un menú definido. Contacta al administrador.");
            return;
        }

        roleMenu.forEach(item => {
            const div = document.createElement('div');
            div.className = 'sidebar-item text-gray-300 hover:text-white';
            div.id = `nav-${item.id}`;
            div.dataset.view = item.view;
            div.innerHTML = `
                <i data-lucide="${item.icon}" class="w-5 h-5 mr-3"></i>
                <span>${item.name}</span>
            `;
            
            div.addEventListener('click', () => {
                navigate(item.id, item.view);
            });
            
            sidebar.appendChild(div);
        });
        
        lucide.createIcons();
    }

    function navigate(viewId, renderFunction) {
        document.querySelectorAll('.sidebar-item').forEach(item => {
            item.classList.remove('active');
        });
        
        const navItem = document.getElementById(`nav-${viewId}`);
        if (navItem) {
            navItem.classList.add('active');
        }

        if (typeof window[renderFunction] === 'function') {
            window[renderFunction]();
        } else {
            renderGenericPlaceholder("Error de Vista", `No se encontró la función de renderizado para: ${renderFunction}`);
        }
    }
    
    function initializeApp() {
        lucide.createIcons(); 
        
        // 1. Mostrar datos de usuario (¡Reales!)
        document.getElementById('user-display-name').textContent = userSession.name;
        document.getElementById('user-role').textContent = userSession.currentRole;

        // 2. VALIDACIÓN DE ACCESO
        if (userSession.currentRole !== ROLES.CLIENTE) {
            renderAccessDenied();
            return; 
        }

        // 3. Cargar el menú lateral
        loadSidebar();

        // 4. Cargar la vista por defecto
        const roleMenu = MENU_OPTIONS[userSession.currentRole];
        if (roleMenu && roleMenu.length > 0) {
            const defaultView = roleMenu[0];
            navigate(defaultView.id, defaultView.view);
        }
    }

    // --- EVENTOS Y PUNTO DE ENTRADA ---
    
    window.onload = initializeApp;

    document.getElementById('logout-button').addEventListener('click', () => {
        alertMessage("Sesión Cerrada", "Has cerrado sesión correctamente. Simulación de redirección.", "info");
        // En un futuro: window.location.href = 'logout.php';
    });
    
    // Función para mostrar mensajes (igual que antes)
    function alertMessage(title, message, type) {
        console.log(`[${type.toUpperCase()}] ${title}: ${message}`);
        
        const messageContainer = document.createElement('div');
        messageContainer.className = 'fixed top-4 right-4 z-[9999] p-4 rounded-xl shadow-2xl transition-all duration-300 transform translate-x-0 opacity-100';
        messageContainer.style.maxWidth = '90%';

        if (type === 'success') {
            messageContainer.classList.add('bg-status-green', 'text-white');
        } else if (type === 'error') {
            messageContainer.classList.add('bg-status-red', 'text-white');
        } else if (type === 'info') {
            messageContainer.classList.add('bg-blue-600', 'text-white');
        } else {
            messageContainer.classList.add('bg-gray-700', 'text-white');
        }

        messageContainer.innerHTML = `<p class="font-bold">${title}</p><p class="text-sm">${message}</p>`;
        document.body.appendChild(messageContainer);

        setTimeout(() => {
            messageContainer.classList.remove('opacity-100');
            messageContainer.classList.add('opacity-0', 'translate-x-full');
            setTimeout(() => {
                messageContainer.remove();
            }, 500); 
        }, 3000);
    }
</script>
</body>
</html>