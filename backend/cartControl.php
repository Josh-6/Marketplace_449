<?php
// Handle actions: add, update, remove
// NOTE: needs the parameter id, name, price, image, description, quantity

function get_available_stock($item_id, $conn) {
  $stmt = $conn->prepare('SELECT Item_Quantity FROM Item WHERE Item_ID = ?');
  $stmt->bind_param('i', $item_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  $stmt->close();
  return $row ? intval($row['Item_Quantity']) : 0;
}

function getAction($action) {
  global $conn;
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
      $id = $_POST['id'] ?? '';
      $name = $_POST['name'] ?? '';
      $price = floatval($_POST['price'] ?? 0);
      $image = $_POST['image'] ?? '';
      $description = $_POST['description'] ?? '';
      $quantity = max(1, intval($_POST['quantity'] ?? 1));

      // Check available stock
      $available_stock = get_available_stock($id, $conn);
      if ($quantity > $available_stock) {
        $quantity = $available_stock;
      }

      // if item exists, increase qty, else push
      $idx = find_cart_index($id);
      if ($idx !== null) {
        $_SESSION['cart'][$idx]['quantity'] += $quantity;
        // Ensure total quantity doesn't exceed stock
        if ($_SESSION['cart'][$idx]['quantity'] > $available_stock) {
          $_SESSION['cart'][$idx]['quantity'] = $available_stock;
        }
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

    }else if (!empty($_POST['remove'])) {     // If a remove button was clicked (we use a named submit button), handle it first
    
      $id = $_POST['remove'];
      $idx = find_cart_index($id);
      if ($idx !== null) {
        array_splice($_SESSION['cart'], $idx, 1);
      }
      header('Location: cart.php');   

    }
    if ($action === 'update') {
      // Update quantities
      if (!empty($_POST['quantities']) && is_array($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $id => $q) {
          $idx = find_cart_index($id);
          if ($idx !== null) {
            $q = max(0, intval($q));
            
            // Check available stock
            $available_stock = get_available_stock($id, $conn);
            if ($q > $available_stock) {
              $q = $available_stock;
            }
            
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

    }
    if ($action === 'remove') {
      // Backwards-compatible: if a dedicated remove form is used (not expected), support it
      $id = $_POST['id'] ?? '';
      $idx = find_cart_index($id);
      if ($idx !== null) {
        array_splice($_SESSION['cart'], $idx, 1);
      }
      header('Location: cart.php');

    }
  }
};

?>


<script>
  const quantityInputs = document.querySelectorAll('.quantity');
  quantityInputs.forEach(input => {
    input.addEventListener('input', () => {
      const quantity = parseInt(input.value);
      const price = parseFloat(input.closest('.product-card').querySelector('.price').textContent.replace('$', ''));
      const total = quantity * price;
      input.closest('.product-card').querySelector('.price').textContent = `$${total.toFixed(2)}`;
    });
  });
</script>