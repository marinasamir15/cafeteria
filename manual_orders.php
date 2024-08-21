<?php
session_start();
include('db.php'); 
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$usersStmt = $pdo->prepare("SELECT id, name FROM Users");
$usersStmt->execute();
$users = $usersStmt->fetchAll();

$roomsStmt = $pdo->prepare("SELECT * FROM Rooms");
$roomsStmt->execute();
$rooms = $roomsStmt->fetchAll();

$productsStmt = $pdo->prepare("SELECT * FROM Products WHERE available = 'yes'");
$productsStmt->execute();
$products = $productsStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $room_id = $_POST['room_id'];
    $notes = $_POST['notes'];

    $stmt = $pdo->prepare("INSERT INTO Orders (user_id, room_id, date, status, notes) VALUES (?, ?, NOW(), 'pending', ?)");
    $stmt->execute([$user_id, $room_id, $notes]);
    $order_id = $pdo->lastInsertId();

    if (!empty($_POST['products'])) {
        foreach ($_POST['products'] as $product_id => $details) {
            $amount = $details['amount'];
            $priceStmt = $pdo->prepare("SELECT price FROM Products WHERE product_id = ?");
            $priceStmt->execute([$product_id]);
            $price = $priceStmt->fetchColumn();
            $total = $price * $amount;

            $orderDetailsStmt = $pdo->prepare("INSERT INTO Order_Details (order_id, product_id, amount) VALUES (?, ?, ?)");
            $orderDetailsStmt->execute([$order_id, $product_id, $amount]);
        }
    }

    $totalAmountStmt = $pdo->prepare("
        UPDATE Orders 
        SET total_amount = (SELECT SUM(p.price * od.amount) 
                            FROM Order_Details od 
                            JOIN Products p ON od.product_id = p.product_id 
                            WHERE od.order_id = Orders.order_id) 
        WHERE order_id = ?
    ");
    $totalAmountStmt->execute([$order_id]);

    header("Location: all_orders.php"); 
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual Orders</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .product-card {
            cursor: pointer;
            display: inline-block;
            margin: 10px;
        }
        .product-card:hover {
            background-color: #f1f1f1;
        }
        .order-form {
            margin-top: 20px;
        }
    </style>
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
   <form action="manual_orders.php" method="POST">
    <h1 class="mt-5">Manual Orders</h1>
 <div class="row gx-5">
     <div class="col-md-6">
        <div>
        <div class="form-group">
            <label for="user_id">Select User:</label>
            <select id="user_id" name="user_id" class="form-control" required>
                <?php foreach ($users as $user): ?>
                    <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="room_id">Select Room:</label>
            <select id="room_id" name="room_id" class="form-control" required>
                <?php foreach ($rooms as $room): ?>
                    <option value="<?= $room['room_id'] ?>"><?= htmlspecialchars($room['room_num']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="notes">Notes:</label>
            <textarea id="notes" name="notes" class="form-control"></textarea>
        </div>
        </div>
    </div>
    <div class="col-md-6">
       <div>
    <h3>Select Products:</h3>
        <div id="products-container" class="form-group">
            <?php foreach ($products as $product): ?>
                <div class="product-card" onclick="addProduct('<?= $product['product_id'] ?>', '<?= htmlspecialchars($product['name']) ?>', '<?= htmlspecialchars($product['price']) ?>')">
                    <img src="<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="width:100px;">
                    <p><?= htmlspecialchars($product['name']) ?> (<?= htmlspecialchars($product['price']) ?> LE)</p>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="order-summary" class="order-form">
           
        </div>

        <h3>Total: <span id="total-price">0</span> LE</h3>
        <button type="submit" class="btn btn-primary">Submit Order</button>
        </div> 
    </div>
 </div>
        
    
  
    </form>
</div>

<script>
    let total = 0;

    function addProduct(productId, productName, productPrice) {
        const productForm = document.createElement('div');
        productForm.classList.add('form-group');
        productForm.setAttribute('id', 'product-' + productId);

        productForm.innerHTML = `
            <label>${productName}</label>
            <div class="input-group mb-3">
                <input type="number" name="products[${productId}][amount]" value="1" min="1" class="form-control" style="width: 60px;" onchange="updateTotal(${productPrice}, this.value, ${productId})">
                <div class="input-group-append">
                    <span class="input-group-text">x ${productPrice} LE</span>
                </div>
                <button type="button" class="btn btn-danger" onclick="removeProduct(${productId}, ${productPrice})">Remove</button>
            </div>
        `;

        document.getElementById('order-summary').appendChild(productForm);
        total += parseFloat(productPrice);
        document.getElementById('total-price').innerText = total.toFixed(2);
    }

    function removeProduct(productId, productPrice) {
        const productForm = document.getElementById('product-' + productId);
        const quantity = productForm.querySelector('input').value;

        
        total -= parseFloat(productPrice) * parseFloat(quantity);
        document.getElementById('total-price').innerText = total.toFixed(2);

        productForm.remove();
    }

    function updateTotal(productPrice, newQuantity, productId) {
        const productForm = document.getElementById('product-' + productId);
        const oldQuantity = productForm.querySelector('input').getAttribute('value');

        
        const difference = parseFloat(newQuantity) - parseFloat(oldQuantity);

       
        total += parseFloat(productPrice) * difference;
        document.getElementById('total-price').innerText = total.toFixed(2);

        
        productForm.querySelector('input').setAttribute('value', newQuantity);
    }
</script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>