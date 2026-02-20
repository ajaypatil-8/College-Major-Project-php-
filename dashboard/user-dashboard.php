<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/config/db.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Locationuser/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user details
$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([$user_id]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

// Get total donations count and amount
$donationStatsStmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_donations,
        COALESCE(SUM(amount), 0) as total_amount
    FROM donations 
    WHERE user_id = ? AND status = 'success'
");
$donationStatsStmt->execute([$user_id]);
$donationStats = $donationStatsStmt->fetch(PDO::FETCH_ASSOC);

// Get campaigns supported count
$campaignsStmt = $pdo->prepare("
    SELECT COUNT(DISTINCT campaign_id) as campaigns_supported 
    FROM donations 
    WHERE user_id = ? AND status = 'success'
");
$campaignsStmt->execute([$user_id]);
$campaignsSupported = $campaignsStmt->fetch(PDO::FETCH_ASSOC)['campaigns_supported'];

// Get recent donations with campaign details
$recentDonationsStmt = $pdo->prepare("
    SELECT 
        d.amount,
        d.created_at,
        d.payment_method,
        c.title as campaign_title,
        c.id as campaign_id
    FROM donations d
    LEFT JOIN campaigns c ON d.campaign_id = c.id
    WHERE d.user_id = ? AND d.status = 'success'
    ORDER BY d.created_at DESC
    LIMIT 5
");
$recentDonationsStmt->execute([$user_id]);
$recentDonations = $recentDonationsStmt->fetchAll(PDO::FETCH_ASSOC);

// Get supported campaigns with their progress and thumbnail
$supportedCampaignsStmt = $pdo->prepare("
    SELECT 
        c.id,
        c.title,
        c.goal,
        c.category,
        SUM(d.amount) as my_contribution,
        (SELECT COALESCE(SUM(amount), 0) FROM donations WHERE campaign_id = c.id AND status = 'success') as total_raised,
        (SELECT media_url FROM campaign_media WHERE campaign_id = c.id AND media_type = 'thumbnail' LIMIT 1) as thumbnail
    FROM campaigns c
    INNER JOIN donations d ON c.id = d.campaign_id
    WHERE d.user_id = ? AND d.status = 'success'
    GROUP BY c.id
    ORDER BY my_contribution DESC
    LIMIT 6
");
$supportedCampaignsStmt->execute([$user_id]);
$supportedCampaigns = $supportedCampaignsStmt->fetchAll(PDO::FETCH_ASSOC);

require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/header.php";
?>


<html lang="en">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - CrowdSpark</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        /* ===== THEME VARIABLES ===== */
        :root {
            --bg-primary: #fafafa;
            --bg-secondary: #f1f5f9;
            --bg-card: rgba(255, 255, 255, 0.95);
            --bg-card-hover: rgba(255, 255, 255, 1);
            
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-tertiary: #64748b;
            
            --border-color: rgba(15, 23, 42, 0.08);
            --border-hover: rgba(107, 142, 35, 0.3);
            
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.04);
            --shadow-md: 0 8px 32px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 20px 60px rgba(0, 0, 0, 0.15);
            
            --orb-opacity: 0.25;
        }

        [data-theme="dark"] {
            --bg-primary: #0f0f0f;
            --bg-secondary: #1a1a1a;
            --bg-card: rgba(20, 20, 30, 0.95);
            --bg-card-hover: rgba(30, 30, 40, 0.95);
            
            --text-primary: #ffffff;
            --text-secondary: #cbd5e1;
            --text-tertiary: #94a3b8;
            
            --border-color: rgba(255, 255, 255, 0.1);
            --border-hover: rgba(107, 142, 35, 0.4);
            
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.3);
            --shadow-md: 0 8px 32px rgba(0, 0, 0, 0.4);
            --shadow-lg: 0 20px 60px rgba(0, 0, 0, 0.6);
            
            --orb-opacity: 0.25;
        }

        /* Olive Green Accent Colors */
        :root {
            --accent-primary: #6b8e23;
            --accent-secondary: #556b2f;
            --accent-light: #8fbc8f;
            --accent-gradient: linear-gradient(135deg, #6b8e23, #556b2f);
            --accent-glow: rgba(107, 142, 35, 0.4);
        }

        /* Orb colors */
        [data-theme="dark"] {
            --orb-1: linear-gradient(45deg, #6b8e23, #8fbc8f);
            --orb-2: linear-gradient(45deg, #556b2f, #6b8e23);
            --orb-3: linear-gradient(45deg, #9acd32, #6b8e23);
        }

        [data-theme="light"] {
            --orb-1: linear-gradient(45deg, #9acd32, #8fbc8f);
            --orb-2: linear-gradient(45deg, #6b8e23, #9acd32);
            --orb-3: linear-gradient(45deg, #8fbc8f, #adff2f);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            overflow-x: hidden;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Background Animation */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
            opacity: var(--orb-opacity);
            transition: opacity 0.3s ease;
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            animation: float 20s infinite ease-in-out;
        }

        .orb-1 {
            width: 500px;
            height: 500px;
            background: var(--orb-1);
            top: -10%;
            left: -10%;
        }

        .orb-2 {
            width: 400px;
            height: 400px;
            background: var(--orb-2);
            bottom: -10%;
            right: -10%;
            animation-delay: 5s;
        }

        .orb-3 {
            width: 350px;
            height: 350px;
            background: var(--orb-3);
            top: 50%;
            left: 50%;
            animation-delay: 10s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(50px, 50px) scale(1.1); }
            50% { transform: translate(-30px, 80px) scale(0.9); }
            75% { transform: translate(40px, -40px) scale(1.05); }
        }

        /* Hero Section */
        .explore-hero {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 40px 20px 60px;
            animation: fadeInUp 0.8s ease;
        }

        .explore-hero h1 {
            font-size: clamp(2.5rem, 6vw, 4rem);
            font-weight: 900;
            margin-bottom: 16px;
            background: linear-gradient(135deg, var(--text-primary), var(--accent-primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .explore-hero p {
            font-size: 1.25rem;
            color: var(--text-secondary);
            max-width: 600px;
            margin: 0 auto;
        }

        /* Dashboard Container */
        .user-dashboard {
            position: relative;
            z-index: 1;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px 80px;
        }

        /* Profile Card */
        .profile-card {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 40px;
            margin-bottom: 40px;
            box-shadow: var(--shadow-md);
            display: flex;
            align-items: center;
            gap: 30px;
            animation: slideIn 0.6s ease;
            transition: all 0.3s ease;
        }

        .profile-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-4px);
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: var(--accent-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 48px;
            font-weight: 900;
            overflow: hidden;
            box-shadow: 0 8px 24px var(--accent-glow);
            flex-shrink: 0;
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-info h2 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 8px;
            color: var(--text-primary);
        }

        .profile-info p {
            color: var(--text-secondary);
            font-size: 1rem;
            margin-bottom: 4px;
        }

        .profile-badge {
            display: inline-block;
            background: var(--accent-gradient);
            color: #fff;
            padding: 6px 16px;
            border-radius: 999px;
            font-size: 14px;
            font-weight: 700;
            margin-top: 12px;
        }

        /* Stats Grid */
        .user-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 50px;
            animation: fadeIn 0.8s ease 0.2s both;
        }

        .u-card {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 32px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
            position: relative;
            overflow: hidden;
        }

        .u-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--accent-gradient);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .u-card:hover::before {
            transform: scaleX(1);
        }

        .u-card:hover {
            transform: translateY(-8px);
            border-color: var(--border-hover);
            box-shadow: var(--shadow-md);
        }

        .u-icon {
            font-size: 3rem;
            margin-bottom: 16px;
            filter: grayscale(0.3);
        }

        .u-card span {
            display: block;
            color: var(--text-secondary);
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .u-card strong {
            display: block;
            font-size: 2.5rem;
            font-weight: 900;
            color: var(--accent-primary);
        }

        /* Section */
        .user-section {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 40px;
            margin-bottom: 40px;
            box-shadow: var(--shadow-md);
            animation: fadeInUp 0.8s ease 0.4s both;
        }

        .user-section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--border-color);
        }

        .user-section-header h2 {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--text-primary);
        }

        .btn-secondary {
            padding: 10px 24px;
            border-radius: 999px;
            background: var(--accent-gradient);
            color: #fff;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px var(--accent-glow);
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px var(--accent-glow);
        }

        /* Recent Donations List */
        .recent-donations {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .recent-donations li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: var(--bg-secondary);
            border-radius: 14px;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .recent-donations li:hover {
            background: var(--bg-card-hover);
            border-color: var(--accent-primary);
            transform: translateX(8px);
        }

        .recent-donations strong {
            color: var(--accent-primary);
            font-size: 1.1rem;
            font-weight: 800;
        }

        .recent-donations div {
            color: var(--text-primary);
            font-weight: 600;
        }

        .donation-date {
            color: var(--text-tertiary);
            font-size: 14px;
            font-weight: 600;
        }

        /* Campaigns Grid */
        .campaigns-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
        }

        .campaign-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
        }

        .campaign-card:hover {
            transform: translateY(-8px);
            border-color: var(--accent-primary);
            box-shadow: var(--shadow-md);
        }

        .campaign-image {
            width: 100%;
            height: 180px;
            background: var(--bg-secondary);
            overflow: hidden;
            position: relative;
        }

        .campaign-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .campaign-card:hover .campaign-image img {
            transform: scale(1.1);
        }

        .campaign-category {
            position: absolute;
            top: 12px;
            left: 12px;
            background: var(--accent-gradient);
            color: #fff;
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            z-index: 2;
        }

        .campaign-body {
            padding: 24px;
        }

        .campaign-body h3 {
            font-size: 1.25rem;
            font-weight: 800;
            margin-bottom: 12px;
            color: var(--text-primary);
        }

        .campaign-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .campaign-stats span {
            color: var(--text-secondary);
            font-size: 14px;
        }

        .campaign-stats strong {
            color: var(--accent-primary);
            font-weight: 800;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: var(--border-color);
            border-radius: 999px;
            overflow: hidden;
            margin-bottom: 12px;
        }

        .progress-fill {
            height: 100%;
            background: var(--accent-gradient);
            border-radius: 999px;
            transition: width 1s ease;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 12px;
            color: var(--text-primary);
        }

        /* Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .profile-card {
                flex-direction: column;
                text-align: center;
            }

            .user-stats {
                grid-template-columns: 1fr;
            }

            .user-section-header {
                flex-direction: column;
                gap: 16px;
                align-items: flex-start;
            }

            .recent-donations li {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
        }
    </style>



    <!-- Background Animation -->
    <div class="bg-animation">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <!-- Hero -->
    <section class="explore-hero">
        <h1>User Dashboard</h1>
        <p>Track your donations, impact, and activity.</p>
    </section>

    <!-- Dashboard -->
    <section class="user-dashboard">

        <!-- Profile Card -->
        <div class="profile-card">
            <div class="profile-avatar">
                <?php if (!empty($user['profile_image'])): ?>
                    <img src="<?= htmlspecialchars($user['profile_image']) ?>" alt="Profile">
                <?php else: ?>
                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                <?php endif; ?>
            </div>
            <div class="profile-info">
                <h2><?= htmlspecialchars($user['name']) ?></h2>
                <p><i class="fa-solid fa-envelope"></i> <?= htmlspecialchars($user['email']) ?></p>
                <p><i class="fa-solid fa-calendar"></i> Member since <?= date('M Y', strtotime($user['created_at'])) ?></p>
                <span class="profile-badge"><?= ucfirst($user['role']) ?></span>
            </div>
        </div>

        <!-- Stats -->
        <div class="user-stats">
            <div class="u-card total">
                <div class="u-icon">💰</div>
                <span>Total Donations</span>
                <strong><?= $donationStats['total_donations'] ?></strong>
            </div>

            <div class="u-card contributed">
                <div class="u-icon">📦</div>
                <span>Total Amount Contributed</span>
                <strong>₹<?= number_format($donationStats['total_amount']) ?></strong>
            </div>

            <div class="u-card supported">
                <div class="u-icon">❤️</div>
                <span>Campaigns Supported</span>
                <strong><?= $campaignsSupported ?></strong>
            </div>
        </div>

        <!-- Recent Donations -->
        <div class="user-section">
            <div class="user-section-header">
                <h2>Recent Donations</h2>
                <a href ="/dashboard/my-donations.php" class="btn-secondary">
                    View All <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            <?php if (count($recentDonations) > 0): ?>
                <ul class="recent-donations">
                    <?php foreach ($recentDonations as $donation): ?>
                        <li>
                            <div>
                                <strong>₹<?= number_format($donation['amount']) ?></strong> — <?= htmlspecialchars($donation['campaign_title']) ?>
                            </div>
                            <span class="donation-date"><?= date('d M Y', strtotime($donation['created_at'])) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fa-solid fa-heart"></i>
                    <h3>No donations yet</h3>
                    <p>Start supporting campaigns to make an impact!</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Supported Campaigns -->
        <?php if (count($supportedCampaigns) > 0): ?>
        <div class="user-section">
            <div class="user-section-header">
                <h2>Campaigns You Support</h2>
                <a href ="/public/explore-campaigns.php" class="btn-secondary">
                    Discover More <i class="fa-solid fa-compass"></i>
                </a>
            </div>

            <div class="campaigns-grid">
                <?php foreach ($supportedCampaigns as $campaign): 
                    $progress = ($campaign['total_raised'] / $campaign['goal']) * 100;
                ?>
                    <div class="campaign-card">
                        <div class="campaign-image">
                            <?php if (!empty($campaign['category'])): ?>
                                <span class="campaign-category"><?= htmlspecialchars($campaign['category']) ?></span>
                            <?php endif; ?>
                            <img src="<?= $campaign['thumbnail'] ?: 'https://via.placeholder.com/400x250' ?>" 
                                 alt="<?= htmlspecialchars($campaign['title']) ?>">
                        </div>
                        <div class="campaign-body">
                            <h3><?= htmlspecialchars($campaign['title']) ?></h3>
                            <div class="campaign-stats">
                                <span>Your contribution</span>
                                <strong>₹<?= number_format($campaign['my_contribution']) ?></strong>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= min($progress, 100) ?>%"></div>
                            </div>
                            <div class="campaign-stats">
                                <span>₹<?= number_format($campaign['total_raised']) ?> raised</span>
                                <span><?= number_format($progress, 1) ?>%</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </section>

    <?php require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/footer.php"; ?>


