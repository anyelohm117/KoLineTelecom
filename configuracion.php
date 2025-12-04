
<?php
session_start();
require 'db_con.php'; 

/* ============================================
   🔒 SEGURIDAD
============================================ */
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 1) {
    header("Location: index.php"); 
    exit();
}

$mensaje = "";

/* ============================================
   ⚙️ LÓGICA: AGREGAR NUEVO PLAN
============================================ */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_nuevo_plan'])) {
    $nombre = $_POST['nombre_plan'];
    $velocidad = $_POST['velocidad'];
    $precio = $_POST['precio'];
    
    try {
        $stmt = $conn->prepare("INSERT INTO planes_internet (nombre_plan, velocidad_mbps, precio_mensual) VALUES (?, ?, ?)");
        $stmt->bind_param("sid", $nombre, $velocidad, $precio);
        
        if ($stmt->execute()) {
            $mensaje = "<div class='alert success'>✅ Plan <b>$nombre</b> agregado al catálogo.</div>";
        } else {
            $mensaje = "<div class='alert error'>❌ Error al agregar plan.</div>";
        }
    } catch (Exception $e) {
        $mensaje = "<div class='alert error'>Error: " . $e->getMessage() . "</div>";
    }
}

/* ============================================
   🗑️ LÓGICA: ELIMINAR PLAN (GET)
============================================ */
if (isset($_GET['borrar_plan'])) {
    $id_borrar = $_GET['borrar_plan'];
    try {
        $conn->query("DELETE FROM planes_internet WHERE id_plan = $id_borrar");
        $mensaje = "<div class='alert success'>🗑️ Plan eliminado correctamente.</div>";
    } catch (Exception $e) {
        // El error 1451 es Constraint Violation (El plan está en uso por un cliente)
        if ($conn->errno == 1451) {
            $mensaje = "<div class='alert error'>⚠️ No puedes borrar este plan: Hay clientes usándolo actualmente.</div>";
        } else {
            $mensaje = "<div class='alert error'>Error: " . $e->getMessage() . "</div>";
        }
    }
}

/* ============================================
   📌 CONSULTAS
============================================ */
$planes = $conn->query("SELECT * FROM planes_internet")->fetch_all(MYSQLI_ASSOC);
$formas_pago = $conn->query("SELECT * FROM formas_pago")->fetch_all(MYSQLI_ASSOC);

