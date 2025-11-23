<?php
session_start();
require "db_con.php"; // Tu archivo de conexión

// 1. Verificar que el formulario fue enviado
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.html"); // Redirigir si acceden al script directamente
    exit;
}

// 2. Obtener datos del formulario
$correo_electronico = $_POST['correo_electronico'];
$clave = $_POST['clave'];

if (empty($correo_electronico) || empty($clave)) {
    echo "<script>alert('Correo y contraseña son requeridos'); window.location.href='login.html';</script>";
    exit;
}

// 3. Buscar al usuario en la tabla 'usuarios'
//    ¡Cambiamos la consulta para que también traiga el ROL!
$sql = $conn->prepare("SELECT id, nombre, clave, rol FROM usuarios WHERE correo_electronico = ?");
$sql->bind_param("s", $correo_electronico);
$sql->execute();
$result = $sql->get_result();

if ($result->num_rows !== 1) {
    // Si el correo no se encuentra
    echo "<script>alert('El correo no está registrado'); window.location.href='login.html';</script>";
    exit;
}

// 4. Verificar la contraseña
$usuario = $result->fetch_assoc();
if (!password_verify($clave, $usuario['clave'])) {
    // Si la contraseña es incorrecta
    echo "<script>alert('Contraseña incorrecta'); window.location.href='login.html';</script>";
    exit;
}

// 5. ¡ÉXITO! Guardar datos de sesión comunes
$_SESSION['usuario_id'] = $usuario['id'];
$_SESSION['usuario_nombre'] = $usuario['nombre'];
$_SESSION['usuario_rol'] = $usuario['rol'];

// 6. LÓGICA DE REDIRECCIÓN BASADA EN ROL
//  Esta es la nueva lógica que soluciona tu problema
switch ($usuario['rol']) {
    case 'Cliente':
        // Si es Cliente, buscamos su ID en la tabla 'clientes' usando el email
        // (Esto es crucial para que tu cliente_dashboard.php funcione)
        $stmt_cliente = $conn->prepare("SELECT id FROM clientes WHERE email = ?");
        $stmt_cliente->bind_param("s", $correo_electronico);
        $stmt_cliente->execute();
        $result_cliente = $stmt_cliente->get_result();
        
        if ($result_cliente->num_rows === 1) {
            $cliente_data = $result_cliente->fetch_assoc();
            // Creamos la sesión 'cliente_id' que el dashboard de cliente necesita
            $_SESSION['cliente_id'] = $cliente_data['id']; 
            header("Location: dashboardC.php"); // Redirigir a dashboard de cliente
        } else {
            // Error: El usuario es 'Cliente' pero no existe en la tabla 'clientes'
            echo "<script>alert('Error de cuenta de cliente. Contacte a soporte.'); window.location.href='login.html';</script>";
        }
        $stmt_cliente->close();
        break;

    case 'Admin':
    case 'Empleado':
    case 'Soporte':
        // Si es Admin, Empleado o Soporte, va al dashboard de empleados
        header("Location: dashboard.php"); // Redirigir a dashboard de empleado
        break;

    default:
        // Si tiene un rol desconocido
        echo "<script>alert('Rol de usuario no reconocido. Contacte a soporte.'); window.location.href='login.html';</script>";
        break;
}

// 7. Cerrar conexiones y salir
$sql->close();
$conn->close();
exit;

?>