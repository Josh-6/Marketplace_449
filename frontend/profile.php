<?php
// Ensure session is started so login state (e.g., $_SESSION['username']) is available
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pagination logic
/*
$itemsPerPage = 10;
$totalItems = 20;
$totalPages = $totalItems > 0 ? ceil($totalItems / $itemsPerPage) : 1;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$start = ($page - 1) * $itemsPerPage;
$paginatedProducts = array_slice($productsToShow, $start, $itemsPerPage);
*/
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace - eCommerce Home Page</title>
    <!-- Correct CSS path -->
    <link rel="stylesheet" href="css/profile.css?v=1" /><!-- If stylesheet changes notrelected increment number -->
</head>

<header>
    <div class="logo">Marketplace</div>
    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="product_page.php">Products</a></li>
            <li><a href="#">Categories</a></li>
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

<!-- Section 1: Profile Header -->
<section class="profile-header">
    <div class="profile-container">
        <!-- Profile Picture -->
        <div class="profile-image">
            <img src="images/kitten.png" alt="Profile Picture">
            <button class="edit-photo-btn">✎</button>
        </div>

        <!-- User Info -->
        <div class="profile-info">
            <h2 class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></h2>
            <p class="username-text">To change your username, visit</p>
            <a href="#" class="account-settings-link">Account settings</a>
        </div>

        <!-- Action Buttons -->
        <div class="profile-actions">
            <button class="edit-profile">Edit Profile</button>
            <div class="edit-buttons">
                <button class="cancel-btn">Cancel</button>
                <button class="save-btn">Save</button>
            </div>
        </div>
    </div>
</section>



<!-- JS file -->
<script src="js/profile.js"></script>

</html>