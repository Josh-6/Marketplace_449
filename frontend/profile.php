<?php
// Ensure session is started so login state (e.g., $_SESSION['username']) is available
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure user is logged in
/*

}*/

include __DIR__ . '/../database/db_connect.php'; // your DB connection

$conn = get_db_connection();
//echo 'console.log("Database connected!")';

$userId = $_SESSION['user_id'] ?? 0;


// Fetch Item History (20 most recent)
$stmt1 = $conn->prepare("
    SELECT I.Item_ID, I.Item_Name, I.Item_Price, UH.Viewed_At, I.Item_Description
    FROM Marketplace.User_History UH
    JOIN Marketplace.Item I ON UH.Item_ID = I.Item_ID
    WHERE UH.User_ID = ? AND UH.History_Type = 'view'
    ORDER BY UH.Viewed_At DESC
    LIMIT 20
");
$stmt1->bind_param("i", $userId);
$stmt1->execute();
$itemHistory = $stmt1->get_result()->fetch_all(MYSQLI_ASSOC);
$itemCount = count($itemHistory) > 0; // true if greater than 0, else false

// Fetch Order History (20 most recent)
$stmt2 = $conn->prepare("
    SELECT I.Item_ID, I.Item_Name, I.Item_Price, UH.Quantity, UH.Purchased_At
    FROM Marketplace.User_History UH
    JOIN Marketplace.Item I ON UH.Item_ID = I.Item_ID
    WHERE UH.User_ID = ? AND UH.History_Type = 'purchase'
    ORDER BY UH.Purchased_At DESC
    LIMIT 20
");
$stmt2->bind_param("i", $userId);
$stmt2->execute();
$orderHistory = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
$orderCount = count($orderHistory) > 0; // true if greater than 0, else false

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
    <link rel="stylesheet" href="css/profile.css?v=2" /><!-- If stylesheet changes notrelected increment number -->
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
            <button class="edit-profile">Edit Profile</button> <!--✎(black pen)-->
            <div class="edit-buttons">
                <button class="cancel-btn">Cancel</button>
                <button class="save-btn">Save</button>
            </div>
        </div>
    </div>
</section>

<!-- Section 2: Profile Tabs -->
<section class="profile-tabs">
    <button class="tab-button active" data-tab="item-history">Item History</button>
    <button class="tab-button" data-tab="order-history">Order History</button>
    <button class="tab-button" data-tab="account-settings">Account Settings</button>
</section>

<!--Section 3 (Tab Content) -->
<section class="profile-content">
    <div id="item-history" class="tab-content active">
        <div class="item-history-container">
            <?php if ($itemCount): ?>
                <?php foreach ($itemHistory as $item): ?>
                    <div class="product-card">
                        <img src="images/placeholder.png" alt="<?= htmlspecialchars($item['Item_Name']) ?>">
                        <h3><?= htmlspecialchars($item['Item_Name']) ?></h3>
                        <p><?= htmlspecialchars($item['Item_Description']) ?></p>
                        <div class="price">$<?= htmlspecialchars($item['Item_Price']) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-message">No items viewed</div>
            <?php endif; ?>
        </div>
    </div>

    <div id="order-history" class="tab-content">
        <div class="order-history-container">
            <?php if ($orderCount): ?>
                <?php foreach ($orderHistory as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div><strong>ORDER PLACED</strong><br><?= date("M d, Y", strtotime($order['Purchased_At'])) ?></div>
                            <div><strong>TOTAL</strong><br>$<?= $order['Item_Price'] * $order['Quantity'] ?></div>
                            <div><strong>ORDERED BY</strong><br><?= htmlspecialchars($_SESSION['username']) ?></div>
                        </div>
                        <div class="order-body">
                            <img src="images/placeholder.png" alt="<?= htmlspecialchars($order['Item_Name']) ?>">
                            <span><?= htmlspecialchars($order['Item_Name']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-message">No previous orders</div>
            <?php endif; ?>
        </div>
    </div>

    <div id="account-settings" class="tab-content">
        <div class="account-settings-container">
            <div class="empty-message">Account Settings</div>
        </div>
    </div>

</section>



<!-- JS file -->
<script src="js/profile.js"></script>

</html>