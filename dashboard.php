<?php
session_start();
require 'db_con.php';

/* ============================================
   🔒 SEGURIDAD: SOLO ADMIN (Rol = 1)
============================================ */
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 1) {
    header("Location: index.php");
    exit();
}

/* ============================================
   📌 CONSULTAS PARA DASHBOARD
============================================ */
$total_usuarios = 0;
$total_clientes = 0;
$total_tickets_abiertos = 0;
$ultimos_usuarios = [];
$ultimos_tickets = [];

try {
    // Total de usuarios
    $res = $conn->query("SELECT COUNT(*) FROM usuarios");
    $total_usuarios = $res->fetch_row()[0];

    // Total clientes activos (Rol = 2)
    $res = $conn->query("SELECT COUNT(*) FROM usuarios WHERE id_rol = 2 AND activo = 1");
    $total_clientes = $res->fetch_row()[0];

    // Tickets abiertos
    $res = $conn->query("SELECT COUNT(*) FROM tickets WHERE estado = 'Abierto'");
    $total_tickets_abiertos = $res->fetch_row()[0];

    // Últimos usuarios registrados
    $sql = "SELECT u.*, r.nombre_rol FROM usuarios u JOIN roles r ON u.id_rol = r.id_rol ORDER BY fecha_registro DESC LIMIT 5";
    $ultimos_usuarios = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

    // Últimos tickets
    $sql = "SELECT t.*, u.nombres, u.apellido_paterno FROM tickets t JOIN clientes c ON t.id_cliente = c.id_cliente JOIN usuarios u ON c.id_usuario = u.id_usuario ORDER BY fecha_creacion DESC LIMIT 5";
    $ultimos_tickets = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Dashboard KoLine</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
/* =========================================
   🎨 PALETA DE COLORES (Clean & Professional)
   ========================================= */
:root {
    --bg-body: #f3f4f6;       /* Gris muy claro de fondo */
    --bg-card: #ffffff;       /* Blanco puro para tarjetas */
    --sidebar-bg: #ffffff;
    
    --primary: #2563eb;       /* Azul Rey (Profesional) */
    --primary-hover: #1d4ed8;
    
    --text-dark: #111827;     /* Negro suave */
    --text-gray: #6b7280;     /* Gris para textos secundarios */
    
    --border-color: #e5e7eb;  /* Bordes sutiles */
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

body {
    font-family: 'Inter', sans-serif;
    background-color: var(--bg-body);
    margin: 0;
    color: var(--text-dark);
    min-height: 100vh;
}

.layout {
    display: flex;
    min-height: 100vh;
}

/* ================= SIDEBAR (Lateral) ================= */
.sidebar {
    width: 260px;
    background: var(--sidebar-bg);
    border-right: 1px solid var(--border-color);
    padding: 24px;
    display: flex;
    flex-direction: column;
    position: fixed; /* Fijo a la izquierda */
    height: 100%;
    overflow-y: auto;
    z-index: 10;
}

/* PERFIL DE USUARIO (Estilo Menú Desplegable) */
.user-profile {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    border-radius: 12px;
    background: #f9fafb;
    border: 1px solid var(--border-color);
    cursor: pointer;
    transition: all 0.2s ease;
    margin-bottom: 24px; /* Separación del menú */
}

.user-profile:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
}

.user-avatar {
    width: 40px;
    height: 40px;
    background: var(--primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 16px;
}

.user-details {
    display: flex;
    flex-direction: column;
}

.user-name {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-dark);
}

.user-role {
    font-size: 12px;
    color: var(--text-gray);
    font-weight: 500;
}

/* NAVEGACIÓN */
.nav-links {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.nav-item {
    display: flex;
    align-items: center;
    padding: 10px 12px;
    text-decoration: none;
    color: var(--text-gray);
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s;
}

.nav-item:hover {
    background-color: #f3f4f6;
    color: var(--text-dark);
}

.nav-item.active {
    background-color: #eff6ff; /* Azul muy suave */
    color: var(--primary);
    font-weight: 600;
}

.logout-btn {
    margin-top: auto; /* Empuja al fondo */
    padding: 12px 0;
    color: #ef4444; /* Rojo suave */
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
}
.logout-btn:hover { text-decoration: underline; }

/* LOGO SIDEBAR */
.brand {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid var(--border-color);
    text-align: center;
}
.brand img {
    height: 30px;
    opacity: 0.8;
}

/* ================= CONTENIDO PRINCIPAL ================= */
.main-content {
    flex: 1;
    margin-left: 260px; /* Mismo ancho que sidebar */
    padding: 40px;
}

.header-title {
    margin-top: 0;
    margin-bottom: 32px;
    font-size: 24px;
    font-weight: 700;
    color: var(--text-dark);
}

/* TARJETAS KPI */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 24px;
    margin-bottom: 40px;
}

.kpi-card {
    background: var(--bg-card);
    padding: 24px;
    border-radius: 16px;
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
    transition: transform 0.2s, box-shadow 0.2s;
}

.kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.kpi-label {
    font-size: 14px;
    color: var(--text-gray);
    font-weight: 500;
    margin-bottom: 8px;
    display: block;
}

.kpi-value {
    font-size: 32px;
    font-weight: 700;
    color: var(--text-dark);
    margin: 0;
}

/* TABLAS */
.section-card {
    background: var(--bg-card);
    border-radius: 16px;
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
    margin-bottom: 32px;
    overflow: hidden; /* Para los bordes redondeados */
}

.section-header {
    padding: 20px 24px;
    border-bottom: 1px solid var(--border-color);
    background: #f9fafb;
}

.section-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: var(--text-dark);
}

