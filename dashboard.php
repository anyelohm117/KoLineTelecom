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
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
/* =========================================
   🎨 PALETA DE COLORES (Cyberpunk / Glass)
   ========================================= */
:root {
    --bg-dark: #020c1b; 
    --accent: #00eaff;         /* Cyan Neón */
    --accent-hover: #00cce6;
    --glass-bg: rgba(13, 25, 40, 0.85); 
    --glass-border: rgba(0, 234, 255, 0.15); 
    --text-main: #ffffff;
    --text-muted: #94a3b8;
}

body {
    font-family: 'Poppins', sans-serif;
    background: radial-gradient(circle at top center, #0f3460 0%, var(--bg-dark) 80%);
    background-color: var(--bg-dark);
    background-attachment: fixed;
    margin: 0;
    color: var(--text-main);
    min-height: 100vh;
}

.wrap {
    max-width: 1200px;
    margin: 30px auto;
    display: grid;
    grid-template-columns: 260px 1fr;
    gap: 30px;
    padding: 20px;
}

/* ========== SIDEBAR ========== */
.sidebar {
    background: var(--glass-bg);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    padding: 20px;
    border-radius: 20px;
    border: 1px solid var(--glass-border);
    box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
    /* Flexbox para ordenar elementos verticalmente */
    display: flex;
    flex-direction: column;
    gap: 15px;
    height: fit-content;
    min-height: 500px; /* Altura mínima para que se vea bien */
}

/* --- 1. USUARIO (Estilo Horizontal Superior) --- */
.user-box {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.05);
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 10px;
}

.user-box:hover {
    background: rgba(0, 234, 255, 0.08);
    border-color: rgba(0, 234, 255, 0.3);
    box-shadow: 0 0 15px rgba(0, 234, 255, 0.1);
}

.user-icon {
    width: 40px;
    height: 40px;
    min-width: 40px; /* Evita que se aplaste */
    background: rgba(0, 234, 255, 0.1);
    border: 2px solid var(--accent);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    font-weight: 700;
    color: var(--accent);
    box-shadow: 0 0 8px rgba(0, 234, 255, 0.2);
}

.user-info {
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.user-name {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-main);
    margin: 0;
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
}

