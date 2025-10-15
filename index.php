<?php
// Define variables for dynamic content
$websiteName = "Coupon.is-great.org";

// Start session to check login status
session_start();

// Include database connection
include 'includes/db.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']) || isset($_SESSION['username']);

// Update expired coupons to "expired" status
$update_sql = "UPDATE coupons SET c_status = 'expired' WHERE expiration_date < CURDATE() AND c_status = 'active'";
mysqli_query($connection, $update_sql);

// Fetch coupons from the database based on status
// Active coupons: show to all users
// Used coupons: show to all users
// Expired coupons: don't show to anyone
$sql = "SELECT id, description, link, code, terms, expiration_date, created_at, is_top, c_status FROM coupons WHERE c_status IN ('active', 'used') ORDER BY is_top DESC, created_at DESC";
$result = mysqli_query($connection, $sql);
$coupons = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        if ($isLoggedIn && $row['c_status'] === 'active') {
            $row['code'] = $row['code'];
        } else {
            $row['code'] = null;
        }
        $coupons[] = $row;
    }
}

if ($isLoggedIn) {
    $sql = "SELECT id, description, link, code, terms, expiration_date, created_at, is_top, c_status FROM coupons WHERE c_status IN ('active', 'used') ORDER BY is_top DESC, created_at DESC";
    $result = mysqli_query($connection, $sql);
    $coupons = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            if ($row['c_status'] === 'active') {
                $row['code'] = $row['code'];
            } else {
                $row['code'] = null;
            }
            $coupons[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php echo $websiteName; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" />
    <!-- Custom CSS -->
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <!-- Animated background particles -->
    <div class="particles" id="particles"></div>
    
    <!-- Include the navbar -->
    <?php include 'includes/navbar.php'; ?>
    
    <!-- Main Content -->
    <div class="coupons-container container">
        <div class="d-flex justify-content-between align-items-center mb-4" style="animation: fadeIn 1s ease-in;">
            <h1 class="mb-0">Available Coupons</h1>
            <a href="search_coupons.php" class="btn btn-success d-flex align-items-center" style="font-weight:bold;">
                <i class="bi bi-search me-2"></i> See All Coupons
            </a>
        </div>
        <?php if (!$isLoggedIn): ?>
            <div class="alert alert-info text-center mb-4" style="animation: fadeIn 1s ease-in;">
                <i class="bi bi-info-circle me-2"></i>
                Please <a href="login.php" class="alert-link">log in</a> to see coupon codes
            </div>
        <?php endif; ?>
        <?php if (!empty($coupons)): ?>
            <div class="row">
                <?php $count = 0; foreach ($coupons as $coupon): if (++$count > 10) break; ?>
                <div class="col-6 col-md-6 col-lg-4 mb-4">
                    <div class="card coupon-card h-100 <?php echo $coupon['is_top'] ? 'top-coupon' : ''; ?> <?php echo $coupon['c_status']; ?>">
                        <div class="card-body">
                            <?php if ($coupon['is_top']): ?>
                                <div class="top-badge">
                                    <i class="bi bi-star-fill me-1"></i> TOP
                                </div>
                            <?php endif; ?>
                            <?php if ($coupon['c_status'] === 'used'): ?>
                                <div class="status-badge">
                                    <i class="bi bi-check-circle-fill me-1"></i> Used
                                </div>
                            <?php endif; ?>
                            <?php if ($isLoggedIn): ?>
                                <div class="login-indicator">
                                    <i class="bi bi-check-circle me-1"></i> Logged In
                                </div>
                            <?php endif; ?>
                            <div class="coupon-header">
                                <h5 class="coupon-title">Special Offer</h5>
                                <span class="coupon-category">Deal</span>
                            </div>
                            <p class="coupon-description">
                                <?php echo htmlspecialchars($coupon['description'] ?? 'No description available'); ?>
                            </p>
                            <?php if (!empty($coupon['link'])): ?>
                                <a href="<?php echo htmlspecialchars($coupon['link']); ?>" target="_blank" class="coupon-link">
                                    <i class="bi bi-link-45deg me-1"></i> Visit Store
                                </a>
                            <?php endif; ?>
                            <div class="coupon-code-container">
                                <?php if ($isLoggedIn && !empty($coupon['code']) && $coupon['c_status'] === 'active'): ?>
                                    <div class="coupon-code" onclick="copyToClipboard('<?php echo htmlspecialchars($coupon['code']); ?>')">
                                        <?php echo htmlspecialchars($coupon['code']); ?>
                                    </div>
                                <?php elseif ($coupon['c_status'] === 'used'): ?>
                                    <div class="coupon-code" style="background: linear-gradient(45deg, #28a745, #218838);">
                                        <i class="bi bi-check-circle me-2"></i>USED
                                    </div>
                                <?php else: ?>
                                    <div class="coupon-code login-required" onclick="window.location.href='login.php'">
                                        <i class="bi bi-lock me-1"></i> Login to view code
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($coupon['terms'])): ?>
                                <div class="terms-section">
                                    <strong>Terms:</strong> <?php echo htmlspecialchars($coupon['terms']); ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($isLoggedIn && $coupon['c_status'] === 'active'): ?>
                                <div class="i-used-it-btn" onclick="markAsUsed(<?php echo $coupon['id']; ?>, this)">
                                    <i class="bi bi-check-circle me-2"></i>I Used It
                                </div>
                            <?php elseif ($isLoggedIn && $coupon['c_status'] === 'used'): ?>
                                <div class="i-used-it-btn used">
                                    <i class="bi bi-check-circle-fill me-2"></i>Used!
                                </div>
                            <?php endif; ?>
                            <div class="coupon-meta">
                                <?php if (!empty($coupon['expiration_date'])): ?>
                                    <span class="coupon-expiration">
                                        <i class="bi bi-clock me-1"></i>
                                        <?php echo date('M d', strtotime($coupon['expiration_date'])); ?>
                                    </span>
                                <?php endif; ?>
                                <span class="coupon-date">
                                    <?php echo date('M d', strtotime($coupon['created_at'] ?? date('Y-m-d'))); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-coupons">
                <i class="bi bi-tag"></i>
                <h3>No coupons available</h3>
                <p>Check back later for new deals!</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JavaScript -->
    <script src="script.js"></script>
</body>
</html>