// Datos del Servidor (Para el Monitor)
$php_version = phpversion();
$server_software = $_SERVER['SERVER_SOFTWARE'];
$db_status = $conn->ping() ? "Conectado" : "Error";
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Configuración | KoLine</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
/* Estilos Base */
:root { --bg-dark: #020c1b; --accent: #00eaff; --accent-hover: #00cce6; --glass-bg: rgba(13, 25, 40, 0.85); --glass-border: rgba(0, 234, 255, 0.15); --text-main: #ffffff; --text-muted: #8899a6; }
body { font-family: 'Poppins', sans-serif; background: radial-gradient(circle at top center, #0f3460 0%, var(--bg-dark) 80%); background-color: var(--bg-dark); background-attachment: fixed; margin: 0; color: var(--text-main); min-height: 100vh; }
.wrap { max-width: 1200px; margin: 40px auto; display: grid; grid-template-columns: 260px 1fr; gap: 30px; padding: 20px; }
.sidebar { background: var(--glass-bg); backdrop-filter: blur(12px); padding: 30px 20px; border-radius: 20px; border: 1px solid var(--glass-border); height: fit-content; }
.sidebar img { width: 140px; display: block; margin: 0 auto 30px; filter: drop-shadow(0 0 5px rgba(0,234,255,0.3)); }
.sidebar nav a { color: var(--text-muted); padding: 12px 15px; display: block; text-decoration: none; border-radius: 10px; margin-bottom: 5px; transition: 0.3s; }
.sidebar nav a:hover { background: var(--accent); color: var(--bg-dark); font-weight: 600; box-shadow: 0 0 15px rgba(0, 234, 255, 0.4); }
.sidebar nav a.active { background: rgba(0, 234, 255, 0.1); color: var(--accent); border: 1px solid var(--accent); }
.main-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
h1 { margin: 0; text-shadow: 0 0 20px rgba(0, 234, 255, 0.1); }
.alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; }
.alert.success { background: rgba(0, 255, 136, 0.1); border: 1px solid #00ff88; color: #00ff88; }
.alert.error { background: rgba(255, 51, 85, 0.1); border: 1px solid #ff3355; color: #ff3355; }

/* GRID DE CONFIGURACIÓN */
.config-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
}

.card-panel {
    background: var(--glass-bg);
    backdrop-filter: blur(12px);
    padding: 25px;
    border-radius: 20px;
    border: 1px solid var(--glass-border);
}

.card-header {
    display: flex; justify-content: space-between; align-items: center;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    padding-bottom: 15px; margin-bottom: 20px;
}
.card-header h3 { margin: 0; color: var(--accent); font-weight: 500; }

/* Lista de Planes */
.plan-item {
    display: flex; justify-content: space-between; align-items: center;
    background: rgba(255,255,255,0.03);
    padding: 15px; border-radius: 10px; margin-bottom: 10px;
    border: 1px solid transparent;
    transition: 0.3s;
}
.plan-item:hover { border-color: var(--accent); background: rgba(0, 234, 255, 0.05); }

.plan-info strong { display: block; color: white; }
.plan-info span { font-size: 12px; color: var(--text-muted); }
.plan-price { font-weight: bold; color: #00ff88; font-size: 16px; margin-right: 15px; }

.btn-delete {
    color: #ff3355; text-decoration: none; font-size: 18px; padding: 5px;
    transition: 0.3s;
}
.btn-delete:hover { color: #ff88aa; transform: scale(1.2); }

/* Formulario pequeño */
.mini-form { display: flex; gap: 10px; margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px; }
.mini-form input {
    background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1);
    padding: 10px; border-radius: 6px; color: white; width: 100%;
}
.btn-mini {
    background: var(--accent); color: var(--bg-dark); border: none; padding: 0 20px;
    border-radius: 6px; font-weight: bold; cursor: pointer;
}

/* System Info */
.sys-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
.sys-val { font-family: monospace; color: #aaddff; }

@media (max-width: 768px) { .wrap { grid-template-columns: 1fr; } }
</style>
</head>

<body>
<div class="wrap">
    
    <aside class="sidebar">
        <img src="imagenes/logo.png" alt="KoLine">
        <nav>
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="funciones/usuarios">👥 Usuarios</a>
            <a href="funciones/clientes.php">🛰 Clientes</a>
            <a href="funciones/tickets">🎫 Tickets</a>
            <a href="funciones/inventario.php">📦 Inventario</a>
            <a href="funciones/pagos.php">💰 Pagos</a>
            <a href="configuracion.php" class="active">⚙ Configuración</a>
        </nav>
        <div style="text-align:center; margin-top:30px;">
            <a href="../index.php" style="color:#ff5577; text-decoration:none;">← Volver</a>
        </div>
    </aside>

    <main>
        <div class="main-header">
            <h1>Ajustes del Sistema</h1>
        </div>

        <?= $mensaje ?>

        <div class="config-grid">
            
            <div class="card-panel">
                <div class="card-header">
                    <h3>📡 Planes de Internet</h3>
                    <small style="color:#888;">Catálogo de Servicios</small>
                </div>

                <div class="plans-list">
                    <?php foreach($planes as $p): ?>
                    <div class="plan-item">
                        <div class="plan-info">
                            <strong><?= $p['nombre_plan'] ?></strong>
                            <span><?= $p['velocidad_mbps'] ?> Mbps de Bajada</span>
                        </div>
                        <div style="display:flex; align-items:center;">
                            <span class="plan-price">$<?= number_format($p['precio_mensual'], 2) ?></span>
                            <a href="?borrar_plan=<?= $p['id_plan'] ?>" class="btn-delete" onclick="return confirm('¿Seguro que deseas eliminar este plan?');">×</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <form method="POST" class="mini-form">
                    <input type="text" name="nombre_plan" placeholder="Nombre (Ej: Gamer)" required>
                    <input type="number" name="velocidad" placeholder="Mbps" style="width:80px" required>
                    <input type="number" name="precio" placeholder="Precio" style="width:100px" required>
                    <button type="submit" name="btn_nuevo_plan" class="btn-mini">+</button>
                </form>
            </div>

            <div class="card-panel">
                <div class="card-header">
                    <h3>🖥️ System Health</h3>
                    <div style="width:10px; height:10px; background:#00ff88; border-radius:50%; box-shadow:0 0 10px #00ff88;"></div>
                </div>
                
                <div class="sys-row">
                    <span>Base de Datos</span>
                    <span class="sys-val" style="color:#00ff88;"><?= $db_status ?></span>
                </div>
                <div class="sys-row">
                    <span>Versión PHP</span>
                    <span class="sys-val"><?= $php_version ?></span>
                </div>
                <div class="sys-row">
                    <span>Servidor Web</span>
                    <span class="sys-val"><?= substr($server_software, 0, 20) ?>...</span>
                </div>
                <div class="sys-row">
                    <span>Usuario Actual</span>
                    <span class="sys-val"><?= $_SESSION['nombre_usuario'] ?></span>
                </div>

                <div style="margin-top:20px; padding:15px; background:rgba(0, 234, 255, 0.05); border-radius:10px; border:1px dashed var(--accent);">
                    <h4 style="margin:0 0 5px 0; color:var(--accent);">Métodos de Pago Activos:</h4>
                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                        <?php foreach($formas_pago as $fp): ?>
                            <span style="font-size:11px; background:#020c1b; padding:4px 8px; border-radius:4px;"><?= $fp['metodo'] ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

</body>
</html>
