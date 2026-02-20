<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT']."/config/db.php";

/* ADMIN CHECK */
if(!isset($_SESSION['user_id']) || $_SESSION['role']!="admin"){
    echo "<script>window.location='../index.php';</script>";
    exit;
}

/* =======================
   ANALYTICS
======================= */

/* TOTAL USERS */
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$newUsersThisMonth = $pdo->query("SELECT COUNT(*) FROM users WHERE MONTH(created_at)=MONTH(NOW())")->fetchColumn();

/* TOTAL CAMPAIGNS */
$totalCampaigns = $pdo->query("SELECT COUNT(*) FROM campaigns")->fetchColumn();
$totalApproved = $pdo->query("SELECT COUNT(*) FROM campaigns WHERE status='approved'")->fetchColumn();
$totalPending = $pdo->query("SELECT COUNT(*) FROM campaigns WHERE status='pending'")->fetchColumn();
$totalRejected = $pdo->query("SELECT COUNT(*) FROM campaigns WHERE status='rejected'")->fetchColumn();

/* TOTAL MONEY RAISED */
$totalMoney = $pdo->query("SELECT IFNULL(SUM(amount),0) FROM donations WHERE status='success'")->fetchColumn();
$totalDonations = $pdo->query("SELECT COUNT(*) FROM donations WHERE status='success'")->fetchColumn();
$avgDonation = $totalDonations > 0 ? $totalMoney / $totalDonations : 0;

/* TOP CAMPAIGNS BY FUNDING */
$topCampaigns = $pdo->query("
    SELECT c.id, c.title, c.goal, IFNULL(SUM(d.amount),0) as raised
    FROM campaigns c
    LEFT JOIN donations d ON c.id=d.campaign_id AND d.status='success'
    WHERE c.status='approved'
    GROUP BY c.id
    ORDER BY raised DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

/* RECENT DONATIONS */
$recentDonations = $pdo->query("
    SELECT d.*, u.name, c.title 
    FROM donations d
    LEFT JOIN users u ON d.user_id=u.id
    LEFT JOIN campaigns c ON d.campaign_id=c.id
    WHERE d.status='success'
    ORDER BY d.id DESC 
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

/* RECENT CAMPAIGNS */
$recentCampaigns = $pdo->query("
    SELECT c.*, u.name as creator_name
    FROM campaigns c
    LEFT JOIN users u ON c.user_id=u.id
    ORDER BY c.id DESC 
    LIMIT 8
")->fetchAll(PDO::FETCH_ASSOC);

/* PENDING CAMPAIGNS FOR APPROVAL */
$pendingCampaigns = $pdo->query("
    SELECT 
        c.*, 
        u.name, 
        u.email,
        (
            SELECT media_url 
            FROM campaign_media 
            WHERE campaign_id = c.id 
            AND media_type='thumbnail'
            LIMIT 1
        ) as thumbnail
    FROM campaigns c
    LEFT JOIN users u ON c.user_id=u.id
    WHERE c.status='pending'
    ORDER BY c.id DESC
")->fetchAll(PDO::FETCH_ASSOC);


?>

<?php require_once $_SERVER['DOCUMENT_ROOT']."/includes/header.php"; ?>

<style>
/* ===== THEME VARIABLES ===== */
:root {
    /* Light Theme */
    --bg-primary: #ffffff;
    --bg-secondary: #f8fafc;
    --bg-card: rgba(255, 255, 255, 0.9);
    --bg-card-hover: rgba(255, 255, 255, 0.95);
    
    --text-primary: #0f172a;
    --text-secondary: #475569;
    --text-tertiary: #64748b;
    
    --border-color: rgba(15, 23, 42, 0.1);
    --border-hover: rgba(239, 68, 68, 0.3);
    
    --orb-opacity: 0.25;
    
    --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
    --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.12);
}

[data-theme="dark"] {
    /* Dark Theme */
    --bg-primary: #0f0f0f;
    --bg-secondary: #1a1a1a;
    --bg-card: rgba(20, 20, 30, 0.85);
    --bg-card-hover: rgba(30, 30, 40, 0.9);
    
    --text-primary: #ffffff;
    --text-secondary: #cbd5e1;
    --text-tertiary: #94a3b8;
    
    --border-color: rgba(255, 255, 255, 0.15);
    --border-hover: rgba(239, 68, 68, 0.4);
    
    --orb-opacity: 0.25;
    
    --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.3);
    --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.4);
    --shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.5);
}

