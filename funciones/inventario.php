<?php
session_start();
require '../db_con.php'; 

/* ============================================
   🔒 SEGURIDAD
============================================ */
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 1) {
    header("Location: ../index.php"); 
    exit();
}

/* ============================================
   📦 LÓGICA: REGISTRAR NUEVO EQUIPO FÍSICO
============================================ */
$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_registrar_item'])) {
    $id_producto = $_POST['id_producto'];
    $mac = strtoupper(trim($_POST['mac_address'])); // Convertir MAC a mayúsculas
    $serie = trim($_POST['numero_serie']);
    $estado = $_POST['estado'];
    $sucursal = 1; // Por defecto sucursal 1

    try {
        // Validar duplicados antes de insertar (Opcional, pero SQL lanzará error si unique falla)
        $stmt = $conn->prepare("INSERT INTO inventario (id_producto, mac_address, numero_serie, estado, id_sucursal) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isssi", $id_producto, $mac, $serie, $estado, $sucursal);
        
        if ($stmt->execute()) {
            $mensaje = "<div class='alert success'>✅ Equipo registrado en inventario.</div>";
        } else {
            $mensaje = "<div class='alert error'>❌ Error. Verifica que la MAC o Serie no existan ya.</div>";
        }
    } catch (Exception $e) {
        // Capturar error de duplicados (Código 1062 en MySQL)
        if ($conn->errno == 1062) {
             $mensaje = "<div class='alert error'>⚠️ Error: La MAC Address o el No. Serie ya existen en el sistema.</div>";
        } else {
             $mensaje = "<div class='alert error'>Error: " . $e->getMessage() . "</div>";
        }
    }
}

/* ============================================
   📌 CONSULTAS
============================================ */
// 1. Obtener lista de productos base (Para el select)
$sql_productos = "SELECT p.id_producto, p.nombre_producto, p.modelo, m.nombre_marca 
                  FROM productos p 
                  JOIN marcas m ON p.id_marca = m.id_marca 
                  ORDER BY m.nombre_marca, p.nombre_producto";
$productos = $conn->query($sql_productos)->fetch_all(MYSQLI_ASSOC);

// 2. Obtener inventario actual
$sql_inventario = "SELECT i.*, p.nombre_producto, p.modelo, m.nombre_marca, cat.nombre_categoria, c.id_cliente, u.nombres, u.apellido_paterno
                   FROM inventario i
                   JOIN productos p ON i.id_producto = p.id_producto
                   JOIN marcas m ON p.id_marca = m.id_marca
                   JOIN categorias_producto cat ON p.id_categoria = cat.id_categoria
                   LEFT JOIN clientes c ON i.id_cliente_asignado = c.id_cliente
                   LEFT JOIN usuarios u ON c.id_usuario = u.id_usuario
                   ORDER BY i.id_item DESC LIMIT 50";
$items = $conn->query($sql_inventario)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Control de Inventario | KoLine</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
/* =========================================
   🎨 ESTILOS (Mismos de pagos.php)
   ========================================= */
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
.form-panel { background: linear-gradient(145deg, rgba(255,255,255,0.03) 0%, rgba(255,255,255,0.01) 100%); backdrop-filter: blur(10px); padding: 25px; border-radius: 16px; border: 1px solid var(--glass-border); margin-bottom: 30px; }
.form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
.input-group { display: flex; flex-direction: column; }
.input-group label { font-size: 12px; color: var(--accent); margin-bottom: 5px; font-weight: 600; text-transform: uppercase; }
input, select { background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); padding: 12px; border-radius: 8px; color: white; font-family: inherit; outline: none; transition: 0.3s; }
input:focus, select:focus { border-color: var(--accent); box-shadow: 0 0 10px rgba(0, 234, 255, 0.2); }
.btn-submit { grid-column: 1 / -1; background: var(--accent); color: var(--bg-dark); padding: 12px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 16px; margin-top: 10px; transition: 0.3s; }
.btn-submit:hover { background: var(--accent-hover); box-shadow: 0 0 15px rgba(0, 234, 255, 0.5); }
.table-panel { background: var(--glass-bg); backdrop-filter: blur(12px); padding: 25px; border-radius: 20px; border: 1px solid var(--glass-border); }
table { width: 100%; border-collapse: collapse; font-size: 14px; }
th { text-align: left; color: var(--text-muted); padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.1); }
td { padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.03); color: #e0e0e0; }
tr:hover td { background: rgba(0, 234, 255, 0.03); }

