<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT']."/config/db.php";

// Login required
if(!isset($_SESSION['user_id'])){
    header("Locationuser/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get filter from URL
$filter = $_GET['filter'] ?? 'all';


$allCampaigns = $pdo->prepare("
    SELECT c.*,
    (
        SELECT media_url 
        FROM campaign_media 
        WHERE campaign_id = c.id 
        AND media_type='thumbnail'
        LIMIT 1
    ) as thumbnail
    FROM campaigns c
    WHERE c.user_id=? AND c.status != 'draft'
    ORDER BY c.id DESC
");
$allCampaigns->execute([$user_id]);
$allCampaigns = $allCampaigns->fetchAll(PDO::FETCH_ASSOC);

// Count by status
$totalCampaigns = count($allCampaigns);
$approvedCount = count(array_filter($allCampaigns, fn($c) => $c['status'] == 'approved'));
$pendingCount = count(array_filter($allCampaigns, fn($c) => $c['status'] == 'pending'));
$rejectedCount = count(array_filter($allCampaigns, fn($c) => $c['status'] == 'rejected'));

// Filter campaigns based on selection
if($filter == 'all') {
    $campaigns = $allCampaigns;
} else {
    $campaigns = array_filter($allCampaigns, fn($c) => $c['status'] == $filter);
}

// Get donations data for each campaign
$campaignStats = [];
foreach($campaigns as $campaign) {
    $stats = $pdo->prepare("
        SELECT 
            COUNT(*) as donation_count,
            IFNULL(SUM(amount), 0) as total_raised
        FROM donations 
        WHERE campaign_id = ? AND status = 'success'
    ");
    $stats->execute([$campaign['id']]);
    $campaignStats[$campaign['id']] = $stats->fetch(PDO::FETCH_ASSOC);
}

require_once $_SERVER['DOCUMENT_ROOT']."/includes/header.php";
?>


<html lang="en" data-theme="light">

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Campaigns - CrowdSpark</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

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
    --border-hover: rgba(236, 72, 153, 0.3);
    
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
    --border-hover: rgba(236, 72, 153, 0.4);
    
    --orb-opacity: 0.25;
    
    --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.3);
    --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.4);
    --shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.5);
}

