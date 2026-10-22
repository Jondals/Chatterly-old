<?php
//un unico punto de entrada para todos los php/*.php (el plan Hobby de Vercel limita a 12 funciones)
$archivo = basename($_GET['f'] ?? '');
unset($_GET['f']);
$ruta = __DIR__ . '/../php/' . $archivo;

if (substr($archivo, -4) !== '.php' || !is_file($ruta)) 
{
    http_response_code(404);
    exit('No encontrado');
}

chdir(dirname($ruta));
require $ruta;
