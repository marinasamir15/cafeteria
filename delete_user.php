<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

include('db.php');

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM Users WHERE id = :id");
    $stmt->execute(['id' => $_GET['id']]);

    header("Location: all_users.php");
    exit;
} else {
    echo "User ID not provided!";
}
?>