<?php
session_start();
include('db.php'); 

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

// Fetch products
$productsStmt = $pdo->query("SELECT p.*, c.name as category_name FROM Products p LEFT JOIN Categories c ON p.category_id = c.id");
$products = $productsStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Products</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="mt-5">All Products</h1>
        <a href="add_product.php" class="btn btn-primary mb-3">Add Product</a>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Category</th>
                    <th>Available</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
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

                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= htmlspecialchars($product['product_id']) ?></td>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td><?= htmlspecialchars($product['price']) ?></td>
                        <td><?= htmlspecialchars($product['category_name']) ?></td>
                        <td><?= htmlspecialchars($product['available']) ?></td>
                        <td>
                            <?php if ($product['image_path']): ?>
                                <img src="<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" width="100">
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="edit_product.php?id=<?= $product['product_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="delete_product.php?id=<?= $product['product_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
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