/* Pink/Magenta accent colors - STAY CONSTANT */
:root,
[data-theme="dark"] {
    --accent-primary: #ec4899;
    --accent-secondary: #f472b6;
    --accent-gradient: linear-gradient(135deg, #ec4899, #f472b6);
    --orb-1: linear-gradient(45deg, #ec4899, #f472b6);
    --orb-2: linear-gradient(45deg, #db2777, #ec4899);
    --orb-3: linear-gradient(45deg, #be185d, #db2777);
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

/* Animated Background - Pink/Magenta theme */
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
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(-30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* ===== HERO SECTION ===== */
.campaigns-hero {
    position: relative;
    z-index: 1;
    padding: 120px 20px 80px;
    text-align: center;
    animation: fadeInUp 0.8s ease;
}

.campaigns-hero h1 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(3rem, 8vw, 5rem);
    font-weight: 900;
    padding-bottom: 20px;
    background: linear-gradient(135deg, var(--text-primary), var(--accent-primary));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    line-height: 1.1;
}

.campaigns-hero p {
    font-size: 1.25rem;
    color: var(--text-secondary);
    max-width: 600px;
    margin: 0 auto;
}

/* ===== CONTAINER ===== */
.campaigns-container {
    position: relative;
    z-index: 1;
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 20px 80px;
}

/* ===== STATS GRID ===== */
.stats-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 24px;
    margin-bottom: 50px;
    animation: fadeInUp 0.8s ease 0.2s both;
}

.stat-card {
    background: var(--bg-card);
    backdrop-filter: blur(20px);
    padding: 28px;
    border-radius: 20px;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-color);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: var(--accent-gradient);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.stat-card:hover::before {
    transform: scaleX(1);
}

.stat-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-lg);
}

.stat-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.stat-info h3 {
    font-size: 36px;
    font-weight: 900;
    color: var(--text-primary);
    margin: 0;
    line-height: 1;
}

.stat-info p {
    color: var(--text-secondary);
    margin: 8px 0 0;
    font-weight: 600;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-icon {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    color: #fff;
}

.icon-all { background: linear-gradient(135deg, #8b5cf6, #a78bfa); }
.icon-approved { background: linear-gradient(135deg, #10b981, #34d399); }
.icon-pending { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
.icon-rejected { background: linear-gradient(135deg, #ef4444, #f87171); }

/* ===== FILTER TABS ===== */
.filter-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    flex-wrap: wrap;
    gap: 20px;
    animation: slideInRight 0.8s ease 0.3s both;
}

.filter-tabs {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.filter-tab {
    padding: 12px 24px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 700;
    font-size: 14px;
    transition: all 0.3s ease;
    border: 2px solid var(--border-color);
    color: var(--text-secondary);
    background: var(--bg-secondary);
}

.filter-tab:hover {
    border-color: var(--accent-primary);
    color: var(--accent-primary);
    transform: translateY(-2px);
}

.filter-tab.active {
    background: var(--accent-gradient);
    border-color: transparent;
    color: #fff;
    box-shadow: 0 4px 12px rgba(236, 72, 153, 0.3);
}

.create-btn {
    padding: 12px 28px;
    border-radius: 50px;
    background: var(--accent-gradient);
    color: #fff;
    text-decoration: none;
    font-weight: 700;
    font-size: 14px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(236, 72, 153, 0.3);
    display: inline-flex;
    align-items: center;
    gap: 8px;
    position: relative;
    overflow: hidden;
}

.create-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s;
}

.create-btn:hover::before {
    left: 100%;
}

.create-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(236, 72, 153, 0.4);
}

/* ===== CAMPAIGNS GRID ===== */
.campaigns-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 30px;
    margin-bottom: 40px;
    animation: fadeInUp 0.8s ease 0.4s both;
}

.campaign-card {
    background: var(--bg-card);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    overflow: hidden;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-color);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    cursor: pointer;
    text-decoration: none;
    color: inherit;
    display: block;
    position: relative;
}

.campaign-card::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(236, 72, 153, 0.05) 0%, transparent 100%);
    opacity: 0;
    transition: opacity 0.4s ease;
    pointer-events: none;
}

.campaign-card:hover {
    transform: translateY(-10px);
    box-shadow: var(--shadow-lg);
}

.campaign-card:hover::after {
    opacity: 1;
}

.campaign-thumbnail {
    position: relative;
    height: 220px;
    overflow: hidden;
    background: rgba(236, 72, 153, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
}

.campaign-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.campaign-card:hover .campaign-thumbnail img {
    transform: scale(1.1);
}

.no-thumb {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #ec4899, #f472b6);
    color: #fff;
    font-size: 80px;
    font-weight: 900;
    font-family: 'Playfair Display', serif;
}

.campaign-status-badge {
    position: absolute;
    top: 16px;
    right: 16px;
    padding: 8px 16px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.status-approved {
    background: rgba(16, 185, 129, 0.9);
    color: #fff;
}

.status-pending {
    background: rgba(245, 158, 11, 0.9);
    color: #fff;
}

.status-rejected {
    background: rgba(239, 68, 68, 0.9);
    color: #fff;
}

.campaign-body {
    padding: 24px;
}

.campaign-title {
    font-size: 20px;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0 0 10px;
    line-height: 1.3;
}

.campaign-description {
    color: var(--text-secondary);
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 20px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.campaign-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
    margin-bottom: 20px;
}

.stat-item {
    background: var(--bg-secondary);
    padding: 12px;
    border-radius: 10px;
    text-align: center;
    border: 1px solid var(--border-color);
}

.stat-item label {
    display: block;
    font-size: 11px;
    color: var(--text-tertiary);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}

.stat-item strong {
    display: block;
    font-size: 18px;
    color: var(--text-primary);
    font-weight: 800;
}

.campaign-progress {
    margin-top: 16px;
}

.progress-label {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    font-size: 13px;
}

.progress-label span {
    color: var(--text-tertiary);
    font-weight: 600;
}

.progress-label strong {
    color: var(--accent-primary);
    font-weight: 800;
}

.progress-bar {
    height: 10px;
    background: rgba(236, 72, 153, 0.1);
    border-radius: 999px;
    overflow: hidden;
    position: relative;
}

.progress-fill {
    height: 100%;
    background: var(--accent-gradient);
    border-radius: 999px;
    transition: width 0.8s ease;
    position: relative;
}

.progress-fill::after {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    animation: shimmer 2s infinite;
}

.campaign-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 16px;
    border-top: 1px solid var(--border-color);
    margin-top: 16px;
}

.campaign-date {
    font-size: 12px;
    color: var(--text-tertiary);
    display: flex;
    align-items: center;
    gap: 6px;
}

.view-details {
    color: var(--accent-primary);
    font-weight: 700;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: gap 0.3s ease;
}

.campaign-card:hover .view-details {
    gap: 10px;
}

/* ===== EMPTY STATE ===== */
.empty-state {
    text-align: center;
    padding: 100px 20px;
    animation: fadeInUp 0.8s ease;
}

.empty-state i {
    font-size: 80px;
    color: rgba(236, 72, 153, 0.3);
    margin-bottom: 24px;
}

.empty-state h3 {
    font-size: 28px;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: 12px;
}

.empty-state p {
    font-size: 16px;
    color: var(--text-secondary);
    margin-bottom: 30px;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

.empty-state .create-btn {
    display: inline-flex;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .campaigns-hero h1 {
        font-size: 2.5rem;
    }
    
    .campaigns-grid {
        grid-template-columns: 1fr;
    }
    
    .filter-section {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-tabs {
        justify-content: center;
    }
    
    .stats-overview {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .campaigns-hero {
        padding: 100px 20px 60px;
    }
    
    .stat-card {
        padding: 20px;
    }
}
</style>




<!-- Background Animation -->
<div class="bg-animation">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
</div>

<!-- HERO -->
<section class="campaigns-hero">
    <h1>My Campaigns</h1>
    <p>Track and manage all your fundraising campaigns in one place.</p>
</section>

<!-- MAIN CONTENT -->
<div class="campaigns-container">

    <!-- STATS OVERVIEW -->
    <div class="stats-overview">
        
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <h3><?= $totalCampaigns ?></h3>
                    <p>Total Campaigns</p>
                </div>
                <div class="stat-icon icon-all">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <h3><?= $approvedCount ?></h3>
                    <p>Approved & Live</p>
                </div>
                <div class="stat-icon icon-approved">
                    <i class="fa-solid fa-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <h3><?= $pendingCount ?></h3>
                    <p>Pending Review</p>
                </div>
                <div class="stat-icon icon-pending">
                    <i class="fa-solid fa-clock"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <h3><?= $rejectedCount ?></h3>
                    <p>Rejected</p>
                </div>
                <div class="stat-icon icon-rejected">
                    <i class="fa-solid fa-times-circle"></i>
                </div>
            </div>
        </div>

    </div>

    <!-- FILTER TABS -->
    <div class="filter-section">
        <div class="filter-tabs">
            <a href="?filter=all" class="filter-tab <?= $filter == 'all' ? 'active' : '' ?>">
                <i class="fa-solid fa-th-large"></i> All (<?= $totalCampaigns ?>)
            </a>
            <a href="?filter=approved" class="filter-tab <?= $filter == 'approved' ? 'active' : '' ?>">
                <i class="fa-solid fa-check"></i> Approved (<?= $approvedCount ?>)
            </a>
            <a href="?filter=pending" class="filter-tab <?= $filter == 'pending' ? 'active' : '' ?>">
                <i class="fa-solid fa-clock"></i> Pending (<?= $pendingCount ?>)
            </a>
            <a href="?filter=rejected" class="filter-tab <?= $filter == 'rejected' ? 'active' : '' ?>">
                <i class="fa-solid fa-times"></i> Rejected (<?= $rejectedCount ?>)
            </a>
        </div>
        
        <a href ="/create-campaign.php" class="create-btn">
            <i class="fa-solid fa-plus"></i> Create New Campaign
        </a>
    </div>

    <!-- CAMPAIGNS GRID -->
    <?php if(count($campaigns) > 0): ?>
    <div class="campaigns-grid">
        
        <?php foreach($campaigns as $campaign): ?>
            <?php
            $stats = $campaignStats[$campaign['id']];
            $raised = $stats['total_raised'];
            $goal = $campaign['goal'];
            $progress = $goal > 0 ? ($raised / $goal) * 100 : 0;
            $progress = min($progress, 100);
            
            // Detail page link
            $detailLink = "campaign-details.php?id=" . $campaign['id'];
            ?>
            
            <a href="<?= $detailLink ?>" class="campaign-card">
                
                <!-- Thumbnail -->
                <div class="campaign-thumbnail">
                    <?php if(!empty($campaign['thumbnail'])): ?>
                        <img 
                            src="<?= htmlspecialchars($campaign['thumbnail']) ?>"
                            alt="<?= htmlspecialchars($campaign['title']) ?>"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                        >
                        <!-- fallback letter hidden -->
                        <div class="no-thumb" style="display:none;">
                            <?= strtoupper(substr($campaign['title'],0,1)) ?>
                        </div>
                    <?php else: ?>
                        <!-- if no thumbnail uploaded -->
                        <div class="no-thumb">
                            <?= strtoupper(substr($campaign['title'],0,1)) ?>
                        </div>
                    <?php endif; ?>

                    <div class="campaign-status-badge status-<?= $campaign['status'] ?>">
                        <?= ucfirst($campaign['status']) ?>
                    </div>
                </div>

                <!-- Body -->
                <div class="campaign-body">
                    
                    <h3 class="campaign-title"><?= htmlspecialchars($campaign['title']) ?></h3>
                    
                    <p class="campaign-description">
                        <?= htmlspecialchars($campaign['short_desc'] ?? substr($campaign['story'], 0, 100)) ?>
                    </p>

                    <!-- Stats Grid -->
                    <div class="campaign-stats">
                        <div class="stat-item">
                            <label>Goal</label>
                            <strong>₹<?= number_format($goal) ?></strong>
                        </div>
                        <div class="stat-item">
                            <label>Raised</label>
                            <strong style="color: #10b981;">₹<?= number_format($raised) ?></strong>
                        </div>
                        <div class="stat-item">
                            <label>Donations</label>
                            <strong><?= $stats['donation_count'] ?></strong>
                        </div>
                        <div class="stat-item">
                            <label>Category</label>
                            <strong><?= ucfirst($campaign['category']) ?></strong>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="campaign-progress">
                        <div class="progress-label">
                            <span>Funding Progress</span>
                            <strong><?= number_format($progress, 1) ?>%</strong>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= $progress ?>%"></div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="campaign-footer">
                        <div class="campaign-date">
                            <i class="fa-solid fa-calendar"></i>
                            Created <?= date('d M Y', strtotime($campaign['created_at'])) ?>
                        </div>
                        <div class="view-details">
                            View Details <i class="fa-solid fa-arrow-right"></i>
                        </div>
                    </div>

                </div>

            </a>
        <?php endforeach; ?>

    </div>
    
    <?php else: ?>
    <!-- EMPTY STATE -->
    <div class="empty-state">
        <i class="fa-solid fa-folder-open"></i>
        <h3>No <?= $filter != 'all' ? ucfirst($filter) : '' ?> Campaigns Found</h3>
        <p>
            <?php if($filter == 'all'): ?>
                You haven't submitted any campaigns yet. Start your first fundraising campaign today!
            <?php else: ?>
                No campaigns with "<?= ucfirst($filter) ?>" status. Try a different filter.
            <?php endif; ?>
        </p>
        <?php if($filter == 'all'): ?>
        <a href ="/create-campaign.php" class="create-btn">
            <i class="fa-solid fa-plus"></i> Create Your First Campaign
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>

<script>
// Theme System
function getTheme() {
    return localStorage.getItem('crowdspark-theme') || 'light';
}

function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('crowdspark-theme', theme);
}

function toggleTheme() {
    const currentTheme = getTheme();
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    setTheme(newTheme);
}

// Initialize theme
(function() {
    const savedTheme = getTheme();
    setTheme(savedTheme);
})();

// Expose globally
window.CrowdSparkTheme = {
    toggle: toggleTheme,
    set: setTheme,
    get: getTheme
};
</script>




<?php require_once $_SERVER['DOCUMENT_ROOT']."/includes/footer.php"; ?>