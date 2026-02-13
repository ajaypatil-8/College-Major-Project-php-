<?php
session_start();
require_once __DIR__."/../config/db.php";

// Login required
if(!isset($_SESSION['user_id'])){
    header("Location: /CroudSpark-X/user/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get filter from URL
$filter = $_GET['filter'] ?? 'all';

// Fetch all campaigns for stats
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
        WHERE c.user_id=?
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

require_once __DIR__."/../includes/header.php";
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

/* Animated Background - Pink/Magenta theme */
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
    background: linear-gradient(45deg, #ec4899, #f472b6);
    top: -10%;
    left: -10%;
    animation-delay: 0s;
}

.orb-2 {
    width: 400px;
    height: 400px;
    background: linear-gradient(45deg, #db2777, #ec4899);
    bottom: -10%;
    right: -10%;
    animation-delay: 5s;
}

.orb-3 {
    width: 350px;
    height: 350px;
    background: linear-gradient(45deg, #be185d, #db2777);
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
    margin-bottom: 20px;
    background: linear-gradient(135deg, #fff, #ec4899);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    line-height: 1.1;
}

.campaigns-hero p {
    font-size: 1.25rem;
    color: #cbd5e1;
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
    background: rgba(20, 20, 30, 0.85);
    backdrop-filter: blur(20px);
    padding: 28px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(236, 72, 153, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.15);
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
    background: linear-gradient(90deg, #ec4899, #f472b6);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.stat-card:hover::before {
    transform: scaleX(1);
}

.stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 60px rgba(236, 72, 153, 0.15);
}

.stat-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.stat-info h3 {
    font-size: 36px;
    font-weight: 900;
    color: #fff;
    margin: 0;
    line-height: 1;
}

.stat-info p {
    color: #cbd5e1;
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
    border: 2px solid rgba(255, 255, 255, 0.15);
    color: #cbd5e1;
    background: rgba(20, 20, 30, 0.6);
}

.filter-tab:hover {
    border-color: #ec4899;
    color: #ec4899;
    transform: translateY(-2px);
}

.filter-tab.active {
    background: linear-gradient(135deg, #ec4899, #f472b6);
    border-color: transparent;
    color: #fff;
    box-shadow: 0 4px 12px rgba(236, 72, 153, 0.3);
}

.create-btn {
    padding: 12px 28px;
    border-radius: 50px;
    background: linear-gradient(135deg, #ec4899, #f472b6);
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
    background: rgba(20, 20, 30, 0.85);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(236, 72, 153, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.15);
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
    box-shadow: 0 20px 60px rgba(236, 72, 153, 0.2);
}

.campaign-card:hover::after {
    opacity: 1;
}

.campaign-thumbnail {
    position: relative;
    height: 220px;
    overflow: hidden;
    background: rgba(236, 72, 153, 0.1);
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
    color: #fff;
    margin: 0 0 10px;
    line-height: 1.3;
}

.campaign-description {
    color: #cbd5e1;
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
    background: rgba(30, 30, 40, 0.6);
    padding: 12px;
    border-radius: 10px;
    text-align: center;
    border: 1px solid rgba(255, 255, 255, 0.05);
}

.stat-item label {
    display: block;
    font-size: 11px;
    color: #94a3b8;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}

.stat-item strong {
    display: block;
    font-size: 18px;
    color: #fff;
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
    color: #94a3b8;
    font-weight: 600;
}

.progress-label strong {
    color: #ec4899;
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
    background: linear-gradient(90deg, #ec4899, #f472b6);
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
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    margin-top: 16px;
}

.campaign-date {
    font-size: 12px;
    color: #94a3b8;
    display: flex;
    align-items: center;
    gap: 6px;
}

.view-details {
    color: #ec4899;
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
    color: #fff;
    margin-bottom: 12px;
}

.empty-state p {
    font-size: 16px;
    color: #cbd5e1;
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
        
        <a href="/CroudSpark-X/creator/create-campaign.php" class="create-btn">
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
            $detailLink = "/CroudSpark-X/creator/view-campaign.php?id=" . $campaign['id'];
            ?>
            
            <a href="<?= $detailLink ?>" class="campaign-card">
                
                <!-- Thumbnail -->
                <div class="campaign-thumbnail">
                    <?php
                    $thumb = !empty($campaign['thumbnail']) 
                    ? $campaign['thumbnail'] 
                    : "/CroudSpark-X/assets/noimg.jpg";
                    ?>

                    <img src="<?= htmlspecialchars($thumb) ?>"
                    alt="<?= htmlspecialchars($campaign['title']) ?>"
                    onerror="this.src='/CroudSpark-X/assets/noimg.jpg'">

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
                You haven't created any campaigns yet. Start your first fundraising campaign today!
            <?php else: ?>
                No campaigns with "<?= ucfirst($filter) ?>" status. Try a different filter.
            <?php endif; ?>
        </p>
        <?php if($filter == 'all'): ?>
        <a href="/CroudSpark-X/creator/create-campaign.php" class="create-btn">
            <i class="fa-solid fa-plus"></i> Create Your First Campaign
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__."/../includes/footer.php"; ?>