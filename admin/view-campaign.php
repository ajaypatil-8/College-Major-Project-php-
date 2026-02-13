<?php
session_start();
require_once __DIR__."/../config/db.php";

if(!isset($_SESSION['user_id']) || $_SESSION['role']!="admin"){
    echo "<script>window.location='../public/index.php';</script>";
    exit;
}

$id = $_GET['id'] ?? 0;

/* CAMPAIGN */
$stmt = $pdo->prepare("SELECT c.*, u.name, u.email, u.phone, u.created_at as user_joined
    FROM campaigns c 
    LEFT JOIN users u ON c.user_id=u.id
    WHERE c.id=?");
$stmt->execute([$id]);
$campaign = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$campaign){
    echo "<h2 style='text-align:center;margin-top:120px;color:#fff;'>❌ Campaign not found</h2>";
    exit;
}

/* MEDIA */
$media = $pdo->prepare("SELECT * FROM campaign_media WHERE campaign_id=?");
$media->execute([$id]);
$media = $media->fetchAll(PDO::FETCH_ASSOC);

/* BANK */
$bank = $pdo->prepare("SELECT * FROM campaign_bank WHERE campaign_id=?");
$bank->execute([$id]);
$bank = $bank->fetch(PDO::FETCH_ASSOC);

/* DOC */
$doc = $pdo->prepare("SELECT * FROM campaign_documents WHERE campaign_id=?");
$doc->execute([$id]);
$doc = $doc->fetch(PDO::FETCH_ASSOC);

/* DONATIONS FOR THIS CAMPAIGN */
$donations = $pdo->prepare("SELECT d.*, u.name as donor_name 
    FROM donations d 
    LEFT JOIN users u ON d.user_id=u.id 
    WHERE d.campaign_id=? AND d.status='success' 
    ORDER BY d.id DESC LIMIT 10");
$donations->execute([$id]);
$donations = $donations->fetchAll(PDO::FETCH_ASSOC);

/* TOTAL RAISED */
$totalRaised = $pdo->prepare("SELECT IFNULL(SUM(amount),0) as total FROM donations WHERE campaign_id=? AND status='success'");
$totalRaised->execute([$id]);
$totalRaised = $totalRaised->fetchColumn();

/* APPROVE */
if(isset($_POST['approve'])){
    $pdo->prepare("UPDATE campaigns SET status='approved', approved_at=NOW() WHERE id=?")->execute([$id]);
    echo "<script>alert('✅ Campaign Approved Successfully!'); window.location='admin-dashboard.php';</script>";
    exit;
}

/* REJECT */
if(isset($_POST['reject'])){
    $reason = $_POST['reason'] ?? 'No reason provided';
    $pdo->prepare("UPDATE campaigns SET status='rejected', reject_reason=? WHERE id=?")->execute([$reason, $id]);
    echo "<script>alert('❌ Campaign Rejected'); window.location='admin-dashboard.php';</script>";
    exit;
}
?>

<?php require_once __DIR__."/../includes/header.php"; ?>

<style>
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

/* Animated Background */
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
    background: linear-gradient(45deg, #ef4444, #f87171);
    top: -10%;
    left: -10%;
}

.orb-2 {
    width: 400px;
    height: 400px;
    background: linear-gradient(45deg, #ec4899, #f472b6);
    bottom: -10%;
    right: -10%;
    animation-delay: 5s;
}

.orb-3 {
    width: 350px;
    height: 350px;
    background: linear-gradient(45deg, #dc2626, #ef4444);
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

/* Container - FIXED WIDTH */
.review-container {
    position: relative;
    z-index: 1;
    max-width: 1600px; /* Increased from 1400px */
    margin: 0 auto;
    padding: 120px 20px 80px; /* Reduced horizontal padding */
}

/* Header */
.review-header {
    background: rgba(20, 20, 30, 0.85);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    padding: 30px 40px;
    color: #fff;
    margin-bottom: 40px;
    animation: fadeInUp 0.6s ease;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.review-header h1 {
    font-family: 'Playfair Display', serif;
    font-size: 2.2rem;
    font-weight: 900;
    margin: 0 0 8px;
    background: linear-gradient(135deg, #fff, #ef4444);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.review-breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #cbd5e1;
}

.review-breadcrumb a {
    color: #ef4444;
    text-decoration: none;
}

/* Grid Layout - FIXED RESPONSIVE */
.review-grid {
    display: grid;
    grid-template-columns: 1fr 400px; /* Fixed sidebar width */
    gap: 24px;
}

/* Card */
.info-card {
    background: rgba(20, 20, 30, 0.85);
    backdrop-filter: blur(20px);
    padding: 28px;
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.15);
    margin-bottom: 24px;
    transition: all 0.3s ease;
    animation: fadeInUp 0.6s ease;
    width: 100%; /* Ensure full width */
    overflow: hidden; /* Prevent overflow */
}

.info-card:hover {
    transform: translateY(-4px);
    border-color: rgba(239, 68, 68, 0.4);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 2px solid rgba(255, 255, 255, 0.1);
    flex-wrap: wrap;
    gap: 12px;
}

.card-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    font-weight: 900;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 12px;
}

.card-icon {
    width: 36px;
    height: 36px;
    border-radius: 12px;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}

.status-badge {
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    text-transform: capitalize;
}

.status-pending {
    background: rgba(245, 158, 11, 0.2);
    color: #f59e0b;
}

.status-approved {
    background: rgba(16, 185, 129, 0.2);
    color: #10b981;
}

.status-rejected {
    background: rgba(239, 68, 68, 0.2);
    color: #ef4444;
}

/* Campaign Info */
.campaign-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
    font-weight: 900;
    color: #fff;
    margin-bottom: 16px;
    line-height: 1.3;
    word-wrap: break-word;
}

.campaign-meta {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
    margin-top: 20px;
}

.meta-item {
    background: rgba(30, 30, 40, 0.6);
    padding: 16px;
    border-radius: 12px;
    border-left: 4px solid #ef4444;
}

.meta-label {
    font-size: 11px;
    font-weight: 700;
    color: #cbd5e1;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
}

.meta-value {
    font-size: 16px;
    font-weight: 800;
    color: #fff;
    word-wrap: break-word;
}

.category-badge {
    display: inline-block;
    padding: 6px 14px;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #fff;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 700;
    margin-top: 12px;
}

/* Story Box */
.story-box {
    background: rgba(30, 30, 40, 0.6);
    padding: 24px;
    border-radius: 14px;
    line-height: 1.8;
    color: #cbd5e1;
    font-size: 14px;
    border-left: 4px solid #ef4444;
    max-height: 500px;
    overflow-y: auto;
}

.story-box::-webkit-scrollbar {
    width: 6px;
}

.story-box::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
}

.story-box::-webkit-scrollbar-thumb {
    background: rgba(239, 68, 68, 0.3);
    border-radius: 10px;
}

/* Thumbnail */
.thumbnail-showcase {
    position: relative;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    margin-bottom: 20px;
}

.thumbnail-showcase img {
    width: 100%;
    height: 350px;
    object-fit: cover;
    display: block;
}

.thumbnail-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
    padding: 20px;
    color: #fff;
}

.thumbnail-overlay h4 {
    font-size: 16px;
    margin-bottom: 6px;
}

.thumbnail-overlay p {
    font-size: 13px;
}

/* Media Grid */
.media-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 14px;
    margin-top: 16px;
}

