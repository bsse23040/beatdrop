<?php
include('../../logins/db_config.php');

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
} catch (PDOException $e) {
    die("DB error");
}

$playlist_id = $_GET['playlist_id'] ?? 0;

$stmt = $pdo->prepare("
  SELECT t.track_id, t.name 
  FROM playlist_tracks pt
  JOIN tracks t ON pt.track_id = t.track_id
  WHERE pt.playlist_id = :pid
  ORDER BY pt.\"Order\" ASC
");
$stmt->execute(['pid' => $playlist_id]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
