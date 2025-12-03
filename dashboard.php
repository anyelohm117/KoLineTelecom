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
    --bg1:#0a1d37;
    --bg2:#112240;
    --accent:#00eaff;
    --glass: rgba(255,255,255,0.04);
}

body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg,var(--bg1),var(--bg2));
    margin: 0;
    color: #eaf6ff;
}

/* Layout */
.wrap {
    max-width: 1200px;
    margin: 30px auto;
    display: grid;
    grid-template-columns: 260px 1fr;
    gap: 20px;
    padding: 20px;
}

/* Sidebar */
.sidebar {
    background: var(--glass);
    padding: 20px;
    border-radius: 15px;
    border: 1px solid rgba(0,234,255,0.15);
}
.sidebar h2 {
    margin: 0;
    color: var(--accent);
    font-weight: 700;
}
.sidebar nav a {
    color: #dceeff;
    padding: 8px 0;
    display: block;
    text-decoration: none;
    font-weight: 500;
}
.sidebar nav a:hover {
    color: var(--accent);
}
.logout {
    margin-top: 20px;
    display: block;
    color: #ff99aa;
    text-decoration: none;
    font-weight: 600;
}

/* Tarjetas */
.cards {
    display: flex;
    gap: 20px;
}
.card {
    background: var(--glass);
    padding: 20px;
    border-radius: 15px;
    flex: 1;
    border: 1px solid rgba(0,234,255,0.15);
    backdrop-filter: blur(6px);
}
.card h3 {
    margin: 0;
    color: var(--accent);
}
.card p {
    font-size: 32px;
    margin: 0;
    font-weight: bold;
}

/* Paneles */
.panel {
    background: var(--glass);
    padding: 20px;
    border-radius: 15px;
    margin-top: 20px;
    border: 1px solid rgba(0,234,255,0.15);
}

/* Tablas */
table {
    width: 100%;
    border-collapse: collapse;
}
th {
    color: var(--accent);
    padding: 10px;
    border-bottom: 1px solid rgba(255,255,255,0.15);
}
td {
    padding: 10px;
    border-bottom: 1px solid rgba(255,255,255,0.07);
}

/* Badges */
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

<!-- SIDEBAR -->
<aside class="sidebar">
    <h2>KoLine Admin</h2>
    <p>Bienvenido<br><strong><?= $_SESSION['nombre_usuario']; ?></strong></p>

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


<!-- CONTENIDO -->
<main>
    <h1 style="color: var(--accent);">Panel de Control</h1>

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

    <!-- Últimos usuarios -->
    <div class="panel">
        <h3 style="color: var(--accent);">Últimos Usuarios Registrados</h3>
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

    <!-- Últimos tickets -->
    <div class="panel">
        <h3 style="color: var(--accent);">Últimos Tickets</h3>
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
