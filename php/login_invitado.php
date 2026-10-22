<?php
require 'conexion.php';
session_start();

try 
{
    //crea un usuario invitado con nombre unico y sin contraseña conocida
    $username = 'invitado_' . bin2hex(random_bytes(3));
    $password = bin2hex(random_bytes(8));
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO usuarios (alias, username, email, password, fecha_nacimiento, terminos_aceptados, en_linea) VALUES (?, ?, ?, ?, '1990-01-01', 1, 1)");
    $stmt->execute(['Invitado', $username, $username . '@chatterly-old.local', $hash]);
    $id = $pdo->lastInsertId();

    //se hace amigo de los usuarios demo para tener con quien probar
    $amigo = $pdo->prepare("INSERT INTO amigos (id_user1, id_user2, estado) VALUES (?, ?, 'aceptado')");
    foreach ($pdo->query("SELECT id_user FROM usuarios WHERE username IN ('demo1', 'demo2')") as $demo) 
    {
        $amigo->execute([$demo['id_user'], $id]);
    }

    $_SESSION['usuario'] = $username;
    $_SESSION['password'] = $password;

    echo json_encode(["status" => "success", "message" => "Sesión de invitado iniciada."]);
} 
catch (PDOException $e) 
{
    echo json_encode(["status" => "error", "message" => "Error en la base de datos: " . $e->getMessage()]);
}
?>
