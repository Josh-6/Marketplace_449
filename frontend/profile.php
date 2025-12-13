<?php
// Ensure session is started so login state (e.g., $_SESSION['username']) is available
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
/*echo '<pre>';
var_dump($_SESSION); // Checking session variables
echo '</pre>';*/
// Ensure user is logged in
/*

}*/

require_once __DIR__ . '/../database/db_connect.php'; // DB connection

$conn = get_db_connection();
//echo 'console.log("Database connected!")';

$userId = $_SESSION['user_id'] ?? 0;

if ($userId) {
    $stmt = $conn->prepare("
    SELECT 
        u.User_Email,
        u.Full_Name,
        s.Seller_Phone_Number,
        s.Seller_Location
    FROM Users u
    LEFT JOIN Seller s ON s.Seller_ID = u.User_ID
    WHERE u.User_ID = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $sessionEmail = $row['User_Email'] ?? 'Unknown';
    $sessionFullName = $row['Full_Name'] ?? null;
    $sessionPhone = $row['Seller_Phone_Number'] ?? null;
    $sessionAddress = $row['Seller_Location'] ?? null;
}
    $stmt->close();
}
$sessionUsername = $_SESSION['username'] ?? 'Unknown';
$isSeller = !empty($_SESSION['seller_registered']);

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

<!-- Nav Bar -->
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
                        <a href="profile.php">History</a>
                        <a href="profile.php">Orders</a>
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
            <h2 class="username"><?php echo htmlspecialchars($sessionUsername); ?></h2>
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

