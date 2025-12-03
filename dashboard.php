<?php
session_start();
require 'db_con.php';

// Si no está logueado → afuera
if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

// Traer datos del usuario
$sql = "SELECT u.*, r.nombre_rol 
        FROM usuarios u 
        INNER JOIN roles r ON u.id_rol = r.id_rol
        WHERE u.id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();


// Estadísticas
$totalClientes = $conn->query("SELECT COUNT(*) AS total FROM clientes")->fetch_assoc()['total'];
$totalTickets = $conn->query("SELECT COUNT(*) AS total FROM tickets")->fetch_assoc()['total'];
$pendientes = $conn->query("SELECT COUNT(*) AS total FROM tickets WHERE estado='Abierto'")->fetch_assoc()['total'];
$pagosPendientes = $conn->query("SELECT COUNT(*) AS total FROM pagos_servicios WHERE estado_pago='Pendiente'")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Koline WISP - Dashboard</title>

    <!-- ✔ Mantengo tus estilos y tonos -->
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'koline-primary': '#00eaff',
                        'koline-dark': '#0a1d37',
                        'koline-card': '#112240',
                        'koline-error': '#ff3366',
                    }
                }
            }
        }
    </script>

    <!-- ✔ Mantengo tu logo -->
    <link rel="icon" href="imagenes/logo.png?v=10" type="image/png">
</head>

<body class="bg-koline-dark text-white">

    <!-- HEADER -->
    <header class="flex justify-between items-center p-5 bg-koline-card shadow-lg">
        <div class="flex items-center gap-3">
            <img src="imagenes/logo.png" alt="Koline Logo" class="h-12">
            <h1 class="text-2xl font-bold text-koline-primary">Koline WISP - Panel</h1>
        </div>

        <div class="text-right">
            <p class="text-lg">Hola, <b><?php echo $usuario['nombres']; ?></b></p>
            <p class="text-sm text-koline-primary"><?php echo $usuario['nombre_rol']; ?></p>
        </div>
    </header>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        <!-- Tarjeta Clientes -->
        <div class="bg-koline-card p-6 rounded-xl shadow-lg border border-koline-primary/40">
            <h2 class="text-xl font-bold">Clientes</h2>
            <p class="text-4xl font-bold text-koline-primary mt-3"><?php echo $totalClientes; ?></p>
        </div>

        <!-- Tarjeta Tickets -->
        <div class="bg-koline-card p-6 rounded-xl shadow-lg border border-koline-primary/40">
            <h2 class="text-xl font-bold">Tickets Totales</h2>
            <p class="text-4xl font-bold text-koline-primary mt-3"><?php echo $totalTickets; ?></p>
        </div>

        <!-- Tarjeta Pendientes -->
        <div class="bg-koline-card p-6 rounded-xl shadow-lg border border-koline-primary/40">
            <h2 class="text-xl font-bold">Tickets Abiertos</h2>
            <p class="text-4xl font-bold text-koline-error mt-3"><?php echo $pendientes; ?></p>
        </div>

        <!-- Pagos Pendientes -->
        <div class="bg-koline-card p-6 rounded-xl shadow-lg border border-koline-primary/40">
            <h2 class="text-xl font-bold">Pagos Pendientes</h2>
            <p class="text-4xl font-bold text-yellow-300 mt-3"><?php echo $pagosPendientes; ?></p>
        </div>

    </main>

    <!-- TABLA DE TICKETS -->
    <section class="p-6">
        <h2 class="text-2xl font-bold mb-4">Tickets Recientes</h2>

        <div class="overflow-x-auto bg-koline-card p-4 rounded-xl shadow-lg border border-koline-primary/40">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-koline-primary border-b border-koline-primary/40">
                        <th class="p-3">ID</th>
                        <th class="p-3">Título</th>
                        <th class="p-3">Cliente</th>
                        <th class="p-3">Estado</th>
                        <th class="p-3">Prioridad</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    $tickets = $conn->query("
                        SELECT t.id_ticket, t.titulo, t.estado, t.prioridad, c.id_cliente
                        FROM tickets t
                        INNER JOIN clientes c ON t.id_cliente = c.id_cliente
                        ORDER BY t.id_ticket DESC
                        LIMIT 10
                    ");

                    while ($row = $tickets->fetch_assoc()):
                    ?>
                    <tr class="border-b border-white/10 hover:bg-white/5">
                        <td class="p-3"><?php echo $row['id_ticket']; ?></td>
                        <td class="p-3"><?php echo $row['titulo']; ?></td>
                        <td class="p-3">Cliente #<?php echo $row['id_cliente']; ?></td>
                        <td class="p-3"><?php echo $row['estado']; ?></td>
                        <td class="p-3 text-koline-error font-bold"><?php echo $row['prioridad']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </section>

</body>
</html>
