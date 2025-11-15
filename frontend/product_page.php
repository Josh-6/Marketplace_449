<?php
require_once __DIR__ . '/../backend/cartControl.php';

// Ensure session is started so login state (e.g., $_SESSION['username']) is available
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Include database connection
require_once __DIR__ . '/../database/db_connect.php';
$conn = get_db_connection();

// Category filter: optional ?category=Electronics
$selectedCategory = isset($_GET['category']) ? trim($_GET['category']) : '';
$selectedCategory = preg_replace('/[^\w\s&-]/', '', $selectedCategory);

// Search functionality
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchTerm = preg_replace('/[^\w\s-]/', '', $searchTerm);

// Pagination logic
$itemsPerPage = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $itemsPerPage;

// Prepare the base SQL query
$countQuery = "SELECT COUNT(*) as total FROM Marketplace.Item WHERE 1=1";
$productsQuery = "SELECT i.*, u.Username as seller_name 
                 FROM Marketplace.Item i 
                 LEFT JOIN Marketplace.Seller s ON i.Seller_ID = s.Seller_ID 
                 LEFT JOIN Marketplace.Users u ON s.Seller_ID = u.User_ID
                 WHERE 1=1";

$params = [];
$paramTypes = '';

// Add category filter if specified
if ($selectedCategory !== '') {
    $countQuery .= " AND Item_Tags LIKE ?";
    $productsQuery .= " AND Item_Tags LIKE ?";
    $categoryParam = "%$selectedCategory%";
    $params[] = $categoryParam;
    $paramTypes .= 's';
}

// Add search filter if specified
if ($searchTerm !== '') {
    $countQuery .= " AND (Item_Name LIKE ? OR Item_Description LIKE ?)";
    $productsQuery .= " AND (Item_Name LIKE ? OR Item_Description LIKE ?)";
    $searchParam = "%$searchTerm%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $paramTypes .= 'ss';
}

