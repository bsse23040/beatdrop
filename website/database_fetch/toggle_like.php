<?php
include('../../logins/db_config.php');
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if (!isset($_POST['track_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing track ID']);
    exit;
}

$user_id = $_SESSION['user_id'];
$track_id = intval($_POST['track_id']);

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Check if already liked
    $check = $pdo->prepare("SELECT * FROM likes WHERE user_id = :user_id AND track_id = :track_id");
    $check->execute(['user_id' => $user_id, 'track_id' => $track_id]);

    if ($check->rowCount() > 0) {
        // Already liked, remove like
        $delete = $pdo->prepare("DELETE FROM likes WHERE user_id = :user_id AND track_id = :track_id");
        $delete->execute(['user_id' => $user_id, 'track_id' => $track_id]);

        echo json_encode(['success' => true, 'liked' => false]);
    } else {
        // Not liked yet, insert like
        $insert = $pdo->prepare("INSERT INTO likes (user_id, track_id) VALUES (:user_id, :track_id)");
        $insert->execute(['user_id' => $user_id, 'track_id' => $track_id]);

        echo json_encode(['success' => true, 'liked' => true]);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
