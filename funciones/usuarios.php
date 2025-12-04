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
   📝 LÓGICA: REGISTRAR NUEVO USUARIO (STAFF)
============================================ */
$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_registrar_usuario'])) {
    $nombres = trim($_POST['nombres']);
    $apellido_p = trim($_POST['apellido_p']);
    $apellido_m = trim($_POST['apellido_m']);
    $telefono = trim($_POST['telefono']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $id_rol = $_POST['id_rol'];

    try {
        // Encriptar contraseña
        $pass_hash = password_hash($password, PASSWORD_BCRYPT);
        
        $stmt = $conn->prepare("INSERT INTO usuarios (username, email, password_hash, nombres, apellido_paterno, apellido_materno, telefono, id_rol, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("sssssssi", $username, $email, $pass_hash, $nombres, $apellido_p, $apellido_m, $telefono, $id_rol);
        
        if ($stmt->execute()) {
            $mensaje = "<div class='alert success'>✅ Usuario <b>$username</b> creado correctamente.</div>";
        } else {
            $mensaje = "<div class='alert error'>❌ Error al crear usuario.</div>";
        }
    } catch (Exception $e) {
        if ($conn->errno == 1062) {
             $mensaje = "<div class='alert error'>⚠️ Error: El nombre de usuario o email ya existe.</div>";
        } else {
             $mensaje = "<div class='alert error'>Error: " . $e->getMessage() . "</div>";
        }
    }
}

/* ============================================
   📌 CONSULTAS
============================================ */
// 1. Obtener Roles para el select
$roles = $conn->query("SELECT * FROM roles")->fetch_all(MYSQLI_ASSOC);

// 2. Obtener Lista de Usuarios con su Rol
$sql_usuarios = "SELECT u.*, r.nombre_rol 
                 FROM usuarios u 
                 JOIN roles r ON u.id_rol = r.id_rol 
                 ORDER BY u.id_rol ASC, u.fecha_registro DESC";
$lista_usuarios = $conn->query($sql_usuarios)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Gestión de Usuarios | KoLine</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
/* =========================================
   🎨 ESTILOS (Idénticos al resto)
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

/* ROLES BADGES */
.badge { padding: 5px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
.rol-admin { background: rgba(255, 51, 102, 0.15); color: #ff3366; border: 1px solid rgba(255, 51, 102, 0.3); }
.rol-cliente { background: rgba(0, 234, 255, 0.15); color: var(--accent); border: 1px solid rgba(0, 234, 255, 0.3); }
.rol-soporte { background: rgba(255, 170, 0, 0.15); color: #ffaa00; border: 1px solid rgba(255, 170, 0, 0.3); }

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
            <a href="../index.php">📊 Dashboard</a>
            <a href="usuarios.php" class="active">👥 Usuarios</a>
            <a href="clientes.php">🛰 Clientes</a>
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
            <h1>Administración de Usuarios</h1>
        </div>

        <?= $mensaje ?>

        <div class="form-panel">
            <h3 style="margin-top:0; color:var(--text-muted); border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:10px;">Crear Nuevo Usuario (Staff)</h3>
            <p style="font-size:12px; color:#ffaa00; background:rgba(255,170,0,0.1); padding:10px; border-radius:5px;">
                ⚠️ Nota: Si deseas crear un cliente con servicio de internet, ve al módulo <b><a href="clientes.php" style="color:#ffaa00;">Clientes</a></b>. Usa este formulario solo para Administradores o Técnicos.
            </p>
            
            <form method="POST" action="">
                <div class="form-grid">
                    
                    <div class="input-group">
                        <label>Nombre(s)</label>
                        <input type="text" name="nombres" required placeholder="Nombre del empleado">
                    </div>
                    <div class="input-group">
                        <label>Apellido Paterno</label>
                        <input type="text" name="apellido_p" required placeholder="Apellido">
                    </div>
                    <div class="input-group">
                        <label>Apellido Materno</label>
                        <input type="text" name="apellido_m" placeholder="Opcional">
                    </div>
                    <div class="input-group">
                        <label>Rol de Acceso</label>
                        <select name="id_rol" required>
                            <?php foreach($roles as $r): ?>
                                <option value="<?= $r['id_rol'] ?>"><?= $r['nombre_rol'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="input-group">
                        <label>Email</label>
                        <input type="email" name="email" required placeholder="correo@empresa.com">
                    </div>
                    <div class="input-group">
                        <label>Teléfono</label>
                        <input type="tel" name="telefono" placeholder="5511223344">
                    </div>

                    <div class="input-group">
                        <label>Usuario (Login)</label>
                        <input type="text" name="username" required placeholder="Ej: admin2">
                    </div>
                    <div class="input-group">
                        <label>Contraseña</label>
                        <input type="password" name="password" required placeholder="******">
                    </div>

                    <button type="submit" name="btn_registrar_usuario" class="btn-submit">CREAR USUARIO</button>
                </div>
            </form>
        </div>

        <div class="table-panel">
            <h3 style="margin-top:0; color:var(--accent);">Usuarios del Sistema</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre Completo</th>
                        <th>Usuario / Email</th>
                        <th>Rol</th>
                        <th>Fecha Registro</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($lista_usuarios) > 0): ?>
                        <?php foreach($lista_usuarios as $u): ?>
                        <tr>
                            <td>#<?= $u['id_usuario'] ?></td>
                            <td>
                                <strong style="color:white;"><?= $u['nombres'] . " " . $u['apellido_paterno'] ?></strong>
                                <br><small style="color:var(--text-muted);"><?= $u['telefono'] ?></small>
                            </td>
                            <td>
                                <span style="color:var(--accent);"><?= $u['username'] ?></span><br>
                                <span style="font-size:12px;"><?= $u['email'] ?></span>
                            </td>
                            <td>
                                <?php 
                                    $rol = $u['id_rol'];
                                    $class = '';
                                    if ($rol == 1) $class = 'rol-admin';
                                    elseif ($rol == 2) $class = 'rol-cliente';
                                    else $class = 'rol-soporte';
                                ?>
                                <span class="badge <?= $class ?>"><?= $u['nombre_rol'] ?></span>
                            </td>
                            <td><?= date("d/m/Y", strtotime($u['fecha_registro'])) ?></td>
                            <td>
                                <?php if($u['activo'] == 1): ?>
                                    <span style="color:#00ff88;">● Activo</span>
                                <?php else: ?>
                                    <span style="color:#ff3355;">● Bloqueado</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center;">No hay usuarios registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>

</body>
</html>
