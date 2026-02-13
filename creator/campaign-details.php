<?php
session_start();
require_once __DIR__ . "/../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'creator') {
    header("Location: ../public/index.php");
    exit;
}

$creator_id = $_SESSION['user_id'];

// ─── Get campaign ID from URL ───────────────────────────────────────
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid campaign ID");
}

$campaign_id = (int)$_GET['id'];

// ─── Fetch campaign + thumbnail from campaign_media ─────────────────
$stmt = $pdo->prepare("
    SELECT 
        c.*,
        COALESCE(
            (SELECT cm.media_url 
             FROM campaign_media cm 
             WHERE cm.campaign_id = c.id 
               AND cm.media_type = 'thumbnail' 
             LIMIT 1),
            '/assets/placeholder-large.jpg'
        ) AS thumbnail_url,
        COALESCE(
            (SELECT SUM(d.amount)
             FROM donations d
             WHERE d.campaign_id = c.id 
               AND d.status = 'success'),
            0
        ) AS raised
    FROM campaigns c
    WHERE c.id = ? AND c.user_id = ?
");
$stmt->execute([$campaign_id, $creator_id]);
$campaign = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$campaign) {
    die("Campaign not found or you don't have permission to view it.");
}

$raised  = $campaign['raised'];
$goal    = $campaign['goal'] ?? 0;
$percent = $goal > 0 ? min(100, ($raised / $goal) * 100) : 0;

// ─── Recent donors for this campaign ────────────────────────────────
$donors_stmt = $pdo->prepare("
    SELECT u.name, d.amount, d.created_at
    FROM donations d
    JOIN users u ON d.user_id = u.id
    WHERE d.campaign_id = ? AND d.status = 'success'
    ORDER BY d.created_at DESC
    LIMIT 12
");
$donors_stmt->execute([$campaign_id]);
$donors = $donors_stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . "/../includes/header.php";
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@400;500;600;700;800;900&display=swap');

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'DM Sans', sans-serif;
    background: #0f0f0f;
    color: #fff;
    overflow-x: hidden;
    position: relative;
}

