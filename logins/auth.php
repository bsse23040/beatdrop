<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    // Not signed in
    header("Location: ../auth/signin.html");
    exit;
}
?>