.media-item {
    position: relative;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
    transition: all 0.3s ease;
    cursor: pointer;
}

.media-item:hover {
    transform: scale(1.05);
}

.media-item img {
    width: 100%;
    height: 150px;
    object-fit: cover;
    display: block;
}

.media-item video {
    width: 100%;
    height: 150px;
}

/* Sidebar */
.sidebar-card {
    background: rgba(20, 20, 30, 0.85);
    backdrop-filter: blur(20px);
    padding: 24px;
    border-radius: 18px;
    border: 1px solid rgba(255, 255, 255, 0.15);
    margin-bottom: 20px;
}

.sidebar-title {
    font-size: 16px;
    font-weight: 800;
    color: #fff;
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    font-size: 13px;
    gap: 10px;
}

.info-row:last-child {
    border-bottom: none;
}

.info-row label {
    color: #cbd5e1;
    font-weight: 600;
    flex-shrink: 0;
}

.info-row span {
    color: #fff;
    font-weight: 700;
    text-align: right;
    word-wrap: break-word;
}

/* Creator Card */
.creator-profile {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 18px;
    background: rgba(239, 68, 68, 0.1);
    border-radius: 14px;
    margin-bottom: 16px;
}

.creator-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: 900;
    flex-shrink: 0;
}

.creator-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.creator-info h4 {
    font-size: 16px;
    font-weight: 800;
    margin-bottom: 4px;
    color: #fff;
}

.creator-info p {
    font-size: 12px;
    color: #cbd5e1;
    margin: 2px 0;
}

/* ID Proof */
.id-proof-image {
    width: 100%;
    border-radius: 14px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
    cursor: pointer;
    transition: all 0.3s ease;
}

.id-proof-image:hover {
    transform: scale(1.02);
}

