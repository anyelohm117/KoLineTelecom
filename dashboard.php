<?php
session_start();
require 'db_con.php';

// --- SEGURIDAD ---
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] == 2) {
    header("Location: index.php");
    exit();
}

$total_usuarios = 0;
$total_clientes = 0;
$ultimos_usuarios = [];

try {
    // Total usuarios
    $sql_total = "SELECT COUNT(*) FROM usuarios";
    $res = $conn->query($sql_total);
    if ($res) $total_usuarios = $res->fetch_row()[0];

    // Clientes activos (rol 2)
    $sql_clientes = "SELECT COUNT(*) FROM usuarios WHERE id_rol = 2 AND activo = 1";
    $res = $conn->query($sql_clientes);
    if ($res) $total_clientes = $res->fetch_row()[0];

    // Últimos 5 usuarios
    $sql_latest = "SELECT u.*, r.nombre_rol 
                   FROM usuarios u 
                   JOIN roles r ON u.id_rol = r.id_rol 
                   ORDER BY u.fecha_registro DESC LIMIT 5";
    $res = $conn->query($sql_latest);
    if ($res) $ultimos_usuarios = $res->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    die("Error de BD: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - KoLine Telecom</title>

    <!-- FAVICON -->
    <link rel="icon" type="image/png" href="imagenes/logo.png?v=5">

    <!-- TAILWIND -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- PALETA KOLINE -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        "koline-primary": "#00eaff",
                        "koline-dark": "#0a1d37",
                        "koline-card": "#112240",
                        "koline-error": "#ff3366",
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-koline-dark text-white">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-koline-card h-screen fixed left-0 top-0 p-6 shadow-xl border-r border-koline-primary/20">
        <h1 class="text-2xl font-bold text-koline-primary">KoLine Admin</h1>

        <p class="mt-4 text-sm">
            Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?></strong>
        </p>

        <nav class="mt-6 space-y-3 text-base">
            <a href="#" class="block p-2 rounded hover:bg-koline-primary hover:text-black font-semibold transition">
                🏠 Dashboard
            </a>
            <a href="#" class="block p-2 text-gray-300 hover:text-white">👤 Usuarios</a>
            <a href="#" class="block p-2 text-gray-300 hover:text-white">⚙ Configuración</a>
        </nav>

        <a href="index.php" class="text-red-400 hover:text-red-500 text-sm mt-6 inline-block">← Cerrar Sesión</a>
    </aside>

    <!-- NAVBAR -->
    <header class="ml-64 bg-koline-card p-4 border-b border-koline-primary/20 flex justify-between items-center">
        <h2 class="text-xl font-semibold">Dashboard</h2>
        <span class="text-koline-primary">Admin</span>
    </header>

    <!-- CONTENIDO -->
    <main class="ml-64 p-6">

        <!-- CARDS -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <div class="bg-koline-card p-6 rounded-xl shadow border border-koline-primary/10">
                <h3 class="text-sm text-gray-300">TOTAL USUARIOS</h3>
                <p class="text-4xl font-bold text-koline-primary mt-2">
                    <?php echo $total_usuarios; ?>
                </p>
            </div>

            <div class="bg-koline-card p-6 rounded-xl shadow border border-koline-primary/10">
                <h3 class="text-sm text-gray-300">CLIENTES ACTIVOS</h3>
                <p class="text-4xl font-bold text-green-400 mt-2">
                    <?php echo $total_clientes; ?>
                </p>
            </div>

            <div class="bg-koline-card p-6 rounded-xl shadow border border-koline-primary/10">
                <h3 class="text-sm text-gray-300">SISTEMA</h3>
                <p class="text-xl font-bold text-koline-primary mt-2">En Línea 🟢</p>
            </div>

        </div>

        <!-- TABLA: ÚLTIMOS REGISTROS -->
        <div class="mt-10 bg-koline-card p-6 rounded-xl shadow border border-koline-primary/10">
            <h3 class="text-xl font-bold mb-4">Últimos Registros</h3>

            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-koline-primary/20 text-koline-primary">
                        <th class="p-2">Nombre</th>
                        <th class="p-2">Email</th>
                        <th class="p-2">Rol</th>
                        <th class="p-2">Fecha</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (!empty($ultimos_usuarios)): ?>
                        <?php foreach ($ultimos_usuarios as $u): ?>
                            <tr class="border-b border-white/10">
                                <td class="p-2"><?php echo htmlspecialchars($u['nombres'] . ' ' . $u['apellido_paterno']); ?></td>
                                <td class="p-2"><?php echo htmlspecialchars($u['email']); ?></td>
                                <td class="p-2">
                                    <?php if ($u['id_rol'] == 1): ?>
                                        <span class="px-3 py-1 text-xs bg-koline-error text-white rounded-lg font-bold">Admin</span>
                                    <?php elseif ($u['id_rol'] == 2): ?>
                                        <span class="px-3 py-1 text-xs bg-koline-primary text-black rounded-lg font-bold">Cliente</span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 text-xs bg-gray-400 text-black rounded-lg font-bold">Staff</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-2"><?php echo date('d/m/Y', strtotime($u['fecha_registro'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="p-2 text-gray-400">No hay usuarios registrados aún.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>

</body>
</html>
