<?php
session_start();
require '../db_con.php';

/* ============================================
   🔒 SEGURIDAD
============================================ */
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 1) {
    header("Location: index.php");
    exit();
}

/* ============================================
   📝 LÓGICA: REGISTRAR NUEVO PAGO
============================================ */
$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_registrar_pago'])) {
    $id_cliente = $_POST['id_cliente'];
    $monto = $_POST['monto'];
    $id_forma_pago = $_POST['id_forma_pago'];
    $referencia = $_POST['referencia'];
    $estado = $_POST['estado'];

    try {
        $stmt = $conn->prepare("INSERT INTO pagos_servicios (id_cliente, monto, id_forma_pago, referencia_pago, estado_pago) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("idiss", $id_cliente, $monto, $id_forma_pago, $referencia, $estado);
        
        if ($stmt->execute()) {
            $mensaje = "<div class='alert success'>✅ Pago registrado correctamente.</div>";
        } else {
            $mensaje = "<div class='alert error'>❌ Error al registrar pago.</div>";
        }
    } catch (Exception $e) {
        $mensaje = "<div class='alert error'>Error: " . $e->getMessage() . "</div>";
    }
}

/* ============================================
   📌 CONSULTAS PARA LISTAS Y TABLAS
============================================ */

// 1. Obtener lista de clientes (Para el select del formulario)
// Hacemos JOIN con usuarios y planes para mostrar nombre y precio sugerido
$sql_clientes = "SELECT c.id_cliente, u.nombres, u.apellido_paterno, p.nombre_plan, p.precio_mensual 
                 FROM clientes c 
                 JOIN usuarios u ON c.id_usuario = u.id_usuario 
                 JOIN planes_internet p ON c.id_plan = p.id_plan 
                 WHERE u.activo = 1";
$clientes = $conn->query($sql_clientes)->fetch_all(MYSQLI_ASSOC);

// 2. Obtener formas de pago
$formas_pago = $conn->query("SELECT * FROM formas_pago")->fetch_all(MYSQLI_ASSOC);

// 3. Obtener historial de pagos (Últimos 20)
$sql_historial = "SELECT p.*, u.nombres, u.apellido_paterno, fp.metodo 
                  FROM pagos_servicios p 
                  JOIN clientes c ON p.id_cliente = c.id_cliente 
                  JOIN usuarios u ON c.id_usuario = u.id_usuario 
                  LEFT JOIN formas_pago fp ON p.id_forma_pago = fp.id_forma_pago 
                  ORDER BY p.fecha_pago DESC LIMIT 20";
$pagos = $conn->query($sql_historial)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Gestión de Pagos | KoLine</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
/* =========================================
   🎨 ESTILOS GENERALES (Mismos del Dashboard)
   ========================================= */
:root {
    --bg-dark: #020c1b; 
    --accent: #00eaff;
    --accent-hover: #00cce6;
    --glass-bg: rgba(13, 25, 40, 0.85); 
    --glass-border: rgba(0, 234, 255, 0.15);
    --text-main: #ffffff;
    --text-muted: #8899a6;
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
    margin: 40px auto;
    display: grid;
    grid-template-columns: 260px 1fr;
    gap: 30px;
    padding: 20px;
}

/* SIDEBAR (Reutilizado) */
.sidebar {
    background: var(--glass-bg);
    backdrop-filter: blur(12px);
    padding: 30px 20px;
    border-radius: 20px;
    border: 1px solid var(--glass-border);
    height: fit-content;
}
.sidebar img { width: 140px; display: block; margin: 0 auto 30px; filter: drop-shadow(0 0 5px rgba(0,234,255,0.3)); }
.sidebar nav a { color: var(--text-muted); padding: 12px 15px; display: block; text-decoration: none; border-radius: 10px; margin-bottom: 5px; transition: 0.3s; }
.sidebar nav a:hover { background: var(--accent); color: var(--bg-dark); font-weight: 600; box-shadow: 0 0 15px rgba(0, 234, 255, 0.4); }
.sidebar nav a.active { background: rgba(0, 234, 255, 0.1); color: var(--accent); border: 1px solid var(--accent); }

/* CONTENIDO PRINCIPAL */
.main-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
h1 { margin: 0; text-shadow: 0 0 20px rgba(0, 234, 255, 0.1); }

/* FORMULARIO ESTILO GLASS */
.form-panel {
    background: linear-gradient(145deg, rgba(255,255,255,0.03) 0%, rgba(255,255,255,0.01) 100%);
    backdrop-filter: blur(10px);
    padding: 25px;
    border-radius: 16px;
    border: 1px solid var(--glass-border);
    margin-bottom: 30px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.input-group { display: flex; flex-direction: column; }
.input-group label { font-size: 12px; color: var(--accent); margin-bottom: 5px; font-weight: 600; text-transform: uppercase; }

input, select {
    background: rgba(0,0,0,0.3);
    border: 1px solid rgba(255,255,255,0.1);
    padding: 12px;
    border-radius: 8px;
    color: white;
    font-family: inherit;
    outline: none;
    transition: 0.3s;
}

input:focus, select:focus { border-color: var(--accent); box-shadow: 0 0 10px rgba(0, 234, 255, 0.2); }

.btn-submit {
    grid-column: 1 / -1;
    background: var(--accent);
    color: var(--bg-dark);
    padding: 12px;
    border: none;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
    font-size: 16px;
    margin-top: 10px;
    transition: 0.3s;
}
.btn-submit:hover { background: var(--accent-hover); box-shadow: 0 0 15px rgba(0, 234, 255, 0.5); }

/* TABLA */
.table-panel {
    background: var(--glass-bg);
    backdrop-filter: blur(12px);
    padding: 25px;
    border-radius: 20px;
    border: 1px solid var(--glass-border);
}
table { width: 100%; border-collapse: collapse; font-size: 14px; }
th { text-align: left; color: var(--text-muted); padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.1); }
td { padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.03); color: #e0e0e0; }
tr:hover td { background: rgba(0, 234, 255, 0.03); }

