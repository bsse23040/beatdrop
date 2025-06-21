<?php
include('../../logins/db_config.php');

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trackName = trim($_POST['track_name'] ?? '');
    $artistId  = $_POST['artist_id'] ?? null;
    $duration  = $_POST['duration'] ?? null;

    if (!$trackName || !$artistId || !$duration || !isset($_FILES['music_file'])) {
        die("❌ Missing required fields.");
    }

    // Get artist name from DB
    $stmt = $pdo->prepare("SELECT name FROM artists WHERE artist_id = :id");
    $stmt->execute(['id' => $artistId]);
    $artist = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$artist) {
        die("❌ Artist not found.");
    }

    // Songs are now stored directly in the 'songs' folder
    $uploadDir = "../assets/songs/";

    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $fileName = basename($_FILES['music_file']['name']);
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['music_file']['tmp_name'], $targetPath)) {
        $relativePath = "assets/songs/" . $fileName;

        $stmt = $pdo->prepare("
            INSERT INTO tracks (name, duration, path, artist_id)
            VALUES (:name, :duration, :path, :artist_id)
        ");

        try {
            $stmt->execute([
                'name'     => $trackName,
                'duration' => $duration,
                'path'     => $relativePath,
                'artist_id'=> $artistId
            ]);
            echo "✅ Track uploaded and saved successfully!";
        } catch (PDOException $e) {
            echo "❌ Insert failed: " . $e->getMessage();
        }
    } else {
        echo "❌ File upload failed.";
    }
} else {
    echo "❌ Invalid request.";
}