/* STATUS INVENTARIO */
.status { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
.almacen { background: rgba(0, 255, 136, 0.15); color: #00ff88; border: 1px solid rgba(0, 255, 136, 0.3); } /* Verde */
.asignado { background: rgba(0, 150, 255, 0.15); color: #0096ff; border: 1px solid rgba(0, 150, 255, 0.3); } /* Azul */
.dañado { background: rgba(255, 51, 85, 0.15); color: #ff3355; border: 1px solid rgba(255, 51, 85, 0.3); } /* Rojo */
.reparacion { background: rgba(255, 170, 0, 0.15); color: #ffaa00; border: 1px solid rgba(255, 170, 0, 0.3); } /* Naranja */

.mac-text { font-family: 'Courier New', monospace; letter-spacing: 1px; color: #aaddff; }

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
            <a href="usuarios.php">👥 Usuarios</a>
            <a href="clientes.php">🛰 Clientes</a>
            <a href="tickets.php">🎫 Tickets</a>
            <a href="inventario.php" class="active">📦 Inventario</a>
            <a href="pagos.php">💰 Pagos</a>
            <a href="#">⚙ Configuración</a>
        </nav>
        <div style="text-align:center; margin-top:30px;">
            <a href="../index.php" style="color:#ff5577; text-decoration:none;">← Volver</a>
        </div>
    </aside>

    <main>
        <div class="main-header">
            <h1>Inventario & Hardware</h1>
        </div>

        <?= $mensaje ?>

        <div class="form-panel">
            <h3 style="margin-top:0; color:var(--text-muted); border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:10px;">Ingresar Nuevo Equipo</h3>
            
            <form method="POST" action="">
                <div class="form-grid">
                    
                    <div class="input-group">
                        <label>Modelo / Producto</label>
                        <select name="id_producto" required>
                            <option value="">Seleccione Equipo...</option>
                            <?php foreach($productos as $prod): ?>
                                <option value="<?= $prod['id_producto'] ?>">
                                    <?= $prod['nombre_marca'] . " - " . $prod['modelo'] . " (" . $prod['nombre_producto'] . ")" ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="input-group">
                        <label>MAC Address</label>
                        <input type="text" name="mac_address" placeholder="Ej: AA:BB:CC:11:22:33" maxlength="17" style="text-transform:uppercase;">
                        <small style="color:gray; font-size:10px; margin-top:2px;">Crucial para monitoreo WISP</small>
                    </div>

                    <div class="input-group">
                        <label>Número de Serie (S/N)</label>
                        <input type="text" name="numero_serie" placeholder="S/N del fabricante">
                    </div>

                    <div class="input-group">
                        <label>Estado Inicial</label>
                        <select name="estado">
                            <option value="En Almacén">🟢 En Almacén</option>
                            <option value="Asignado">🔵 Ya Asignado (Instalado)</option>
                            <option value="Dañado">🔴 Dañado / Defectuoso</option>
                            <option value="En Reparación">🟠 En Reparación</option>
                        </select>
                    </div>

                    <button type="submit" name="btn_registrar_item" class="btn-submit">GUARDAR EQUIPO</button>
                </div>
            </form>
        </div>

        <div class="table-panel">
            <h3 style="margin-top:0; color:var(--accent);">Hardware en Existencia</h3>
            <table>
                <thead>
                    <tr>
                        <th>Cat.</th>
                        <th>Marca / Modelo</th>
                        <th>MAC / Serie</th>
                        <th>Estado</th>
                        <th>Ubicación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($items) > 0): ?>
                        <?php foreach($items as $item): ?>
                        <tr>
                            <td><span style="font-size:11px; opacity:0.7;"><?= $item['nombre_categoria'] ?></span></td>
                            
                            <td>
                                <strong style="color:white;"><?= $item['nombre_marca'] ?></strong><br>
                                <?= $item['modelo'] ?>
                            </td>

                            <td>
                                <?php if($item['mac_address']): ?>
                                    <div class="mac-text">MAC: <?= $item['mac_address'] ?></div>
                                <?php endif; ?>
                                <?php if($item['numero_serie']): ?>
                                    <div style="font-size:11px; color:#888;">SN: <?= $item['numero_serie'] ?></div>
                                <?php endif; ?>
                            </td>
                            
                            <td>
                                <?php 
                                    $estado = $item['estado'];
                                    $clase = 'almacen'; // Default
                                    if ($estado == 'Asignado') $clase = 'asignado';
                                    if ($estado == 'Dañado') $clase = 'dañado';
                                    if ($estado == 'En Reparación') $clase = 'reparacion';
                                ?>
                                <span class="status <?= $clase ?>"><?= $estado ?></span>
                            </td>
                            
                            <td>
                                <?php if ($item['id_cliente_asignado']): ?>
                                    👤 <?= $item['nombres'] . " " . $item['apellido_paterno'] ?>
                                <?php else: ?>
                                    🏢 Bodega Principal
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center;">El inventario está vacío.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>

</body>
</html>
