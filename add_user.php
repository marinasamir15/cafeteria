<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

include('db.php'); 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $room_id = $_POST['room_id']; 

   
    if ($password !== $confirm_password) {
        echo "<div class='alert alert-danger' role='alert'>Passwords do not match!</div>";
        exit;
    }

  
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

   
    $imagePath = '';
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/";
        $imageName = basename($_FILES["image"]["name"]);
        $targetFile = $targetDir . $imageName;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

       
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check === false) {
            echo "<div class='alert alert-danger' role='alert'>File is not an image.</div>";
            exit;
        }

        
        if ($_FILES["image"]["size"] > 2000000) {
            echo "<div class='alert alert-danger' role='alert'>Sorry, your file is too large. Max 2MB.</div>";
            exit;
        }

        
        $allowedTypes = ['jpg', 'jpeg', 'png'];
        if (!in_array($imageFileType, $allowedTypes)) {
            echo "<div class='alert alert-danger' role='alert'>Sorry, only JPG, JPEG, & PNG files are allowed.</div>";
            exit;
        }

      
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            $imagePath = $targetFile;
        } else {
            echo "<div class='alert alert-danger' role='alert'>Error uploading the image.</div>";
            exit;
        }
    }

    
    try {
        $stmt = $pdo->prepare("INSERT INTO Users (name, email, password, room_id, image_path, role) VALUES (?, ?, ?, ?, ?, 'user')");
        $success = $stmt->execute([$name, $email, $hashed_password, $room_id, $imagePath]);

        
        if ($success) {
            
            header("Location: all_users.php");
            exit;
        } else {
            echo "<div class='alert alert-danger' role='alert'>Error inserting user into the database.</div>";
        }
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger' role='alert'>Error: " . $e->getMessage() . "</div>"; // Display any SQL errors
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Add User</h1>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <div class="form-group">
                <label for="room_id">Select Room:</label>
                <select class="form-control" id="room_id" name="room_id" required>
                    <?php
                    // Fetch rooms from the database
                    $stmt = $pdo->query("SELECT room_id, CONCAT(room_num, '-', room_ext) AS room_info FROM Rooms");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value=\"" . htmlspecialchars($row['room_id']) . "\">" . htmlspecialchars($row['room_info']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="image">Profile Image:</label>
                <input type="file" class="form-control-file" id="image" name="image">
            </div>
            <button type="submit" class="btn btn-primary">Add User</button>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
