<?php
include('../../logins/db_config.php');

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST['name'])) {
        $genre_name = trim($_POST['name']);

        // Insert genre
        $stmt = $pdo->prepare("INSERT INTO genres (name) VALUES (:name)");
        $stmt->execute(['name' => $genre_name]);

        echo "<div class='message' style='text-align:center; margin-top:50px; color: #1db954; font-family: sans-serif;'>
                Genre '$genre_name' added successfully!<br><br>
                <a href='add_genre.html' style='color: #fff; text-decoration: underline;'>Add another</a>
              </div>";
    } else {
        echo "<div class='message' style='text-align:center; margin-top:50px; color: red; font-family: sans-serif;'>
                Please provide a valid genre name.<br><br>
                <a href='add_genre.html' style='color: #fff; text-decoration: underline;'>Go back</a>
              </div>";
    }
} catch (PDOException $e) {
    echo "<div class='message' style='text-align:center; margin-top:50px; color: red; font-family: sans-serif;'>
            Error: " . htmlspecialchars($e->getMessage()) . "<br><br>
            <a href='add_genre.html' style='color: #fff; text-decoration: underline;'>Go back</a>
          </div>";
}
?>
