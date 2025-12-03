<?php
session_start();
require 'db_con.php'; // Conexión a wisp_db

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Recibimos los datos del formulario (index.php)
    $email = $_POST['correo_electronico']; 
    $password_ingresada = $_POST['clave'];

    // 1. CONSULTA SEGURA (Prepared Statement)
    // Buscamos al usuario por email y verificamos que esté activo
    $sql = "SELECT id_usuario, password_hash, id_rol, nombres, apellido_paterno, username 
            FROM usuarios 
            WHERE email = ? AND activo = 1 LIMIT 1";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        
        // 2. VERIFICAR CONTRASEÑA
        if (password_verify($password_ingresada, $row['password_hash'])) {
            
            // --- ¡ÉXITO! GUARDAMOS LA SESIÓN ---
            $_SESSION['id_usuario'] = $row['id_usuario'];
            $_SESSION['nombre_usuario'] = $row['nombres'] . " " . $row['apellido_paterno'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['rol'] = $row['id_rol']; // 1=Admin, 2=Cliente, 3=Soporte

            // 3. REDIRECCIÓN SEGÚN EL ROL
            switch ($row['id_rol']) {
                case 1: // Administrador
                case 3: // Soporte Técnico
                    header("Location: dashboard.php");
                    break;
                    
                case 2: // Cliente
                    header("Location: dashboardC.php");
                    break;
                    
                default:
                    header("Location: index.php?error=rol_desconocido");
                    break;
            }
            exit();

        } else {
            // Contraseña incorrecta
            header("Location: index.php?error=1"); 
            exit();
        }
    } else {
        // Usuario no existe o inactivo
        header("Location: index.php?error=2");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>