/* Action Panel */
.action-panel {
    background: rgba(30, 30, 40, 0.8);
    backdrop-filter: blur(20px);
    padding: 24px;
    border-radius: 18px;
    border: 2px solid rgba(239, 68, 68, 0.3);
}

.action-title {
    font-size: 16px;
    font-weight: 800;
    margin-bottom: 16px;
    color: #fff;
    text-align: center;
}

.btn-action {
    width: 100%;
    padding: 14px;
    border: none;
    border-radius: 12px;
    font-weight: 800;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-bottom: 10px;
}

.btn-approve {
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff;
    box-shadow: 0 6px 16px rgba(16, 185, 129, 0.3);
}

.btn-approve:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(16, 185, 129, 0.4);
}

.btn-reject {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #fff;
    box-shadow: 0 6px 16px rgba(239, 68, 68, 0.3);
}

.btn-reject:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(239, 68, 68, 0.4);
}

.reject-reason {
    width: 100%;
    padding: 12px;
    border-radius: 10px;
    border: 2px solid rgba(255, 255, 255, 0.2);
    background: rgba(30, 30, 40, 0.6);
    color: #fff;
    margin-bottom: 10px;
    resize: vertical;
    min-height: 90px;
    font-family: inherit;
    font-size: 13px;
}

.reject-reason:focus {
    outline: none;
    border-color: #ef4444;
}

/* Progress Bar */
.progress-section {
    margin-top: 20px;
}

.progress-label {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    font-size: 13px;
    font-weight: 600;
}

.progress-bar {
    height: 10px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 999px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #ef4444, #f87171);
    border-radius: 999px;
    transition: width 1s ease;
    position: relative;
    overflow: hidden;
}

