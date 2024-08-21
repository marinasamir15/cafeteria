<?php
session_start();
include('db.php'); 

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}


$ordersStmt = $pdo->query("
    SELECT o.*, u.name as user_name, r.room_num, 
    (SELECT SUM(p.price * od.amount) 
     FROM Order_Details od 
     JOIN Products p ON od.product_id = p.product_id 
     WHERE od.order_id = o.order_id) as total_amount 
    FROM Orders o 
    LEFT JOIN Users u ON o.user_id = u.id 
    LEFT JOIN Rooms r ON o.room_id = r.room_id
");
$orders = $ordersStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Orders</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <a class="navbar-brand" href="#">Admin Panel</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="admin.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="all_users.php">Users</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="all_products.php">Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manual_orders.php">Manual Orders</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="all_orders.php">All Orders</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="checks.php">Checks</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h1 class="mt-5">All Orders</h1>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>User</th>
                    <th>Room</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Total Amount</th>
                    <th>Notes</th>
                    <th>Products</th> <!-- New column for products -->
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= htmlspecialchars($order['order_id']) ?></td>
                        <td><?= htmlspecialchars($order['user_name']) ?></td>
                        <td><?= htmlspecialchars($order['room_num']) ?></td>
                        <td><?= htmlspecialchars($order['date']) ?></td>
                        <td><?= htmlspecialchars($order['status']) ?></td>
                        <td><?= htmlspecialchars($order['total_amount']) ?></td>
                        <td><?= htmlspecialchars($order['notes']) ?></td>
                        <td>
                            <?php
                            
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
