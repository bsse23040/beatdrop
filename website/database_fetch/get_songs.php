<?php
include('../../logins/db_config.php');
session_start();
header('Content-Type: application/json');

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["error" => "User not logged in"]);
        exit;
    }

    // Check if playlist_id is provided
    if (!isset($_GET['playlist_id'])) {
        echo json_encode(["error" => "Missing playlist_id"]);
        exit;
    }

    $playlistId = (int) $_GET['playlist_id'];

    // SQL query to get songs in the playlist
    $sql = "
        SELECT 
            t.path, 
            t.name AS track_name, 
            t.duration, 
            a.name AS artist_name,
            t.track_id
        FROM tracks t
        INNER JOIN artists a ON a.artist_id = t.artist_id
        WHERE t.track_id IN (
            SELECT track_id 
            FROM playlist_tracks 
            WHERE playlist_id = :playlistId
        )
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['playlistId' => $playlistId]);

    $songs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($songs, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    echo json_encode([
        "error" => "Database error",
        "message" => $e->getMessage()
    ]);
}
