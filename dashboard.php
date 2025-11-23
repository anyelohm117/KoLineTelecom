<?php
// 1. INICIAR LA SESIÓN
session_start();

// 2. INCLUIR EL ARCHIVO DE CONEXIÓN
// (Asegúrate de tener 'db_con.php' en la misma carpeta)
require 'db_con.php'; 

// 3. OBTENER LOS DATOS QUE EL DASHBOARD NECESITA
// (Iniciamos las variables para que existan siempre)
$total = 0;
$active = 0;
$departments = [];
$latest = [];

// (Opcional) Poner un valor por si no se ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    $_SESSION['usuario'] = 'Admin'; // Un valor de ejemplo
}

try {
    // --- Para el Card "Total empleados" ---
    $sql_total = "SELECT COUNT(*) FROM empleados";
    $result_total = $conn->query($sql_total);
    if ($result_total) $total = $result_total->fetch_row()[0]; 

    // --- Para el Card "Activos" ---
    $sql_active = "SELECT COUNT(*) FROM empleados WHERE activo = 1";
    $result_active = $conn->query($sql_active);
    if ($result_active) $active = $result_active->fetch_row()[0];

    // --- Para el Card "Departamentos" ---
    $sql_deps = "SELECT DISTINCT departamento FROM empleados WHERE departamento IS NOT NULL AND departamento != ''";
    $result_deps = $conn->query($sql_deps);
    if ($result_deps) $departments = $result_deps->fetch_all(MYSQLI_ASSOC); 

    // --- Para la Tabla "Últimos 5 empleados" ---
    $sql_latest = "SELECT * FROM empleados ORDER BY fecha_ingreso DESC LIMIT 5";
    $result_latest = $conn->query($sql_latest);
    if ($result_latest) $latest = $result_latest->fetch_all(MYSQLI_ASSOC);


} catch (Exception $e) {
    // Si hay un error, se mostrará en la página
    die("Error al consultar los datos: " . $e->getMessage() . " (Verifica que la tabla 'empleados' existe y 'db_con.php' es correcto)");
}