/* STATUS BADGES */
.status { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
.pagado { background: rgba(0, 255, 136, 0.15); color: #00ff88; border: 1px solid rgba(0, 255, 136, 0.3); }
.pendiente { background: rgba(255, 170, 0, 0.15); color: #ffaa00; border: 1px solid rgba(255, 170, 0, 0.3); }
.rechazado { background: rgba(255, 51, 85, 0.15); color: #ff3355; border: 1px solid rgba(255, 51, 85, 0.3); }

/* ALERTAS */
.alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; }
.alert.success { background: rgba(0, 255, 136, 0.1); border: 1px solid #00ff88; color: #00ff88; }
.alert.error { background: rgba(255, 51, 85, 0.1); border: 1px solid #ff3355; color: #ff3355; }

@media (max-width: 768px) { .wrap { grid-template-columns: 1fr; } }
</style>
</head>

<body>
<div class="wrap">
    
    <aside class="sidebar">
        <img src="../imagenes/logo.png" alt="KoLine">
        <nav>
            <a href="../dashboard.php">📊 Dashboard</a>
            <a href="uduarios.php">👥 Usuarios</a>
            <a href="clientes.php">🛰 Clientes</a>
            <a href="#tickets.php">🎫 Tickets</a>
            <a href="inventario.php">📦 Inventario</a>
            <a href="../pagos.php" class="active">💰 Pagos</a>
            <a href="#">⚙ Configuración</a>
        </nav>
        <div style="text-align:center; margin-top:30px;">
            <a href="index.php" style="color:#ff5577; text-decoration:none;">← Volver</a>
        </div>
    </aside>

    <main>
        <div class="main-header">
            <h1>Finanzas & Pagos</h1>
        </div>

        <?= $mensaje ?>

        <div class="form-panel">
            <h3 style="margin-top:0; color:var(--text-muted); border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:10px;">Registrar Nuevo Ingreso</h3>
            
            <form method="POST" action="">
                <div class="form-grid">
                    
                    <div class="input-group">
                        <label>Cliente</label>
                        <select name="id_cliente" id="select_cliente" required onchange="actualizarMonto()">
                            <option value="">Seleccione Cliente...</option>
                            <?php foreach($clientes as $c): ?>
                                <option value="<?= $c['id_cliente'] ?>" data-precio="<?= $c['precio_mensual'] ?>">
                                    <?= $c['nombres'] . " " . $c['apellido_paterno'] . " - " . $c['nombre_plan'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="input-group">
                        <label>Monto ($)</label>
                        <input type="number" step="0.01" name="monto" id="input_monto" placeholder="0.00" required>
                    </div>

                    <div class="input-group">
                        <label>Método de Pago</label>
                        <select name="id_forma_pago" required>
                            <?php foreach($formas_pago as $fp): ?>
                                <option value="<?= $fp['id_forma_pago'] ?>"><?= $fp['metodo'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="input-group">
                        <label>Referencia / Folio</label>
                        <input type="text" name="referencia" placeholder="Ej: Ticket #1234 o Folio Banco">
                    </div>

                    <div class="input-group">
                        <label>Estado del Pago</label>
                        <select name="estado">
                            <option value="Pagado">✅ Pagado</option>
                            <option value="Pendiente">⏳ Pendiente</option>
                            <option value="Rechazado">❌ Rechazado</option>
                        </select>
                    </div>

                    <button type="submit" name="btn_registrar_pago" class="btn-submit">REGISTRAR PAGO</button>
                </div>
            </form>
        </div>

        <div class="table-panel">
            <h3 style="margin-top:0; color:var(--accent);">Historial de Transacciones Recientes</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Monto</th>
                        <th>Método</th>
                        <th>Referencia</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($pagos) > 0): ?>
                        <?php foreach($pagos as $p): ?>
                        <tr>
                            <td>#<?= str_pad($p['id_pago'], 4, '0', STR_PAD_LEFT) ?></td>
                            <td>
                                <strong style="color:white;"><?= $p['nombres'] . " " . $p['apellido_paterno'] ?></strong>
                            </td>
                            <td>$<?= number_format($p['monto'], 2) ?></td>
                            <td><?= $p['metodo'] ?></td>
                            <td style="font-family:monospace; color:#aaa;"><?= $p['referencia_pago'] ?: '---' ?></td>
                            <td>
                                <?php 
                                    $clase = strtolower($p['estado_pago']); 
                                    if($clase == 'pagado') $clase = 'pagado';
                                    elseif($clase == 'pendiente') $clase = 'pendiente';
                                    else $clase = 'rechazado';
                                ?>
                                <span class="status <?= $clase ?>"><?= $p['estado_pago'] ?></span>
                            </td>
                            <td><?= date("d/m/Y H:i", strtotime($p['fecha_pago'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align:center;">No hay pagos registrados aún.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>

<script>
function actualizarMonto() {
    var select = document.getElementById('select_cliente');
    var montoInput = document.getElementById('input_monto');
    var selectedOption = select.options[select.selectedIndex];
    var precio = selectedOption.getAttribute('data-precio');
    
    if (precio) {
        montoInput.value = precio;
    } else {
        montoInput.value = '';
    }
}
</script>

</body>
</html>
