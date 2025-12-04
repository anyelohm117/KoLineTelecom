
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
   📝 LÓGICA: REGISTRAR NUEVO CLIENTE (DOBLE INSERT)
============================================ */
$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_registrar_cliente'])) {
    // 1. Datos Personales (Tabla usuarios)
    $nombres = trim($_POST['nombres']);
    $apellido_p = trim($_POST['apellido_p']);
    $apellido_m = trim($_POST['apellido_m']);
    $telefono = trim($_POST['telefono']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $rol = 2; // Rol 2 = Cliente

    // 2. Datos Técnicos (Tabla clientes)
    $direccion = trim($_POST['direccion']);
    $coordenadas = trim($_POST['coordenadas']);
    $ip = trim($_POST['ip']);
    $id_plan = $_POST['id_plan'];
    $fecha_instalacion = $_POST['fecha_instalacion'];

    // Iniciar Transacción (Todo o nada)
    $conn->begin_transaction();

    try {
        // A. Insertar Usuario
        $pass_hash = password_hash($password, PASSWORD_BCRYPT); // Encriptar contraseña
        $stmt1 = $conn->prepare("INSERT INTO usuarios (username, email, password_hash, nombres, apellido_paterno, apellido_materno, telefono, id_rol) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt1->bind_param("sssssssi", $username, $email, $pass_hash, $nombres, $apellido_p, $apellido_m, $telefono, $rol);
        $stmt1->execute();
        
        // Obtener el ID del usuario recién creado
        $id_nuevo_usuario = $conn->insert_id;

        // B. Insertar Cliente (Vinculado al ID anterior)
        $stmt2 = $conn->prepare("INSERT INTO clientes (id_usuario, direccion_instalacion, coordenadas_gps, ip_asignada, id_plan, fecha_instalacion) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt2->bind_param("isssis", $id_nuevo_usuario, $direccion, $coordenadas, $ip, $id_plan, $fecha_instalacion);
        $stmt2->execute();

        // Si todo salió bien, confirmamos cambios
        $conn->commit();
        $mensaje = "<div class='alert success'>✅ Cliente <b>$nombres</b> registrado y activo.</div>";

    } catch (mysqli_sql_exception $exception) {
        $conn->rollback(); // Deshacer cambios si hay error
        if ($exception->getCode() == 1062) {
             $mensaje = "<div class='alert error'>⚠️ Error: El Usuario, Email o IP ya están registrados.</div>";
        } else {
             $mensaje = "<div class='alert error'>Error: " . $exception->getMessage() . "</div>";
        }
    }
}

/* ============================================
   📌 CONSULTAS
============================================ */
// Obtener Planes para el Select
$planes = $conn->query("SELECT * FROM planes_internet")->fetch_all(MYSQLI_ASSOC);

// Obtener Lista de Clientes Completa
$sql_clientes = "SELECT c.*, u.nombres, u.apellido_paterno, u.telefono, u.email, u.activo, p.nombre_plan, p.velocidad_mbps 
                 FROM clientes c
                 JOIN usuarios u ON c.id_usuario = u.id_usuario
                 JOIN planes_internet p ON c.id_plan = p.id_plan
                 ORDER BY u.fecha_registro DESC";
$lista_clientes = $conn->query($sql_clientes)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Gestión de Clientes | KoLine</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
/* Reutilizamos los estilos base */
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
.alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; }
.alert.success { background: rgba(0, 255, 136, 0.1); border: 1px solid #00ff88; color: #00ff88; }
.alert.error { background: rgba(255, 51, 85, 0.1); border: 1px solid #ff3355; color: #ff3355; }

/* Estilos Específicos para formulario dividido */
.section-title { grid-column: 1 / -1; margin-top: 10px; margin-bottom: 10px; color: white; border-left: 3px solid var(--accent); padding-left: 10px; font-size: 1.1rem; }
.form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }

/* Badge de Estado */
.active-dot { height: 10px; width: 10px; background-color: #00ff88; border-radius: 50%; display: inline-block; margin-right: 5px; box-shadow: 0 0 5px #00ff88; }
.inactive-dot { height: 10px; width: 10px; background-color: #ff3355; border-radius: 50%; display: inline-block; margin-right: 5px; }

@media (max-width: 768px) { .wrap { grid-template-columns: 1fr; } }
</style>
</head>

<body>
<div class="wrap">
    
    <aside class="sidebar">
        <img src="../imagenes/logo.png" alt="KoLine">
        <nav>
            <a href="../dashboard.php">📊 Dashboard</a>
            <a href="#">👥 Usuarios</a>
            <a href="clientes.php" class="active">🛰 Clientes</a>
            <a href="#">🎫 Tickets</a>
            <a href="inventario.php">📦 Inventario</a>
            <a href="pagos.php">💰 Pagos</a>
            <a href="#">⚙ Configuración</a>
        </nav>
        <div style="text-align:center; margin-top:30px;">
            <a href="../index.php" style="color:#ff5577; text-decoration:none;">← Volver</a>
        </div>
    </aside>

    <main>
        <div class="main-header">
            <h1>Cartera de Clientes</h1>
        </div>

        <?= $mensaje ?>

        <div class="form-panel">
            <h3 style="margin-top:0; color:var(--text-muted); padding-bottom:10px;">Alta de Nuevo Servicio</h3>
            
            <form method="POST" action="">
                <div class="form-grid">
                    
                    <div class="section-title">👤 Datos Personales & Acceso</div>
                    
                    <div class="input-group">
                        <label>Nombre(s)</label>
                        <input type="text" name="nombres" required placeholder="Ej: Juan Antonio">
                    </div>
                    <div class="input-group">
                        <label>Apellido Paterno</label>
                        <input type="text" name="apellido_p" required placeholder="Ej: Pérez">
                    </div>
                    <div class="input-group">
                        <label>Apellido Materno</label>
                        <input type="text" name="apellido_m" placeholder="Ej: López">
                    </div>
                    <div class="input-group">
                        <label>Teléfono / Celular</label>
                        <input type="tel" name="telefono" required placeholder="Ej: 5512345678">
                    </div>
                    <div class="input-group">
                        <label>Email (Contacto)</label>
                        <input type="email" name="email" required placeholder="cliente@correo.com">
                    </div>

                    <div class="input-group">
                        <label>Usuario (Login)</label>
                        <input type="text" name="username" required placeholder="Ej: jperez2024">
                    </div>
                    <div class="input-group">
                        <label>Contraseña</label>
                        <input type="password" name="password" required placeholder="******">
                    </div>

                    <div class="section-title">📡 Datos de Instalación</div>

                    <div class="input-group" style="grid-column: span 2;">
                        <label>Dirección de Instalación</label>
                        <input type="text" name="direccion" required placeholder="Calle, Número, Colonia, Referencias">
                    </div>
                    
                    <div class="input-group">
                        <label>Coordenadas GPS</label>
                        <input type="text" name="coordenadas" placeholder="Ej: 19.4326, -99.1332">
                    </div>

                    <div class="input-group">
                        <label>Plan de Internet</label>
                        <select name="id_plan" required>
                            <?php foreach($planes as $p): ?>
                                <option value="<?= $p['id_plan'] ?>">
                                    <?= $p['nombre_plan'] . " (" . $p['velocidad_mbps'] . " MB) - $" . $p['precio_mensual'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="input-group">
                        <label>IP Asignada (WAN)</label>
                        <input type="text" name="ip" required placeholder="Ej: 192.168.50.10">
                    </div>

                    <div class="input-group">
                        <label>Fecha Instalación</label>
                        <input type="date" name="fecha_instalacion" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <button type="submit" name="btn_registrar_cliente" class="btn-submit">REGISTRAR CLIENTE</button>
                </div>
            </form>
        </div>

        <div class="table-panel">
            <h3 style="margin-top:0; color:var(--accent);">Clientes Activos</h3>
            <table>
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Dirección / GPS</th>
                        <th>Plan Contratado</th>
                        <th>IP Asignada</th>
                        <th>Contacto</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($lista_clientes) > 0): ?>
                        <?php foreach($lista_clientes as $c): ?>
                        <tr>
                            <td>
                                <strong style="color:white; font-size:15px;"><?= $c['nombres'] . " " . $c['apellido_paterno'] ?></strong><br>
                                <span style="font-size:11px; color:var(--accent);">@<?= $c['nombres'] // Asumiendo username en future ?></span>
                            </td>
                            
                            <td style="max-width:200px;">
                                <div style="font-size:12px; line-height:1.2;"><?= substr($c['direccion_instalacion'], 0, 40) ?>...</div>
                                <?php if($c['coordenadas_gps']): ?>
                                    <a href="https://www.google.com/maps/search/?api=1&query=<?= $c['coordenadas_gps'] ?>" target="_blank" style="color:#ffaa00; font-size:11px; text-decoration:none;">📍 Ver Mapa</a>
                                <?php endif; ?>
                            </td>

                            <td>
                                <span style="color:#fff;"><?= $c['nombre_plan'] ?></span><br>
                                <small style="color:#888;"><?= $c['velocidad_mbps'] ?> Mbps</small>
                            </td>

                            <td>
                                <span style="font-family:monospace; background:rgba(255,255,255,0.1); padding:2px 5px; border-radius:4px;"><?= $c['ip_asignada'] ?></span>
                            </td>

                            <td>
                                <div style="font-size:12px;">📞 <?= $c['telefono'] ?></div>
                                <div style="font-size:12px;">📧 <?= $c['email'] ?></div>
                            </td>

                            <td>
                                <?php if($c['activo'] == 1): ?>
                                    <span class="active-dot"></span> <span style="color:#00ff88; font-weight:bold;">Activo</span>
                                <?php else: ?>
                                    <span class="inactive-dot"></span> <span style="color:#ff3355; font-weight:bold;">Corte</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center;">No hay clientes registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>

</body>
</html>
