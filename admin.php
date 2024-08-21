<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

require_once 'db.php'; 


$adminEmail = $_SESSION['user'];

try {
    $stmt = $pdo->prepare("SELECT image_path FROM Users WHERE role='admin'");
    $stmt->execute(['email' => $adminEmail]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

  
    $adminImage = !empty($admin['image_path']) ? $admin['image_path'] : 'default_image.jpg';
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 56px;
        }
        .nav-link {
            margin-right: 15px;
        }
        .admin-image {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }
        .navbar-brand {
            display: flex;
            align-items: center;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <a class="navbar-brand" href="#">
        <img src="<?php echo $adminImage; ?>" alt="Admin Image" class="admin-image">
        Admin Panel
    </a>
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
    <h1 class="mt-4">Hello Admin</h1>
    <p>Welcome to the admin panel. Use the navigation bar above to access different sections.</p>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
