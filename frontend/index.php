<?php
// index.php
// Load configuration settings
$servername = "localhost";
$username = "root";
$password = "";

require_once __DIR__ . '/../database/db_connect.php';

$conn = get_db_connection();


//check session for user login status
session_start();

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Marketplace - eCommerce Home Page</title>
  <!-- Correct CSS path -->
  <link rel="stylesheet" href="css/style.css?v=2" /><!-- If stylesheet changes notrelected increment number -->
</head>

<body>

  <!-- Navbar -->
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

  <!-- Hero Section -->
  <section class="hero">
    <div class="hero-text">
      <?php if (isset($_SESSION['username'])): ?>
        <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <p>Here are some recommendations for you.</p>
        <!-- <button>Shop Now</button> -->
      <?php else: ?>
        <h1>Exclusive Deals</h1>
        <p>Explore different categories. Find the best deals.</p>
        <button>Shop Now</button>
      <?php endif; ?>
    </div>
    <img src="images/hero_image.jpg" alt="Hero Image">
  </section>

  <!-- Categories Section -->
  <section class="categories">
    <h2>Explore by Category</h2>
    <div class="category-grid">
      <div class="category-card">
        <a href="product_page.php?category=Electronics" style="text-decoration:none;color:inherit;display:block;">
          <img src="images/electronics.jpg" alt="Electronics">
          <span>Electronics</span>
        </a>
      </div>
      <div class="category-card">
        <a href="product_page.php?category=Furniture" style="text-decoration:none;color:inherit;display:block;">
          <img src="images/furniture.jpg" alt="Furniture">
          <span>Furniture</span>
        </a>
      </div>
      <div class="category-card">
        <a href="product_page.php?category=Food" style="text-decoration:none;color:inherit;display:block;">
          <img src="images/food2.webp" alt="Food">
          <span>Food</span>
        </a>
      </div>
      <div class="category-card">
        <a href="product_page.php?category=Beauty%20and%20Personal%20Care" style="text-decoration:none;color:inherit;display:block;">
          <img src="images/beauty.jpg" alt="Beauty">
          <span>Beauty and Personal Care</span>
        </a>
      </div>
      <div class="category-card">
        <a href="product_page.php?category=Fashion%20and%20Apparel" style="text-decoration:none;color:inherit;display:block;">
          <img src="images/fashion2.webp" alt="Fashion">
          <span>Fashion and Apparel</span>
        </a>
      </div>
      <div class="category-card">
        <a href="product_page.php?category=Toy%20and%20Hobbies" style="text-decoration:none;color:inherit;display:block;">
          <img src="images/toy.webp" alt="Toy">
          <span>Toy and Hobbies</span>
        </a>
      </div>
    </div>
  </section>
  <section>

    <h2>Featured Products</h2>
    <div class="product-grid" id="product-grid">
      <?php
      $query = "SELECT Item_ID, Item_Name, Item_Price, Item_Description,Seller_ID, Item_Quantity FROM Marketplace.Item LIMIT 6";
      $result = mysqli_query($conn, $query);
      foreach ($result as $p): ?>
        <div class="product-card">
          <img src="images/products/<?= htmlspecialchars($p['Item_ID']) ?>.jpg" alt="<?= htmlspecialchars($p['Item_Name']) ?>">
          <h3><?= htmlspecialchars($p['Item_Name']) ?></h3>
          <p><?= htmlspecialchars($p['Item_Description']) ?></p>
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
            <form method="post" action="cart.php?action=add">
              <input type="hidden" name="id" value="<?= htmlspecialchars($p['Item_ID'], ENT_QUOTES) ?>">
              <input type="hidden" name="name" value="<?= htmlspecialchars($p['Item_Name'], ENT_QUOTES) ?>">
              <input type="hidden" name="price" value="<?= htmlspecialchars($p['Item_Price'], ENT_QUOTES) ?>">
              <input type="hidden" name="seller_id" value="<?= htmlspecialchars($p['Seller_ID'], ENT_QUOTES) ?>">
              <input type="hidden" name="description" value="<?= htmlspecialchars($p['Item_Description'], ENT_QUOTES) ?>">
              <label style="font-size:0.9rem;">Qty
                <input class="quantity" type="number" name="quantity" min="1" max="<?= $p['Item_Quantity'] ?>" value="1" style="width:60px;padding:6px;border-radius:4px;border:1px solid #ccc;">
              </label>
              <button type="submit">Add to Cart</button>
            </form>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <h2>New Products</h2>
    <div class="product-grid" id="product-grid">
      <!-- Products will be loaded here In the mean time later on we can use js to load more products-->
      <?php
      $query = "SELECT * FROM Marketplace.new_products LIMIT 10";
      $result = mysqli_query($conn, $query);
      foreach ($result as $p): ?>
        <div class="product-card">
          <img src="images/products/<?= htmlspecialchars($p['Item_ID']) ?>.jpg" alt="<?= htmlspecialchars($p['Item_Name']) ?>">
          <h3><?= htmlspecialchars($p['Item_Name']) ?></h3>
          <p><?= htmlspecialchars($p['Item_Description']) ?></p>
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
            <form method="post" action="cart.php?action=add">
              <input type="hidden" name="id" value="<?= htmlspecialchars($p['Item_ID'], ENT_QUOTES) ?>">
              <input type="hidden" name="name" value="<?= htmlspecialchars($p['Item_Name'], ENT_QUOTES) ?>">
              <input type="hidden" name="price" value="<?= htmlspecialchars($p['Item_Price'], ENT_QUOTES) ?>">
              <input type="hidden" name="seller_id" value="<?= htmlspecialchars($p['Seller_ID'], ENT_QUOTES) ?>">
              <input type="hidden" name="description" value="<?= htmlspecialchars($p['Item_Description'], ENT_QUOTES) ?>">
              <label style="font-size:0.9rem;">Qty
                <input class="quantity" type="number" name="quantity" min="1" max="<?= $p['Item_Quantity'] ?>" value="1" style="width:60px;padding:6px;border-radius:4px;border:1px solid #ccc;">
              </label>
              <button type="submit">Add to Cart</button>
            </form>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </section>


  <!-- Footer -->
  <footer>
    <div class="footer-content">
      <div>
        <h3>About Us</h3>
        <p>Marketplace brings you the best collection of furniture, electronics, food, and more. Quality products, exclusive deals.</p>
      </div>
      <div>
        <h3>Quick Links</h3>
        <ul>
          <li><a href="product.html">Products</a></li>
          <li><a href="#">Categories</a></li>
          <li><a href="#">Support</a></li>
          <li><a href="#">Privacy Policy</a></li>
        </ul>
      </div>
      <div>
        <h3>Contact</h3>
        <ul>
          <li>Email: support@Marketplace.com</li>
          <li>Phone: +1 234 567 890</li>
          <li>Location: California, USA</li>
        </ul>
      </div>
    </div>
    <p>&copy; 2025 CSUF. All Rights Reserved.</p>
  </footer>

  <!-- JS file -->
  <script src="js/app.js"></script>
  <script src="js/index.js"></script>
</body>

</html>

<?php
// close connection
mysqli_close($conn);
?>