/* Red/Pink orbs for Admin - visible on both themes */
[data-theme="dark"] {
    --orb-1: linear-gradient(45deg, #ef4444, #f87171);
    --orb-2: linear-gradient(45deg, #ec4899, #f472b6);
    --orb-3: linear-gradient(45deg, #dc2626, #ef4444);
}

[data-theme="light"] {
    --orb-1: linear-gradient(45deg, #ef4444, #f87171);
    --orb-2: linear-gradient(45deg, #ec4899, #f472b6);
    --orb-3: linear-gradient(45deg, #dc2626, #ef4444);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg-primary);
    color: var(--text-primary);
    overflow-x: hidden;
    position: relative;
    transition: background-color 0.3s ease, color 0.3s ease;
}

/* Animated Background - Red/Pink theme for Admin */
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
    animation-delay: 0s;
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

/* Container */
.admin-container {
    position: relative;
    z-index: 1;
    max-width: 1400px;
    margin: 0 auto;
    padding: 120px 40px 80px;
}

/* Page Header */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 60px;
    flex-wrap: wrap;
    gap: 20px;
    animation: fadeInUp 0.8s ease;
}

.page-title {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2.5rem, 6vw, 4rem);
    font-weight: 900;
    background: linear-gradient(135deg, var(--text-primary), #ef4444);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin: 0 0 10px;
}

.page-subtitle {
    color: var(--text-secondary);
    font-size: 1.1rem;
}

.header-actions {
    display: flex;
    gap: 12px;
}

.btn-action {
    padding: 14px 28px;
    border-radius: 999px;
    text-decoration: none;
    font-weight: 700;
    font-size: 14px;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #fff;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4);
}

.btn-secondary {
    background: var(--bg-card);
    color: var(--text-primary);
    border: 2px solid rgba(239, 68, 68, 0.3);
}

.btn-secondary:hover {
    border-color: #ef4444;
    transform: translateY(-2px);
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 24px;
    margin-bottom: 60px;
}

.stat-card {
    background: var(--bg-card);
    backdrop-filter: blur(20px);
    border: 1px solid var(--border-color);
    border-radius: 24px;
    padding: 32px;
    transition: all 0.4s ease;
    position: relative;
    overflow: hidden;
    animation: fadeInUp 0.6s ease both;
}

.stat-card:nth-child(1) { animation-delay: 0.1s; }
.stat-card:nth-child(2) { animation-delay: 0.2s; }
.stat-card:nth-child(3) { animation-delay: 0.3s; }
.stat-card:nth-child(4) { animation-delay: 0.4s; }
.stat-card:nth-child(5) { animation-delay: 0.5s; }
.stat-card:nth-child(6) { animation-delay: 0.6s; }

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #ef4444, #dc2626);
    transform: scaleX(0);
    transition: transform 0.4s ease;
}

.stat-card:hover::before {
    transform: scaleX(1);
}

.stat-card:hover {
    background: var(--bg-card-hover);
    border-color: var(--border-hover);
    transform: translateY(-8px);
}

.stat-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 16px;
}

.stat-icon {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    transition: transform 0.3s ease;
}

.stat-card:hover .stat-icon {
    transform: scale(1.1) rotate(5deg);
}

.icon-users { background: linear-gradient(135deg, #3b82f6, #2563eb); }
.icon-campaigns { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
.icon-approved { background: linear-gradient(135deg, #10b981, #059669); }
.icon-pending { background: linear-gradient(135deg, #f59e0b, #d97706); }
.icon-money { background: linear-gradient(135deg, #ef4444, #dc2626); }
.icon-donations { background: linear-gradient(135deg, #14b8a6, #0d9488); }

.stat-content h2 {
    font-size: 36px;
    font-weight: 900;
    margin: 0;
    color: var(--text-primary);
    line-height: 1;
}

.stat-content p {
    color: var(--text-secondary);
    margin: 8px 0 0;
    font-weight: 600;
    font-size: 14px;
}

.stat-trend {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    font-weight: 700;
    margin-top: 12px;
    padding: 4px 10px;
    border-radius: 999px;
}

.trend-up {
    background: rgba(16, 185, 129, 0.2);
    color: #10b981;
}

/* Cards Grid */
.cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 30px;
    margin-bottom: 60px;
}

.card {
    background: var(--bg-card);
    backdrop-filter: blur(20px);
    border: 1px solid var(--border-color);
    border-radius: 24px;
    padding: 32px;
    animation: fadeInUp 0.8s ease;
}

.card h3 {
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
    margin-bottom: 24px;
    background: linear-gradient(135deg, var(--text-primary), #ef4444);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* Top Campaigns */
.top-campaigns {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.campaign-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    background: var(--bg-secondary);
    border-radius: 12px;
    transition: all 0.3s ease;
}

.campaign-item:hover {
    background: rgba(239, 68, 68, 0.1);
    transform: translateX(8px);
}

.campaign-rank {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    font-size: 14px;
}

.campaign-info {
    flex: 1;
}

.campaign-info h4 {
    font-size: 14px;
    font-weight: 700;
    margin: 0 0 4px;
    color: var(--text-primary);
}

.campaign-info p {
    font-size: 12px;
    color: var(--text-secondary);
    margin: 0;
}

/* Section Header */
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 80px 0 30px;
}

.section-title {
    font-family: 'Playfair Display', serif;
    font-size: 2rem;
    font-weight: 900;
    background: linear-gradient(135deg, var(--text-primary), #ef4444);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    display: flex;
    align-items: center;
    gap: 12px;
}

.section-badge {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #fff;
    padding: 4px 12px;
    border-radius: 999px;
    font-size: 14px;
    font-weight: 700;
}

/* Table */
.table-card {
    background: var(--bg-card);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    overflow: hidden;
    border: 1px solid var(--border-color);
    margin-bottom: 30px;
}

.table-card table {
    width: 100%;
    border-collapse: collapse;
}

.table-card thead {
    background: rgba(239, 68, 68, 0.2);
}

.table-card th {
    padding: 18px 20px;
    text-align: left;
    font-size: 13px;
    font-weight: 700;
    color: var(--text-primary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table-card td {
    padding: 18px 20px;
    border-bottom: 1px solid var(--border-color);
    font-size: 14px;
    color: var(--text-secondary);
}

.table-card tbody tr {
    transition: background 0.2s ease;
}

.table-card tbody tr:hover {
    background: rgba(239, 68, 68, 0.05);
}

.badge {
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    display: inline-block;
}

.badge-success {
    background: rgba(16, 185, 129, 0.2);
    color: #10b981;
}

.badge-warning {
    background: rgba(245, 158, 11, 0.2);
    color: #f59e0b;
}

.badge-danger {
    background: rgba(239, 68, 68, 0.2);
    color: #ef4444;
}

.user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 14px;
}

/* Pending Cards */
.pending-grid {
    display: grid;
    gap: 24px;
    margin-bottom: 40px;
}

.pending-card {
    background: var(--bg-card);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    padding: 28px;
    border: 1px solid var(--border-color);
    display: grid;
    grid-template-columns: 200px 1fr auto;
    gap: 24px;
    align-items: center;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.pending-card::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: linear-gradient(135deg, #ef4444, #dc2626);
}

.pending-card:hover {
    transform: translateY(-4px);
    border-color: var(--border-hover);
}

.pending-image {
    width: 100%;
    height: 140px;
    border-radius: 14px;
    object-fit: cover;
}

.pending-content h3 {
    font-size: 20px;
    font-weight: 800;
    margin: 0 0 8px;
    color: var(--text-primary);
}

.pending-content p {
    color: var(--text-secondary);
    margin: 6px 0;
    font-size: 14px;
    line-height: 1.6;
}

.pending-meta {
    display: flex;
    gap: 20px;
    margin-top: 12px;
    flex-wrap: wrap;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: var(--text-secondary);
}

.meta-item i {
    color: #ef4444;
}

.pending-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.btn-review,
.btn-approve,
.btn-reject {
    padding: 12px 20px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 700;
    font-size: 14px;
    text-align: center;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    white-space: nowrap;
}

.btn-review {
    background: var(--bg-secondary);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
}

.btn-review:hover {
    background: var(--bg-card-hover);
}

.btn-approve {
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-approve:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
}

.btn-reject {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #fff;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.btn-reject:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--text-tertiary);
}

.empty-state i {
    font-size: 64px;
    margin-bottom: 16px;
    opacity: 0.5;
}

.empty-state h3 {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 8px;
    color: var(--text-secondary);
}


        .no-thumb{
            width:100%;
            height:140px;
            background:linear-gradient(135deg,#ec4899,#ef4444);
            color:#fff;
            font-size:40px;
            font-weight:bold;
            display:flex;
            align-items:center;
            justify-content:center;
            border-radius:14px;
        }

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(40px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 1200px) {
    .cards-grid {
        grid-template-columns: 1fr;
    }
    
    .pending-card {
        grid-template-columns: 1fr;
    }
    
    .pending-actions {
        flex-direction: row;
    }
}

@media (max-width: 768px) {
    .admin-container {
        padding: 100px 20px 60px;
    }
    
    .page-title {
        font-size: 2.5rem;
    }
    
    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }
    
    .pending-actions {
        flex-direction: column;
    }
}
</style>

<!-- Background Animation -->
<div class="bg-animation">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
</div>

<div class="admin-container">

    <!-- PAGE HEADER -->
    <div class="page-header">
        <div>
            <h1 class="page-title">🛡️ Admin Dashboard</h1>
            <p class="page-subtitle">Manage campaigns, users, and platform analytics</p>
        </div>
        <div class="header-actions">
            <a href ="/admin/manage-users.php" class="btn-action btn-secondary">
                <i class="fa-solid fa-users"></i> Manage Users
            </a>
            <a href="/admin/admin-reply.php" class="btn-action btn-primary">
                <i class="fa-solid fa-chart-line"></i> View Users Messages 
            </a>
        </div>
    </div>

    <!-- STATS GRID -->
    <div class="stats-grid">
        
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon icon-users">
                    <i class="fa-solid fa-users" style="color: #fff;"></i>
                </div>
            </div>
            <div class="stat-content">
                <h2><?= number_format($totalUsers) ?></h2>
                <p>Total Users</p>
                <?php if($newUsersThisMonth > 0): ?>
                <div class="stat-trend trend-up">
                    <i class="fa-solid fa-arrow-up"></i> +<?= $newUsersThisMonth ?> this month
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon icon-campaigns">
                    <i class="fa-solid fa-layer-group" style="color: #fff;"></i>
                </div>
            </div>
            <div class="stat-content">
                <h2><?= number_format($totalCampaigns) ?></h2>
                <p>Total Campaigns</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon icon-approved">
                    <i class="fa-solid fa-check-circle" style="color: #fff;"></i>
                </div>
            </div>
            <div class="stat-content">
                <h2><?= number_format($totalApproved) ?></h2>
                <p>Approved Campaigns</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon icon-pending">
                    <i class="fa-solid fa-clock" style="color: #fff;"></i>
                </div>
            </div>
            <div class="stat-content">
                <h2><?= number_format($totalPending) ?></h2>
                <p>Pending Approval</p>
                <?php if($totalPending > 0): ?>
                <div class="stat-trend trend-up">
                    <i class="fa-solid fa-exclamation-circle"></i> Requires action
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon icon-money">
                    <i class="fa-solid fa-indian-rupee-sign" style="color: #fff;"></i>
                </div>
            </div>
            <div class="stat-content">
                <h2>₹<?= number_format($totalMoney) ?></h2>
                <p>Total Raised</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon icon-donations">
                    <i class="fa-solid fa-heart" style="color: #fff;"></i>
                </div>
            </div>
            <div class="stat-content">
                <h2><?= number_format($totalDonations) ?></h2>
                <p>Total Donations</p>
                <div class="stat-trend trend-up">
                    <i class="fa-solid fa-chart-line"></i> Avg: ₹<?= number_format($avgDonation) ?>
                </div>
            </div>
        </div>

    </div>

    <!-- CARDS GRID -->
    <div class="cards-grid">
        
        <!-- Top Campaigns -->
        <div class="card">
            <h3>🏆 Top Funded Campaigns</h3>
            <div class="top-campaigns">
                <?php foreach($topCampaigns as $index => $tc): ?>
                <div class="campaign-item">
                    <div class="campaign-rank"><?= $index + 1 ?></div>
                    <div class="campaign-info">
                        <h4><?= htmlspecialchars(substr($tc['title'], 0, 30)) ?>...</h4>
                        <p>₹<?= number_format($tc['raised']) ?> / ₹<?= number_format($tc['goal']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if(empty($topCampaigns)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-chart-simple"></i>
                    <h3>No campaigns yet</h3>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- PENDING CAMPAIGNS -->
    <?php if($pendingCampaigns): ?>
    <div class="section-header">
        <h2 class="section-title">
            <i class="fa-solid fa-clock"></i> Pending Approvals
            <span class="section-badge"><?= count($pendingCampaigns) ?></span>
        </h2>
    </div>

    <div class="pending-grid">
        <?php foreach($pendingCampaigns as $p): ?>
        <div class="pending-card">
            
<?php 
$hasThumb = !empty($p['thumbnail']);
?>

<?php if($hasThumb): ?>
    <img src="<?= htmlspecialchars($p['thumbnail']) ?>" 
         class="pending-image"
         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">

    <!-- fallback if image broken -->
    <div class="no-thumb" style="display:none;">
        <?= strtoupper(substr($p['title'],0,1)) ?>
    </div>

<?php else: ?>
    <div class="no-thumb">
        <?= strtoupper(substr($p['title'],0,1)) ?>
    </div>
<?php endif; ?>


<div class="pending-content">
    <h3><?= htmlspecialchars($p['title']) ?></h3>
    <p><?= htmlspecialchars(substr($p['short_desc'], 0, 120)) ?>...</p>
    
    <div class="pending-meta">
        <div class="meta-item">
            <i class="fa-solid fa-user"></i>
            <?= htmlspecialchars($p['name']) ?>
        </div>
        <div class="meta-item">
            <i class="fa-solid fa-indian-rupee-sign"></i>
            Goal: ₹<?= number_format($p['goal']) ?>
        </div>
        <div class="meta-item">
            <i class="fa-solid fa-tag"></i>
            <?= $p['category'] ?>
        </div>
        <div class="meta-item">
            <i class="fa-solid fa-calendar"></i>
            <?= date('d M Y', strtotime($p['created_at'])) ?>
        </div>
    </div>
</div>

<div class="pending-actions">
    <a class="btn-review" href ="/admin/view-campaign.php?id=<?= $p['id'] ?>">
        <i class="fa-solid fa-eye"></i> Review
    </a>
    <a class="btn-approve" href="/admin/approve.php?id=<?= $p['id'] ?>">
        <i class="fa-solid fa-check"></i> Approve
    </a>
    <a class="btn-reject" href="/admin/reject.php?id=<?= $p['id'] ?>">
        <i class="fa-solid fa-times"></i> Reject
    </a>
</div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- RECENT DONATIONS -->
    <div class="section-header">
        <h2 class="section-title">
            <i class="fa-solid fa-heart"></i> Recent Donations
        </h2>
        <a href="/admin/all-donations.php" class="btn-action btn-secondary">View All</a>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Donor</th>
                    <th>Campaign</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($recentDonations as $d): ?>
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="user-avatar">
                                <?= strtoupper(substr($d['name'], 0, 1)) ?>
                            </div>
                            <?= htmlspecialchars($d['name']) ?>
                        </div>
                    </td>
                    <td><?= htmlspecialchars(substr($d['title'], 0, 40)) ?></td>
                    <td><strong>₹<?= number_format($d['amount']) ?></strong></td>
                    <td><?= date('d M Y', strtotime($d['created_at'])) ?></td>
                    <td><span class="badge badge-success">Success</span></td>
                </tr>
                <?php endforeach; ?>

                <?php if(empty($recentDonations)): ?>
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <i class="fa-solid fa-heart-crack"></i>
                            <h3>No donations yet</h3>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- RECENT CAMPAIGNS -->
    <div class="section-header">
        <h2 class="section-title">
            <i class="fa-solid fa-layer-group"></i> Recent Campaigns
        </h2>
        <a href="/admin/all-campaigns.php" class="btn-action btn-secondary">View All</a>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Campaign</th>
                    <th>Creator</th>
                    <th>Goal</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($recentCampaigns as $c): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars(substr($c['title'], 0, 40)) ?></strong>
                    </td>
                    <td><?= htmlspecialchars($c['creator_name']) ?></td>
                    <td>₹<?= number_format($c['goal']) ?></td>
                    <td><?= $c['category'] ?></td>
                    <td>
                        <?php if($c['status'] == 'approved'): ?>
                            <span class="badge badge-success">Approved</span>
                        <?php elseif($c['status'] == 'pending'): ?>
                            <span class="badge badge-warning">Pending</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Rejected</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('d M Y', strtotime($c['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<?php require_once $_SERVER['DOCUMENT_ROOT']."/includes/footer.php"; ?>