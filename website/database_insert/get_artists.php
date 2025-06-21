<?php
include('../../logins/db_config.php');

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $stmt = $pdo->query("SELECT artist_id, name FROM artists ORDER BY name");
    $artists = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($artists);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