<!-- Section 3 (Tab Content) -->
<section class="profile-content">

    <!-- Item History -->
    <div id="item-history" class="tab-content active">
        <div class="item-history-container">
            <?php if ($itemCount): ?>
                <?php foreach ($itemHistory as $item): ?>
                    <div class="product-card">
                        <img src="images/products/6.jpg" alt="<?= htmlspecialchars($item['Item_Name']) ?>">
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

    <!-- Order History -->
    <div id="order-history" class="tab-content">
        <div class="order-history-container">
            <?php if ($orderCount): ?>
                <?php foreach ($orderHistory as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div><strong>ORDER PLACED</strong><br><?= date("M d, Y", strtotime($order['Purchased_At'])) ?></div>
                            <div><strong>QUANTITY</strong><br><?= $order['Quantity'] ?></div>
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

    <!-- Account Settings -->
    <div id="account-settings" class="tab-content">
        <div class="account-settings-container">
            <!-- Two-column layout, left is buttons, right  content -->
            <div class="account-settings-grid">
                <aside class="account-sidebar">
                    <nav class="account-nav">
                        <button class="account active" data-content="personal-information">Personal Information</button>
                        <button class="account" data-content="payment-information">Payment Information</button>
                        <button class="account" data-content="seller-information">Seller Information</button>
                    </nav>
                </aside>

                <main class="account-main">
                    <!-- PERSONAL INFORMATION -->
                    <div id="personal-information" class="account-panel active">
                        <!-- Username block -->
                        <div class="info-block" data-block="username">
                            <div class="info-label"><strong>Username</strong></div>
                            <div class="info-value">
                                <span class="display-value username-value"><?php echo htmlspecialchars($sessionUsername); ?></span>
                                <div class="edit-controls">
                                    <button class="edit-btn" data-edit="username">Edit</button>
                                </div>
                            </div>

                            <!-- Edit form (hidden by default) -->
                            <div class="edit-form" data-form="username" style="display:none;">
                                <input type="text" name="username_input" class="input-username" value="<?php echo htmlspecialchars($sessionUsername); ?>">
                                <div class="form-actions">
                                    <button class="save-btn small" data-save="username">Save</button>
                                    <button class="cancel-btn small" data-cancel="username">Cancel</button>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Account type block -->
                        <div class="info-block" data-block="account-type">
                            <div class="info-label"><strong>Account Type</strong></div>
                            <div class="info-value">
                                <span class="display-value account-type-value">
                                    <?php echo $isSeller ? 'Buyer &amp; Seller' : 'Buyer'; ?>
                                </span>
                            </div>
                        </div>

                        <hr>

                        <!-- Contact Info -->
                        <div class="info-block" data-block="contact">
                            <div class="info-label"><strong>Contact Info</strong></div>
                            <div class="info-value contact-info-value">
                                <div class="phone-value">
                                    <?php echo $sessionPhone ? htmlspecialchars($sessionPhone) : '<span class="no-info">No Phone Number</span>'; ?>
                                </div>
                                <div class="email-value">
                                    <?php echo $sessionEmail ? htmlspecialchars($sessionEmail) : '<span class="no-info">No Email</span>'; ?>
                                </div>
                                <div class="edit-controls">
                                    <button class="edit-btn" data-edit="contact">Edit</button>
                                </div>
                            </div>
                            <div class="edit-form" data-form="contact" style="display:none;">
                                <input type="text" name="phone_input" class="input-phone" value="<?php echo htmlspecialchars($sessionPhone ?? ''); ?>" placeholder="e.g. 555-123-4567">
                                <input type="text" name="email_input" class="input-email" value="<?php echo htmlspecialchars($sessionEmail ?? ''); ?>" placeholder="e.g. JohnDoe@gmail.com">
                                <div class="form-actions">
                                    <button class="save-btn small" data-save="contact">Save</button>
                                    <button class="cancel-btn small" data-cancel="contact">Cancel</button>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Personal Info -->
                        <div class="info-block" data-block="personal">
                            <div class="info-label"><strong>Personal Info</strong></div>
                            <div class="info-value personal-info-value">
                                <div class="full-name">
                                    <?php echo $sessionFullName ? htmlspecialchars($sessionFullName) : '<span class="no-info">No Name</span>'; ?>
                                </div>
                                <div class="address" style="margin-top:8px;">
                                    <?php echo $sessionAddress ? nl2br(htmlspecialchars($sessionAddress)) : '<span class="no-info">No Address</span>'; ?>
                                </div>
                                <div class="edit-controls">
                                    <button class="edit-btn" data-edit="personal">Edit</button>
                                </div>
                            </div>

                            <div class="edit-form" data-form="personal" style="display:none;">
                                <input type="text" name="full_name_input" class="input-fullname" value="<?php echo htmlspecialchars($sessionFullName ?? ''); ?>" placeholder="Full name">
                                <textarea name="address_input" class="input-address" placeholder="Address"><?php echo htmlspecialchars($sessionAddress ?? ''); ?></textarea>
                                <div class="form-actions">
                                    <button class="save-btn small" data-save="personal">Save</button>
                                    <button class="cancel-btn small" data-cancel="personal">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PAYMENT INFORMATION -->
                    <div id="payment-information" class="account-panel">
                        <div class="panel-heading"><strong>Payment Methods</strong></div>
                        <div class="panel-body">
                            <p class="muted">Add multiple payment methods. (Add / edit / remove functionality will be added later.)</p>
                            <div class="payment-list">
                                <!-- Placeholder items -->
                                <div class="payment-item">Visa ending in 1234</div>
                                <div class="payment-item">Mastercard ending in 9876</div>
                            </div>

                            <div style="margin-top:12px;">
                                <button class="primary-btn" id="add-payment-btn">Add New Payment Method</button>
                            </div>
                        </div>
                    </div>

                    <!-- SELLER INFORMATION -->
                    <div id="seller-information" class="account-panel">
                        <div class="panel-heading"><strong>Items Listed</strong></div>
                        <div class="panel-body">
                            <?php if ($isSeller): ?>
                                <p>Current listed items. (Listing management coming soon.)</p>
                                <div class="seller-listing-placeholder">
                                    <!-- example placeholder -->
                                    <div class="listing-card">
                                        <img src="images/placeholder.png" alt="Item" style="width:80px;height:80px;object-fit:cover;border-radius:6px;">
                                        <div class="listing-info">
                                            <div class="listing-title">Sample Item</div>
                                            <div class="listing-price">$9.99</div>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <p>You are not registered as a seller.</p>
                                <button class="primary-btn" id="become-seller-btn">Become a Seller</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </main>
            </div> <!-- end account-settings-grid -->
        </div> <!-- end account-settings-container -->
    </div> <!-- end account-settings tab -->
</section>

<!-- JS file -->
<script>
    // expose some PHP data for JS to use (read-only)
    window.profileSession = {
        username: <?php echo json_encode($sessionUsername); ?>,
        email: <?php echo json_encode($sessionEmail); ?>,
        fullName: <?php echo json_encode($sessionFullName); ?>,
        phone: <?php echo json_encode($sessionPhone); ?>,
        address: <?php echo json_encode($sessionAddress); ?>,
        isSeller: <?php echo $isSeller ? 'true' : 'false'; ?>
    };
</script>
<script src="js/profile.js"></script>

</html>