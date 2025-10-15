<?php
// Define website name variable
$websiteName = "Coupon.is-great.org";

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
include 'includes/db.php';

// Get user information
$user_id = $_SESSION['user_id'];
$stmt = $connection->prepare("SELECT username, email, mobile, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Profile - <?php echo $websiteName; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" />
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            padding-top: 80px; /* Account for fixed navbar */
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            color: white;
            min-height: 100vh;
            overflow-x: hidden;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .particles {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            pointer-events: none;
            z-index: 1;
        }
        .particle {
            position: absolute;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            animation: float 6s infinite linear;
        }
        @keyframes float {
            0% { transform: translateY(0) translateX(0) rotate(0deg); opacity: 0; }
            10%,90% { opacity: 0.5; }
            100% { transform: translateY(-100vh) translateX(100px) rotate(360deg); opacity: 0; }
        }

        .profile-container {
            max-width: 800px;
            margin: 40px auto;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
            padding: 30px 30px 30px 140px; /* padding-left for avatar */
            position: relative;
            z-index: 2;
        }
        .profile-avatar-top-left {
            position: absolute;
            top: 30px;
            left: 30px;
            width: 100px; height: 100px;
            background: linear-gradient(45deg, #28a745, #218838);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3em;
            box-shadow: 0 6px 20px rgba(40,167,69,0.3);
            z-index: 3;
        }
        .coupon-actions-row {
            margin-bottom: 25px;
            text-align: right;
            position: relative;
            z-index: 3;
        }
        .coupon-actions-row .btn-primary i {
            font-size: 1.5em;
            vertical-align: middle;
        }
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .profile-username {
            font-size: 1.8em;
            color: #28a745;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .profile-email {
            color: #aaa;
            margin-bottom: 20px;
        }
        .profile-stats {
            display: flex;
            justify-content: space-around;
            background: rgba(255,255,255,0.1);
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .stat-item {
            text-align: center;
        }
        .stat-number {
            font-size: 1.5em;
            font-weight: bold;
            color: #28a745;
        }
        .stat-label {
            font-size: 0.9em;
            color: #aaa;
        }
        .profile-section {
            margin-bottom: 30px;
            padding: 20px;
            background: rgba(255,255,255,0.05);
            border-radius: 10px;
        }
        .section-title {
            color: #28a745;
            margin-bottom: 20px;
            font-size: 1.3em;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            padding-bottom: 10px;
        }
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .info-label {
            color: #aaa;
            font-weight: 500;
        }
        .info-value {
            color: white;
            font-weight: 500;
        }
        .btn-primary {
            background: linear-gradient(45deg, #28a745, #218838);
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(45deg, #218838, #1e7e34);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40,167,69,0.4);
        }
        .btn-outline-primary {
            border: 1px solid #28a745;
            color: #28a745;
            background: transparent;
        }
        .btn-outline-primary:hover {
            background: rgba(40,167,69,0.1);
        }
        .profile-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .logout-btn {
            background: linear-gradient(45deg, #dc3545, #c82333);
            border: none;
            padding: 8px 15px;
            border-radius: 8px;
            font-weight: bold;
            transition: all 0.3s ease;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        .logout-btn:hover {
            background: linear-gradient(45deg, #c82333, #bd2130);
            transform: translateY(-2px);
            text-decoration: none;
        }
        .logout-btn i {
            margin-right: 8px;
        }
        .coupon-actions-row {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-end;
    gap: 10px;
    margin-bottom: 25px;
    position: relative;
    z-index: 3;
}

.coupon-actions-row .btn-primary {
    flex: 0 0 auto;
    padding: 8px 16px;
}

@media (max-width: 576px) {
    .coupon-actions-row {
        justify-content: center;
    }
    .coupon-actions-row .btn-primary {
        padding: 8px 12px;
        font-size: 0.9rem;
    }
    .coupon-actions-row .btn-primary i {
        font-size: 1.3em;
    }
}

    </style>
</head>
<body>
    <!-- Animated background particles -->
    <div class="particles" id="particles"></div>

    <!-- Include the navbar -->
    <?php include 'includes/navbar.php'; ?>

    <div class="profile-container">
        <!-- Profile Avatar top-left -->
        <div class="profile-avatar-top-left">
            <i class="bi bi-person"></i>
        </div>

        <!-- Coupon action icons -->
       <div class="coupon-actions-row">
    <a href="add_coupon.php" class="btn btn-primary me-2" title="Add Coupon">
        <i class="bi bi-plus-circle"></i>
    </a>
    <a href="all_coupons.php" class="btn btn-primary me-2" title="See All Coupons">
        <i class="bi bi-ticket-perforated"></i>
    </a>
    <a href="used_coupons.php" class="btn btn-primary" title="See Used Coupons">
        <i class="bi bi-archive"></i>
    </a>
</div>


        <div class="profile-header">
            <h1 class="profile-username"><?php echo htmlspecialchars($user['username']); ?></h1>
            <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
        </div>

        <div class="profile-stats">
            <div class="stat-item">
                <div class="stat-number">150</div>
                <div class="stat-label">Coupons</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">42</div>
                <div class="stat-label">Saved</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">28</div>
                <div class="stat-label">Shared</div>
            </div>
        </div>

        <div class="profile-section">
            <h3 class="section-title">Personal Information</h3>
            <div class="info-item">
                <span class="info-label">Username:</span>
                <span class="info-value"><?php echo htmlspecialchars($user['username']); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Email:</span>
                <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Mobile:</span>
                <span class="info-value"><?php echo htmlspecialchars($user['mobile']); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Member Since:</span>
                <span class="info-value"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
            </div>
        </div>

        <div class="profile-section text-center">
            <h3 class="section-title">Account Actions</h3>
            <div class="profile-actions justify-content-center">
                <button class="btn btn-outline-primary" onclick="window.location.href='index.php'">
                    <i class="bi bi-arrow-left me-2"></i>Back to Coupons
                </button>
                <a href="logout.php" class="logout-btn">
                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                </a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Create floating particles
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 50;

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');

                // Random size
                const size = Math.random() * 8 + 2;
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;

                // Random position
                particle.style.left = `${Math.random() * 100}%`;
                particle.style.top = `${Math.random() * 100}%`;

                // Random animation duration and delay
                const duration = Math.random() * 12 + 6;
                const delay = Math.random() * 5;
                particle.style.animationDuration = `${duration}s`;
                particle.style.animationDelay = `${delay}s`;

                particlesContainer.appendChild(particle);
            }
        }
        window.addEventListener('load', createParticles);
    </script>
</body>
</html>
