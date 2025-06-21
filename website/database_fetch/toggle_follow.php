<?php
include('../../logins/db_config.php');
session_start();
header("Content-Type: application/json");

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $artist_id = $_POST['artist_id'] ?? null;
    $user_id = $_SESSION['user_id'] ?? null;

    if (!$artist_id || !$user_id) {
        echo json_encode(['success' => false, 'message' => 'Missing artist ID or not logged in']);
        exit;
    }

    // Check if user already follows
    $check = $pdo->prepare("SELECT 1 FROM followers WHERE user_id = :uid AND artist_id = :aid");
    $check->execute([':uid' => $user_id, ':aid' => $artist_id]);

    if ($check->fetch()) {
        // Unfollow
        $stmt = $pdo->prepare("DELETE FROM followers WHERE user_id = :uid AND artist_id = :aid");
        $stmt->execute([':uid' => $user_id, ':aid' => $artist_id]);
        echo json_encode(['success' => true, 'following' => false]);
    } else {
        // Follow
        $stmt = $pdo->prepare("INSERT INTO followers (user_id, artist_id) VALUES (:uid, :aid)");
        $stmt->execute([':uid' => $user_id, ':aid' => $artist_id]);
        echo json_encode(['success' => true, 'following' => true]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
