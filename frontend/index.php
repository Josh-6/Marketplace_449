<?php
// index.php
// Load configuration settings
$servername = "localhost";
$username = "root";
$password = "";

$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//check session for user login status
// session_start();



?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Marketplace - eCommerce Home Page</title>
  <!-- Correct CSS path -->
  <link rel="stylesheet" href="css/style.css?v=1" /><!-- If stylesheet changes notrelected increment number -->
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
          <a href="signin.php">Hello, Sign in</a>
          <div class="dropdown-menu">
            <a href="#">Profile</a>
            <a href="#">History</a>
            <a href="#">Orders</a> 
          </div>
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
      <h1>Exclusive Deals</h1>
      <p>Explore different categories. Find the best deals.</p>
      <button>Shop Now</button>
    </div>
    <img src="images/hero_image.jpg" alt="Hero Image">
  </section>

  <!-- Categories Section -->
  <section class="categories">
    <h2>Explore by Category</h2>
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
  <section>

    <h2>Featured Products</h2>
    <div class="product-grid" id="product-grid">
      <!-- Products will be loaded here In the mean time later on we can use js to load more products-->
      <?php
        $query = "SELECT Item_ID, Item_Name, Item_Description, Item_Price, Item_Tags, Item_Quantity FROM Marketplace.Item LIMIT 6";
        $result = mysqli_query($conn, $query);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo '<div class="product-card">';
                echo '<img src="images/product_placeholder.png" alt="' . htmlspecialchars($row['Item_Name']) . ' image">'; // Placeholder image
                echo '<h3>' . htmlspecialchars($row['Item_Name']) . '</h3>';
                echo '<p>' . htmlspecialchars($row['Item_Description']) . '</p>';
                echo '<p>$' . number_format($row['Item_Price'], 2) . '</p>';
                echo '<button>Add to Cart</button>';
                echo '</div>';
            }
        } else {
            echo "<p>Error loading products: " . mysqli_error($conn) . "</p>";
        }
      ?>
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