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
    $sql = "SELECT u.*, r.nombre_rol 
            FROM usuarios u
            JOIN roles r ON u.id_rol = r.id_rol
            ORDER BY fecha_registro DESC LIMIT 5";
    $ultimos_usuarios = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

    // Últimos tickets
    $sql = "SELECT t.*, u.nombres, u.apellido_paterno 
            FROM tickets t
            JOIN clientes c ON t.id_cliente = c.id_cliente
            JOIN usuarios u ON c.id_usuario = u.id_usuario
            ORDER BY fecha_creacion DESC LIMIT 5";
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
:root {
    --bg1:#001f3f;
    --bg2:#0078ff;
    --accent:#00eaff;
    --glass: rgba(255,255,255,0.06);
}

body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg,var(--bg1),#004ea8,var(--bg2));
    margin: 0;
    color: #eaf6ff;
}

.wrap {
    max-width: 1200px;
    margin: 30px auto;
    display: grid;
    grid-template-columns: 260px 1fr;
    gap: 20px;
    padding: 20px;
}

/* ========== SIDEBAR ========== */
.sidebar {
    background: var(--glass);
    padding: 20px;
    border-radius: 15px;
    border: 1px solid rgba(255,255,255,0.1);
}

.sidebar img {
    width: 150px;
    display: block;
    margin: 0 auto 20px auto;
}

/* ICONO DEL USUARIO */
.user-box {
    text-align: center;
    margin-bottom: 25px;
}

.user-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 10px auto;
    background: rgba(255,255,255,0.12);
    border: 2px solid rgba(255,255,255,0.25);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    font-weight: bold;
    color: var(--accent);
    text-shadow: 0 0 10px rgba(0, 234, 255, 0.6);
    backdrop-filter: blur(4px);
}

.user-name {
    font-size: 16px;
    font-weight: 600;
    color: #eaf6ff;
    margin: 5px 0 0;
}

.user-role {
    font-size: 13px;
    opacity: 0.8;
    color: var(--accent);
}

.sidebar nav a {
    color: #eee;
    padding: 8px 0;
    display: block;
    text-decoration: none;
}
.sidebar nav a:hover {
    color: var(--accent);
}

.logout {
    margin-top: 20px;
    display: block;
    color: #ff99aa;
    text-decoration: none;
}

/* ========== CARDS ========== */
.card {
    background: var(--glass);
    padding: 20px;
    border-radius: 15px;
    flex: 1;
    border: 1px solid rgba(255,255,255,0.15);
}

.cards {
    display: flex;
    gap: 20px;
}

.card h3 {
    margin: 0;
    color: #bcdcff;
}

.card p {
    font-size: 32px;
    margin: 0;
    font-weight: bold;
}

/* ========== TABLAS ========== */
.panel {
    background: var(--glass);
    padding: 20px;
    border-radius: 15px;
    margin-top: 20px;
}

table {
    width: 100%;
    margin-top: 10px;
    border-collapse: collapse;
}

th {
    color: #99d6ff;
    padding: 10px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

td {
    padding: 10px;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

/* BADGES */
.badge {
    padding: 4px 8px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: bold;
}
.badge.admin { background: #ff3366; color: white; }
.badge.cliente { background: #00eaff; color: #003344; }
.badge.soporte { background: #ffaa00; color: #222; }

</style>
</head>

<body>
<div class="wrap">

<!-- ================= SIDEBAR ================= -->
<aside class="sidebar">

    <!-- 🔥 TU LOGO SE MANTIENE -->
    <img src="imagenes/logo.png" alt="KoLine Logo">

    <!-- 🔥 ICONO DEL USUARIO LOGEADO -->
    <div class="user-box">
        <div class="user-icon">
            <?= strtoupper(substr($_SESSION['nombre_usuario'], 0, 1)) ?>
        </div>
        <p class="user-name"><?= $_SESSION['nombre_usuario']; ?></p>
        <span class="user-role">Administrador</span>
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
</aside>

<!-- ================= MAIN ================= -->
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

    <!-- ================= Últimos Usuarios ================= -->
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

    <!-- ================= Últimos Tickets ================= -->
    <div class="panel">
        <h3>Últimos Tickets</h3>
        <table>
            <tr>
                <th>Título</th>
                <th>Cliente</th>
                <th>Prioridad</th>
                <th>Estado</th>
                <th>Fecha</th>
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
