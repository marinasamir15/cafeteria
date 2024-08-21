<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

include('db.php');


$roomsStmt = $pdo->query("SELECT room_id, room_num, room_ext FROM Rooms");
$rooms = $roomsStmt->fetchAll(PDO::FETCH_ASSOC);


if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM Users WHERE id = :id");
    $stmt->execute(['id' => $_GET['id']]);
    $user = $stmt->fetch();

    if (!$user) {
        echo "User not found!";
        exit;
    }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $room_id = $_POST['room_id'];

   
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/";
        $targetFile = $targetDir . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            $imagePath = $targetFile;
        } else {
            echo "<div class='alert alert-danger' role='alert'>Error uploading the image.</div>";
            exit;
        }
    } else {
      
        $imagePath = $user['image_path'];
    }

    $stmt = $pdo->prepare("UPDATE Users SET name = ?, email = ?, room_id = ?, image_path = ? WHERE id = ?");
    $stmt->execute([$name, $email, $room_id, $imagePath, $_GET['id']]);

    header("Location: all_users.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
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
        <h1 class="mt-4 mb-4">Edit User</h1>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div class="form-group">
                <label for="room_id">Room:</label>
                <select class="form-control" id="room_id" name="room_id" required>
                    <?php foreach ($rooms as $room): ?>
                        <option value="<?= htmlspecialchars($room['room_id']) ?>" <?= $room['room_id'] == $user['room_id'] ? 'selected' : '' ?>>
                            Room <?= htmlspecialchars($room['room_num']) ?> (Ext: <?= htmlspecialchars($room['room_ext']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="image">Profile Image:</label>
                <input type="file" class="form-control-file" id="image" name="image">
                <?php if ($user['image_path']): ?>
                    <img src="<?= htmlspecialchars($user['image_path']) ?>" alt="Profile Image" class="mt-2" width="100">
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary">Update User</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
