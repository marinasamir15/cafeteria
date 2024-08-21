<?php
include('db.php');

if (isset($_GET['order_id'])) {
    $orderId = $_GET['order_id'];

    $stmt = $pdo->prepare("
        SELECT p.name, p.image_path, od.amount, p.price, (od.amount * p.price) as total_price
        FROM Order_Details od
        JOIN Products p ON od.product_id = p.product_id
        WHERE od.order_id = :order_id
    ");
    $stmt->execute(['order_id' => $orderId]);
    $orderDetails = $stmt->fetchAll();

    foreach ($orderDetails as $detail) {
        echo "<tr>";
        echo "<td><img src='". htmlspecialchars($detail['image_path']) ."' width='50' height='50' alt='". htmlspecialchars($detail['name']) ."'></td>";
        echo "<td>" . htmlspecialchars($detail['name']) . "</td>";
        echo "<td>" . htmlspecialchars($detail['amount']) . "</td>";
        echo "<td>" . htmlspecialchars($detail['price']) . "</td>";
        echo "<td>" . htmlspecialchars($detail['total_price']) . "</td>";
        echo "</tr>";
    }
}
?>
