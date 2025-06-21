<?php
include('db_config.php');

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require", $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}


// Handle POST data
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name     = trim($_POST["name"] ?? '');
    $email    = trim($_POST["email"] ?? '');
    $password = $_POST["password"] ?? '';
    $dob      = $_POST["dob"] ?? null;

    // Validate inputs
    if (empty($name) || empty($email) || empty($password)) {
        die("Please fill in all required fields.");
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT email FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);

    if ($stmt->rowCount() > 0) {
        die("Email is already registered.");
    }

    // Insert new user into the users table
    $insertStmt = $pdo->prepare("
        INSERT INTO users (name, email, password, date_of_birth)
        VALUES (:name, :email, :password, :dob)
    ");

    try {
        $insertStmt->execute([
            'name'     => $name,
            'email'    => $email,
            'password' => $hashedPassword,
            'dob'      => $dob
        ]);
        header("Location: signin.html");
    } catch (PDOException $e) {
        die("Signup failed: " . $e->getMessage());
    }
} else {
    echo "Invalid request method.";
}
?>
