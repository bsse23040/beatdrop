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
    $userId = $_SESSION['user_id'];    

    $sql = "
        SELECT 
            playlist_id,
            lower(replace(name, ' ', '')) AS folder,
            name AS title,
            description,
            encode(image, 'base64') AS image_base64
        FROM playlists
        WHERE user_id = :userId
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['userId' => $userId]);

    $playlists = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($playlists as &$playlist) {
        if ($playlist['image_base64']) {
            $playlist['image_url'] = 'data:image/jpeg;base64,' . $playlist['image_base64'];
        } else {
            $playlist['image_url'] = 'assets/musics/add/cover.jpeg';
        }
        unset($playlist['image_base64']);
    }

    echo json_encode($playlists, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    echo json_encode([
        "error" => "Database error",
        "message" => $e->getMessage()
    ]);
}