.table-responsive {
    width: 100%;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

th {
    text-align: left;
    padding: 12px 24px;
    background: #f9fafb;
    color: var(--text-gray);
    font-weight: 500;
    text-transform: uppercase;
    font-size: 12px;
    letter-spacing: 0.05em;
    border-bottom: 1px solid var(--border-color);
}

td {
    padding: 16px 24px;
    border-bottom: 1px solid var(--border-color);
    color: var(--text-dark);
}

tr:last-child td { border-bottom: none; }
tr:hover td { background-color: #f9fafb; }

/* BADGES (Etiquetas) */
.badge {
    padding: 4px 10px;
    border-radius: 9999px; /* Píldora */
    font-size: 12px;
    font-weight: 600;
}

.badge.admin {
    background-color: #fee2e2;
    color: #ef4444;
}
.badge.cliente {
    background-color: #dbeafe;
    color: #2563eb;
}
.badge.soporte {
    background-color: #fef3c7;
    color: #d97706;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .sidebar { display: none; } /* Ocultar sidebar en móvil (se requeriría un botón hamburguesa) */
    .main-content { margin-left: 0; padding: 20px; }
    .kpi-grid { grid-template-columns: 1fr; }
}
</style>
</head>

<body>
<div class="layout">

    <aside class="sidebar">
        <div class="user-profile">
            <div class="user-avatar">
                <?= strtoupper(substr($_SESSION['nombre_usuario'], 0, 1)) ?>
            </div>
            <div class="user-details">
                <span class="user-name"><?= $_SESSION['nombre_usuario'] ?></span>
                <span class="user-role">Administrador</span>
            </div>
        </div>

        <nav class="nav-links">
            <a href="#" class="nav-item active">📊 Dashboard</a>
            <a href="#" class="nav-item">👥 Usuarios</a>
            <a href="#" class="nav-item">🛰 Clientes</a>
            <a href="#" class="nav-item">🎫 Tickets</a>
            <a href="#" class="nav-item">📦 Inventario</a>
            <a href="#" class="nav-item">💰 Pagos</a>
            <a href="#" class="nav-item">⚙ Configuración</a>
        </nav>

        <a href="index.php" class="logout-btn">← Cerrar sesión</a>
        
        <div class="brand">
            <img src="imagenes/logo.png" alt="KoLine">
        </div>
    </aside>

    <main class="main-content">
        <h1 class="header-title">Resumen General</h1>

        <div class="kpi-grid">
            <div class="kpi-card">
                <span class="kpi-label">Total Usuarios</span>
                <p class="kpi-value"><?= $total_usuarios ?></p>
            </div>
            <div class="kpi-card">
                <span class="kpi-label">Clientes Activos</span>
                <p class="kpi-value" style="color: #2563eb;"><?= $total_clientes ?></p>
            </div>
            <div class="kpi-card">
                <span class="kpi-label">Tickets Abiertos</span>
                <p class="kpi-value" style="color: #ef4444;"><?= $total_tickets_abiertos ?></p>
            </div>
        </div>

        <div class="section-card">
            <div class="section-header">
                <h3>Últimos Registros</h3>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($ultimos_usuarios as $u): ?>
                        <tr>
                            <td>
                                <div style="font-weight:500;"><?= $u['nombres'] . " " . $u['apellido_paterno'] ?></div>
                            </td>
                            <td style="color:#6b7280;"><?= $u['email'] ?></td>
                            <td>
                                <?php 
                                    if ($u['id_rol'] == 1) echo "<span class='badge admin'>Admin</span>";
                                    elseif ($u['id_rol'] == 2) echo "<span class='badge cliente'>Cliente</span>";
                                    else echo "<span class='badge soporte'>Soporte</span>";
                                ?>
                            </td>
                            <td style="color:#6b7280;"><?= date("d M, Y", strtotime($u['fecha_registro'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="section-card">
            <div class="section-header">
                <h3>Tickets Recientes</h3>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Asunto</th>
                            <th>Cliente</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($ultimos_tickets as $t): ?>
                        <tr>
                            <td style="font-weight:500;"><?= $t['titulo'] ?></td>
                            <td><?= $t['nombres'] . " " . $t['apellido_paterno'] ?></td>
                            <td><?= $t['prioridad'] ?></td>
                            <td>
                                <span style="font-weight:600; color: #d97706;"><?= $t['estado'] ?></span>
                            </td>
                            <td style="color:#6b7280;"><?= date("d M, Y", strtotime($t['fecha_creacion'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>
</body>
</html>
