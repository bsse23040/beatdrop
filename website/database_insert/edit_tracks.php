<?php 
include('../../logins/auth.php');
include('../../logins/db_config.php');

try {
    
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $playlist_id = $_GET['playlist_id'];

    // Playlist info
    $playlist_stmt = $pdo->prepare("SELECT name, description FROM playlists WHERE playlist_id = :pid");
    $playlist_stmt->execute(['pid' => $playlist_id]);
    $playlist = $playlist_stmt->fetch();

    // Tracks in playlist
    $stmt = $pdo->prepare("SELECT pt.track_id, tr.name FROM playlist_tracks pt JOIN tracks tr ON pt.track_id = tr.track_id WHERE pt.playlist_id = :pid ORDER BY pt.\"Order\"");
    $stmt->execute(['pid' => $playlist_id]);
    $playlist_tracks = $stmt->fetchAll();

    // All tracks
    $all_tracks = $pdo->query("SELECT track_id, name FROM tracks ORDER BY name")->fetchAll();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Playlist</title>
    <style>
        body {
            background: #121212;
            color: #fff;
            font-family: 'Segoe UI', sans-serif;
            padding: 30px;
        }
        form {
            background: #181818;
            padding: 25px;
            border-radius: 12px;
            max-width: 600px;
            margin: auto;
        }
        h3 {
            color: #1db954;
        }
        input, select, textarea {
            width: 100%;
            margin-top: 10px;
            padding: 10px;
            background: #282828;
            color: white;
            border: none;
            border-radius: 6px;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            background: #282828;
            margin: 5px 0;
            padding: 8px;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        button {
            background: #1db954;
            border: none;
            color: white;
            padding: 6px 10px;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover {
            background: #1ed760;
        }
        .danger {
            background: #e53935;
        }
        .danger:hover {
            background: #f44336;
        }
    </style>
</head>
<body>

<h3>Editing: <?= htmlspecialchars($playlist['name']) ?></h3>

<form method="POST" action="update_playlist_handler.php">
    <input type="hidden" name="playlist_id" value="<?= $playlist_id ?>">

    <label>Rename Playlist</label>
    <input type="text" name="new_name" value="<?= htmlspecialchars($playlist['name']) ?>" maxlength="16" required>

    <label>Update Description</label>
    <textarea name="new_description" maxlength="75"><?= htmlspecialchars($playlist['description']) ?></textarea>

    <label>Tracks in Playlist (click to remove)</label>
    <ul>
        <?php foreach ($playlist_tracks as $track): ?>
            <li>
                <?= htmlspecialchars($track['name']) ?>
                <button name="action" value="remove-<?= $track['track_id'] ?>">✖</button>
            </li>
        <?php endforeach; ?>
    </ul>

    <label>Add Track</label>
    <select name="add_track_id">
        <?php foreach ($all_tracks as $track): ?>
            <option value="<?= $track['track_id'] ?>"><?= htmlspecialchars($track['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <button name="action" value="add">Add Track</button>

    <br><br>
    <button class="danger" name="action" value="delete_playlist">🗑 Delete Playlist</button>
    
    <br><br>
    <a href="update_playlist_cover.php?playlist_id=<?= $playlist_id ?>">
        <button type="button">🖼 Change Cover Image</button>
    </a>

    <button type="submit" name="action" value="update_info">💾 Save Changes</button>
</form>

</body>
</html>
