<?php
// ===== CONEXIÓN A LA BASE DE DATOS =====
$servername = "127.0.0.1"; // Usamos localhost para el servidor local
$username = "root"; // El usuario por defecto en XAMPP
$password = ""; // Sin contraseña por defecto en XAMPP
$dbname = "koline"; // Nombre de tu base de datos

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("❌ Falló la conexión: " . $conn->connect_error);
}

// Procesar el formulario cuando se recibe una solicitud POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger los datos del formulario
    $usuario = trim($_POST['usuario']);
    $clave = password_hash($_POST['clave'], PASSWORD_DEFAULT); // Cifrar la contraseña
    $nombre = trim($_POST['nombre']);
    $apellidoPaterno = trim($_POST['apellido_paterno']);
    $apellidoMaterno = trim($_POST['apellido_materno']);
    $correo = trim($_POST['correo']);
    $rol = $_POST['rol'];

    // Verificar si el correo ya existe
    $checkUser = "SELECT * FROM usuarios WHERE correo_electronico = ?";
    $stmt = $conn->prepare($checkUser);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        echo "<script>alert('⚠️ El correo electrónico ya está registrado.'); window.location='index.html';</script>";
    } else {
        // Insertar nuevo usuario
        $sql = "INSERT INTO usuarios (usuario, clave, nombre, apellido_paterno, apellido_materno, correo_electronico, rol) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $usuario, $clave, $nombre, $apellidoPaterno, $apellidoMaterno, $correo, $rol);

        if ($stmt->execute()) {
            echo "<script>alert('✅ Usuario registrado exitosamente en KoLine Telecom'); window.location='login.html';</script>";
        } else {
            echo "<script>alert('❌ Error al registrar el usuario: " . $conn->error . "'); window.location='index.html';</script>";
        }
    }

    $stmt->close();
    $conn->close();
}
?>
