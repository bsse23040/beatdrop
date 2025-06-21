<?php
include('../../logins/auth.php');
include('../../logins/db_config.php');

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT playlist_id, name FROM playlists WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $playlists = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Playlists</title>
    <style>
        body {
            background: #121212;
            color: #fff;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px;
        }
        h2 {
            color: #1db954;
        }
        form {
            background: #181818;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.6);
            width: 300px;
            text-align: center;
        }
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            background: #282828;
            border: none;
            color: #fff;
            border-radius: 6px;
        }
        .btn {
            background: #1db954;
            padding: 10px 20px;
            border: none;
            color: white;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
        }
        .btn:hover {
            background: #1ed760;
        }
    </style>
</head>
<body>
    <h2>Select Playlist to Edit</h2>
    <form method="GET" action="edit_tracks.php">
        <select name="playlist_id" required>
            <?php foreach ($playlists as $row): ?>
                <option value="<?= $row['playlist_id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn" type="submit">Edit Playlist</button>
    </form>
</body>
</html>
