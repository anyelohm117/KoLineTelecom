<?php
session_start();
require '../db_con.php'; 

/* ============================================
   🔒 SEGURIDAD CORREGIDA: ADMIN (1) Y SOPORTE (3)
============================================ */
// Permitimos entrar a Admin (1) O Soporte (3)
if (!isset($_SESSION['id_usuario']) || ($_SESSION['rol'] != 1 && $_SESSION['rol'] != 3)) {
    // Si es un cliente, lo mandamos a su panel
    if (isset($_SESSION['rol']) && $_SESSION['rol'] == 2) {
        header("Location: ../cliente_dashboard.php");
    } else {
        header("Location: ../index.php");
    }
    exit();
}

// Variable para controlar el menú visualmente (Candados)
$es_admin = ($_SESSION['rol'] == 1); 

$mensaje = "";

/* ============================================
   📝 LÓGICA: CREAR NUEVO TICKET
============================================ */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_crear_ticket'])) {
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $id_cliente = $_POST['id_cliente'];
    $prioridad = $_POST['prioridad'];
    $id_tecnico = !empty($_POST['id_tecnico']) ? $_POST['id_tecnico'] : NULL;
    $estado = 'Abierto';

    try {
        $stmt = $conn->prepare("INSERT INTO tickets (titulo, descripcion, prioridad, estado, id_cliente, id_tecnico_asignado) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssii", $titulo, $descripcion, $prioridad, $estado, $id_cliente, $id_tecnico);
        
        if ($stmt->execute()) {
            $mensaje = "<div class='alert success'>✅ Ticket #{$conn->insert_id} creado correctamente.</div>";
        } else {
            $mensaje = "<div class='alert error'>❌ Error al crear ticket.</div>";
        }
    } catch (Exception $e) {
        $mensaje = "<div class='alert error'>Error: " . $e->getMessage() . "</div>";
    }
}

/* ============================================
   ✅ LÓGICA: CERRAR TICKET (ACCIÓN RÁPIDA)
============================================ */
if (isset($_POST['btn_cerrar_ticket'])) {
    $id_ticket_cerrar = $_POST['id_ticket_cerrar'];
    $fecha_cierre = date('Y-m-d H:i:s');
    
    $stmt = $conn->prepare("UPDATE tickets SET estado = 'Cerrado', fecha_cierre = ? WHERE id_ticket = ?");
    $stmt->bind_param("si", $fecha_cierre, $id_ticket_cerrar);
    
    if ($stmt->execute()) {
        $mensaje = "<div class='alert success'>🔒 Ticket #$id_ticket_cerrar cerrado exitosamente.</div>";
    }
}

/* ============================================
   📌 CONSULTAS
============================================ */
// 1. Obtener Clientes
$sql_clientes = "SELECT c.id_cliente, u.nombres, u.apellido_paterno FROM clientes c JOIN usuarios u ON c.id_usuario = u.id_usuario WHERE u.activo = 1 ORDER BY u.nombres ASC";
$clientes = $conn->query($sql_clientes)->fetch_all(MYSQLI_ASSOC);

// 2. Obtener Técnicos
$sql_tecnicos = "SELECT id_usuario, nombres, apellido_paterno FROM usuarios WHERE id_rol IN (1, 3) AND activo = 1";
$tecnicos = $conn->query($sql_tecnicos)->fetch_all(MYSQLI_ASSOC);

// 3. Obtener Tickets
$sql_tickets = "SELECT t.*, uc.nombres AS nom_cliente, uc.apellido_paterno AS ape_cliente, ut.nombres AS nom_tecnico
                FROM tickets t
                JOIN clientes c ON t.id_cliente = c.id_cliente
                JOIN usuarios uc ON c.id_usuario = uc.id_usuario
                LEFT JOIN usuarios ut ON t.id_tecnico_asignado = ut.id_usuario
                ORDER BY FIELD(t.estado, 'Abierto', 'En Proceso', 'Resuelto', 'Cerrado'), t.fecha_creacion DESC";
