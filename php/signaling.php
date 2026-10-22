<?php
session_start();
require __DIR__ . '/conexion.php'; // asegura que la conexion a la base de datos se haya establecido correctamente
header('Content-Type: application/json');

$tiposValidos = ['offer', 'answer', 'ice']; //columnas de la tabla llamadas que se pueden leer o escribir

if ($_SERVER['REQUEST_METHOD'] === 'POST') //esta funcion maneja las peticiones POST para actualizar datos de la llamada en curso
{
  $data = json_decode(file_get_contents("php://input"), true);
  $id_emisor = $data['id_emisor'] ?? null;
  $id_receptor = $data['id_receptor'] ?? null;
  $type = $data['type'] ?? null; //type es 'offer', 'answer' o 'ice'
  $payload = $data['data'] ?? null; //payload es el objeto que contiene la oferta, respuesta o candidatos ICE
  $from = $data['from'] ?? null; //id de quien envia el candidato ICE

  if (!$id_emisor || !$id_receptor || !in_array($type, $tiposValidos, true) || !$payload) // verifica que no falten datos obligatorios
  {
    echo json_encode(["error"=>"Faltan parámetros"]); exit;
  }

  if ($type === 'ice') //si type es 'ice', se añade a la lista guardando quien lo envia
  {
    //la transaccion evita que dos candidatos enviados a la vez se pisen entre si
    $pdo->exec("BEGIN IMMEDIATE");
    $stmt = $pdo->prepare("SELECT ice FROM llamadas WHERE id_emisor = ? AND id_receptor = ?");
    $stmt->execute([$id_emisor, $id_receptor]);
    $existing = $stmt->fetchColumn();
    $candidates = $existing ? json_decode($existing, true) : [];
    $candidates[] = ["from" => $from, "c" => $payload];
    $pdo->prepare("UPDATE llamadas SET ice = ? WHERE id_emisor = ? AND id_receptor = ?")
        ->execute([json_encode($candidates), $id_emisor, $id_receptor]);
    $pdo->exec("COMMIT");
    echo json_encode(["status"=>"ok"]);
    exit;
  }

  //si es 'offer' o 'answer', se guarda directamente el objeto
  $pdo->prepare("UPDATE llamadas SET {$type} = ? WHERE id_emisor = ? AND id_receptor = ?")
      ->execute([json_encode($payload), $id_emisor, $id_receptor]);
  echo json_encode(["status"=>"ok"]);
  exit;
}

// maneja las peticiones GET para obtener datos de la llamada
$modo = $_GET['modo'] ?? '';
$id_emisor = $_GET['id_emisor'] ?? null; // id_emisor es el ID de quien inicio la llamada
$id_receptor = $_GET['id_receptor'] ?? null; // id_receptor es el ID de quien la recibe

if (!$id_emisor || !$id_receptor) // verifica que no falten datos obligatorios
{
  echo json_encode(["error"=>"Faltan parámetros"]); exit;
}

if ($modo === 'estado') //devuelve el estado de la llamada para saber si la otra parte ha colgado o rechazado
{
  $stmt = $pdo->prepare("SELECT estado FROM llamadas WHERE id_emisor = ? AND id_receptor = ?");
  $stmt->execute([$id_emisor, $id_receptor]);
  echo json_encode(["estado" => $stmt->fetchColumn() ?: null]);
  exit;
}

if ($modo === 'obtener')
{
  $type = $_GET['type'] ?? null; // type es 'offer', 'answer' o 'ice'

  if (!in_array($type, $tiposValidos, true))
  {
    echo json_encode(["error"=>"Parámetros no válidos"]); exit;
  }

  $stmt = $pdo->prepare("SELECT $type FROM llamadas WHERE id_emisor = ? AND id_receptor = ?");
  $stmt->execute([$id_emisor, $id_receptor]);
  $row = $stmt->fetchColumn();

  if ($type === 'ice') // para ICE devolvemos solo los candidatos de la otra parte
  {
    $me = $_GET['me'] ?? null;
    $lista = json_decode($row, true) ?: [];
    $data = [];
    foreach ($lista as $item)
    {
      if ((string)($item['from'] ?? '') !== (string)$me)
      {
        $data[] = $item['c'];
      }
    }
  }
  else // para offer y answer devolvemos el objeto directamente
  {
    $data = $row ? json_decode($row) : null;
  }
  echo json_encode(["data"=>$data]);
  exit;
}

echo json_encode(["error"=>"Modo no válido"]);
