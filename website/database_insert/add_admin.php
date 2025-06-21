<?php
include('../../logins/auth.php');
include('../../logins/db_config.php');

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Check if current user is admin
    $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE user_id = :uid");
    $stmt->execute(['uid' => $_SESSION['user_id']]);
    $is_admin = $stmt->fetchColumn();

    if (!$is_admin) {
        die("Access Denied: You are not an admin.");
    }

    $message = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email']);
        $role = $_POST['role'];

        $isAdminValue = ($role === 'promote') ? true : false;

        $stmt = $pdo->prepare("UPDATE users SET is_admin = :admin WHERE email = :email");
        $stmt->bindValue(':admin', $isAdminValue, PDO::PARAM_BOOL);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        $message = $isAdminValue ? "User promoted to admin." : "User demoted from admin.";
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Admin Control Panel</title>
  <style>
    body {
        font-family: 'Arial', sans-serif;
        background-color: #191414;
        color: #fff;
        text-align: center;
        padding: 50px;
    }

    h2 {
        color: #1DB954;
    }

    form {
        background-color: #282828;
        display: inline-block;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0px 0px 15px rgba(0,0,0,0.5);
    }

    input[type="email"] {
        padding: 10px;
        width: 250px;
        border-radius: 5px;
        border: none;
        margin-bottom: 15px;
    }

    label {
        display: block;
        margin: 10px 0;
        color: #ccc;
    }

    input[type="radio"] {
        margin-right: 10px;
    }

    button {
        background-color: #1DB954;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 25px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    button:hover {
        background-color: #1ed760;
    }

    p {
        color: #1DB954;
        margin-bottom: 20px;
    }
  </style>
</head>
<body>

<h2>Admin Control Panel</h2>

<?php if ($message): ?>
  <p><?= $message ?></p>
<?php endif; ?>

<form method="POST">
    <input type="email" name="email" placeholder="User Email" required><br>
    <label><input type="radio" name="role" value="promote" checked> Promote to Admin</label>
    <label><input type="radio" name="role" value="demote"> Demote from Admin</label><br>
    <button type="submit">Submit</button>
</form>

</body>
</html>
