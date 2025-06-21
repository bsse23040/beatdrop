<?php
include('../../logins/db_config.php');

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $name = trim($_POST['name']);
        $genre_id = $_POST['genre_id'];
        $info = trim($_POST['info']) ?: 'no info';

        if (!empty($name) && !empty($genre_id)) {
            // Check if image was uploaded
            $hasImage = isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK;

            if ($hasImage) {
                $imageData = file_get_contents($_FILES['image']['tmp_name']);
                $stmt = $pdo->prepare("INSERT INTO artists (name, genre_id, image, info) VALUES (:name, :genre_id, :image, :info)");
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':genre_id', $genre_id);
                $stmt->bindParam(':image', $imageData, PDO::PARAM_LOB);
                $stmt->bindParam(':info', $info);
            } else {
                $stmt = $pdo->prepare("INSERT INTO artists (name, genre_id, info) VALUES (:name, :genre_id, :info)");
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':genre_id', $genre_id);
                $stmt->bindParam(':info', $info);
            }

            $stmt->execute();

            echo "<div style='color: #1db954; font-family: sans-serif; text-align:center; margin-top:50px;'>
                    Artist '$name' added successfully!<br><br>
                    <a href='add_artist.html' style='color: #fff; text-decoration: underline;'>Add another</a>
                  </div>";
        } else {
            echo "<div style='color: red; font-family: sans-serif; text-align:center; margin-top:50px;'>
                    Please fill all required fields.<br><br>
                    <a href='add_artist.html' style='color: #fff; text-decoration: underline;'>Go back</a>
                  </div>";
        }
    }
} catch (PDOException $e) {
    echo "<div style='color: red; font-family: sans-serif; text-align:center; margin-top:50px;'>
            Error: " . htmlspecialchars($e->getMessage()) . "<br><br>
            <a href='add_artist.html' style='color: #fff; text-decoration: underline;'>Go back</a>
          </div>";
}
?>
