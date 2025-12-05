<?php
session_start();
require '../db_con.php'; 

/* ============================================
   🔒 SEGURIDAD: SOLO ADMIN (Rol 1)
============================================ */
// 1. Si no hay sesión, al login
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php"); 
    exit();
}

// 2. Si hay sesión pero NO es admin (Ej: es Soporte Rol 3)
if ($_SESSION['rol'] != 1) {
    echo "<script>
            alert('⛔ ACCESO DENEGADO: No tienes permisos de Administrador para ver el módulo de Usuarios.');
            window.location.href='../dashboard.php';
          </script>";
    exit();
}

// Variable para la lógica visual del menú
$es_admin = true;
$mensaje = "";

/* ============================================
   🛑 LÓGICA: BLOQUEAR / ACTIVAR / BORRAR
============================================ */
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $id_target = $_GET['id'];
    
    if ($id_target == $_SESSION['id_usuario']) {
        $mensaje = "<div class='alert error'>⛔ No puedes bloquear o eliminar tu propia cuenta.</div>";
    } else {
        // A. BLOQUEAR / ACTIVAR
        if ($_GET['accion'] == 'toggle') {
            try {
                $check = $conn->query("SELECT activo FROM usuarios WHERE id_usuario = $id_target")->fetch_assoc();
                $nuevo_estado = ($check['activo'] == 1) ? 0 : 1;
                
                $stmt = $conn->prepare("UPDATE usuarios SET activo = ? WHERE id_usuario = ?");
                $stmt->bind_param("ii", $nuevo_estado, $id_target);
                $stmt->execute();
                
                $estado_txt = ($nuevo_estado == 1) ? "Reactivado" : "Bloqueado";
                $mensaje = "<div class='alert success'>🔄 Usuario $estado_txt correctamente.</div>";
            } catch (Exception $e) {
                $mensaje = "<div class='alert error'>Error al cambiar estado.</div>";
            }
        }
        // B. ELIMINAR
        elseif ($_GET['accion'] == 'borrar') {
            try {
                $stmt = $conn->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
                $stmt->bind_param("i", $id_target);
                $stmt->execute();
                $mensaje = "<div class='alert success'>🗑️ Usuario eliminado permanentemente.</div>";
            } catch (Exception $e) {
                if ($conn->errno == 1451) {
                    $mensaje = "<div class='alert error'>⚠️ No puedes eliminar este usuario porque tiene historial. Mejor bloquéalo.</div>";
                } else {
                    $mensaje = "<div class='alert error'>Error: " . $e->getMessage() . "</div>";
                }
            }
        }
    }
}

/* ============================================
   📝 LÓGICA: REGISTRAR NUEVO USUARIO
============================================ */
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
        $pass_hash = password_hash($password, PASSWORD_BCRYPT);
        
        $stmt = $conn->prepare("INSERT INTO usuarios (username, email, password_hash, nombres, apellido_paterno, apellido_materno, telefono, id_rol, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("sssssssi", $username, $email, $pass_hash, $nombres, $apellido_p, $apellido_m, $telefono, $id_rol);
        
        if ($stmt->execute()) {
            $mensaje = "<div class='alert success'>✅ Usuario <b>$username</b> creado correctamente.</div>";
        }
    } catch (Exception $e) {
        if ($conn->errno == 1062) {
             $mensaje = "<div class='alert error'>⚠️ Error: El usuario o email ya existe.</div>";
        } else {
             $mensaje = "<div class='alert error'>Error: " . $e->getMessage() . "</div>";
        }
    }
}

/* ============================================
   📌 CONSULTAS
============================================ */
$roles = $conn->query("SELECT * FROM roles")->fetch_all(MYSQLI_ASSOC);
$sql_usuarios = "SELECT u.*, r.nombre_rol FROM usuarios u JOIN roles r ON u.id_rol = r.id_rol ORDER BY u.id_rol ASC, u.fecha_registro DESC";
$lista_usuarios = $conn->query($sql_usuarios)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Gestión de Usuarios | KoLine</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="icon" type="image/png" href="../imagenes/logo.png">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* =========================================
   🎨 ESTILOS GENERALES
   ========================================= */
