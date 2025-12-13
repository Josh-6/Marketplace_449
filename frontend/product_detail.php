<?php
require_once __DIR__ . '/../backend/cartControl.php';

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Include database connection
require_once __DIR__ . '/../database/db_connect.php';
$conn = get_db_connection();

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header('Location: product_page.php');
    exit();
}


// Add product to user view histroy
$userId = $_SESSION['user_id'] ?? 0;
if ($userId && $product_id) {
  
    $stmt = $conn->prepare("
            INSERT INTO Marketplace.User_History 
            (User_ID, Item_ID, History_Type, Purchased_At)
            VALUES (?, ?, 'view', NOW())
        ");
    $stmt->bind_param("ii", $userId, $product_id);
    $stmt->execute();

    echo "<script>console.log('Viewed Item ID $product_id  for User ID $userId');</script>";
    $stmt->close();
}

// Fetch product details
$stmt = $conn->prepare("SELECT i.*, u.Username as seller_name, u.User_Email as seller_email 
                       FROM Marketplace.Item i 
                       LEFT JOIN Marketplace.Seller s ON i.Seller_ID = s.Seller_ID 
                       LEFT JOIN Marketplace.Users u ON s.Seller_ID = u.User_ID
                       WHERE i.Item_ID = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header('Location: product_page.php');
    exit();
}

// Fetch reviews for this product
$reviews_stmt = $conn->prepare("SELECT r.*, u.Username as reviewer_name 
                               FROM Marketplace.Review r 
                               LEFT JOIN Marketplace.Buyer b ON r.Buyer_ID = b.Buyer_ID 
                               LEFT JOIN Marketplace.Users u ON b.Buyer_ID = u.User_ID
                               WHERE r.Item_ID = ? 
                               ORDER BY r.Review_Date DESC");
$reviews_stmt->bind_param("i", $product_id);
$reviews_stmt->execute();
$reviews = $reviews_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate average rating
$avg_rating = 0;
$total_reviews = count($reviews);
if ($total_reviews > 0) {
    $total_rating = array_sum(array_column($reviews, 'Review_Rating'));
    $avg_rating = $total_rating / $total_reviews;
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (isset($_SESSION['username'])) {
        $rating = (int)$_POST['rating'];
        $review_text = trim($_POST['review_text']);
        
        if ($rating >= 1 && $rating <= 5 && !empty($review_text)) {
            // Get user ID first
            $user_stmt = $conn->prepare("SELECT User_ID FROM Marketplace.Users WHERE Username = ?");
            $user_stmt->bind_param("s", $_SESSION['username']);
            $user_stmt->execute();
            $user_result = $user_stmt->get_result()->fetch_assoc();
            
            if ($user_result) {
                $user_id = $user_result['User_ID'];
                
                // Check if user exists in Buyer table, if not, create entry
                $buyer_check = $conn->prepare("SELECT Buyer_ID FROM Marketplace.Buyer WHERE Buyer_ID = ?");
                $buyer_check->bind_param("i", $user_id);
                $buyer_check->execute();
                $buyer_exists = $buyer_check->get_result()->fetch_assoc();
                
                if (!$buyer_exists) {
                    // Create buyer entry with default values
                    $phone_number = '000-000-' . str_pad($user_id, 4, '0', STR_PAD_LEFT);
                    $create_buyer = $conn->prepare("INSERT INTO Marketplace.Buyer (Buyer_ID, CS_ID, Buyer_Phone_Number, Buyer_Location) VALUES (?, 1, ?, 'Unknown')");
                    $create_buyer->bind_param("is", $user_id, $phone_number);
                    $create_buyer->execute();
                }
                
                $buyer_id = $user_id;
                
                // Check if user already reviewed this product
                $check_stmt = $conn->prepare("SELECT Review_ID FROM Marketplace.Review WHERE Buyer_ID = ? AND Item_ID = ?");
                $check_stmt->bind_param("ii", $buyer_id, $product_id);
                $check_stmt->execute();
                $existing_review = $check_stmt->get_result()->fetch_assoc();
                
                if (!$existing_review) {
                    // Get next review ID
                    $id_stmt = $conn->prepare("SELECT COALESCE(MAX(Review_ID), 0) + 1 as next_id FROM Marketplace.Review");
                    $id_stmt->execute();
                    $next_id = $id_stmt->get_result()->fetch_assoc()['next_id'];
                    
                    // Insert new review
                    $insert_stmt = $conn->prepare("INSERT INTO Marketplace.Review (Review_ID, Review_text, Review_Rating, Review_Date, Buyer_ID, Item_ID) VALUES (?, ?, ?, CURDATE(), ?, ?)");
                    $insert_stmt->bind_param("isiii", $next_id, $review_text, $rating, $buyer_id, $product_id);
                    
                    if ($insert_stmt->execute()) {
                        $success_message = "Review submitted successfully!";
                        // Refresh page to show new review
                        header("Location: product_detail.php?id=" . $product_id);
                        exit();
                    } else {
                        $error_message = "Error submitting review. Please try again.";
                    }
                } else {
                    $error_message = "You have already reviewed this product.";
                }
            }
        } else {
            $error_message = "Please provide a valid rating (1-5) and review text.";
        }
    } else {
        $error_message = "Please log in to submit a review.";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo htmlspecialchars($product['Item_Name']); ?> - Marketplace</title>
  <link rel="stylesheet" href="css/style.css?v=1" />
  <style>
    .product-detail-container {
      max-width: 1200px;
      margin: 20px auto;
      padding: 0 20px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 40px;
      align-items: start;
    }
    .product-image {
      text-align: center;
    }
    .product-image img {
      width: 100%;
      max-width: 500px;
      height: auto;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    .product-info {
      padding: 20px 0;
    }
    .product-title {
      font-size: 2.5rem;
      color: #00695c;
      margin-bottom: 10px;
      font-weight: 600;
    }
    .product-description {
      font-size: 1.1rem;
      color: #666;
      line-height: 1.6;
      margin-bottom: 20px;
    }
    .product-price {
      font-size: 2.2rem;
      font-weight: bold;
      color: #093028;
      margin-bottom: 15px;
    }
    .seller-info {
      background: #f8f9fa;
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 20px;
    }
    .seller-info h4 {
      color: #00695c;
      margin-bottom: 8px;
    }
    .rating-container {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 20px;
    }
    .stars {
      display: flex;
      gap: 2px;
    }
    .star {
      font-size: 1.5rem;
      color: #ddd;
    }
    .star.filled {
      color: #ffd700;
    }
    .star.half {
      background: linear-gradient(90deg, #ffd700 50%, #ddd 50%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    .rating-text {
      font-size: 1rem;
      color: #666;
    }
    .quantity-selector {
      display: flex;
      align-items: center;
      gap: 15px;
      margin: 20px 0;
    }
    .quantity-input {
      width: 80px;
      padding: 10px;
      border: 2px solid #00695c;
      border-radius: 5px;
      text-align: center;
      font-size: 1rem;
    }
    .add-to-cart-btn {
      background: #00695c;
      color: white;
      border: none;
      padding: 15px 30px;
      border-radius: 8px;
      font-size: 1.1rem;
      cursor: pointer;
      transition: background 0.3s ease;
      margin-top: 20px;
    }
    .add-to-cart-btn:hover {
      background: #004d40;
    }
    .add-to-cart-btn:disabled {
      background: #ccc;
      cursor: not-allowed;
    }
    .stock-info {
      font-size: 1rem;
      margin: 10px 0;
    }
    .in-stock { color: #2e7d32; }
    .low-stock { color: #f57c00; }
    .out-of-stock { color: #d32f2f; }
    
    .reviews-section {
      grid-column: 1 / -1;
      margin-top: 40px;
    }
    .reviews-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
    }
    .review-form {
      background: #f8f9fa;
      padding: 25px;
      border-radius: 10px;
      margin-bottom: 30px;
    }
    .review-form h3 {
      color: #00695c;
      margin-bottom: 20px;
    }
    .rating-input {
      display: flex;
      flex-direction: row-reverse;
      gap: 5px;
      margin-bottom: 15px;
      justify-content: flex-end;
    }
    .rating-input input {
      display: none;
    }
    .rating-input label {
      font-size: 1.8rem;
      color: #ddd;
      cursor: pointer;
      transition: color 0.2s ease;
    }
    .rating-input label:hover,
    .rating-input label:hover ~ label {
      color: #ffd700;
    }
    .rating-input input:checked ~ label {
      color: #ffd700;
    }
    .review-textarea {
      width: 100%;
      min-height: 120px;
      padding: 15px;
      border: 2px solid #ddd;
      border-radius: 8px;
      font-size: 1rem;
      font-family: inherit;
      resize: vertical;
    }
    .submit-review-btn {
      background: #00695c;
      color: white;
      border: none;
      padding: 12px 25px;
      border-radius: 5px;
      cursor: pointer;
      margin-top: 15px;
    }
    .review-item {
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 15px;
      background: white;
    }
    .review-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 10px;
    }
    .review-author {
      font-weight: 600;
      color: #00695c;
    }
    .review-date {
      color: #999;
      font-size: 0.9rem;
    }
    .review-rating {
      margin-bottom: 10px;
    }
    .review-text {
      line-height: 1.6;
      color: #333;
    }
    .message {
      padding: 15px;
      border-radius: 5px;
      margin-bottom: 20px;
    }
    .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    
    @media (max-width: 768px) {
      .product-detail-container {
        grid-template-columns: 1fr;
        gap: 20px;
      }
      .product-title {
        font-size: 2rem;
      }
      .product-price {
        font-size: 1.8rem;
      }
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

<div class="product-detail-container">
    <div class="product-image">
        <?php 
        $imagePath = "images/products/" . htmlspecialchars($product['Item_ID']) . ".jpg";
        if (!file_exists($imagePath)) {
            $imagePath = "images/products/default.jpg";
        }
        ?>
        <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($product['Item_Name']) ?>" onerror="this.src='images/products/default.jpg'">
    </div>
    
    <div class="product-info">
        <h1 class="product-title"><?= htmlspecialchars($product['Item_Name']) ?></h1>
        
        <div class="rating-container">
            <div class="stars">
                <?php 
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= floor($avg_rating)) {
                        echo '<span class="star filled">★</span>';
                    } elseif ($i == ceil($avg_rating) && $avg_rating - floor($avg_rating) >= 0.5) {
                        echo '<span class="star half">★</span>';
                    } else {
                        echo '<span class="star">★</span>';
                    }
                }
                ?>
            </div>
            <span class="rating-text">
                <?php if ($total_reviews > 0): ?>
                    <?= number_format($avg_rating, 1) ?> out of 5 (<?= $total_reviews ?> review<?= $total_reviews != 1 ? 's' : '' ?>)
                <?php else: ?>
                    No reviews yet
                <?php endif; ?>
            </span>
        </div>
        
        <div class="product-price">$<?= number_format($product['Item_Price'], 2) ?></div>
        
        <p class="product-description"><?= htmlspecialchars($product['Item_Description']) ?></p>
        
        <div class="seller-info">
            <h4>Sold by: <?= htmlspecialchars($product['seller_name']) ?></h4>
            <p>Contact: <?= htmlspecialchars($product['seller_email']) ?></p>
        </div>
        
        <?php $qty = (int)$product['Item_Quantity']; ?>
        <div class="stock-info <?= $qty <= 0 ? 'out-of-stock' : ($qty < 10 ? 'low-stock' : 'in-stock') ?>">
            <?php if ($qty <= 0): ?>
                Out of Stock
            <?php elseif ($qty < 10): ?>
                Only <?= $qty ?> left in stock!
            <?php else: ?>
                In Stock (<?= $qty ?> available)
            <?php endif; ?>
        </div>
        
        <?php if ($qty > 0): ?>
        <form method="post" action="cart.php?action=add">
            <input type="hidden" name="id" value="<?= htmlspecialchars($product['Item_ID'], ENT_QUOTES) ?>">
            <input type="hidden" name="name" value="<?= htmlspecialchars($product['Item_Name'], ENT_QUOTES) ?>">
            <input type="hidden" name="price" value="<?= htmlspecialchars($product['Item_Price'], ENT_QUOTES) ?>">
            <input type="hidden" name="seller_id" value="<?= htmlspecialchars($product['Seller_ID'], ENT_QUOTES) ?>">
            <input type="hidden" name="description" value="<?= htmlspecialchars($product['Item_Description'], ENT_QUOTES) ?>">
            
            <div class="quantity-selector">
                <label for="quantity">Quantity:</label>
                <input type="number" id="quantity" name="quantity" class="quantity-input" 
                       min="1" max="<?= $qty ?>" value="1">
            </div>
            
            <button type="submit" class="add-to-cart-btn">Add to Cart</button>
        </form>
        <?php else: ?>
            <button class="add-to-cart-btn" disabled>Out of Stock</button>
        <?php endif; ?>
    </div>
    
    <div class="reviews-section">
        <div class="reviews-header">
            <h2>Customer Reviews</h2>
        </div>
        
        <?php if (isset($success_message)): ?>
            <div class="message success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="message error"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['username'])): ?>
        <div class="review-form">
            <h3>Write a Review</h3>
            <form method="post">
                <div class="rating-input">
                    <input type="radio" id="star5" name="rating" value="5" />
                    <label for="star5">★</label>
                    <input type="radio" id="star4" name="rating" value="4" />
                    <label for="star4">★</label>
                    <input type="radio" id="star3" name="rating" value="3" />
                    <label for="star3">★</label>
                    <input type="radio" id="star2" name="rating" value="2" />
                    <label for="star2">★</label>
                    <input type="radio" id="star1" name="rating" value="1" />
                    <label for="star1">★</label>
                </div>
                <textarea name="review_text" class="review-textarea" 
                          placeholder="Share your experience with this product..." required></textarea>
                <button type="submit" name="submit_review" class="submit-review-btn">Submit Review</button>
            </form>
        </div>
        <?php else: ?>
        <div class="review-form">
            <p><a href="signin.php">Sign in</a> to write a review for this product.</p>
        </div>
        <?php endif; ?>
        
        <div class="reviews-list">
            <?php if (empty($reviews)): ?>
                <p style="text-align: center; color: #666; margin: 40px 0;">No reviews yet. Be the first to review this product!</p>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                <div class="review-item">
                    <div class="review-header">
                        <div class="review-author"><?= htmlspecialchars($review['reviewer_name']) ?></div>
                        <div class="review-date"><?= date('M j, Y', strtotime($review['Review_Date'])) ?></div>
                    </div>
                    <div class="review-rating">
                        <div class="stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star <?= $i <= $review['Review_Rating'] ? 'filled' : '' ?>">★</span>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="review-text"><?= htmlspecialchars($review['Review_text']) ?></div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<footer>
  <p style="text-align:center;margin-top:40px;">&copy; 2025 Marketplace. All Rights Reserved.</p>
</footer>

<script>
// Rating input functionality
const ratingInputs = document.querySelectorAll('.rating-input input[type="radio"]');
const ratingLabels = document.querySelectorAll('.rating-input label');

// Handle hover effects
ratingLabels.forEach((label, index) => {
    label.addEventListener('mouseenter', () => {
        // Light up stars from right to left (due to flex-reverse)
        for (let i = 0; i <= index; i++) {
            ratingLabels[i].style.color = '#ffd700';
        }
    });
    
    label.addEventListener('mouseleave', () => {
        // Reset colors based on current selection
        updateStarColors();
    });
    
    // Handle clicks
    label.addEventListener('click', () => {
        updateStarColors();
    });
});

function updateStarColors() {
    // Find checked input
    const checkedInput = document.querySelector('.rating-input input:checked');
    const checkedValue = checkedInput ? parseInt(checkedInput.value) : 0;
    
    ratingLabels.forEach((label, index) => {
        // Stars are in reverse order (5,4,3,2,1)
        const starValue = 5 - index;
        if (starValue <= checkedValue) {
            label.style.color = '#ffd700';
        } else {
            label.style.color = '#ddd';
        }
    });
}
</script>

</body>
</html>

<?php
$conn->close();
?>