<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['id'];
  $name = $_POST['name'];
  $price = $_POST['price'];

  // Initialize cart if empty
  if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
  }

  // Check if item already exists in cart
  if (isset($_SESSION['cart'][$id])) {
    $_SESSION['cart'][$id]['quantity']++;
  } else {
    $_SESSION['cart'][$id] = [
      'name' => $name,
      'price' => $price,
      'quantity' => 1
    ];
  }

  header("Location: ../frontend/cart.php");
  exit;
}
?>
