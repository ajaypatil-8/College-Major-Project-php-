<?php
session_start();
require_once __DIR__."/../config/db.php";

/* ===== ADMIN CHECK ===== */
if(!isset($_SESSION['user_id']) || $_SESSION['role']!='admin'){
    header("Location: ../login.php");
    exit;
}

/* ===== PAGINATION ===== */
$limit = 30;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1) $page = 1;

$offset = ($page - 1) * $limit;

/* ===== FETCH DONATIONS WITH USER DETAILS ===== */
$stmt = $pdo->prepare("
SELECT d.*, u.name, u.email, u.city, u.profile_image, c.title as campaign_title
FROM donations d
JOIN users u ON d.user_id = u.id
LEFT JOIN campaigns c ON d.campaign_id = c.id
ORDER BY d.id DESC
LIMIT $limit OFFSET $offset
");
$stmt->execute();
$donations = $stmt->fetchAll();

/* ===== COUNT TOTAL & STATS ===== */
$total = $pdo->query("SELECT COUNT(*) FROM donations")->fetchColumn();
$totalAmount = $pdo->query("SELECT IFNULL(SUM(amount),0) FROM donations WHERE status='success'")->fetchColumn();
$successCount = $pdo->query("SELECT COUNT(*) FROM donations WHERE status='success'")->fetchColumn();
$avgDonation = $successCount > 0 ? $totalAmount / $successCount : 0;

$total_pages = ceil($total / $limit);
?>

<?php require_once __DIR__."/../includes/header.php"; ?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@400;500;600;700;800;900&display=swap');

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
    --orb-1: linear-gradient(45deg, #fca5a5, #f87171);
    --orb-2: linear-gradient(45deg, #f9a8d4, #f472b6);
    --orb-3: linear-gradient(45deg, #ef4444, #fca5a5);
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

/* Animated Background - Red/Pink theme */
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

/* Container */
.admin-container {
    position: relative;
    z-index: 1;
    max-width: 1600px;
    margin: 0 auto;
    padding: 120px 40px 80px;
}

/* Page Header */
.page-header {
    margin-bottom: 40px;
    animation: fadeInUp 0.8s ease;
}

.page-title {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2.5rem, 6vw, 3.5rem);
    font-weight: 900;
    background: linear-gradient(135deg, var(--text-primary), #ef4444);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin: 0 0 10px;
}

.page-subtitle {
    color: var(--text-secondary);
    font-size: 1.1rem;
    font-weight: 500;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 24px;
    margin-bottom: 40px;
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

.icon-total { background: linear-gradient(135deg, #ef4444, #dc2626); }
.icon-amount { background: linear-gradient(135deg, #10b981, #059669); }
.icon-success { background: linear-gradient(135deg, #3b82f6, #2563eb); }
.icon-avg { background: linear-gradient(135deg, #f59e0b, #d97706); }

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

/* Table Card */
.table-card {
    background: var(--bg-card);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    overflow: hidden;
    border: 1px solid var(--border-color);
    margin-bottom: 40px;
    animation: fadeInUp 0.6s ease 0.5s both;
}

.table-card table {
    width: 100%;
    border-collapse: collapse;
}

.table-card thead {
    background: rgba(239, 68, 68, 0.2);
}

.table-card th {
    padding: 20px;
    text-align: left;
    font-size: 13px;
    font-weight: 800;
    color: var(--text-primary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table-card td {
    padding: 20px;
    border-bottom: 1px solid var(--border-color);
    font-size: 14px;
    color: var(--text-secondary);
}

.table-card tbody tr {
    transition: all 0.2s ease;
}

.table-card tbody tr:hover {
    background: rgba(239, 68, 68, 0.05);
}

/* User Cell */
.user-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(239, 68, 68, 0.3);
    transition: all 0.3s ease;
}

.user-avatar:hover {
    transform: scale(1.1);
    border-color: #ef4444;
}

.user-name {
    font-weight: 700;
    color: var(--text-primary);
}

/* Status Badge */
.badge {
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    display: inline-block;
    text-transform: uppercase;
}

.badge-success {
    background: rgba(16, 185, 129, 0.2);
    color: #10b981;
}

.badge-pending {
    background: rgba(245, 158, 11, 0.2);
    color: #f59e0b;
}

.badge-failed {
    background: rgba(239, 68, 68, 0.2);
    color: #ef4444;
}

/* Amount Highlight */
.amount-cell {
    font-size: 16px;
    font-weight: 900;
    color: #10b981;
}

/* Campaign Cell */
.campaign-cell {
    max-width: 300px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Pagination */
.pagination-container {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    margin-top: 40px;
    animation: fadeInUp 0.6s ease 0.6s both;
}

.pagination-btn {
    padding: 12px 18px;
    border-radius: 12px;
    background: var(--bg-card);
    color: var(--text-primary);
    text-decoration: none;
    font-weight: 700;
    font-size: 14px;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.pagination-btn:hover {
    background: var(--bg-card-hover);
    border-color: var(--border-hover);
    transform: translateY(-2px);
}

.pagination-btn.active {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #fff;
    border-color: #ef4444;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 80px 20px;
    color: var(--text-tertiary);
}

.empty-state i {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.5;
    color: #ef4444;
}

.empty-state h3 {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 8px;
    color: var(--text-secondary);
}

/* Responsive */
@media (max-width: 1024px) {
    .table-card {
        overflow-x: auto;
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
    
    .table-card th,
    .table-card td {
        padding: 12px;
        font-size: 13px;
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
        <h1 class="page-title">💰 All Donations</h1>
        <p class="page-subtitle">Track and manage all platform donations</p>
    </div>

    <!-- STATS GRID -->
    <div class="stats-grid">
        
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon icon-total">
                    <i class="fa-solid fa-heart" style="color: #fff;"></i>
                </div>
            </div>
            <div class="stat-content">
                <h2><?= number_format($total) ?></h2>
                <p>Total Donations</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon icon-amount">
                    <i class="fa-solid fa-indian-rupee-sign" style="color: #fff;"></i>
                </div>
            </div>
            <div class="stat-content">
                <h2>₹<?= number_format($totalAmount) ?></h2>
                <p>Total Amount Raised</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon icon-success">
                    <i class="fa-solid fa-check-circle" style="color: #fff;"></i>
                </div>
            </div>
            <div class="stat-content">
                <h2><?= number_format($successCount) ?></h2>
                <p>Successful Donations</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon icon-avg">
                    <i class="fa-solid fa-chart-line" style="color: #fff;"></i>
                </div>
            </div>
            <div class="stat-content">
                <h2>₹<?= number_format($avgDonation) ?></h2>
                <p>Average Donation</p>
            </div>
        </div>

    </div>

    <!-- TABLE -->
    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Donor</th>
                    <th>Email</th>
                    <th>Campaign</th>
                    <th>Amount</th>
                    <th>Payment ID</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($donations)): ?>
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <i class="fa-solid fa-heart-crack"></i>
                            <h3>No donations yet</h3>
                            <p>Donations will appear here</p>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                
                <?php foreach($donations as $d): ?>
                <tr>
                    <td><strong>#<?= $d['id'] ?></strong></td>
                    
                    <td>
                        <div class="user-cell">
                            <img 
                                class="user-avatar" 
                                src="<?= $d['profile_image'] ?: 'https://via.placeholder.com/48' ?>"
                                alt="User"
                            >
                            <div class="user-name">
                                <?= htmlspecialchars($d['name']) ?>
                            </div>
                        </div>
                    </td>
                    
                    <td><?= htmlspecialchars($d['email']) ?></td>
                    
                    <td>
                        <div class="campaign-cell" title="<?= htmlspecialchars($d['campaign_title']) ?>">
                            <?= htmlspecialchars($d['campaign_title']) ?>
                        </div>
                    </td>
                    
                    <td class="amount-cell">₹<?= number_format($d['amount']) ?></td>
                    
                    <td>
                        <code style="font-size: 12px; color: var(--text-tertiary);">
                            <?= htmlspecialchars($d['razorpay_payment_id'] ?: 'N/A') ?>
                        </code>
                    </td>
                    
                    <td>
                        <span class="badge badge-<?= $d['status'] ?>">
                            <?= ucfirst($d['status']) ?>
                        </span>
                    </td>
                    
                    <td><?= date('d M Y, h:i A', strtotime($d['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    <?php if($total_pages > 1): ?>
    <div class="pagination-container">
        <?php for($i=1; $i<=$total_pages; $i++): ?>
        <a 
            class="pagination-btn <?= ($i==$page)?'active':'' ?>" 
            href="?page=<?= $i ?>"
        >
            <?= $i ?>
        </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__."/../includes/footer.php"; ?>