:root { --bg-dark: #020c1b; --accent: #00eaff; --accent-hover: #00cce6; --glass-bg: rgba(13, 25, 40, 0.85); --glass-border: rgba(0, 234, 255, 0.15); --text-main: #ffffff; --text-muted: #8899a6; }
body { font-family: 'Poppins', sans-serif; background: radial-gradient(circle at top center, #0f3460 0%, var(--bg-dark) 80%); background-color: var(--bg-dark); background-attachment: fixed; margin: 0; color: var(--text-main); min-height: 100vh; }

.wrap { max-width: 1200px; margin: 40px auto; display: grid; grid-template-columns: 260px 1fr; gap: 30px; padding: 20px; align-items: start; }

/* SIDEBAR STICKY */
.sidebar { 
    background: var(--glass-bg); backdrop-filter: blur(12px); padding: 30px 20px; border-radius: 20px; border: 1px solid var(--glass-border); 
    position: sticky; top: 20px; max-height: calc(100vh - 40px); overflow-y: auto; scrollbar-width: none;
}
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
input, select { background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); padding: 12px; border-radius: 8px; color: white; font-family: inherit; outline: none; transition: 0.3s; }
input:focus, select:focus { border-color: var(--accent); box-shadow: 0 0 10px rgba(0, 234, 255, 0.2); }
.btn-submit { grid-column: 1 / -1; background: var(--accent); color: var(--bg-dark); padding: 12px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 16px; margin-top: 10px; transition: 0.3s; }
.btn-submit:hover { background: var(--accent-hover); box-shadow: 0 0 15px rgba(0, 234, 255, 0.5); }
.table-panel { background: var(--glass-bg); backdrop-filter: blur(12px); padding: 25px; border-radius: 20px; border: 1px solid var(--glass-border); }
table { width: 100%; border-collapse: collapse; font-size: 14px; }
th { text-align: left; color: var(--text-muted); padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.1); }
td { padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.03); color: #e0e0e0; vertical-align: middle; }
tr:hover td { background: rgba(0, 234, 255, 0.03); }

/* BADGES & BOTONES */
.badge { padding: 5px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
.rol-admin { background: rgba(255, 51, 102, 0.15); color: #ff3366; border: 1px solid rgba(255, 51, 102, 0.3); }
.rol-cliente { background: rgba(0, 234, 255, 0.15); color: var(--accent); border: 1px solid rgba(0, 234, 255, 0.3); }
.rol-soporte { background: rgba(255, 170, 0, 0.15); color: #ffaa00; border: 1px solid rgba(255, 170, 0, 0.3); }

.btn-action { display: inline-flex; justify-content: center; align-items: center; width: 32px; height: 32px; border-radius: 8px; margin-right: 5px; text-decoration: none; font-size: 16px; transition: 0.3s; border: 1px solid transparent; }
.btn-block { background: rgba(255, 170, 0, 0.15); color: #ffaa00; border-color: rgba(255, 170, 0, 0.3); }
.btn-block:hover { background: #ffaa00; color: #000; }
.btn-activate { background: rgba(0, 255, 136, 0.15); color: #00ff88; border-color: rgba(0, 255, 136, 0.3); }
.btn-activate:hover { background: #00ff88; color: #000; }
.btn-delete { background: rgba(255, 51, 85, 0.15); color: #ff3355; border-color: rgba(255, 51, 85, 0.3); }
.btn-delete:hover { background: #ff3355; color: white; }

.alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; }
.alert.success { background: rgba(0, 255, 136, 0.1); border: 1px solid #00ff88; color: #00ff88; }
.alert.error { background: rgba(255, 51, 85, 0.1); border: 1px solid #ff3355; color: #ff3355; }

@media (max-width: 768px) { .wrap { grid-template-columns: 1fr; } .sidebar { position: relative; top: 0; max-height: none; } }
</style>
</head>

<body>
<div class="wrap">
    
    <aside class="sidebar">
        <img src="../imagenes/logo.png" alt="KoLine">
        
        <nav>
            <a href="../dashboard.php">📊 Dashboard</a>

            <a href="usuarios.php" class="active">👥 Usuarios</a>

            <a href="clientes.php">🛰 Clientes</a>
            <a href="tickets.php">🎫 Tickets</a>
            <a href="inventario.php">📦 Inventario</a>

            <a href="pagos.php">💰 Pagos</a>
            <a href="../configuracion.php">⚙ Configuración</a>
        </nav>
        <div style="text-align:center; margin-top:30px;">
            <a href="../dashboard.php" style="color:#ff5577; text-decoration:none;">← Volver</a>
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
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($lista_usuarios) > 0): ?>
                        <?php foreach($lista_usuarios as $u): ?>
                        <tr>
                            <td>#<?= $u['id_usuario'] ?></td>
                            <td>
                                <strong style="color:white;"><?= $u['nombres'] . " " . $u['apellido_paterno'] ?></strong><br>
                                <span style="font-size:12px; color:var(--text-muted);"><?= $u['email'] ?></span>
                            </td>
                            <td>
                                <?php 
                                    $rol = $u['id_rol'];
                                    $class = ($rol == 1) ? 'rol-admin' : (($rol == 2) ? 'rol-cliente' : 'rol-soporte');
                                ?>
                                <span class="badge <?= $class ?>"><?= $u['nombre_rol'] ?></span>
                            </td>
                            <td>
                                <?php if($u['activo'] == 1): ?>
                                    <span style="color:#00ff88;">● Activo</span>
                                <?php else: ?>
                                    <span style="color:#ff3355;">● Bloqueado</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($u['activo'] == 1): ?>
                                    <a href="?accion=toggle&id=<?= $u['id_usuario'] ?>" class="btn-action btn-block" title="Bloquear Acceso" onclick="return confirm('¿Bloquear acceso a este usuario?');">
                                        🔒
                                    </a>
                                <?php else: ?>
                                    <a href="?accion=toggle&id=<?= $u['id_usuario'] ?>" class="btn-action btn-activate" title="Reactivar Acceso">
                                        🔓
                                    </a>
                                <?php endif; ?>

                                <a href="?accion=borrar&id=<?= $u['id_usuario'] ?>" class="btn-action btn-delete" title="Eliminar Definitivamente" onclick="return confirm('⚠ ¿Estás seguro de eliminar este usuario? Esta acción es irreversible.');">
                                    🗑️
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center;">No hay usuarios registrados.</td></tr>
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
