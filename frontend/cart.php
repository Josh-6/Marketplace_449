<?php
session_start();

// initialize cart in session if missing
if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}
$cart = &$_SESSION['cart'];

// Helper: find index by product id
function find_cart_index($id) {
  foreach ($_SESSION['cart'] as $i => $it) {
    if ((string)$it['id'] === (string)$id) return $i;
  }
  return null;
}

$total = 0.0;

// Handle actions: add, update, remove
$action = $_GET['action'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($action === 'add') {
    $id = $_POST['id'] ?? '';
    $name = $_POST['name'] ?? '';
    $price = floatval($_POST['price'] ?? 0);
    $image = $_POST['image'] ?? '';
    $description = $_POST['description'] ?? '';
    $quantity = max(1, intval($_POST['quantity'] ?? 1));

    // if item exists, increase qty, else push
    $idx = find_cart_index($id);
    if ($idx !== null) {
      $_SESSION['cart'][$idx]['quantity'] += $quantity;
    } else {
      $_SESSION['cart'][] = [
        'id' => $id,
        'name' => $name,
        'price' => $price,
        'image' => $image,
        'description' => $description,
        'quantity' => $quantity,
      ];
    }

    // redirect to avoid form resubmission
    header('Location: cart.php');
    exit;
  }
  // If a remove button was clicked (we use a named submit button), handle it first
  if (!empty($_POST['remove'])) {
    $id = $_POST['remove'];
    $idx = find_cart_index($id);
    if ($idx !== null) {
      array_splice($_SESSION['cart'], $idx, 1);
    }
    header('Location: cart.php');
    exit;
  }

  if ($action === 'update') {
    // Update quantities
    if (!empty($_POST['quantities']) && is_array($_POST['quantities'])) {
      foreach ($_POST['quantities'] as $id => $q) {
        $idx = find_cart_index($id);
        if ($idx !== null) {
          $q = max(0, intval($q));
          if ($q === 0) {
            // remove
            array_splice($_SESSION['cart'], $idx, 1);
          } else {
            $_SESSION['cart'][$idx]['quantity'] = $q;
          }
        }
      }
    }
    header('Location: cart.php');
    exit;
  }

  // Backwards-compatible: if a dedicated remove form is used (not expected), support it
  if ($action === 'remove') {
    $id = $_POST['id'] ?? '';
    $idx = find_cart_index($id);
    if ($idx !== null) {
      array_splice($_SESSION['cart'], $idx, 1);
    }
    header('Location: cart.php');
    exit;
  }
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
      <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="product_page.php">Products</a></li>
        <li><a href="#">Categories</a></li>
        <li><a href="#">About</a></li>
        <li><a href="#">Contact Us</a></li>
        <li class="dropdown">
          <?php if (isset($_SESSION['username'])): ?>
            <a href="#">Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></a>
            <div class="dropdown-menu">
              <a href="#">Profile</a>
              <a href="#">History</a>
              <a href="#">Orders</a>
              <a href="../backend/logout.php">Sign Out</a>
            </div>
          <?php else: ?>
            <a href="signin.php">Hello, Sign in</a>
          <?php endif; ?>
        </li>
      </ul>
    </nav>
    <div class="icons">
      🔍 <a href="cart.php" style="text-decoration:none;color:inherit;">🛒 Cart (<?php echo isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0; ?>)</a>
    </div>
  </header>

<h2 style="text-align:center; margin-top:30px;">Your Shopping Cart</h2>

<?php if (empty($cart)): ?>
  <p style="text-align:center;">Your cart is empty.</p>
<?php else: ?>
  <table>
    <tr>
      <th style="width:40%">Product</th>
      <th>Price</th>
      <th>Quantity</th>
      <th>Subtotal</th>
      <th>Action</th>
    </tr>
    <form method="post" action="cart.php?action=update">
    <?php foreach ($cart as $item): ?>
      <?php $subtotal = floatval($item['price']) * intval($item['quantity']); $total += $subtotal; ?>
      <tr>
        <td style="text-align:left;">
          <div style="display:flex;gap:12px;align-items:center;">
            <?php if (!empty($item['image'])): ?>
              <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="width:80px;height:80px;object-fit:cover;border-radius:6px;border:1px solid #eee;">
            <?php endif; ?>
            <div>
              <strong><?= htmlspecialchars($item['name']) ?></strong>
              <div style="font-size:0.9rem;color:#555;"><?= htmlspecialchars($item['description']) ?></div>
            </div>
          </div>
        </td>
        <td>$<?= number_format(floatval($item['price']), 2) ?></td>
        <td>
          <input type="number" name="quantities[<?= htmlspecialchars($item['id'], ENT_QUOTES) ?>]" value="<?= intval($item['quantity']) ?>" min="0" style="width:70px;padding:6px;border-radius:4px;border:1px solid #ccc;">
        </td>
        <td>$<?= number_format($subtotal, 2) ?></td>
        <td>
          <!-- Use a named submit button so we avoid nested forms. The top-level POST handler
               will look for $_POST['remove'] and remove the item. -->
          <button type="submit" name="remove" value="<?= htmlspecialchars($item['id'], ENT_QUOTES) ?>" style="background:#b71c1c;">Remove</button>
        </td>
      </tr>
    <?php endforeach; ?>
    <tr>
      <td colspan="5" style="text-align:right;">
        <button type="submit">Update Cart</button>
      </td>
    </tr>
    </form>
    <!-- end update form -->


    <tr>
      <th colspan="3" style="text-align:right;">Total:</th>
      <th colspan="2">$<?= number_format($total, 2) ?></th>
    </tr>
          <tr>
      <td colspan="5" style="text-align:right;">
        <!-- Checkout button: non-nested, links to the checkout page -->
        <a href="checkout.php" style="display:inline-block;background:#00796b;color:#fff;padding:10px 16px;border-radius:6px;text-decoration:none;">Proceed to Payment</a>
      </td>
    </tr>
  </table>


<?php endif; ?>

</body>
</html>
