<?php
// Ensure session is started so login state (e.g., $_SESSION['username']) is available
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// load product array from backend
include __DIR__ . '/../backend/product.php'; // load product array

// Category filter: optional ?category=Electronics
$selectedCategory = isset($_GET['category']) ? trim($_GET['category']) : '';
if ($selectedCategory !== '') {
  // Basic sanitization - allow letters, numbers, spaces, and ampersand
  $selectedCategory = preg_replace('/[^\w\s&-]/', '', $selectedCategory);
  // Filter products by category (case-insensitive)
  $filtered = array_filter($products, function($p) use ($selectedCategory) {
    return isset($p['category']) && strcasecmp($p['category'], $selectedCategory) === 0;
  });
  $productsToShow = array_values($filtered);
} else {
  $productsToShow = $products;
}

// Pagination logic
$itemsPerPage = 12;
$totalItems = count($productsToShow);
$totalPages = $totalItems > 0 ? ceil($totalItems / $itemsPerPage) : 1;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$start = ($page - 1) * $itemsPerPage;
$paginatedProducts = array_slice($productsToShow, $start, $itemsPerPage);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Marketplace Products</title>
  <link rel="stylesheet" href="css/style.css?v=1" />
  <style>
    .product-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 25px;
      padding: 60px 10%;
    }
    .product-card {
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      background: #fff;
      text-align: center;
      padding: 15px;
      transition: transform 0.2s ease;
      position: relative;
    }
    .product-card:hover { transform: translateY(-5px); }
    .product-card img { width: 100%; height: 200px; object-fit: cover; }
    .product-card h3 { color: #00695c; margin: 10px 0; }
    .product-card p { font-size: 0.9rem; color: #555; }
    .price { font-weight: bold; color: #093028; }
    .product-card button {
      background: #00695c; color: white; border: none;
      padding: 8px 20px; border-radius: 5px; margin-top: 10px; cursor: pointer;
    }
    .product-card button:hover { background: #004d40; }
    .sold-out-overlay {
      position: absolute; top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0,0,0,0.6); color: white;
      display: flex; justify-content: center; align-items: center;
      font-size: 1.4rem; font-weight: bold;
    }
    .low-stock {
      margin-top: 8px;
      display: block;
      color: #b71c1c;
      padding: 6px 10px;
      border-radius: 20px;
      font-weight: 700;
      font-size: 0.95rem;
    }
    .pagination {
      display: flex; justify-content: center; align-items: center; gap: 10px; margin: 40px 0;
    }
    .pagination a {
      padding: 8px 14px; background: #f0f0f0; color: #333;
      border-radius: 5px; text-decoration: none;
    }
    .pagination a.active { background: #00695c; color: white; }
    .pagination a:hover { background: #004d40; color: white; }
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

<section class="product-grid">
  <div style="width:100%;padding:20px 10%;">
    <h2 style="margin:0;padding:0;"><?php echo $selectedCategory !== '' ? htmlspecialchars($selectedCategory) : 'All Products'; ?></h2>
  </div>
  
  <?php foreach ($paginatedProducts as $p): ?>
    <div class="product-card">
      <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
      <h3><?= htmlspecialchars($p['name']) ?></h3>
      <p><?= htmlspecialchars($p['description']) ?></p>
      <div class="price">$<?= htmlspecialchars($p['price']) ?></div>

      <?php $qty = isset($p['quantity']) ? (int)$p['quantity'] : 0; ?>
      <?php if ($qty <= 0): ?>
        <div class="sold-out-overlay">Sold Out</div>
        <button disabled style="background:#ccc;cursor:not-allowed;">Sold Out</button>
      <?php else: ?>
        <?php if ($qty < 10): ?>
          <div class="low-stock">Only <?= htmlspecialchars($qty) ?> left!</div>
        <?php endif; ?>
        <form method="post" action="cart.php?action=add" style="margin-top:10px;display:flex;justify-content:center;gap:8px;align-items:center;">
          <input type="hidden" name="id" value="<?= htmlspecialchars($p['id'], ENT_QUOTES) ?>">
          <input type="hidden" name="name" value="<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>">
          <input type="hidden" name="price" value="<?= htmlspecialchars($p['price'], ENT_QUOTES) ?>">
          <input type="hidden" name="image" value="<?= htmlspecialchars($p['image'], ENT_QUOTES) ?>">
          <input type="hidden" name="description" value="<?= htmlspecialchars($p['description'], ENT_QUOTES) ?>">
          <label style="font-size:0.9rem;">Qty
            <input type="number" name="quantity" min="1" max="<?= $qty ?>" value="1" style="width:60px;padding:6px;border-radius:4px;border:1px solid #ccc;">
          </label>
          <button type="submit">Add to Cart</button>
        </form>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</section>

<div class="pagination">
  <?php if ($page > 1): ?>
    <a href="?page=<?= $page - 1 ?>">&laquo; Prev</a>
  <?php endif; ?>
  <?php for ($i = 1; $i <= $totalPages; $i++): ?>
    <a href="?page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
  <?php endfor; ?>
  <?php if ($page < $totalPages): ?>
    <a href="?page=<?= $page + 1 ?>">Next &raquo;</a>
  <?php endif; ?>
</div>

<footer>
  <p style="text-align:center;margin-top:20px;">&copy; 2025 Marketplace. All Rights Reserved.</p>
</footer>

</body>
</html>
