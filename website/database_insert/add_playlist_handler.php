<?php
include('../../logins/auth.php');
include('../../logins/db_config.php');

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user_id = $_SESSION['user_id'];
        $name = trim($_POST['name']);
        $description = !empty($_POST['description']) ? trim($_POST['description']) : 'description not added';

        if (strlen($name) > 16 || strlen($description) > 75) {
            echo "<script>alert('❌ Validation failed.'); window.location.href='add_playlists.php';</script>";
            exit;
        }

        $imageData = null;
        if (!empty($_POST['cropped_image'])) {
            $base64 = $_POST['cropped_image'];
            $base64 = preg_replace('#^data:image/\w+;base64,#i', '', $base64);
            $imageData = base64_decode($base64);
        }

        if ($imageData !== null) {
            $stmt = $pdo->prepare("
                INSERT INTO playlists (user_id, name, image, description)
                VALUES (:user_id, :name, :image, :description)
            ");
            $stmt->bindParam(':image', $imageData, PDO::PARAM_LOB);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO playlists (user_id, name, description)
                VALUES (:user_id, :name, :description)
            ");
        }

        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);

        $stmt->execute();

        echo "<script>
                window.location.href = '../beatdrop.html';
              </script>";
    }
} catch (PDOException $e) {
    echo "<script>alert('❌ Error: " . addslashes($e->getMessage()) . "');</script>";
}
?>
