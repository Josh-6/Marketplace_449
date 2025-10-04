<?php
session_start();
// Example: for testing, uncomment the next line to simulate a logged-in user
 $_SESSION['username'] = "Alejandro";
// $_SESSION['username'] = "Josh";
// $_SESSION['username'] = "Tony";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Marketplace - eCommerce Home Page</title>
  <link rel="stylesheet" href="css/style.css?v=1" />
</head>
<body>

  <!-- Navbar -->
  <header>
    <div class="logo">Marketplace</div>
    <nav>
      <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="product.php">Products</a></li>
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
              <a href="logout.php">Sign Out</a>
            </div>
          <?php else: ?>
            <a href="signin.php">Hello, Sign in</a>
            <div class="dropdown-menu">
              <a href="createacc.php">Create Account</a>
            </div>
          <?php endif; ?>
        </li> 
      </ul>
    </nav>
    <div class="icons">
      🔍 🛒
    </div>
  </header>

  <!-- Hero Section -->
  <section class="hero">
    <div class="hero-text">
      <?php if (isset($_SESSION['username'])): ?>
        <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <p>Here are some recommendations for you.</p>
        <button>Shop Now</button>
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
    <h2>
      <?php if (isset($_SESSION['username'])): ?>
        Recommended for You
      <?php else: ?>
        Explore by Category
      <?php endif; ?>
    </h2>
    <div class="category-grid">
      <div class="category-card">
        <img src="images/electronics.jpg" alt="Electronics">
        <span>Electronics</span>
      </div>
      <div class="category-card">
        <img src="images/furniture.jpg" alt="Furniture">
        <span>Furniture</span>
      </div>
      <div class="category-card">
        <img src="images/food2.webp" alt="Food">
        <span>Food</span>
      </div>
      <div class="category-card">
        <img src="images/beauty.jpg" alt="Beauty">
        <span>Beauty and Personal Care</span>
      </div>
      <div class="category-card">
        <img src="images/fashion2.webp" alt="Fashion">
        <span>Fashion and Apparel</span>
      </div>
      <div class="category-card">
        <img src="images/toy.webp" alt="Toy">
        <span>Toy and Hobbies</span>
      </div>
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
