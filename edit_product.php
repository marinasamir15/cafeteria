<?php
session_start();
include('db.php'); 

$product_id = $_GET['id'];


$stmt = $pdo->prepare("SELECT * FROM Products WHERE product_id = :product_id");
$stmt->execute(['product_id' => $product_id]);
$product = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];
    $available = $_POST['available'];

    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image_name = $_FILES['image']['name'];
        $image_tmp = $_FILES['image']['tmp_name'];
        $image_path = 'uploads/' . $image_name;
        if (move_uploaded_file($image_tmp, $image_path)) {
            
            $stmt = $pdo->prepare("UPDATE Products SET image_path = :image_path WHERE product_id = :product_id");
            $stmt->execute(['image_path' => $image_path, 'product_id' => $product_id]);
        } else {
            echo "Error uploading the image.";
            exit;
        }
    }

    
    $stmt = $pdo->prepare("UPDATE Products SET name = :name, price = :price, category_id = :category_id, available = :available 
                           WHERE product_id = :product_id");
    $stmt->execute([
        'name' => $name,
        'price' => $price,
        'category_id' => $category_id,
        'available' => $available,
        'product_id' => $product_id
    ]);

    
    header("Location: all_products.php");
    exit;
}


$categories = $pdo->query("SELECT * FROM Categories")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
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
                    <a class="nav-link" href="checks.php">Checks</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-5">
        <h1 class="mt-4 mb-4">Edit Product</h1>
        <form action="edit_product.php?id=<?= $product_id ?>" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Product Name:</label>
                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>
            <div class="form-group">
                <label for="price">Price:</label>
                <input type="text" class="form-control" id="price" name="price" value="<?= htmlspecialchars($product['price']) ?>" required>
            </div>
            <div class="form-group">
                <label for="category_id">Category:</label>
                <select id="category_id" name="category_id" class="form-control" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" <?= $category['id'] == $product['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="available">Available:</label>
                <select id="available" name="available" class="form-control" required>
                    <option value="yes" <?= $product['available'] == 'yes' ? 'selected' : '' ?>>Yes</option>
                    <option value="no" <?= $product['available'] == 'no' ? 'selected' : '' ?>>No</option>
                </select>
            </div>
            <div class="form-group">
                <label for="image">Product Image:</label>
                <input type="file" class="form-control-file" id="image" name="image">
                <?php if ($product['image_path']): ?>
                    <img src="<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="mt-2" width="100">
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary">Update Product</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
