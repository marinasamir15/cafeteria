<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Include the database connection file
include('db.php'); // Ensure this file correctly initializes the $pdo variable

// Check if the user is logged in and has the 'user' role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit;
}

// Ensure $pdo is a valid PDO object
if (!$pdo) {
    die('Database connection failed.');
}

// Ensure 'user_id' is set in the session
if (!isset($_SESSION['user_id'])) {
    die('User ID not set in session.');
}

$loggedInUserId = $_SESSION['user_id'];

// Retrieve the logged-in user's details
$userStmt = $pdo->prepare("SELECT id, name, image_path FROM Users WHERE id = ?");
$userStmt->execute([$loggedInUserId]);
$user = $userStmt->fetch();

if (!$user) {
    die('User not found.');
}

// Fetch other necessary data
$roomsStmt = $pdo->prepare("SELECT * FROM Rooms");
$roomsStmt->execute();
$rooms = $roomsStmt->fetchAll();

$productsStmt = $pdo->prepare("SELECT * FROM Products WHERE available = 'yes'");
$productsStmt->execute();
$products = $productsStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $room_id = $_POST['room_id'];
    $notes = $_POST['notes'];

    // Insert the order into the Orders table
    $stmt = $pdo->prepare("INSERT INTO Orders (user_id, room_id, date, status, notes) VALUES (?, ?, NOW(), 'pending', ?)");
    $stmt->execute([$loggedInUserId, $room_id, $notes]);
    $order_id = $pdo->lastInsertId();

    // Initialize total amount
    $totalAmount = 0;

    // Check if any products were selected
    if (!empty($_POST['products'])) {
        foreach ($_POST['products'] as $product_id => $details) {
            $amount = $details['amount'];

            // Fetch product price
            $priceStmt = $pdo->prepare("SELECT price FROM Products WHERE product_id = ?");
            $priceStmt->execute([$product_id]);
            $price = $priceStmt->fetchColumn();
            $total = $price * $amount;
            $totalAmount += $total;

            // Insert product details into Order_Details table
            $orderDetailsStmt = $pdo->prepare("INSERT INTO Order_Details (order_id, product_id, amount) VALUES (?, ?, ?)");
            $orderDetailsStmt->execute([$order_id, $product_id, $amount]);
        }
    }

    // Update total amount for the order
    $totalAmountStmt = $pdo->prepare("UPDATE Orders SET total_amount = ? WHERE order_id = ?");
    $totalAmountStmt->execute([$totalAmount, $order_id]);

    // Redirect to a page to show all orders or an order summary page
   // Redirect to the user's orders page after submitting the order
header("Location: myorders.php"); 


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
        .profile-info {
            margin-bottom: 20px;
        }
        .profile-info img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
        }
    </style>
</head>
<body>
<div class="container">
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
    <div class="profile-info mt-5" >
        <h1>Welcome, <?= htmlspecialchars($user['name']) ?>!</h1>
        <img src="<?= htmlspecialchars($user['image_path'] ? $user['image_path'] : 'default_profile_picture.jpg') ?>" alt="<?= htmlspecialchars($user['name']) ?>">
    </div>
    
    <form action="user.php" method="POST">
        <h1 class="mt-5">Manual Orders</h1>
        <div class="row gx-5">
            <div class="col-md-6">
                <div>
                    <div class="form-group">
                        <label for="room_id">Select Room:</label>
                        <select id="room_id" name="room_id" class="form-control" required>
                            <?php foreach ($rooms as $room): ?>
                                <option value="<?= htmlspecialchars($room['room_id']) ?>"><?= htmlspecialchars($room['room_num']) ?></option>
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
                            <div class="product-card" onclick="addProduct('<?= htmlspecialchars($product['product_id']) ?>', '<?= htmlspecialchars($product['name']) ?>', '<?= htmlspecialchars($product['price']) ?>')">
                                <img src="<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="width:100px;">
                                <p><?= htmlspecialchars($product['name']) ?> (<?= htmlspecialchars($product['price']) ?> LE)</p>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div id="order-summary" class="order-form">
                        <!-- Dynamically added product forms will appear here -->
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
</body>
</html>