.user-role {
    font-size: 11px;
    color: var(--text-muted);
    font-weight: 500;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

/* --- 2. NAVEGACIÓN --- */
.sidebar nav {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.sidebar nav a {
    color: var(--text-muted);
    padding: 10px 12px;
    display: block;
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.2s ease;
    font-size: 14px;
}

.sidebar nav a:hover {
    background: rgba(255, 255, 255, 0.05);
    color: #fff;
    padding-left: 18px; /* Efecto de desplazamiento */
    border-left: 3px solid var(--accent);
}

.logout {
    margin-top: 5px;
    padding-left: 12px;
    color: #ff5577;
    text-decoration: none;
    font-size: 13px;
    transition: 0.3s;
    display: block;
}
.logout:hover {
    color: #ff88aa;
}

/* --- 3. LOGO (Al fondo) --- */
.sidebar-logo {
    margin-top: auto; /* Empuja el logo al final */
    padding-top: 20px;
    border-top: 1px solid rgba(255,255,255,0.05);
    text-align: center;
}
.sidebar-logo img {
    width: 100px;
    opacity: 0.6;
    transition: 0.3s;
    filter: drop-shadow(0 0 5px rgba(0,234,255,0.2));
}
.sidebar-logo img:hover { opacity: 1; }

/* ========== CONTENIDO PRINCIPAL ========== */
h1 {
    font-weight: 600;
    margin-top: 0;
    margin-bottom: 25px;
    font-size: 24px;
}

/* CARDS */
.cards {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
}

.card {
    background: linear-gradient(145deg, rgba(255,255,255,0.03) 0%, rgba(255,255,255,0.01) 100%);
    backdrop-filter: blur(10px);
    padding: 20px;
    border-radius: 16px;
    flex: 1;
    border: 1px solid var(--glass-border);
    position: relative;
    overflow: hidden;
    transition: transform 0.3s ease;
}
.card:hover {
    transform: translateY(-5px);
    border-color: var(--accent);
}
.card::before { /* Barra superior cyan */
    content: '';
    position: absolute;
    top: 0; left: 0; width: 100%; height: 3px;
    background: var(--accent);
    box-shadow: 0 0 10px var(--accent);
}
.card h3 {
    margin: 5px 0;
    font-size: 13px;
    color: var(--text-muted);
    font-weight: 500;
    text-transform: uppercase;
}
.card p {
    font-size: 32px;
    margin: 0;
    font-weight: 700;
    color: var(--text-main);
    text-shadow: 0 0 15px rgba(0, 234, 255, 0.3);
}

/* TABLAS */
.panel {
    background: var(--glass-bg);
    backdrop-filter: blur(12px);
    padding: 25px;
    border-radius: 20px;
    margin-top: 25px;
    border: 1px solid var(--glass-border);
}
.panel h3 {
    margin-top: 0;
    color: var(--accent);
    font-weight: 500;
    font-size: 18px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    padding-bottom: 15px;
}
table {
    width: 100%;
    margin-top: 10px;
    border-collapse: collapse;
    font-size: 14px;
}
th {
    text-align: left;
    color: var(--text-muted);
    padding: 12px 10px;
    font-weight: 500;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}
td {
    padding: 12px 10px;
    border-bottom: 1px solid rgba(255,255,255,0.03);
    color: #e0e0e0;
}
tr:hover td { background: rgba(0, 234, 255, 0.03); }

/* BADGES */
.badge {
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}
.badge.admin { 
    background: rgba(255, 51, 102, 0.15); color: #ff3366; 
    border: 1px solid rgba(255, 51, 102, 0.3);
}
.badge.cliente { 
    background: rgba(0, 234, 255, 0.15); color: var(--accent); 
    border: 1px solid rgba(0, 234, 255, 0.3);
}
.badge.soporte { 
    background: rgba(255, 170, 0, 0.15); color: #ffaa00; 
    border: 1px solid rgba(255, 170, 0, 0.3);
}

/* RESPONSIVE */
@media (max-width: 800px) {
    .wrap { grid-template-columns: 1fr; margin: 10px; }
    .cards { flex-direction: column; }
    .sidebar { min-height: auto; margin-bottom: 20px; }
    .sidebar-logo { display: none; } /* Ocultar logo en móvil si molesta */
}
</style>
</head>

<body>
<div class="wrap">

<aside class="sidebar">

    <div class="user-box" onclick="alert('Aquí iría al perfil...')" title="Ver Perfil">
        <div class="user-icon">
            <?= strtoupper(substr($_SESSION['nombre_usuario'], 0, 1)) ?>
        </div>
        
        <div class="user-info">
            <p class="user-name"><?= $_SESSION['nombre_usuario']; ?></p>
            <span class="user-role">Administrador</span>
        </div>
    </div>

    <nav>
        <a href="#">📊 Dashboard</a>
        <a href="#">👥 Usuarios</a>
        <a href="#">🛰 Clientes</a>
        <a href="#">🎫 Tickets</a>
        <a href="#">📦 Inventario</a>
        <a href="#">💰 Pagos</a>
        <a href="#">⚙ Configuración</a>
    </nav>

    <a href="index.php" class="logout">← Cerrar sesión</a>

    <div class="sidebar-logo">
        <img src="imagenes/logo.png" alt="KoLine Logo">
    </div>

</aside>

<main>
    <h1>Panel de Control</h1>

    <div class="cards">
        <div class="card">
            <h3>Total Usuarios</h3>
            <p><?= $total_usuarios ?></p>
        </div>
        <div class="card">
            <h3>Clientes Activos</h3>
            <p><?= $total_clientes ?></p>
        </div>
        <div class="card">
            <h3>Tickets Abiertos</h3>
            <p><?= $total_tickets_abiertos ?></p>
        </div>
    </div>

    <div class="panel">
        <h3>Últimos Usuarios Registrados</h3>
        <table>
            <tr>
                <th>Nombre</th><th>Email</th><th>Rol</th><th>Fecha</th>
            </tr>
            <?php foreach($ultimos_usuarios as $u): ?>
            <tr>
                <td><?= $u['nombres'] . " " . $u['apellido_paterno'] ?></td>
                <td><?= $u['email'] ?></td>
                <td>
                    <?php 
                        if ($u['id_rol'] == 1) echo "<span class='badge admin'>Admin</span>";
                        elseif ($u['id_rol'] == 2) echo "<span class='badge cliente'>Cliente</span>";
                        else echo "<span class='badge soporte'>Soporte</span>";
                    ?>
                </td>
                <td><?= date("d/m/Y", strtotime($u['fecha_registro'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="panel">
        <h3>Últimos Tickets</h3>
        <table>
            <tr>
                <th>Título</th><th>Cliente</th><th>Prioridad</th><th>Estado</th><th>Fecha</th>
            </tr>
            <?php foreach($ultimos_tickets as $t): ?>
            <tr>
                <td><?= $t['titulo'] ?></td>
                <td><?= $t['nombres'] . " " . $t['apellido_paterno'] ?></td>
                <td><?= $t['prioridad'] ?></td>
                <td><?= $t['estado'] ?></td>
                <td><?= date("d/m/Y", strtotime($t['fecha_creacion'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

</main>
</div>

</body>
</html>
