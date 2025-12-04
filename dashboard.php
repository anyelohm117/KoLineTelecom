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
// (Misma lógica de PHP que tu código original)
$total_usuarios = 0;
$total_clientes = 0;
$total_tickets_abiertos = 0;
$ultimos_usuarios = [];
$ultimos_tickets = [];

try {
    $res = $conn->query("SELECT COUNT(*) FROM usuarios");
    $total_usuarios = $res->fetch_row()[0];

    $res = $conn->query("SELECT COUNT(*) FROM usuarios WHERE id_rol = 2 AND activo = 1");
    $total_clientes = $res->fetch_row()[0];

    $res = $conn->query("SELECT COUNT(*) FROM tickets WHERE estado = 'Abierto'");
    $total_tickets_abiertos = $res->fetch_row()[0];

    $sql = "SELECT u.*, r.nombre_rol FROM usuarios u JOIN roles r ON u.id_rol = r.id_rol ORDER BY fecha_registro DESC LIMIT 5";
    $ultimos_usuarios = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

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

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
/* =========================================
   🎨 PALETA DE COLORES (Basada en la Imagen)
   ========================================= */
:root {
    /* Fondo profundo (Deep Space Blue) */
    --bg-dark: #020c1b; 
    --bg-glow: #0a1f35;
    
    /* El Cyan Neón exacto de la imagen */
    --accent: #00eaff;
    --accent-hover: #00cce6;
    
    /* Efecto Cristal Oscuro */
    --glass-bg: rgba(13, 25, 40, 0.7); 
    --glass-border: rgba(0, 234, 255, 0.15); /* Borde sutil cyan */
    
    /* Textos */
    --text-main: #ffffff;
    --text-muted: #8899a6;
}

body {
    font-family: 'Poppins', sans-serif;
    /* Degradado Radial para imitar la iluminación de la imagen */
    background: radial-gradient(circle at top center, #0f3460 0%, var(--bg-dark) 80%);
    background-color: var(--bg-dark);
    background-attachment: fixed; /* Mantiene el fondo fijo al hacer scroll */
    margin: 0;
    color: var(--text-main);
    min-height: 100vh;
}

.wrap {
    max-width: 1200px;
    margin: 40px auto;
    display: grid;
    grid-template-columns: 260px 1fr;
    gap: 30px;
    padding: 20px;
}

/* ========== SIDEBAR ========== */
.sidebar {
    background: var(--glass-bg);
    backdrop-filter: blur(12px); /* Efecto difuminado detrás del cristal */
    -webkit-backdrop-filter: blur(12px);
    padding: 30px 20px;
    border-radius: 20px;
    border: 1px solid var(--glass-border);
    box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
    height: fit-content;
}

.sidebar img {
    width: 140px;
    display: block;
    margin: 0 auto 30px auto;
    /* Un pequeño brillo al logo */
    filter: drop-shadow(0 0 5px rgba(0,234,255,0.3));
}

/* ICONO DEL USUARIO */
.user-box {
    text-align: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.user-icon {
    width: 70px;
    height: 70px;
    margin: 0 auto 15px auto;
    background: rgba(0, 234, 255, 0.05);
    border: 2px solid var(--accent);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    font-weight: bold;
    color: var(--accent);
    /* Efecto Glow del neón */
    box-shadow: 0 0 15px rgba(0, 234, 255, 0.2); 
}

.user-name {
    font-size: 16px;
    font-weight: 600;
    color: var(--text-main);
    margin: 0;
}

.user-role {
    font-size: 12px;
    color: var(--text-muted);
    letter-spacing: 1px;
    text-transform: uppercase;
}

.sidebar nav a {
    color: var(--text-muted);
    padding: 12px 15px;
    display: block;
    text-decoration: none;
    border-radius: 10px;
    transition: all 0.3s ease;
    margin-bottom: 5px;
    font-size: 14px;
}

.sidebar nav a:hover {
    color: var(--bg-dark);
    background: var(--accent); /* Botón se vuelve Cyan al pasar el mouse */
    font-weight: 600;
    box-shadow: 0 0 15px rgba(0, 234, 255, 0.4);
}

.logout {
    margin-top: 30px;
    display: block;
    text-align: center;
    color: #ff5577; /* Un rojo rosado que combina con el tema oscuro */
    text-decoration: none;
    font-size: 14px;
    transition: 0.3s;
}
.logout:hover {
    color: #ff88aa;
    text-shadow: 0 0 8px rgba(255, 85, 119, 0.4);
}

/* ========== MAIN CONTENT ========== */
h1 {
    font-weight: 600;
    margin-top: 0;
    margin-bottom: 25px;
    text-shadow: 0 0 20px rgba(0, 234, 255, 0.1);
}

/* ========== CARDS ========== */
.cards {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
}

.card {
    background: linear-gradient(145deg, rgba(255,255,255,0.03) 0%, rgba(255,255,255,0.01) 100%);
    backdrop-filter: blur(10px);
    padding: 25px;
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

/* Barra decorativa superior en las cards */
.card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; width: 100%; height: 4px;
    background: var(--accent);
    box-shadow: 0 0 10px var(--accent);
}

.card h3 {
    margin: 10px 0 5px 0;
    font-size: 14px;
    color: var(--text-muted);
    font-weight: 400;
    text-transform: uppercase;
}

.card p {
    font-size: 36px;
    margin: 0;
    font-weight: 700;
    color: var(--text-main);
    text-shadow: 0 0 15px rgba(0, 234, 255, 0.3);
}

/* ========== TABLAS ========== */
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
    padding: 15px 10px;
    font-weight: 500;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

td {
    padding: 15px 10px;
    border-bottom: 1px solid rgba(255,255,255,0.03);
    color: #e0e0e0;
}

tr:last-child td {
    border-bottom: none;
}

tr:hover td {
    background: rgba(0, 234, 255, 0.03);
}

/* BADGES - Colores ajustados para fondo oscuro */
.badge {
    padding: 5px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}
.badge.admin { 
    background: rgba(255, 51, 102, 0.15); 
    color: #ff3366; 
    border: 1px solid rgba(255, 51, 102, 0.3);
}
.badge.cliente { 
    background: rgba(0, 234, 255, 0.15); 
    color: var(--accent); 
    border: 1px solid rgba(0, 234, 255, 0.3);
}
.badge.soporte { 
    background: rgba(255, 170, 0, 0.15); 
    color: #ffaa00; 
    border: 1px solid rgba(255, 170, 0, 0.3);
}

/* Responsive */
@media (max-width: 768px) {
    .wrap { grid-template-columns: 1fr; }
    .cards { flex-direction: column; }
    .sidebar { text-align: center; }
}

</style>
</head>

<body>
<div class="wrap">

<aside class="sidebar">
    <img src="imagenes/logo.png" alt="KoLine Logo">

    <div class="user-box">
        <div class="user-icon">
            <?= strtoupper(substr($_SESSION['nombre_usuario'], 0, 1)) ?>
        </div>
        <p class="user-name"><?= $_SESSION['nombre_usuario']; ?></p>
        <span class="user-role">Administrador</span>
    </div>

    <nav>
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="funciones/usuarios.php">👥 Usuarios</a>
        <a href="funciones/clientes.php">🛰 Clientes</a>
        <a href="funciones/tickets">🎫 Tickets</a>
        <a href="funciones/inventario.php">📦 Inventario</a>
        <a href="funciones/pagos.php">💰 Pagos</a>
        <a href="configuracion.php">⚙ Configuración</a>
    </nav>

    <a href="index.php" class="logout">← Cerrar sesión</a>
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
