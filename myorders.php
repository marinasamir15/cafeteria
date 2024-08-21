<?php
session_start();
include('db.php'); 


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}


$userId = $_SESSION['user_id'];
$ordersStmt = $pdo->prepare("
    SELECT o.*, r.room_num, 
    (SELECT SUM(p.price * od.amount) 
     FROM Order_Details od 
     JOIN Products p ON od.product_id = p.product_id 
     WHERE od.order_id = o.order_id) as total_amount 
    FROM Orders o 
    LEFT JOIN Rooms r ON o.room_id = r.room_id
    WHERE o.user_id = ?
");
$ordersStmt->execute([$userId]);
$orders = $ordersStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_order_id'])) {
    $orderIdToCancel = $_POST['cancel_order_id'];

    // Update the order status to 'cancelled'
    $updateStmt = $pdo->prepare("UPDATE Orders SET status = 'cancelled' WHERE order_id = ?");
    $updateStmt->execute([$orderIdToCancel]);

    
    header("Location: myorders.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <a class="navbar-brand" href="#">User Panel</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="user.php">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="myorders.php">My Orders</a>
            </li>
           
        </ul>
    </div>
</nav>

<div class="container">
    <h1 class="mt-5">My Orders</h1>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Room</th>
                <th>Date</th>
                <th>Status</th>
                <th>Total Amount</th>
                <th>Notes</th>
                <th>Products</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= htmlspecialchars($order['order_id']) ?></td>
                    <td><?= htmlspecialchars($order['room_num']) ?></td>
                    <td><?= htmlspecialchars($order['date']) ?></td>
                    <td><?= htmlspecialchars($order['status']) ?></td>
                    <td><?= htmlspecialchars($order['total_amount']) ?></td>
                    <td><?= htmlspecialchars($order['notes']) ?></td>
                    <td>
                        <?php
                        // Fetch products for this order
                        $productsStmt = $pdo->prepare("
                            SELECT p.name, p.image_path, od.amount 
                            FROM Order_Details od 
                            JOIN Products p ON od.product_id = p.product_id 
                            WHERE od.order_id = ?
                        ");
                        $productsStmt->execute([$order['order_id']]);
                        $products = $productsStmt->fetchAll();

                        foreach ($products as $product):
                        ?>
                            <div>
                                <img src="<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="width: 50px; height: 50px;">
                                <span><?= htmlspecialchars($product['name']) ?></span>
                                <span>(x<?= htmlspecialchars($product['amount']) ?>)</span>
                            </div>
                        <?php endforeach; ?>
                    </td>
                    <td>
                        <?php if ($order['status'] == 'pending'): ?>
                            <form action="myorders.php" method="POST">
                                <input type="hidden" name="cancel_order_id" value="<?= htmlspecialchars($order['order_id']) ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                            </form>
                        <?php elseif ($order['status'] == 'completed'): ?>
                            Done
                        <?php elseif ($order['status'] == 'cancelled'): ?>
                            Cancelled
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