/* Animated Background - Yellow/Amber theme */
.bg-animation {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 0;
    overflow: hidden;
    opacity: 0.25;
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
    background: linear-gradient(45deg, #f59e0b, #fbbf24);
    top: -10%;
    left: -10%;
    animation-delay: 0s;
}

.orb-2 {
    width: 400px;
    height: 400px;
    background: linear-gradient(45deg, #d97706, #f59e0b);
    bottom: -10%;
    right: -10%;
    animation-delay: 5s;
}

.orb-3 {
    width: 350px;
    height: 350px;
    background: linear-gradient(45deg, #b45309, #d97706);
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

/* ===== ANIMATIONS ===== */
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

@keyframes shimmer {
    0% { background-position: -1000px 0; }
    100% { background-position: 1000px 0; }
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.8; }
}

/* ===== CONTAINER ===== */
.campaign-detail-container {
    position: relative;
    z-index: 1;
    max-width: 1100px;
    margin: 0 auto;
    padding: 120px 1.5rem 80px;
    animation: fadeInUp 0.8s ease;
}

/* ===== BACK LINK ===== */
.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 2rem;
    color: #f59e0b;
    font-weight: 700;
    text-decoration: none;
    padding: 12px 24px;
    border-radius: 50px;
    background: rgba(245, 158, 11, 0.1);
    border: 1px solid rgba(245, 158, 11, 0.2);
    transition: all 0.3s ease;
}

.back-link:hover {
    background: rgba(245, 158, 11, 0.2);
    transform: translateX(-4px);
}

/* ===== CAMPAIGN HERO ===== */
.campaign-hero {
    background: rgba(20, 20, 30, 0.85);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    box-shadow: 0 20px 60px rgba(245, 158, 11, 0.1);
    overflow: hidden;
    margin-bottom: 2rem;
    border: 1px solid rgba(255, 255, 255, 0.15);
    position: relative;
}

.campaign-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #f59e0b, #fbbf24);
}

.hero-image {
    width: 100%;
    height: 400px;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.campaign-hero:hover .hero-image {
    transform: scale(1.05);
}

/* ===== DETAIL CONTENT ===== */
.detail-content {
    padding: 2.5rem;
}

.title {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2rem, 5vw, 2.8rem);
    font-weight: 900;
    margin: 0 0 1.5rem;
    background: linear-gradient(135deg, #fff, #f59e0b);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    line-height: 1.2;
}

.meta {
    color: #cbd5e1;
    font-size: 1rem;
    margin-bottom: 2rem;
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    align-items: center;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
}

.meta-item i {
    color: #f59e0b;
}

.status-badge-detail {
    padding: 6px 16px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
}

.status-approved { 
    background: rgba(16, 185, 129, 0.2); 
    color: #10b981; 
}

.status-pending { 
    background: rgba(245, 158, 11, 0.2); 
    color: #f59e0b; 
}

.status-rejected { 
    background: rgba(239, 68, 68, 0.2); 
    color: #ef4444; 
}

/* ===== STATS BAR ===== */
.stats-bar {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 2rem;
    margin: 2.5rem 0 3rem;
    padding: 2rem;
    background: rgba(30, 30, 40, 0.6);
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.stat-block {
    text-align: center;
}

.stat-number {
    font-size: clamp(2rem, 5vw, 3rem);
    font-weight: 900;
    background: linear-gradient(45deg, #f59e0b, #fbbf24);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    line-height: 1;
    margin-bottom: 0.5rem;
}

.stat-label {
    color: #cbd5e1;
    font-size: 0.95rem;
    font-weight: 600;
}

.progress-large {
    height: 16px;
    background: rgba(245, 158, 11, 0.1);
    border-radius: 999px;
    overflow: hidden;
    margin: 1.5rem 0;
    position: relative;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
}

.progress-fill-large {
    height: 100%;
    background: linear-gradient(90deg, #f59e0b, #fbbf24);
    transition: width 1.2s cubic-bezier(0.65, 0, 0.35, 1);
    position: relative;
    box-shadow: 0 0 20px rgba(245, 158, 11, 0.5);
}

.progress-fill-large::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    animation: shimmer 2s infinite;
}

/* ===== DESCRIPTION ===== */
.description {
    line-height: 1.8;
    color: #cbd5e1;
    font-size: 1.05rem;
    margin-bottom: 3rem;
}

/* ===== REJECT NOTICE ===== */
.reject-notice {
    background: rgba(239, 68, 68, 0.1);
    padding: 1.5rem 2rem;
    border-radius: 16px;
    margin: 2rem 0;
    color: #fca5a5;
    border-left: 4px solid #ef4444;
    animation: pulse 2s infinite;
}

.reject-notice strong {
    display: block;
    margin-bottom: 0.5rem;
    color: #ef4444;
    font-size: 1.1rem;
}

/* ===== DONORS SECTION ===== */
.donors-section {
    background: rgba(20, 20, 30, 0.85);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 2.5rem;
    box-shadow: 0 10px 30px rgba(245, 158, 11, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.15);
    margin-top: 3rem;
}

.donors-section h2 {
    font-family: 'Playfair Display', serif;
    margin: 0 0 2rem;
    font-size: 1.8rem;
    font-weight: 800;
    background: linear-gradient(135deg, #fff, #f59e0b);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.donor-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.2rem 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
}

.donor-row:hover {
    background: rgba(245, 158, 11, 0.05);
    padding-left: 1rem;
    padding-right: 1rem;
    border-radius: 12px;
    transform: translateX(8px);
}

.donor-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.donor-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f59e0b, #fbbf24);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 1.2rem;
    color: #fff;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
}

.donor-details {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.donor-name {
    font-weight: 700;
    font-size: 1.05rem;
    color: #fff;
}

.donor-date {
    font-size: 0.85rem;
    color: #94a3b8;
}

.donor-amount {
    background: linear-gradient(45deg, #10b981, #34d399);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    font-weight: 900;
    font-size: 1.3rem;
}

/* ===== EMPTY STATE ===== */
.empty-donors {
    text-align: center;
    padding: 3rem;
    color: #94a3b8;
}

.empty-donors i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .campaign-detail-container {
        padding: 100px 1rem 60px;
    }
    
    .stats-bar { 
        grid-template-columns: 1fr;
        gap: 1.5rem; 
        padding: 1.5rem;
    }
    
    .hero-image { 
        height: 280px; 
    }
    
    .detail-content {
        padding: 2rem;
    }
    
    .title {
        font-size: 2rem;
    }
    
    .meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    
    .donor-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    
    .donors-section {
        padding: 1.5rem;
    }
}

@media (max-width: 480px) {
    .back-link {
        padding: 10px 20px;
        font-size: 14px;
    }
    
    .stat-number {
        font-size: 2rem;
    }
}
</style>

<!-- Background Animation -->
<div class="bg-animation">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
</div>

<div class="campaign-detail-container">

    <a href="creator-dashboard.php" class="back-link">
        <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
    </a>

    <div class="campaign-hero">
        <img src="<?= htmlspecialchars($campaign['thumbnail_url']) ?>"
             alt="<?= htmlspecialchars($campaign['title'] ?? 'Campaign image') ?>"
             class="hero-image"
             onerror="this.onerror=null; this.src='/assets/placeholder-large.jpg';">
    </div>

    <div class="detail-content">

        <h1 class="title"><?= htmlspecialchars($campaign['title'] ?? 'Untitled Campaign') ?></h1>

        <div class="meta">
            <div class="meta-item">
                <i class="fa-solid fa-calendar"></i>
                <span>Created: <?= date('d M Y', strtotime($campaign['created_at'] ?? 'now')) ?></span>
            </div>
            <div class="meta-item">
                <i class="fa-solid fa-tag"></i>
                <span>Category: <?= htmlspecialchars($campaign['category'] ?? 'General') ?></span>
            </div>
            <div class="meta-item">
                <i class="fa-solid fa-circle-info"></i>
                <span>Status: 
                    <span class="status-badge-detail status-<?= htmlspecialchars($campaign['status'] ?? 'unknown') ?>">
                        <?= ucfirst($campaign['status'] ?? 'Unknown') ?>
                    </span>
                </span>
            </div>
        </div>

        <div class="stats-bar">
            <div class="stat-block">
                <div class="stat-number">₹<?= number_format($raised) ?></div>
                <div class="stat-label">Raised of ₹<?= number_format($goal) ?> goal</div>
            </div>
            <div class="stat-block">
                <div class="progress-large">
                    <div class="progress-fill-large" style="width: <?= $percent ?>%"></div>
                </div>
                <div class="stat-label"><?= round($percent) ?>% Funded</div>
            </div>
            <div class="stat-block">
                <div class="stat-number"><?= count($donors) ?></div>
                <div class="stat-label">Total Supporters</div>
            </div>
        </div>

        <div class="description">
            <?= nl2br(htmlspecialchars($campaign['description'] ?? $campaign['story'] ?? 'No description provided.')) ?>
        </div>

        <?php if (!empty($campaign['reject_reason']) && $campaign['status'] === 'rejected'): ?>
        <div class="reject-notice">
            <strong><i class="fa-solid fa-triangle-exclamation"></i> Rejection Reason:</strong>
            <?= htmlspecialchars($campaign['reject_reason']) ?>
        </div>
        <?php endif; ?>

        <?php if ($donors): ?>
        <div class="donors-section">
            <h2><i class="fa-solid fa-heart"></i> Recent Supporters</h2>
            <?php foreach ($donors as $d): ?>
            <div class="donor-row">
                <div class="donor-info">
                    <div class="donor-avatar">
                        <?= strtoupper(substr($d['name'] ?? 'A', 0, 1)) ?>
                    </div>
                    <div class="donor-details">
                        <div class="donor-name"><?= htmlspecialchars($d['name'] ?? 'Anonymous') ?></div>
                        <div class="donor-date">
                            <i class="fa-solid fa-clock"></i>
                            <?= date('d M Y, h:i A', strtotime($d['created_at'] ?? 'now')) ?>
                        </div>
                    </div>
                </div>
                <div class="donor-amount">₹<?= number_format($d['amount'] ?? 0) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="donors-section">
            <h2><i class="fa-solid fa-heart"></i> Recent Supporters</h2>
            <div class="empty-donors">
                <i class="fa-solid fa-heart-crack"></i>
                <p>No donations yet. Share your campaign to get started!</p>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>