// 4. CERRAMOS LA CONEXIÓN
$conn->close();

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Dashboard - KoLine Telecom</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
  :root{
    --bg1:#001f3f;
    --bg2:#0078ff;
    --accent:#00c6ff;
    --glass: rgba(255,255,255,0.06);
    --card-shadow: 0 8px 30px rgba(0,0,0,0.45);
  }
  *{box-sizing:border-box}
  body{
    font-family:'Poppins',sans-serif;
    margin:0;
    min-height:100vh;
    background: linear-gradient(135deg,var(--bg1), #004ea8 40%, var(--bg2));
    color:#eaf6ff;
    -webkit-font-smoothing:antialiased;
  }
  .wrap{
    width:96%;             /* <- CORREGIDO */
    max-width:1100px;      /* <- CORREGIDO */
    margin:36px auto;
    display:grid;
    grid-template-columns: 260px 1fr;
    gap:22px;
    align-items:start;
  }

  /* SIDEBAR */
  .sidebar{
    background:var(--glass);
    padding:22px;
    border-radius:14px;
    box-shadow:var(--card-shadow);
    border:1px solid rgba(255,255,255,0.06);
    height: calc(100vh - 72px);
    position:sticky;
    top:36px;
  }
  .brand{ font-size:20px; font-weight:700; color:var(--accent); text-shadow:0 0 10px rgba(0,198,255,0.12) }
  .small{font-size:13px;color:#cfefff; opacity:0.9;margin-top:6px}
  nav{margin-top:22px}
  nav a{display:block;padding:10px;border-radius:8px;color:#dff6ff;text-decoration:none;margin-bottom:8px}
  nav a.active, nav a:hover{background:linear-gradient(90deg, rgba(0,120,255,0.12), rgba(0,198,255,0.08));box-shadow:0 4px 12px rgba(0,198,255,0.06)}

  /* MAIN */
  .main{
    min-height:600px;
  }
  .topbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:18px;
  }
  .search{
    display:flex;
    gap:12px;
    align-items:center;
  }
  .search input{
    padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.08);
    background: rgba(255,255,255,0.03); color:#eaf6ff; width:360px; outline:none;
  }

  .cards{
    display:flex; gap:14px; margin-bottom:18px;
  }
  .card{
    background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
    padding:18px;
    border-radius:12px;
    box-shadow:var(--card-shadow);
    border:1px solid rgba(255,255,255,0.045);
    flex:1;
  }
  .card h3{margin:0;font-size:18px;color:var(--accent)}
  .card p{margin:6px 0 0;font-size:28px;font-weight:700;color:#fff}

  /* table */
  .panel{
    background:var(--glass);
    padding:16px;border-radius:12px;border:1px solid rgba(255,255,255,0.05); box-shadow:var(--card-shadow);
    overflow-x: auto; /* <- AÑADIDO */
  }
  table{
    width:100%; border-collapse:collapse; color:#eaffff;
  }
  thead th{
    text-align:left; font-size:13px; padding:10px 8px; color:#cfefff; font-weight:600;
    border-bottom:1px solid rgba(255,255,255,0.04)
  }
  tbody td{ 
    padding:12px 8px; 
    border-bottom:1px dashed rgba(255,255,255,0.03); 
    font-size:14px;
    white-space: nowrap; /* <- AÑADIDO */
  }
  .badge { padding:6px 10px;border-radius:999px;font-weight:600; font-size:13px; display:inline-block }
  .badge.act { background:linear-gradient(90deg,#00eaff,#0078ff); color:#003046 }
  .badge.off { background:rgba(255,255,255,0.06); color:#d8f3ff }

  /* botones */
  .btn{
    background:linear-gradient(90deg,#0078ff,#00c6ff);
    border:none;padding:10px 14px;border-radius:10px;color:#fff;font-weight:600;cursor:pointer;
    box-shadow:0 6px 18px rgba(0,198,255,0.12)
  }
  .btn.ghost{ background:transparent;border:1px solid rgba(255,255,255,0.06) }

  /* modal simple */
  .modal{
    position:fixed; inset:0; display:none; align-items:center; justify-content:center;
    background:rgba(0,0,0,0.5); z-index:60;
  }
  .modal .cardbox{
    width:520px; max-width:94%; background: linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.02));
    padding:18px;border-radius:12px;border:1px solid rgba(255,255,255,0.06);
  }

  /* --- INICIO ESTILOS FORMULARIO MODAL (AÑADIDO) --- */
  .modal input {
    width: 100%;
    padding: 10px 12px;
    border-radius: 10px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(255, 255, 255, 0.03);
    color: #eaf6ff;
    outline: none;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    box-sizing: border-box; /* Importante */
  }
  .modal input::placeholder {
    color: rgba(255, 255, 255, 0.4);
  }
  /* --- FIN ESTILOS FORMULARIO MODAL --- */

  .form-row{
    display:flex; 
    gap:10px;
    margin-bottom: 12px; /* <- AÑADIDO */
  }
  .form-row .col{flex:1}

  footer{margin-top:18px;color:#bfefff;font-size:13px;opacity:0.9}

  @media(max-width:900px){
    .wrap{grid-template-columns:1fr; padding:12px}
    .sidebar{position:static;height:auto}
    .search input{width:180px}
  }
</style>
</head>
<body>

<div class="wrap">
  <aside class="sidebar">
    <div class="brand">KoLine Telecom</div>
    <div class="small">Dashboard de empleados</div>

    <nav>
      <a href="#" class="active">Empleados</a>
      <a href="#">Departamentos</a>
      <a href="#">Reportes</a>
      <a href="#">Ajustes</a>
      <a href="login.php" style="margin-top:12px;color:#ffccd5">Cerrar sesión</a>
    </nav>

    <footer>
      Usuario: <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong>
      <div style="margin-top:10px">Total empleados: <strong><?php echo $total; ?></strong></div>
    </footer>
  </aside>

  <main class="main">
    <div class="topbar">
      <div>
        <h1 style="margin:0;font-size:20px">Empleados</h1>
        <div style="font-size:13px; color:#cfefff; opacity:0.9">Visión general y gestión</div>
      </div>
      <div class="search">
        <input id="search" placeholder="Buscar por nombre, email o puesto..." />
        <button class="btn" id="openAdd">+ Nuevo empleado</button>
      </div>
    </div>

    <div class="cards">
      <div class="card">
        <h3>Total empleados</h3>
        <p><?php echo $total; ?></p>
      </div>
      <div class="card">
        <h3>Activos</h3>
        <p><?php echo $active; ?></p>
      </div>
      <div class="card">
        <h3>Departamentos</h3>
        <p style="font-size:15px;margin-top:6px">
          <?php
            // Esta línea ahora funcionará porque $departments existe
            $dlist = array_column($departments, 'departamento');
            echo htmlspecialchars(implode(", ", array_filter($dlist)));
          ?>
        </p>
      </div>
    </div>

    <div class="panel">
      <table id="empTable">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Correo</th>
            <th>Puesto</th>
            <th>Departamento</th>
            <th>Ingreso</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          // Este bucle ahora funcionará porque $latest existe
          if (!empty($latest)): 
              foreach($latest as $e): 
          ?>
            <tr>
              <td><?php echo htmlspecialchars($e['nombre'].' '.$e['apellido_paterno'].' '.$e['apellido_materno']); ?></td>
              <td><?php echo htmlspecialchars($e['email']); ?></td>
              <td><?php echo htmlspecialchars($e['puesto']); ?></td>
              <td><?php echo htmlspecialchars($e['departamento'] ?? '-'); ?></td>
              <td><?php echo htmlspecialchars($e['fecha_ingreso']); ?></td>
              <td><?php echo $e['activo'] ? '<span class="badge act">Activo</span>' : '<span class="badge off">Inactivo</span>'; ?></td>
            </tr>
          <?php 
              endforeach;
          else:
          ?>
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px;">No se encontraron empleados.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>

      <div style="display:flex; justify-content:space-between; align-items:center; margin-top:12px">
        <div style="color:#cfefff; font-size:13px">Mostrando últimos 5 empleados</div>
        <div>
          <button class="btn ghost" id="exportBtn">Exportar CSV</button>
        </div>
      </div>
    </div>

  </main>
</div>

<div class="modal" id="modal">
  <div class="cardbox">
    <h3 style="margin:0 0 10px 0; color:var(--accent)">Agregar nuevo empleado</h3>
    <form id="addForm">
      <div class="form-row">
        <div class="col"><input name="nombre" placeholder="Nombre" required></div>
        <div class="col"><input name="apellido_paterno" placeholder="Apellido paterno" required></div>
      </div>
      <div class="form-row">
        <div class="col"><input name="apellido_materno" placeholder="Apellido materno"></div>
        <div class="col"><input name="email" type="email" placeholder="Correo" required></div>
      </div>
      <div class="form-row" style="margin-top:8px"> 
        <div class="col"><input name="puesto" placeholder="Puesto" required></div>
        <div class="col"><input name="departamento" placeholder="Departamento"></div>
      </div>

      <div style="display:flex; gap:8px; margin-top:12px; justify-content:flex-end">
        <button type="button" class="btn ghost" id="closeModal">Cancelar</button>
        <button type="submit" class="btn">Guardar</button>
      </div>
    </form>
  </div>
</div>

<script>
  // Abrir modal
  const modal = document.getElementById('modal');
  document.getElementById('openAdd').addEventListener('click', ()=> modal.style.display='flex');
  document.getElementById('closeModal').addEventListener('click', ()=> modal.style.display='none');
  window.addEventListener('click', (e)=> { if(e.target===modal) modal.style.display='none' });

  // Buscar (cliente)
  const search = document.getElementById('search');
  search.addEventListener('input', ()=> {
    const q = search.value.toLowerCase();
    const rows = document.querySelectorAll('#empTable tbody tr');
    rows.forEach(r=>{
      const text = r.innerText.toLowerCase();
      r.style.display = text.includes(q) ? '' : 'none';
    })
  });

  // Exportar CSV simple
  document.getElementById('exportBtn').addEventListener('click', ()=>{
    const rows = Array.from(document.querySelectorAll('#empTable tr')).map(tr=> Array.from(tr.querySelectorAll('th,td')).map(td=>td.innerText.replace(/\n/g,' ').trim()));
    const csv = rows.map(r=> r.map(cell => `"${cell.replace(/"/g,'""')}"`).join(',')).join('\n');
    const blob = new Blob([csv], {type:'text/csv'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a'); a.href = url; a.download = 'empleados.csv'; a.click(); URL.revokeObjectURL(url);
  });

  // Enviar form agregar (AJAX fetch)
  document.getElementById('addForm').addEventListener('submit', async (e)=>{
    e.preventDefault();
    const fd = new FormData(e.target);
    // Asegúrate de crear este archivo 'add_employee.php'
    const res = await fetch('add_employee.php', { method:'POST', body: fd });
    const txt = await res.text();
    if (res.ok) {
      alert('Empleado creado correctamente.');
      location.reload();
    } else {
      alert('Error: '+txt);
    }
  });
</script>

</body>
</html>