<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: signin.php");
    exit();
}

if (isset($_POST['enroll'])) {
    
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "marketplace";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    //add role to roles in User table
    $stmt = $conn->prepare(
        "UPDATE Users SET Role = 'buyer seller' WHERE User_ID = ?"
    );
    if(!$stmt){
        die('Prepare failed: ' . $conn->error);
    };

    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->close();

    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare(
        "INSERT INTO Seller (Seller_ID, Seller_Phone_Number, Seller_Stars, Seller_Location) VALUES (?, ?, 1, ?)"
    );
    if(!$stmt){
        die('Prepare failed: ' . $conn->error);
    };

    $stmt->bind_param('iss', $user_id, $phone, $address);
    $stmt->execute();
    $stmt->close();
    
    $conn->close();

    $_SESSION['seller_registered'] = true;
    header("Location: upload_item.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Your Cart</title>
  <link rel="stylesheet" href="css/style.css?v=1">
  <style>
    table { width: 80%; margin: 40px auto; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: center; }
    th { background: #00695c; color: white; }
    button { background: #00695c; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; }
  </style>
</head>
<body>

  <header>
    <div class="logo">Marketplace</div>
    <nav>
        <a href="index.php">Go Back</a>
    </nav>
  </header>
  <h2 style="text-align:center; margin-top:30px;">Enroll as a Seller</h2>

  <form action="enroll_as_seller.php" method="post">
    <label for="phone">Phone Number:</label>
    <input type="text" id="phone" name="phone" required><br>

    <label for="address">Address:</label>
    <input type="text" id="address" name="address" required><br>

    <input type="submit" name="enroll" value="Enroll">
  </form>
</body>
</html>