.progress-fill::after {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

/* Donations Table */
.donations-table {
    margin-top: 16px;
    overflow-x: auto;
}

.donations-table table {
    width: 100%;
    border-collapse: collapse;
}

.donations-table th {
    background: rgba(239, 68, 68, 0.2);
    padding: 10px;
    text-align: left;
    font-size: 11px;
    font-weight: 700;
    color: #fff;
    text-transform: uppercase;
}

.donations-table td {
    padding: 10px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    font-size: 13px;
    color: #cbd5e1;
}

/* Lightbox */
.lightbox {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.95);
    z-index: 10000;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.lightbox.active {
    display: flex;
}

.lightbox img {
    max-width: 90%;
    max-height: 90%;
    border-radius: 12px;
}

.lightbox-close {
    position: absolute;
    top: 30px;
    right: 30px;
    font-size: 36px;
    color: #fff;
    cursor: pointer;
    z-index: 10001;
}

/* Responsive */
@media (max-width: 1200px) {
    .review-grid {
        grid-template-columns: 1fr;
    }
    
    .review-container {
        padding: 120px 15px 60px;
    }
}

@media (max-width: 768px) {
    .review-header {
        padding: 20px 24px;
    }
    
    .review-header h1 {
        font-size: 1.8rem;
    }
    
    .campaign-title {
        font-size: 1.5rem;
    }
    
    .campaign-meta {
        grid-template-columns: 1fr;
    }
    
    .thumbnail-showcase img {
        height: 250px;
    }
    
    .media-gallery {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .info-card {
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

<div class="review-container">

    <!-- HEADER -->
    <div class="review-header">
        <h1>🛡️ Campaign Review Panel</h1>
        <div class="review-breadcrumb">
            <a href="/CroudSpark-X/admin/admin-dashboard.php">Dashboard</a>
            <i class="fa-solid fa-chevron-right"></i>
            <span>Review Campaign</span>
        </div>
    </div>

    <!-- GRID -->
    <div class="review-grid">
        
        <!-- LEFT COLUMN -->
        <div>

            <!-- CAMPAIGN INFO -->
            <div class="info-card">
                <div class="card-header">
                    <h2 class="card-title">
                        <div class="card-icon"><i class="fa-solid fa-info-circle"></i></div>
                        Campaign Details
                    </h2>
                    <span class="status-badge status-<?= $campaign['status'] ?>">
                        <?= ucfirst($campaign['status']) ?>
                    </span>
                </div>

                <h3 class="campaign-title"><?= htmlspecialchars($campaign['title']) ?></h3>
                <span class="category-badge"><i class="fa-solid fa-tag"></i> <?= $campaign['category'] ?></span>

                <div class="campaign-meta">
                    <div class="meta-item">
                        <div class="meta-label">Goal Amount</div>
                        <div class="meta-value">₹<?= number_format($campaign['goal']) ?></div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Raised</div>
                        <div class="meta-value">₹<?= number_format($totalRaised) ?></div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Location</div>
                        <div class="meta-value"><?= htmlspecialchars($campaign['location']) ?></div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">End Date</div>
                        <div class="meta-value"><?= date('d M Y', strtotime($campaign['end_date'])) ?></div>
                    </div>
                </div>

                <!-- Progress -->
                <?php 
                $progress = $campaign['goal'] > 0 ? ($totalRaised / $campaign['goal']) * 100 : 0;
                $progress = min($progress, 100);
                ?>
                <div class="progress-section">
                    <div class="progress-label">
                        <span>Funding Progress</span>
                        <span><?= number_format($progress, 1) ?>%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?= $progress ?>%"></div>
                    </div>
                </div>
            </div>

            <!-- STORY -->
            <div class="info-card">
                <div class="card-header">
                    <h2 class="card-title">
                        <div class="card-icon"><i class="fa-solid fa-book"></i></div>
                        Campaign Story
                    </h2>
                </div>
                <div class="story-box">
                    <?= nl2br(htmlspecialchars($campaign['story'])) ?>
                </div>
            </div>

            <!-- THUMBNAIL -->
            <div class="info-card">
                <div class="card-header">
                    <h2 class="card-title">
                        <div class="card-icon"><i class="fa-solid fa-image"></i></div>
                        Campaign Thumbnail
                    </h2>
                </div>
                <div class="thumbnail-showcase">
                    <img src="<?= $campaign['thumbnail'] ?? '/CroudSpark-X/assets/noimg.jpg' ?>" 
                         alt="Campaign Thumbnail"
                         onclick="openLightbox(this.src)">
                    <div class="thumbnail-overlay">
                        <h4><?= htmlspecialchars($campaign['title']) ?></h4>
                        <p>Click to view full size</p>
                    </div>
                </div>
            </div>

            <!-- MEDIA GALLERY -->
            <?php if($media): ?>
            <div class="info-card">
                <div class="card-header">
                    <h2 class="card-title">
                        <div class="card-icon"><i class="fa-solid fa-photo-film"></i></div>
                        Media Gallery (<?= count($media) ?> items)
                    </h2>
                </div>
                <div class="media-gallery">
                    <?php foreach($media as $m): ?>
                        <div class="media-item">
                            <?php if($m['media_type'] == 'image'): ?>
                                <img src="<?= $m['media_url'] ?>" 
                                     alt="Campaign Media"
                                     onclick="openLightbox(this.src)">
                            <?php elseif($m['media_type'] == 'video'): ?>
                                <video controls>
                                    <source src="<?= $m['media_url'] ?>" type="video/mp4">
                                </video>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- DONATIONS -->
            <?php if($donations): ?>
            <div class="info-card">
                <div class="card-header">
                    <h2 class="card-title">
                        <div class="card-icon"><i class="fa-solid fa-heart"></i></div>
                        Recent Donations (<?= count($donations) ?>)
                    </h2>
                </div>
                <div class="donations-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Donor</th>
                                <th>Amount</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($donations as $d): ?>
                            <tr>
                                <td><?= htmlspecialchars($d['donor_name']) ?></td>
                                <td><strong>₹<?= number_format($d['amount']) ?></strong></td>
                                <td><?= date('d M Y', strtotime($d['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- RIGHT COLUMN (SIDEBAR) -->
        <div>

            <!-- CREATOR INFO -->
            <div class="sidebar-card">
                <h3 class="sidebar-title">
                    <i class="fa-solid fa-user"></i> Creator Information
                </h3>
                
                <div class="creator-profile">
                    <div class="creator-avatar">
                        <?= strtoupper(substr($campaign['name'], 0, 1)) ?>
                    </div>
                    <div class="creator-info">
                        <h4><?= htmlspecialchars($campaign['name']) ?></h4>
                        <p><i class="fa-solid fa-envelope"></i> <?= htmlspecialchars($campaign['email']) ?></p>
                        <p><i class="fa-solid fa-phone"></i> <?= htmlspecialchars($campaign['phone']) ?></p>
                        <p><i class="fa-solid fa-calendar"></i> Joined: <?= date('M Y', strtotime($campaign['user_joined'])) ?></p>
                    </div>
                </div>
            </div>

            <!-- BANK DETAILS -->
            <?php if($bank): ?>
            <div class="sidebar-card">
                <h3 class="sidebar-title">
                    <i class="fa-solid fa-building-columns"></i> Bank Details
                </h3>
                <div class="info-row">
                    <label>UPI ID</label>
                    <span><?= htmlspecialchars($bank['upi_id']) ?></span>
                </div>
                <div class="info-row">
                    <label>Account Number</label>
                    <span><?= htmlspecialchars($bank['account_no']) ?></span>
                </div>
                <div class="info-row">
                    <label>IFSC Code</label>
                    <span><?= htmlspecialchars($bank['ifsc']) ?></span>
                </div>
                <div class="info-row">
                    <label>Account Holder</label>
                    <span><?= htmlspecialchars($bank['holder_name']) ?></span>
                </div>
            </div>
            <?php endif; ?>

            <!-- ID PROOF -->
            <?php if($doc): ?>
            <div class="sidebar-card">
                <h3 class="sidebar-title">
                    <i class="fa-solid fa-id-card"></i> Identity Document
                </h3>
                <img src="<?= $doc['doc_url'] ?>" 
                     alt="ID Proof" 
                     class="id-proof-image"
                     onclick="openLightbox(this.src)">
                <p style="text-align: center; color: #cbd5e1; font-size: 11px; margin-top: 10px;">
                    <i class="fa-solid fa-info-circle"></i> Click to view full size
                </p>
            </div>
            <?php endif; ?>

            <!-- CAMPAIGN STATS -->
            <div class="sidebar-card">
                <h3 class="sidebar-title">
                    <i class="fa-solid fa-chart-simple"></i> Campaign Stats
                </h3>
                <div class="info-row">
                    <label>Total Raised</label>
                    <span style="color: #10b981;">₹<?= number_format($totalRaised) ?></span>
                </div>
                <div class="info-row">
                    <label>Goal Amount</label>
                    <span>₹<?= number_format($campaign['goal']) ?></span>
                </div>
                <div class="info-row">
                    <label>Donations</label>
                    <span><?= count($donations) ?></span>
                </div>
                <div class="info-row">
                    <label>Created On</label>
                    <span><?= date('d M Y', strtotime($campaign['created_at'])) ?></span>
                </div>
                <div class="info-row">
                    <label>Days Remaining</label>
                    <span>
                        <?php 
                        $daysLeft = ceil((strtotime($campaign['end_date']) - time()) / 86400);
                        echo max(0, $daysLeft) . ' days';
                        ?>
                    </span>
                </div>
            </div>

            <!-- ADMIN ACTIONS -->
            <?php if($campaign['status'] == 'pending'): ?>
            <div class="action-panel">
                <h3 class="action-title">⚡ Admin Action Required</h3>
                
                <!-- Approve -->
                <form method="POST" onsubmit="return confirm('Are you sure you want to APPROVE this campaign?')">
                    <button type="submit" name="approve" class="btn-action btn-approve">
                        <i class="fa-solid fa-check-circle"></i> Approve Campaign
                    </button>
                </form>

                <!-- Reject -->
                <form method="POST" onsubmit="return confirm('Are you sure you want to REJECT this campaign?')">
                    <textarea 
                        name="reason" 
                        class="reject-reason" 
                        placeholder="Enter reason for rejection (required)..."
                        required
                    ></textarea>
                    <button type="submit" name="reject" class="btn-action btn-reject">
                        <i class="fa-solid fa-times-circle"></i> Reject Campaign
                    </button>
                </form>
            </div>
            <?php else: ?>
            <div class="action-panel" style="text-align: center; padding: 32px 20px;">
                <i class="fa-solid fa-<?= $campaign['status'] == 'approved' ? 'check-circle' : 'times-circle' ?>" 
                   style="font-size: 56px; color: <?= $campaign['status'] == 'approved' ? '#10b981' : '#ef4444' ?>; margin-bottom: 14px;"></i>
                <h3 style="margin-bottom: 6px; font-size: 16px;">Campaign <?= ucfirst($campaign['status']) ?></h3>
                <p style="color: #cbd5e1; font-size: 13px;">
                    <?= $campaign['status'] == 'approved' ? 'This campaign has been approved and is now live.' : 'This campaign was rejected.' ?>
                </p>
            </div>
            <?php endif; ?>

        </div>

    </div>

</div>

<!-- LIGHTBOX -->
<div class="lightbox" id="lightbox" onclick="closeLightbox()">
    <span class="lightbox-close">&times;</span>
    <img src="" alt="Full View" id="lightboxImage">
</div>

<script>
function openLightbox(src) {
    document.getElementById('lightbox').classList.add('active');
    document.getElementById('lightboxImage').src = src;
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    document.getElementById('lightbox').classList.remove('active');
    document.body.style.overflow = 'auto';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeLightbox();
    }
});
</script>

<?php require_once __DIR__."/../includes/footer.php"; ?>