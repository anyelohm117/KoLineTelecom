<?php
session_start();
require 'db_con.php';

/* ============================================
   🔒 SEGURIDAD: CONTROL DE ACCESO
============================================ */
// Permitimos Rol 1 (Admin) y Rol 3 (Soporte/Empleado)
// Si es Cliente (Rol 2) o no hay sesión, va para afuera.
if (!isset($_SESSION['id_usuario']) || ($_SESSION['rol'] != 1 && $_SESSION['rol'] != 3)) {
    header("Location: index.php");
    exit();
}

// Variable clave para controlar qué se muestra
$es_admin = ($_SESSION['rol'] == 1); 

/* ============================================
   📌 CONSULTAS DASHBOARD
============================================ */
$total_usuarios = 0;
$total_clientes = 0;
$total_tickets_abiertos = 0;
$ultimos_usuarios = [];
$ultimos_tickets = [];

try {
    // Consultas generales visibles para todos
    $res = $conn->query("SELECT COUNT(*) FROM usuarios WHERE id_rol = 2 AND activo = 1");
    $total_clientes = $res->fetch_row()[0];

    $res = $conn->query("SELECT COUNT(*) FROM tickets WHERE estado = 'Abierto'");
    $total_tickets_abiertos = $res->fetch_row()[0];
    
    // Tickets recientes (Visible para todos para dar seguimiento)
    $sql = "SELECT t.*, u.nombres, u.apellido_paterno FROM tickets t JOIN clientes c ON t.id_cliente = c.id_cliente JOIN usuarios u ON c.id_usuario = u.id_usuario ORDER BY fecha_creacion DESC LIMIT 5";
    $ultimos_tickets = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

    // DATOS EXCLUSIVOS DE ADMIN
    if ($es_admin) {
        $res = $conn->query("SELECT COUNT(*) FROM usuarios");
        $total_usuarios = $res->fetch_row()[0];

        $sql = "SELECT u.*, r.nombre_rol FROM usuarios u JOIN roles r ON u.id_rol = r.id_rol ORDER BY fecha_registro DESC LIMIT 5";
        $ultimos_usuarios = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* ... TUS ESTILOS EXISTENTES ... */
:root { --bg-dark: #020c1b; --accent: #00eaff; --glass-bg: rgba(13, 25, 40, 0.7); --glass-border: rgba(0, 234, 255, 0.15); --text-main: #ffffff; --text-muted: #8899a6; }
body { font-family: 'Poppins', sans-serif; background: radial-gradient(circle at top center, #0f3460 0%, var(--bg-dark) 80%); background-color: var(--bg-dark); margin: 0; color: var(--text-main); min-height: 100vh; }
.wrap { max-width: 1200px; margin: 40px auto; display: grid; grid-template-columns: 260px 1fr; gap: 30px; padding: 20px; }

/* SIDEBAR ESTILO */
.sidebar { background: var(--glass-bg); backdrop-filter: blur(12px); padding: 30px 20px; border-radius: 20px; border: 1px solid var(--glass-border); position: sticky; top: 20px; height: fit-content; }
.sidebar img { width: 140px; display: block; margin: 0 auto 30px; }
.user-box { text-align: center; margin-bottom: 30px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 20px; }
.user-role { font-size: 11px; letter-spacing: 1px; text-transform: uppercase; color: var(--accent); background: rgba(0, 234, 255, 0.1); padding: 4px 8px; border-radius: 4px; }

/* NAV LINKS */
.sidebar nav a { color: var(--text-muted); padding: 12px 15px; display: block; text-decoration: none; border-radius: 10px; transition: 0.3s; margin-bottom: 5px; font-size: 14px; }
.sidebar nav a:hover { color: var(--bg-dark); background: var(--accent); font-weight: 600; }

/* 🔒 ESTILO PARA ENLACES BLOQUEADOS */
.nav-locked { 
    opacity: 0.5; 
    cursor: not-allowed; 
    display: flex; 
    justify-content: space-between; 
    align-items: center;
}
.nav-locked:hover { 
    background: rgba(255, 51, 85, 0.1) !important; 
    color: #ff3355 !important; 
}

/* Resto de estilos (Cards, Tablas, etc) */
.cards { display: flex; gap: 20px; margin-bottom: 30px; }
.card { background: linear-gradient(145deg, rgba(255,255,255,0.03) 0%, rgba(255,255,255,0.01) 100%); padding: 25px; border-radius: 16px; flex: 1; border: 1px solid var(--glass-border); }
.card h3 { margin: 10px 0 5px; font-size: 14px; color: var(--text-muted); text-transform: uppercase; }
.card p { font-size: 36px; margin: 0; font-weight: 700; color: white; }
.panel { background: var(--glass-bg); padding: 25px; border-radius: 20px; border: 1px solid var(--glass-border); margin-top: 25px; }
table { width: 100%; border-collapse: collapse; margin-top: 10px; }
th { text-align: left; color: var(--text-muted); padding: 15px 10px; border-bottom: 1px solid rgba(255,255,255,0.1); }
td { padding: 15px 10px; border-bottom: 1px solid rgba(255,255,255,0.03); color: #e0e0e0; }
.badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; text-transform: uppercase; font-weight: bold; }
.logout { display: block; text-align: center; color: #ff5577; text-decoration: none; margin-top: 30px; font-size: 14px; }

@media (max-width: 768px) { .wrap { grid-template-columns: 1fr; } .cards { flex-direction: column; } }
</style>
</head>

<body>
<div class="wrap">

<aside class="sidebar">
    <img src="imagenes/logo.png" alt="KoLine Logo">

    <div class="user-box">
        <p style="font-weight:bold; color:white; margin:0 0 5px 0;"><?= $_SESSION['nombre_usuario']; ?></p>
        <span class="user-role">
            <?= $es_admin ? 'ADMINISTRADOR' : 'SOPORTE TÉCNICO' ?>
        </span>
    </div>

    <nav>
        <a href="dashboard.php">📊 Dashboard</a>
        
        <?php if($es_admin): ?>
            <a href="funciones/usuarios.php">👥 Usuarios</a>
        <?php else: ?>
            <a href="#" class="nav-locked" onclick="accesoDenegado(event)">👥 Usuarios <span>🔒</span></a>
        <?php endif; ?>

        <a href="funciones/clientes.php">🛰 Clientes</a>
        <a href="funciones/tickets.php">🎫 Tickets</a>
        <a href="funciones/inventario.php">📦 Inventario</a>

        <?php if($es_admin): ?>
            <a href="funciones/pagos.php">💰 Pagos</a>
        <?php else: ?>
            <a href="#" class="nav-locked" onclick="accesoDenegado(event)">💰 Pagos <span>🔒</span></a>
        <?php endif; ?>

        <?php if($es_admin): ?>
            <a href="configuracion.php">⚙ Configuración</a>
        <?php else: ?>
            <a href="#" class="nav-locked" onclick="accesoDenegado(event)">⚙ Configuración <span>🔒</span></a>
        <?php endif; ?>
    </nav>

    <a href="index.php" class="logout">← Cerrar sesión</a>
</aside>

<main>
    <h1>Panel de Control</h1>

    <div class="cards">
        <?php if($es_admin): ?>
            <div class="card">
                <h3>Total Usuarios (Staff)</h3>
                <p><?= $total_usuarios ?></p>
            </div>
        <?php endif; ?>

        <div class="card">
            <h3>Clientes Activos</h3>
            <p><?= $total_clientes ?></p>
        </div>
        <div class="card">
            <h3>Tickets Abiertos</h3>
            <p><?= $total_tickets_abiertos ?></p>
        </div>
    </div>

    <?php if($es_admin): ?>
    <div class="panel">
        <h3>Últimos Usuarios (Staff) Registrados</h3>
        <table>
            <tr>
                <th>Nombre</th><th>Email</th><th>Rol</th><th>Fecha</th>
            </tr>
            <?php foreach($ultimos_usuarios as $u): ?>
            <tr>
                <td><?= $u['nombres'] ?></td>
                <td><?= $u['email'] ?></td>
                <td><span class="badge" style="background:#333; color:white;"><?= $u['nombre_rol'] ?></span></td>
                <td><?= date("d/m/Y", strtotime($u['fecha_registro'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php else: ?>
        <div class="panel" style="text-align:center; border: 1px dashed var(--accent);">
            <h3 style="border:none; color:white;">👋 Bienvenido al Panel de Soporte</h3>
            <p style="color:#aaa;">Tu perfil tiene acceso limitado a Clientes, Inventario y Tickets.</p>
        </div>
    <?php endif; ?>

    <div class="panel">
        <h3>Últimos Tickets de Soporte</h3>
        <table>
            <tr>
                <th>Título</th><th>Cliente</th><th>Prioridad</th><th>Estado</th>
            </tr>
            <?php foreach($ultimos_tickets as $t): ?>
            <tr>
                <td><?= $t['titulo'] ?></td>
                <td><?= $t['nombres'] ?></td>
                <td><?= $t['prioridad'] ?></td>
                <td><?= $t['estado'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

</main>
</div>

<script>
    function accesoDenegado(e) {
        e.preventDefault();
        
        // Opción 1: Alerta Simple del Navegador
        // alert("⛔ PERMISOS REQUERIDOS\n\nNo tienes permisos de administrador para acceder a esta área. Contacta a gerencia.");

        // Opción 2: Alerta Bonita (SweetAlert2) - Ya incluida en el head
        Swal.fire({
            icon: 'error',
            title: 'Acceso Restringido',
            text: 'No tienes los permisos de administrador necesarios para ver este módulo.',
            background: '#0a1f35',
            color: '#fff',
            confirmButtonColor: '#ff3366'
        });
    }
</script>

</body>
</html>