$lista_tickets = $conn->query($sql_tickets)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Soporte Técnico | KoLine</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="icon" type="image/png" href="../imagenes/logo.png">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* Estilos Base */
:root { --bg-dark: #020c1b; --accent: #00eaff; --accent-hover: #00cce6; --glass-bg: rgba(13, 25, 40, 0.85); --glass-border: rgba(0, 234, 255, 0.15); --text-main: #ffffff; --text-muted: #8899a6; }
body { font-family: 'Poppins', sans-serif; background: radial-gradient(circle at top center, #0f3460 0%, var(--bg-dark) 80%); background-color: var(--bg-dark); background-attachment: fixed; margin: 0; color: var(--text-main); min-height: 100vh; }
.wrap { max-width: 1200px; margin: 40px auto; display: grid; grid-template-columns: 260px 1fr; gap: 30px; padding: 20px; align-items: start; }

/* SIDEBAR STICKY */
.sidebar { background: var(--glass-bg); backdrop-filter: blur(12px); padding: 30px 20px; border-radius: 20px; border: 1px solid var(--glass-border); position: sticky; top: 20px; max-height: calc(100vh - 40px); overflow-y: auto; scrollbar-width: none; }
.sidebar::-webkit-scrollbar { display: none; }

.sidebar img { width: 140px; display: block; margin: 0 auto 30px; filter: drop-shadow(0 0 5px rgba(0,234,255,0.3)); }
.sidebar nav a { color: var(--text-muted); padding: 12px 15px; display: block; text-decoration: none; border-radius: 10px; margin-bottom: 5px; transition: 0.3s; font-size: 14px; }
.sidebar nav a:hover { background: var(--accent); color: var(--bg-dark); font-weight: 600; box-shadow: 0 0 15px rgba(0, 234, 255, 0.4); }
.sidebar nav a.active { background: rgba(0, 234, 255, 0.1); color: var(--accent); border: 1px solid var(--accent); }

/* ESTILO BLOQUEADO */
.nav-locked { opacity: 0.5; cursor: not-allowed; display: flex; justify-content: space-between; align-items: center; }
.nav-locked:hover { background: rgba(255, 51, 85, 0.1) !important; color: #ff3355 !important; box-shadow: none !important; }

.main-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
h1 { margin: 0; text-shadow: 0 0 20px rgba(0, 234, 255, 0.1); }
.form-panel { background: linear-gradient(145deg, rgba(255,255,255,0.03) 0%, rgba(255,255,255,0.01) 100%); backdrop-filter: blur(10px); padding: 25px; border-radius: 16px; border: 1px solid var(--glass-border); margin-bottom: 30px; }
.form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
.input-group { display: flex; flex-direction: column; }
.input-group label { font-size: 12px; color: var(--accent); margin-bottom: 5px; font-weight: 600; text-transform: uppercase; }
input, select, textarea { background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); padding: 12px; border-radius: 8px; color: white; font-family: inherit; outline: none; transition: 0.3s; }
input:focus, select:focus, textarea:focus { border-color: var(--accent); box-shadow: 0 0 10px rgba(0, 234, 255, 0.2); }
textarea { resize: vertical; min-height: 80px; }
.btn-submit { grid-column: 1 / -1; background: var(--accent); color: var(--bg-dark); padding: 12px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 16px; margin-top: 10px; transition: 0.3s; }
.btn-submit:hover { background: var(--accent-hover); box-shadow: 0 0 15px rgba(0, 234, 255, 0.5); }
.table-panel { background: var(--glass-bg); backdrop-filter: blur(12px); padding: 25px; border-radius: 20px; border: 1px solid var(--glass-border); }
table { width: 100%; border-collapse: collapse; font-size: 14px; }
th { text-align: left; color: var(--text-muted); padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.1); }
td { padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.03); color: #e0e0e0; vertical-align: top; }
tr:hover td { background: rgba(0, 234, 255, 0.03); }

/* PRIORIDAD BADGES */
.prioridad-baja { color: #00ff88; }
.prioridad-media { color: #ffaa00; }
.prioridad-alta { color: #ff5577; font-weight: bold; }
.prioridad-critica { color: #ff0000; text-shadow: 0 0 5px red; font-weight: bold; }

/* ESTADO BADGES */
.badge { padding: 4px 8px; border-radius: 4px; font-size: 10px; text-transform: uppercase; font-weight: bold; letter-spacing: 0.5px; }
.estado-abierto { background: rgba(0, 234, 255, 0.15); color: var(--accent); border: 1px solid var(--glass-border); }
.estado-cerrado { background: rgba(255, 255, 255, 0.05); color: #666; border: 1px solid rgba(255, 255, 255, 0.1); text-decoration: line-through; }
.estado-proceso { background: rgba(255, 170, 0, 0.15); color: #ffaa00; }

.btn-mini-action { background: none; border: 1px solid #444; color: #888; padding: 5px 10px; cursor: pointer; border-radius: 4px; font-size: 11px; transition: 0.3s; }
.btn-mini-action:hover { border-color: #00ff88; color: #00ff88; background: rgba(0, 255, 136, 0.1); }

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

            <?php if($es_admin): ?>
                <a href="usuarios.php">👥 Usuarios</a>
            <?php else: ?>
                <a href="#" class="nav-locked" onclick="noPermiso(event)">👥 Usuarios <span>🔒</span></a>
            <?php endif; ?>

            <a href="clientes.php">🛰 Clientes</a>
            <a href="tickets.php" class="active">🎫 Tickets</a>
            <a href="inventario.php">📦 Inventario</a>

            <?php if($es_admin): ?>
                <a href="pagos.php">💰 Pagos</a>
            <?php else: ?>
                <a href="#" class="nav-locked" onclick="noPermiso(event)">💰 Pagos <span>🔒</span></a>
            <?php endif; ?>

            <?php if($es_admin): ?>
                <a href="../configuracion.php">⚙ Configuración</a>
            <?php else: ?>
                <a href="#" class="nav-locked" onclick="noPermiso(event)">⚙ Configuración <span>🔒</span></a>
            <?php endif; ?>
        </nav>
        <div style="text-align:center; margin-top:30px;">
            <a href="../dashboard.php" style="color:#ff5577; text-decoration:none;">← Volver</a>
        </div>
    </aside>

    <main>
        <div class="main-header">
            <h1>Mesa de Ayuda (HelpDesk)</h1>
        </div>

        <?= $mensaje ?>

        <div class="form-panel">
            <h3 style="margin-top:0; color:var(--text-muted); border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:10px;">Nuevo Ticket de Soporte</h3>
            
            <form method="POST" action="">
                <div class="form-grid">
                    
                    <div class="input-group">
                        <label>Asunto / Falla</label>
                        <input type="text" name="titulo" required placeholder="Ej: Sin acceso a internet">
                    </div>

                    <div class="input-group">
                        <label>Cliente Afectado</label>
                        <select name="id_cliente" required>
                            <option value="">Seleccione Cliente...</option>
                            <?php foreach($clientes as $c): ?>
                                <option value="<?= $c['id_cliente'] ?>">
                                    <?= $c['nombres'] . " " . $c['apellido_paterno'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="input-group">
                        <label>Prioridad</label>
                        <select name="prioridad">
                            <option value="Baja">🟢 Baja (Consultas)</option>
                            <option value="Media" selected>🟠 Media (Lentitud)</option>
                            <option value="Alta">🔴 Alta (Corte Servicio)</option>
                            <option value="Crítica">🔥 Crítica (Corte Zona)</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label>Asignar Técnico</label>
                        <select name="id_tecnico">
                            <option value="">-- Sin Asignar --</option>
                            <?php foreach($tecnicos as $t): ?>
                                <option value="<?= $t['id_usuario'] ?>"><?= $t['nombres'] . " " . $t['apellido_paterno'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="input-group" style="grid-column: 1 / -1;">
                        <label>Descripción del Problema</label>
                        <textarea name="descripcion" placeholder="Detalles técnicos del reporte..." required></textarea>
                    </div>

                    <button type="submit" name="btn_crear_ticket" class="btn-submit">ABRIR TICKET</button>
                </div>
            </form>
        </div>

        <div class="table-panel">
            <h3 style="margin-top:0; color:var(--accent);">Tickets Recientes</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Asunto & Cliente</th>
                        <th>Asignado a</th>
                        <th>Prioridad</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($lista_tickets) > 0): ?>
                        <?php foreach($lista_tickets as $tk): ?>
                        <tr style="<?= $tk['estado'] == 'Cerrado' ? 'opacity:0.5;' : '' ?>">
                            <td>#<?= str_pad($tk['id_ticket'], 4, '0', STR_PAD_LEFT) ?></td>
                            
                            <td>
                                <strong style="color:white; font-size:15px;"><?= $tk['titulo'] ?></strong><br>
                                <span style="font-size:12px; color:var(--text-muted);">👤 <?= $tk['nom_cliente'] . " " . $tk['ape_cliente'] ?></span><br>
                                <small style="color:#666;">Creado: <?= date("d/m H:i", strtotime($tk['fecha_creacion'])) ?></small>
                            </td>

                            <td>
                                <?php if ($tk['nom_tecnico']): ?>
                                    🛠 <?= $tk['nom_tecnico'] ?>
                                <?php else: ?>
                                    <span style="color:#666;">-- Pendiente --</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php 
                                    $clase_prio = 'prioridad-' . strtolower($tk['prioridad']);
                                    if($tk['prioridad'] == 'Crítica') $clase_prio = 'prioridad-critica';
                                ?>
                                <span class="<?= $clase_prio ?>"><?= $tk['prioridad'] ?></span>
                            </td>

                            <td>
                                <?php 
                                    $clase_estado = 'estado-' . strtolower(str_replace(' ', '', $tk['estado'])); // Elimina espacios para clase CSS
                                    if ($tk['estado'] == 'En Proceso') $clase_estado = 'estado-proceso';
                                ?>
                                <span class="badge <?= $clase_estado ?>"><?= $tk['estado'] ?></span>
                            </td>

                            <td>
                                <?php if($tk['estado'] != 'Cerrado'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="id_ticket_cerrar" value="<?= $tk['id_ticket'] ?>">
                                        <button type="submit" name="btn_cerrar_ticket" class="btn-mini-action" onclick="return confirm('¿Marcar ticket como resuelto/cerrado?');">
                                            ✓ Cerrar
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span style="font-size:10px;">✅ Resuelto</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center;">¡Excelente! No hay tickets pendientes.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>

<script>
    function noPermiso(e) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Acceso Restringido',
            text: 'Tu perfil de Soporte Técnico no tiene permisos para acceder a este módulo.',
            background: '#0a1f35',
            color: '#fff',
            confirmButtonColor: '#ff3366',
            confirmButtonText: 'Entendido'
        });
    }
</script>

</body>
</html>
