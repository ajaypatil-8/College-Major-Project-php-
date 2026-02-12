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
    echo "<h2 style='text-align:center;margin-top:120px'>❌ Campaign not found</h2>";
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
/* ===== CONTAINER ===== */
.review-container{
    max-width: 1400px;
    margin: 100px auto 60px;
    padding: 20px;
}

/* ===== HEADER ===== */
.review-header{
    background: linear-gradient(135deg, #0f172a, #1e293b);
    border-radius: 24px;
    padding: 40px;
    color: #fff;
    margin-bottom: 40px;
    position: relative;
    overflow: hidden;
    animation: fadeIn 0.6s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

.review-header::before{
    content: "";
    position: absolute;
    top: -50%;
    right: -20%;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(245, 158, 11, 0.2), transparent 70%);
    border-radius: 50%;
}

.review-header h1{
    font-size: 36px;
    font-weight: 900;
    margin: 0 0 12px;
    position: relative;
    z-index: 1;
}

.review-breadcrumb{
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    opacity: 0.8;
    position: relative;
    z-index: 1;
}

.review-breadcrumb a{
    color: #f59e0b;
    text-decoration: none;
}

/* ===== GRID LAYOUT ===== */
.review-grid{
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
}

/* ===== CARD ===== */
.info-card{
    background: #fff;
    padding: 32px;
    border-radius: 24px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06);
    border: 1px solid #f1f5f9;
    margin-bottom: 30px;
    transition: all 0.3s ease;
    animation: fadeInUp 0.6s ease;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.info-card:hover{
    box-shadow: 0 16px 50px rgba(0, 0, 0, 0.08);
    transform: translateY(-4px);
}

.card-header{
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f1f5f9;
}

.card-title{
    font-size: 24px;
    font-weight: 900;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 12px;
}

.card-icon{
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: linear-gradient(135deg, #f59e0b, #fb923c);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.status-badge{
    padding: 8px 16px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 700;
    text-transform: capitalize;
}

.status-pending{
    background: rgba(245, 158, 11, 0.1);
    color: #d97706;
}

.status-approved{
    background: rgba(16, 185, 129, 0.1);
    color: #059669;
}

.status-rejected{
    background: rgba(239, 68, 68, 0.1);
    color: #dc2626;
}

/* ===== INFO SECTIONS ===== */
.campaign-title{
    font-size: 32px;
    font-weight: 900;
    color: #0f172a;
    margin-bottom: 16px;
    line-height: 1.2;
}

.campaign-meta{
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 24px;
}

.meta-item{
    background: #f8fafc;
    padding: 18px;
    border-radius: 14px;
    border-left: 4px solid #f59e0b;
}

.meta-label{
    font-size: 12px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
}

.meta-value{
    font-size: 18px;
    font-weight: 800;
    color: #0f172a;
}

.category-badge{
    display: inline-block;
    padding: 8px 16px;
    background: linear-gradient(135deg, #f59e0b, #fb923c);
    color: #fff;
    border-radius: 999px;
    font-size: 14px;
    font-weight: 700;
    margin-top: 12px;
}

/* ===== STORY BOX ===== */
.story-box{
    background: #f8fafc;
    padding: 28px;
    border-radius: 16px;
    line-height: 1.8;
    color: #334155;
    font-size: 15px;
    border-left: 4px solid #f59e0b;
}

/* ===== THUMBNAIL ===== */
.thumbnail-showcase{
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    margin-bottom: 20px;
}

.thumbnail-showcase img{
    width: 100%;
    height: 400px;
    object-fit: cover;
    display: block;
}

.thumbnail-overlay{
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
    padding: 24px;
    color: #fff;
}

.thumbnail-overlay h4{
    font-size: 18px;
    font-weight: 800;
    margin-bottom: 8px;
}

.thumbnail-overlay p{
    font-size: 14px;
    opacity: 0.9;
}

/* ===== MEDIA GRID ===== */
.media-gallery{
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 16px;
    margin-top: 20px;
}

.media-item{
    position: relative;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    cursor: pointer;
}

.media-item:hover{
    transform: scale(1.05);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
}

.media-item img{
    width: 100%;
    height: 180px;
    object-fit: cover;
    display: block;
}

.media-item video{
    width: 100%;
    height: 180px;
    object-fit: cover;
}

.media-badge{
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(0, 0, 0, 0.7);
    color: #fff;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
}

/* ===== SIDEBAR ===== */
.sidebar-card{
    background: #fff;
    padding: 28px;
    border-radius: 20px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06);
    border: 1px solid #f1f5f9;
    margin-bottom: 24px;
}

.sidebar-title{
    font-size: 18px;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-row{
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #f1f5f9;
    font-size: 14px;
}

.info-row:last-child{
    border-bottom: none;
}

.info-row label{
    color: #64748b;
    font-weight: 600;
}

.info-row span{
    color: #0f172a;
    font-weight: 700;
    text-align: right;
}

/* ===== CREATOR CARD ===== */
.creator-profile{
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px;
    background: linear-gradient(135deg, #f8fafc, #fff);
    border-radius: 16px;
    margin-bottom: 20px;
}

.creator-avatar{
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f59e0b, #fb923c);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    font-weight: 900;
    box-shadow: 0 8px 20px rgba(245, 158, 11, 0.3);
}

.creator-info h4{
    font-size: 18px;
    font-weight: 800;
    margin-bottom: 6px;
    color: #0f172a;
}

.creator-info p{
    font-size: 13px;
    color: #64748b;
    margin: 3px 0;
}

/* ===== ID PROOF ===== */
.id-proof-image{
    width: 100%;
    border-radius: 16px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    transition: all 0.3s ease;
}

.id-proof-image:hover{
    transform: scale(1.02);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
}

/* ===== ACTION BUTTONS ===== */
.action-panel{
    background: linear-gradient(135deg, #f8fafc, #fff);
    padding: 28px;
    border-radius: 20px;
    border: 2px solid #f1f5f9;
}

.action-title{
    font-size: 18px;
    font-weight: 800;
    margin-bottom: 20px;
    color: #0f172a;
    text-align: center;
}

.action-form{
    margin-bottom: 16px;
}

.btn-action{
    width: 100%;
    padding: 16px;
    border: none;
    border-radius: 14px;
    font-weight: 800;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.btn-approve{
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff;
    box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
}

.btn-approve:hover{
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(16, 185, 129, 0.4);
}

.btn-reject{
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #fff;
    box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3);
}

.btn-reject:hover{
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(239, 68, 68, 0.4);
}

.reject-reason{
    width: 100%;
    padding: 14px;
    border-radius: 12px;
    border: 2px solid #e2e8f0;
    margin-bottom: 12px;
    resize: vertical;
    min-height: 100px;
    font-family: inherit;
    font-size: 14px;
    transition: border-color 0.3s ease;
}

.reject-reason:focus{
    outline: none;
    border-color: #f59e0b;
}

/* ===== PROGRESS BAR ===== */
.progress-section{
    margin-top: 24px;
}

.progress-label{
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
    font-size: 14px;
    font-weight: 600;
}

.progress-bar{
    height: 12px;
    background: #f1f5f9;
    border-radius: 999px;
    overflow: hidden;
    position: relative;
}

.progress-fill{
    height: 100%;
    background: linear-gradient(90deg, #f59e0b, #fb923c);
    border-radius: 999px;
    transition: width 1s ease;
    position: relative;
    overflow: hidden;
}

.progress-fill::after{
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

/* ===== DONATIONS TABLE ===== */
.donations-table{
    margin-top: 20px;
}

.donations-table table{
    width: 100%;
    border-collapse: collapse;
}

.donations-table th{
    background: #f8fafc;
    padding: 12px;
    text-align: left;
    font-size: 12px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    border-bottom: 2px solid #e2e8f0;
}

.donations-table td{
    padding: 12px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 14px;
    color: #334155;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 1024px) {
    .review-grid{
        grid-template-columns: 1fr;
    }
    
    .campaign-meta{
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .review-header h1{
        font-size: 28px;
    }
    
    .campaign-title{
        font-size: 24px;
    }
    
    .thumbnail-showcase img{
        height: 250px;
    }
    
    .media-gallery{
        grid-template-columns: repeat(2, 1fr);
    }
}

/* ===== LIGHTBOX ===== */
.lightbox{
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

.lightbox.active{
    display: flex;
}

.lightbox img{
    max-width: 90%;
    max-height: 90%;
    border-radius: 12px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

.lightbox-close{
    position: absolute;
    top: 30px;
    right: 30px;
    font-size: 36px;
    color: #fff;
    cursor: pointer;
    z-index: 10001;
}
</style>

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
                                <div class="media-badge">Image</div>
                            <?php elseif($m['media_type'] == 'video'): ?>
                                <video controls>
                                    <source src="<?= $m['media_url'] ?>" type="video/mp4">
                                </video>
                                <div class="media-badge">Video</div>
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
                <p style="text-align: center; color: #64748b; font-size: 12px; margin-top: 12px;">
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
                    <span style="color: #059669;">₹<?= number_format($totalRaised) ?></span>
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
                <form method="POST" class="action-form" onsubmit="return confirm('Are you sure you want to APPROVE this campaign?')">
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
            <div class="action-panel" style="text-align: center; padding: 40px 20px;">
                <i class="fa-solid fa-<?= $campaign['status'] == 'approved' ? 'check-circle' : 'times-circle' ?>" 
                   style="font-size: 64px; color: <?= $campaign['status'] == 'approved' ? '#059669' : '#dc2626' ?>; margin-bottom: 16px;"></i>
                <h3 style="margin-bottom: 8px;">Campaign <?= ucfirst($campaign['status']) ?></h3>
                <p style="color: #64748b; font-size: 14px;">
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

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeLightbox();
    }
});
</script>

<?php require_once __DIR__."/../includes/footer.php"; ?>