// Get total count for pagination
$stmt = $conn->prepare($countQuery);
if (!empty($params)) {
    $stmt->bind_param($paramTypes, ...$params);
}
$stmt->execute();
$totalResult = $stmt->get_result()->fetch_assoc();
$totalItems = $totalResult['total'];
$totalPages = $totalItems > 0 ? ceil($totalItems / $itemsPerPage) : 1;


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
    
    .category-filter {
      background: #f8f9fa; padding: 20px; margin: 20px 10%; border-radius: 10px;
      display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; align-items: center;
    }
    .category-filter h3 { margin: 0 15px 0 0; color: #00695c; }
    .category-btn {
      padding: 8px 15px; background: white; color: #00695c; 
      border: 2px solid #00695c; border-radius: 20px; text-decoration: none;
      transition: all 0.3s ease; font-size: 14px; font-weight: 500;
    }
    .category-btn:hover, .category-btn.active {
      background: #00695c; color: white;
    }
    .search-bar {
      margin: 20px 10%; text-align: center;
    }
    .search-bar input {
      padding: 10px 15px; width: 300px; border: 2px solid #00695c;
      border-radius: 25px; outline: none; font-size: 14px;
    }
    .search-bar button {
      padding: 10px 20px; background: #00695c; color: white;
      border: none; border-radius: 20px; margin-left: 10px; cursor: pointer;
    }
  </style>
</head>
<body>

 <header>
    <div class="logo">Marketplace</div>
    <nav>
      <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="product_page.php">Products</a></li>
        <!-- <li><a href="#">Categories</a></li> -->
        <li><a href="upload_item.php">Sell Item</a></li>
        <li><a href="#">About</a></li>
        <li><a href="#">Contact Us</a></li>
        <li class="dropdown">
              <?php if (isset($_SESSION['username'])): ?>
            <a href="#">Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></a>
            <div class="dropdown-menu">
              <a href="profile.php">Profile</a>
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

<!-- Category Filter Section -->
<div class="category-filter">
  <h3>Browse by Category:</h3>
  <a href="product_page.php" class="category-btn <?php echo $selectedCategory === '' ? 'active' : ''; ?>">All Products</a>
  <a href="product_page.php?category=Electronics" class="category-btn <?php echo $selectedCategory === 'Electronics' ? 'active' : ''; ?>">Electronics</a>
  <a href="product_page.php?category=Furniture" class="category-btn <?php echo $selectedCategory === 'Furniture' ? 'active' : ''; ?>">Furniture</a>
  <a href="product_page.php?category=Food" class="category-btn <?php echo $selectedCategory === 'Food' ? 'active' : ''; ?>">Food</a>
  <a href="product_page.php?category=Beauty%20and%20Personal%20Care" class="category-btn <?php echo $selectedCategory === 'Beauty and Personal Care' ? 'active' : ''; ?>">Beauty & Personal Care</a>
  <a href="product_page.php?category=Fashion%20and%20Apparel" class="category-btn <?php echo $selectedCategory === 'Fashion and Apparel' ? 'active' : ''; ?>">Fashion & Apparel</a>
  <a href="product_page.php?category=Toy%20and%20Hobbies" class="category-btn <?php echo $selectedCategory === 'Toy and Hobbies' ? 'active' : ''; ?>">Toys & Hobbies</a>
</div>

<!-- Search Bar -->
<div class="search-bar">
  <form method="GET" action="product_page.php">
    <input type="text" name="search" placeholder="Search products..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
    <?php if ($selectedCategory): ?>
      <input type="hidden" name="category" value="<?php echo htmlspecialchars($selectedCategory); ?>">
    <?php endif; ?>
    <button type="submit">Search</button>
  </form>
</div>

<section class="product-grid">
  <div style="width:100%;padding:20px 10%;">
    <h2 style="margin:0;padding:0;">
      <?php 
        if ($searchTerm !== '' && $selectedCategory !== '') {
          echo "Search results for \"" . htmlspecialchars($searchTerm) . "\" in " . htmlspecialchars($selectedCategory);
        } elseif ($searchTerm !== '') {
          echo "Search results for \"" . htmlspecialchars($searchTerm) . "\"";
        } elseif ($selectedCategory !== '') {
          echo htmlspecialchars($selectedCategory);
        } else {
          echo 'All Products';
        }
        echo " ($totalItems items found)";
      ?>
    </h2>
  </div>
  
  <?php
    // Get paginated products
  $productsQuery .= " ORDER BY Added_On DESC LIMIT ? OFFSET ?";
  $stmt = $conn->prepare($productsQuery);
  
  // Add pagination parameters
  $paginationParams = $params;
  $paginationParams[] = $itemsPerPage;
  $paginationParams[] = $offset;
  $paginationParamTypes = $paramTypes . 'ii';
  
  if (!empty($paginationParams)) {
      $stmt->bind_param($paginationParamTypes, ...$paginationParams);
  }
  
  $stmt->execute();
  $paginatedProducts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  
  if (empty($paginatedProducts)): ?>
    <div style="text-align: center; padding: 60px 20px; grid-column: 1 / -1;">
      <h3 style="color: #666; margin-bottom: 20px;">No products found</h3>
      <p style="color: #999; margin-bottom: 20px;">
        <?php if ($searchTerm !== '' || $selectedCategory !== ''): ?>
          Try adjusting your search criteria or browse all products.
        <?php else: ?>
          No products have been uploaded yet. Be the first to <a href="upload_item.php" style="color: #00695c;">upload an item</a>!
        <?php endif; ?>
      </p>
      <?php if ($searchTerm !== '' || $selectedCategory !== ''): ?>
        <a href="product_page.php" style="background: #00695c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">View All Products</a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
 
  <?php foreach ($paginatedProducts as $p): 
    $imagePath = "images/products/" . htmlspecialchars($p['Item_ID']) . ".jpg";
    if (!file_exists($imagePath)) {
      $imagePath = "images/products/default.jpg";
    }
  ?>
    <div class="product-card">
      <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($p['Item_Name']) ?>" onerror="this.src='images/products/default.jpg'">
      <h3><?= htmlspecialchars($p['Item_Name']) ?></h3>
      <p><?= htmlspecialchars($p['Item_Description']) ?></p>
      <p><small>Sold by: <?= htmlspecialchars($p['seller_name']) ?></small></p>
      <div class="price">$<?= htmlspecialchars(number_format($p['Item_Price'], 2)) ?></div>

      <?php $qty = (int)$p['Item_Quantity']; ?>
      <?php if ($qty <= 0): ?>
        <div class="sold-out-overlay">Sold Out</div>
        <button disabled style="background:#ccc;cursor:not-allowed;">Sold Out</button>
      <?php else: ?>
        <?php if ($qty < 10): ?>
          <div class="low-stock">Only <?= htmlspecialchars($qty) ?> left!</div>
        <?php else: ?>
          <div class="in-stock">In Stock: <?= htmlspecialchars($qty) ?> left</div>
        <?php endif; ?>
        <form method="post" action="cart.php?action=add" style="margin-top:10px;display:flex;justify-content:center;gap:8px;align-items:center;">
          <input type="hidden" name="id" value="<?= htmlspecialchars($p['Item_ID'], ENT_QUOTES) ?>">
          <input type="hidden" name="name" value="<?= htmlspecialchars($p['Item_Name'], ENT_QUOTES) ?>">
          <input type="hidden" name="price" value="<?= htmlspecialchars($p['Item_Price'], ENT_QUOTES) ?>">
          <input type="hidden" name="seller_id" value="<?= htmlspecialchars($p['Seller_ID'], ENT_QUOTES) ?>">
          <input type="hidden" name="description" value="<?= htmlspecialchars($p['Item_Description'], ENT_QUOTES) ?>">
          <label style="font-size:0.9rem;">Qty
            <input class="quantity" type="number" name="quantity" min="1" max="<?= $qty ?>" value="1" style="width:60px;padding:6px;border-radius:4px;border:1px solid #ccc;">
          </label>
          <button type="submit">Add to Cart</button>
        </form>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</section>

<div class="pagination">
  <?php 
  // Build query string for pagination
  $queryParams = [];
  if ($selectedCategory !== '') $queryParams['category'] = $selectedCategory;
  if ($searchTerm !== '') $queryParams['search'] = $searchTerm;
  $queryString = !empty($queryParams) ? '&' . http_build_query($queryParams) : '';
  ?>
  <?php if ($page > 1): ?>
    <a href="?page=<?= $page - 1 ?><?= $queryString ?>">&laquo; Prev</a>
  <?php endif; ?>
  <?php for ($i = 1; $i <= $totalPages; $i++): ?>
    <a href="?page=<?= $i ?><?= $queryString ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
  <?php endfor; ?>
  <?php if ($page < $totalPages): ?>
    <a href="?page=<?= $page + 1 ?><?= $queryString ?>">Next &raquo;</a>
  <?php endif; ?>
</div>

<?php
// Close the database connection
$stmt->close();
$conn->close();
?>

<footer>
  <p style="text-align:center;margin-top:20px;">&copy; 2025 Marketplace. All Rights Reserved.</p>
</footer>

</body>
</html>
