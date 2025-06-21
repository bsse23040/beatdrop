<?php
session_start();
header('Content-Type: application/json');

// Get input values
$user_id  = $_SESSION['user_id'] ?? null;
$track_id = $_POST['track_id'] ?? null;

// Basic validation
if (!$user_id || !$track_id) {
    echo json_encode(['liked' => false]);
    exit;
}

// Include secure Aiven config
include('../../logins/db_config.php');

try {
    // PDO with port + SSL for Aiven
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Check if the user liked the track
    $stmt = $pdo->prepare("SELECT 1 FROM likes WHERE user_id = :user_id AND track_id = :track_id");
    $stmt->execute(['user_id' => $user_id, 'track_id' => $track_id]);

    echo json_encode(['liked' => $stmt->rowCount() > 0]);
} catch (PDOException $e) {
    echo json_encode(['liked' => false, 'error' => $e->getMessage()]);
}
