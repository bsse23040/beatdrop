<?php
include('../../logins/db_config.php');
session_start();
header('Content-Type: application/json');

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Get and sanitize search term
    $search = $_GET['search'] ?? '';
    $search = trim($search);

    if (empty($search)) {
        echo json_encode(["error" => "Missing search term"]);
        exit;
    }

    // SQL query to search for tracks by name (case-insensitive)
    $sql = "
        SELECT 
            t.path,
            t.name AS track_name,
            t.duration,
            a.name AS artist_name,
            t.track_id
        FROM tracks t
        INNER JOIN artists a ON a.artist_id = t.artist_id
        WHERE LOWER(t.name) LIKE LOWER(:search)
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['search' => "%$search%"]);

    $songs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($songs, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    echo json_encode([
        "error" => "Database error",
        "message" => $e->getMessage()
    ]);
}
