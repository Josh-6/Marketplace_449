<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

include __DIR__ . '/../database/db_connect.php'; // your DB connection

$conn = get_db_connection();
//echo 'console.log("Database connected!")';

// Compute total
$total = 0.0;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
  foreach ($_SESSION['cart'] as $item) {
    $total += floatval($item['price']) * intval($item['quantity']);
  }
}

// Simulate payment
$paid = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'pay') {

  $userId = $_SESSION['user_id'] ?? 0;

  if ($userId && !empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $stmt = $conn->prepare("
            INSERT INTO Marketplace.User_History 
            (User_ID, Item_ID, History_Type, Quantity, Purchased_At)
            VALUES (?, ?, 'purchase', ?, NOW())
        ");

    foreach ($_SESSION['cart'] as $item) {
      $itemId = intval($item['id']);
      $quantity = intval($item['quantity']);
      $stmt->bind_param("iii", $userId, $itemId, $quantity);
      $stmt->execute();
    }
    echo "<script>console.log('Added Item ID $itemId x $quantity for User ID $userId');</script>";
    $stmt->close();
  }

  // In a real app you'd call a payment gateway here.
  // For now clear the cart and show success.
  $_SESSION['cart'] = [];
  $paid = true;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Checkout</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 40px;
    }

    .box {
      max-width: 600px;
      margin: 40px auto;
      border: 1px solid #eee;
      padding: 20px;
      border-radius: 8px;
    }

    .btn {
      display: inline-block;
      background: #00796b;
      color: #fff;
      padding: 10px 16px;
      border-radius: 6px;
      text-decoration: none;
      border: none;
      cursor: pointer;
    }
  </style>
</head>

<body>
  <div class="box">
    <h2>Checkout</h2>
    <?php if ($paid): ?>
      <p>Payment successful. Thank you for your purchase!</p>
      <p><a href="index.php">Return to shop</a></p>
    <?php else: ?>
      <p>Your total is: <strong>$<?= number_format($total, 2) ?></strong></p>
      <?php if (empty($_SESSION['cart'])): ?>
        <p>Your cart is empty. <a href="index.php">Continue shopping</a></p>
      <?php else: ?>
        <form method="post" action="checkout.php">
          <input type="hidden" name="action" value="pay">
          <button type="submit" class="btn">Pay Now</button>
        </form>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</body>

</html>