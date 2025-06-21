<?php
include('../../logins/auth.php');
include('../../logins/db_config.php');

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user_id = $_SESSION['user_id'];
        $playlist_id = $_POST['playlist_id'];
        $action = $_POST['action'];
        $redirectTo = "edit_tracks.php?playlist_id=$playlist_id"; // default redirect

        switch (true) {
            case $action === "add":
                $track_id = $_POST['add_track_id'];

                $stmt = $pdo->prepare("SELECT MAX(\"Order\") AS max_order FROM playlist_tracks WHERE playlist_id = :pid");
                $stmt->execute(['pid' => $playlist_id]);
                $order = ($stmt->fetch()['max_order'] ?? 0) + 1;

                $stmt = $pdo->prepare("INSERT INTO playlist_tracks (playlist_id, track_id, \"Order\") VALUES (:pid, :tid, :ord)");
                $stmt->execute(['pid' => $playlist_id, 'tid' => $track_id, 'ord' => $order]);
                break;

            case str_starts_with($action, "remove-"):
                $track_id = explode('-', $action)[1];
                $stmt = $pdo->prepare("DELETE FROM playlist_tracks WHERE playlist_id = :pid AND track_id = :tid");
                $stmt->execute(['pid' => $playlist_id, 'tid' => $track_id]);
                break;

            case $action === "delete_playlist":
                $stmt = $pdo->prepare("DELETE FROM playlists WHERE playlist_id = :pid AND user_id = :uid");
                $stmt->execute(['pid' => $playlist_id, 'uid' => $user_id]);
                $redirectTo = "../beatdrop.html";
                break;

            case $action === "update_info":
                $new_name = trim($_POST['new_name']);
                $new_desc = trim($_POST['new_description']);

                if (strlen($new_name) <= 16 && strlen($new_desc) <= 75) {
                    $stmt = $pdo->prepare("UPDATE playlists SET name = :name, description = :desc WHERE playlist_id = :pid AND user_id = :uid");
                    $stmt->execute([
                        'name' => $new_name,
                        'desc' => $new_desc,
                        'pid' => $playlist_id,
                        'uid' => $user_id
                    ]);
                }
                $redirectTo = "../beatdrop.html";
                break;

            default:
                // Optional: unknown action fallback
                break;
        }

        header("Location: $redirectTo");
        exit;
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
