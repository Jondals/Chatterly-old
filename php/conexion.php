<?php
//en Vercel el disco del proyecto es de solo lectura, solo se puede escribir en /tmp
$dbFile = getenv('VERCEL') ? sys_get_temp_dir() . '/chatterly-old.sqlite' : __DIR__ . '/../data/chatterly-old.sqlite';
$nuevaBD = !file_exists($dbFile);

try 
{
    $pdo = new PDO("sqlite:" . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("PRAGMA foreign_keys = ON");
    $pdo->exec("PRAGMA busy_timeout = 5000");

    if ($nuevaBD) //primera ejecucion: crea las tablas y los usuarios demo
    {
        $pdo->exec(<<<'SQL'
CREATE TABLE usuarios (
    id_user INTEGER PRIMARY KEY AUTOINCREMENT,
    alias VARCHAR(50) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    terminos_aceptados TINYINT NOT NULL DEFAULT 0,
    en_linea TINYINT DEFAULT 0
);

CREATE TABLE amigos (
    id_user1 INTEGER,
    id_user2 INTEGER,
    fecha_amistad TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado VARCHAR(20),
    PRIMARY KEY (id_user1, id_user2),
    FOREIGN KEY (id_user1) REFERENCES usuarios(id_user),
    FOREIGN KEY (id_user2) REFERENCES usuarios(id_user)
);

CREATE TABLE mensajes (
    id_mensaje INTEGER PRIMARY KEY AUTOINCREMENT,
    id_emisor INTEGER,
    id_receptor INTEGER,
    contenido TEXT,
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tipo VARCHAR(20),
    FOREIGN KEY (id_emisor) REFERENCES usuarios(id_user),
    FOREIGN KEY (id_receptor) REFERENCES usuarios(id_user)
);

CREATE TABLE llamadas (
    id_llamada INTEGER PRIMARY KEY AUTOINCREMENT,
    id_emisor INTEGER NOT NULL,
    id_receptor INTEGER NOT NULL,
    estado TEXT DEFAULT 'pendiente' CHECK (estado IN ('pendiente', 'aceptada', 'rechazada', 'finalizada')),
    offer TEXT,
    answer TEXT,
    ice TEXT,
    fecha_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_fin TIMESTAMP NULL,
    FOREIGN KEY (id_emisor) REFERENCES usuarios(id_user),
    FOREIGN KEY (id_receptor) REFERENCES usuarios(id_user)
);
SQL);

        $hash = password_hash('Demo!1', PASSWORD_DEFAULT);
        $ins = $pdo->prepare("INSERT INTO usuarios (alias, username, email, password, fecha_nacimiento, terminos_aceptados) VALUES (?, ?, ?, ?, '1990-01-01', 1)");
        $ins->execute(['Demo Uno', 'demo1', 'demo1@chatterly-old.local', $hash]);
        $ins->execute(['Demo Dos', 'demo2', 'demo2@chatterly-old.local', $hash]);
        $pdo->exec("INSERT INTO amigos (id_user1, id_user2, estado) VALUES (1, 2, 'aceptado')");
        $pdo->exec("INSERT INTO mensajes (id_emisor, id_receptor, contenido, tipo) VALUES (1, 2, 'Hola, bienvenido a Chatterly-Old', 'texto')");
    }
} 
catch (PDOException $e) 
{
    echo json_encode(["status" => "error", "message" => "Conexión fallida: " . $e->getMessage()]);
    exit();
}
