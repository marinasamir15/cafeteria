<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

include('db.php');

if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM Products WHERE product_id = ?");
        $stmt->execute([$product_id]);

        header("Location: all_products.php");
        exit;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    header("Location: all_products.php");
    exit;
}
