<?php
session_start();
require 'db_con.php';

// --- SEGURIDAD: CANDADO ---
// Si NO está logueado O es un Cliente (Rol 2), lo sacamos
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] == 2) {
    header("Location: index.php");
    exit();
}

$total_usuarios = 0;
$total_clientes = 0;
$ultimos_usuarios = [];

try {
    // 1. Total de usuarios registrados
    $sql_total = "SELECT COUNT(*) FROM usuarios";
    $res = $conn->query($sql_total);
    if($res) $total_usuarios = $res->fetch_row()[0];

    // 2. Total de clientes activos (Rol 2)
    $sql_clientes = "SELECT COUNT(*) FROM usuarios WHERE id_rol = 2 AND activo = 1";
    $res = $conn->query($sql_clientes);
    if($res) $total_clientes = $res->fetch_row()[0];

    // 3. Últimos 5 registros (Uniendo con la tabla de roles para saber qué son)
    $sql_latest = "SELECT u.*, r.nombre_rol 
                   FROM usuarios u 
                   JOIN roles r ON u.id_rol = r.id_rol 
                   ORDER BY u.fecha_registro DESC LIMIT 5";
    $res = $conn->query($sql_latest);
    if($res) $ultimos_usuarios = $res->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    die("Error de BD: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Admin Dashboard - KoLine</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
  :root{ --bg1:#001f3f; --bg2:#0078ff; --accent:#00c6ff; --glass: rgba(255,255,255,0.06); }
  body{ font-family:'Poppins',sans-serif; margin:0; background: linear-gradient(135deg,var(--bg1), #004ea8, var(--bg2)); color:#eaf6ff; min-height:100vh; }
  .wrap{ max-width:1100px; margin:30px auto; display:grid; grid-template-columns: 260px 1fr; gap:20px; padding:20px; }
  .sidebar, .card, .panel { background:var(--glass); padding:20px; border-radius:14px; border:1px solid rgba(255,255,255,0.1); }
  .sidebar h2 { color:var(--accent); margin-top:0; }
  .cards { display:flex; gap:20px; margin-bottom:20px; }
  .card { flex:1; } .card h3 { margin:0 0 10px 0; color:#ccc; font-size:14px; } .card p { font-size:32px; font-weight:bold; margin:0; }
  table { width:100%; border-collapse:collapse; margin-top:10px; }
  th { text-align:left; color:#88cbff; border-bottom:1px solid rgba(255,255,255,0.1); padding:10px; }
  td { padding:12px 10px; border-bottom:1px solid rgba(255,255,255,0.05); }
  .badge { padding:4px 8px; border-radius:10px; font-size:12px; font-weight:bold; }
  .badge.admin { background:#ff3366; color:white; }
  .badge.cliente { background:#00eaff; color:#003344; }
  a.logout { display:inline-block; margin-top:20px; color:#ff99aa; text-decoration:none; }
</style>
</head>
<body>
<div class="wrap">
  <aside class="sidebar">
    <h2>KoLine Admin</h2>
    <p>Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?></strong></p>
    <nav style="margin-top:20px; display:flex; flex-direction:column; gap:10px;">
      <a href="#" style="color:white; font-weight:bold;">Dashboard</a>
      <a href="#" style="color:#ccc;">Usuarios</a>
      <a href="#" style="color:#ccc;">Configuración</a>
    </nav>
    <a href="index.php" class="logout">← Cerrar Sesión</a>
  </aside>

  <main>
    <h1>Panel de Control</h1>
    
    <div class="cards">
      <div class="card">
        <h3>TOTAL USUARIOS</h3>
        <p><?php echo $total_usuarios; ?></p>
      </div>
      <div class="card">
        <h3>CLIENTES ACTIVOS</h3>
        <p><?php echo $total_clientes; ?></p>
      </div>
      <div class="card">
        <h3>SISTEMA</h3>
        <p style="font-size:18px; color:#00eaff;">En Línea 🟢</p>
      </div>
    </div>

    <div class="panel">
      <h3>Últimos Registros</h3>
      <table>
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Email</th>
            <th>Rol</th>
            <th>Fecha</th>
          </tr>
        </thead>
        <tbody>
          <?php if(!empty($ultimos_usuarios)): ?>
            <?php foreach($ultimos_usuarios as $u): ?>
              <tr>
                <td><?php echo htmlspecialchars($u['nombres'] . ' ' . $u['apellido_paterno']); ?></td>
                <td><?php echo htmlspecialchars($u['email']); ?></td>
                <td>
                    <?php 
                        if($u['id_rol'] == 1) echo '<span class="badge admin">Admin</span>';
                        elseif($u['id_rol'] == 2) echo '<span class="badge cliente">Cliente</span>';
                        else echo '<span class="badge">Staff</span>';
                    ?>
                </td>
                <td><?php echo date('d/m/Y', strtotime($u['fecha_registro'])); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="4">No hay usuarios registrados aún.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
</body>
</html>
