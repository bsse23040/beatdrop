<?php
require_once("../../logins/auth.php"); // Ensure user is logged in
include('../../logins/db_config.php');

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}

$artist_name = $_GET['artist'] ?? '';

// Fetch artist details including image (base64 encoded)
$stmt = $pdo->prepare("
    SELECT 
        artist_id,
        name,
        info,
        encode(image, 'base64') AS image_base64
    FROM artists 
    WHERE name = :name 
    LIMIT 1
");
$stmt->execute([':name' => $artist_name]);
$artist = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$artist) {
    echo "Artist not found.";
    exit;
}

// Follower count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM followers WHERE artist_id = :aid");
$stmt->execute([':aid' => $artist['artist_id']]);
$follower_count = $stmt->fetchColumn();

// Check if user already follows
$stmt = $pdo->prepare("SELECT 1 FROM followers WHERE user_id = :uid AND artist_id = :aid");
$stmt->execute([':uid' => $_SESSION['user_id'], ':aid' => $artist['artist_id']]);
$is_following = $stmt->fetch() ? true : false;

// Determine image source
$artist_img_url = $artist['image_base64']
    ? 'data:image/jpeg;base64,' . $artist['image_base64']
    : 'assets/musics/add/cover.jpeg'; // fallback
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($artist['name']) ?> - Followers</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap');

    html, body {
    height: 100%;
    margin: 0;
    padding: 0;
    overflow-y: auto;
    font-family: 'Poppins', sans-serif;
    background-color: #121212;
    color: #ffffff;
    text-align: center;
    /* padding: 40px; */
}

.artist-box {
    background: #1e1e1e;
    padding: 30px;
    border-radius: 20px;
    display: inline-block;
    max-width: 800px;
    width: 90%;
    box-shadow: 0 0 30px rgba(0, 0, 0, 0.7);
    transition: all 0.3s ease;
    margin-bottom: 10vh;
    margin-top: 8vh;
}

    .img-container {
        width: 200px;
        height: 200px;
        border-radius: 20px;
        overflow: hidden;
        margin: 0 auto;
    }

    .artist-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 1.5s ease;
    }

    .artist-img:hover {
        transform: scale(1.3);
    }

    h1 {
    font-size: 2.4rem;
    margin-bottom: 20px;
    display: inline-block;
    position: relative;
    text-align: center;
    overflow: hidden;
    }

    h1::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        height: 100%;
        width: 2px;
        background-color: white;
        animation: blink 0.7s infinite;
    }

    @keyframes blink {
        50% { opacity: 0; }
    }

    @keyframes typing {
        to { width: 100%; }
    }

    @keyframes blink {
        50% { border-color: transparent; }
    }

    strong:hover{
        color: #1db954;
        transition: all 0.3s ease-out;
    }

    .follow-btn {
        margin-top: 25px;
        padding: 10px 30px;
        font-size: 16px;
        background: #ffffff10;
        border: 1px solid #1db954;
        border-radius: 50px;
        cursor: pointer;
        color: #fff;
        transition: all 0.3s ease;
        margin-bottom: 8vh;
    }

    .follow-btn:hover {
        background: #1db954;
        color: #000;
    }

    .follow-btn.unfollow {
        border-color: #ff4b4b;
    }

    .follow-btn.unfollow:hover {
        background: #ff4b4b;
        color: #000;
    }

    p {
        font-size: 1rem;
        line-height: 1.6;
        margin-top: 20px;
    }
</style>
</head>
<body>

<div class="artist-box">
    <h1 id="artist-name"><?= htmlspecialchars($artist['name']) ?></h1>
    <br>
    <div class="img-container">
        <img class="artist-img" src="<?= $artist_img_url ?>" alt="Artist Image">
    </div>
    
    <?php
    $formatted_info = htmlspecialchars($artist['info']);
    // Bold any text wrapped in **double asterisks**
    $formatted_info = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $formatted_info);
    // Convert newlines to <br>
    $formatted_info = nl2br($formatted_info);
    ?>
    <p><?= $formatted_info ?></p>

    <p style="font-size: 1.2em;"><strong><?= $follower_count ?> followers</strong></p>

    <button class="follow-btn <?= $is_following ? 'unfollow' : '' ?>" id="follow-btn">
        <?= $is_following ? 'Unfollow' : 'Follow' ?>
    </button>
</div>

<script>
document.getElementById("follow-btn").addEventListener("click", async () => {
    const formData = new FormData();
    formData.append("artist_id", <?= $artist['artist_id'] ?>);

    try {
        const res = await fetch("../database_fetch/toggle_follow.php", {
            method: "POST",
            body: formData
        });

        const data = await res.json();
        if (data.success) {
            const btn = document.getElementById("follow-btn");
            btn.classList.toggle("unfollow", data.following);
            btn.textContent = data.following ? "Unfollow" : "Follow";
        }
    } catch (e) {
        alert("Error toggling follow. Please try again.");
        console.error(e);
    }
});
</script>
<script>
    const text = "<?= htmlspecialchars($artist['name']) ?>";
    const title = document.getElementById("artist-name");
    let i = 0;

    function typeWriter() {
        if (i < text.length) {
            title.textContent += text.charAt(i);
            i++;
            setTimeout(typeWriter, 100);
        }
    }

    title.textContent = ""; // Clear before typing
    typeWriter();
</script>

</body>
</html>


