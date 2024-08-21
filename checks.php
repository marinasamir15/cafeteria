<?php
session_start();
include('db.php'); 

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;

if ($startDate && $endDate) {
    $ordersStmt = $pdo->prepare("
        SELECT o.order_id, o.date, u.name as user_name, 
        SUM(od.amount * p.price) as total_amount 
        FROM Orders o
        LEFT JOIN Users u ON o.user_id = u.id
        LEFT JOIN Order_Details od ON o.order_id = od.order_id
        LEFT JOIN Products p ON od.product_id = p.product_id
        WHERE o.date BETWEEN :startDate AND :endDate
        GROUP BY o.order_id, u.name
    ");
    $ordersStmt->execute(['startDate' => $startDate, 'endDate' => $endDate]);
    $orders = $ordersStmt->fetchAll();
} else {
    $orders = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checks</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
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

    <div class="container mt-5">
        <h1>Checks</h1>
        <form method="GET" class="mb-4">
            <div class="form-group">
                <label for="start_date">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" required>
            </div>
            <div class="form-group">
                <label for="end_date">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" required>
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>

        <?php if ($orders): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Total Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order['user_name']) ?></td>
                            <td><?= htmlspecialchars($order['total_amount']) ?></td>
                            <td>
                                <button class="btn btn-info btn-sm toggle-orders" data-order-id="<?= $order['order_id'] ?>">+</button>
                            </td>
                        </tr>
                        <tr class="order-details" id="order-details-<?= $order['order_id'] ?>" style="display: none;">
                            <td colspan="3">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Product Image</th>
                                            <th>Product Name</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                            <th>Total Price</th>
                                        </tr>
                                    </thead>
                                    <tbody id="order-products-<?= $order['order_id'] ?>">
                                        <!-- Order details will be inserted here via AJAX -->
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No orders found for the selected date range.</p>
        <?php endif; ?>
    </div>

    <script>
        $(document).ready(function() {
            $('.toggle-orders').on('click', function() {
                var orderId = $(this).data('order-id');
                var orderDetailsRow = $('#order-details-' + orderId);

                if (orderDetailsRow.is(':visible')) {
                    orderDetailsRow.hide();
                } else {
                    if (orderDetailsRow.find('tbody tr').length === 0) {
                        $.ajax({
                            url: 'fetch_order_details.php',
                            method: 'GET',
                            data: { order_id: orderId },
                            success: function(response) {
                                $('#order-products-' + orderId).html(response);
                            }
                        });
                    }
                    orderDetailsRow.show();
                }
            });
        });
    </script>
</body>
</html>
