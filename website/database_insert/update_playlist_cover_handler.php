<?php
include('../../logins/auth.php');
include('../../logins/db_config.php');

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['cropped_image'])) {
        $playlist_id = $_POST['playlist_id'];
        $user_id = $_SESSION['user_id'];

        $imageData = file_get_contents($_FILES['cropped_image']['tmp_name']);

        $stmt = $pdo->prepare("UPDATE playlists SET image = :cover WHERE playlist_id = :pid AND user_id = :uid");
        $stmt->bindParam(':cover', $imageData, PDO::PARAM_LOB);
        $stmt->bindParam(':pid', $playlist_id, PDO::PARAM_INT);
        $stmt->bindParam(':uid', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        echo "✅ Cover image updated successfully.";
    } else {
        echo "❌ Invalid request.";
    }
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
