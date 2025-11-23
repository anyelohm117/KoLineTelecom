<?php
session_start(); // Iniciar la sesión para guardar datos del usuario

// 1. CONEXIÓN A LA BASE DE DATOS
$host = "127.0.0.1";
$user = "root";       // Cambia esto si tu usuario de BD es diferente
$pass = "";           // Tu contraseña de BD
$db   = "wisp_db";    // El nombre de la base de datos que creamos

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error fatal de conexión: " . $conn->connect_error);
}

// 2. PROCESAR EL FORMULARIO
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Limpiamos los datos para evitar inyección SQL básica
    $email = $conn->real_escape_string($_POST['correo_electronico']);
    $password_ingresada = $_POST['clave'];

    // 3. CONSULTA SQL
    // Buscamos al usuario por email y verificamos que esté activo
    $sql = "SELECT id_usuario, password_hash, id_rol, nombres, apellido_paterno 
            FROM usuarios 
            WHERE email = '$email' AND activo = 1 LIMIT 1";
            
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        
        // 4. VERIFICAR CONTRASEÑA (HASH)
        // Compara la contraseña escrita con la encriptada en la BD
        if (password_verify($password_ingresada, $row['password_hash'])) {
            
            // --- ¡ÉXITO! GUARDAMOS DATOS EN SESIÓN ---
            $_SESSION['id_usuario'] = $row['id_usuario'];
            $_SESSION['nombre_usuario'] = $row['nombres'] . " " . $row['apellido_paterno'];
            $_SESSION['rol'] = $row['id_rol'];

            // 5. REDIRECCIÓN SEGÚN EL ROL
            switch ($row['id_rol']) {
                case 1: // Administrador
                case 3: // Soporte / Técnico
                    header("Location: dashboard.php");
                    break;
                    
                case 2: // Cliente
                    header("Location: portal_cliente.php");
                    break;
                    
                default: // Invitado u otros
                    header("Location: index.php"); 
                    break;
            }
            exit();

        } else {
            // Contraseña incorrecta: Redirigir con error tipo 1
            header("Location: index.php?error=1");
            exit();
        }
    } else {
        // Usuario no existe: Redirigir con error tipo 2
        header("Location: index.php?error=2");
        exit();
    }
} else {
    // Si intentan entrar directo a este archivo sin formulario
    header("Location: index.php");
    exit();
}
?>
