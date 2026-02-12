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
.campaign-detail-container {
    max-width: 1100px;
    margin: 2.5rem auto;
    padding: 0 1.5rem;
}

.back-link {
    display: inline-block;
    margin-bottom: 1.5rem;
    color: #f59e0b;
    font-weight: 600;
    text-decoration: none;
}

.back-link:hover {
    text-decoration: underline;
}

.campaign-hero {
    background: white;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.08);
    overflow: hidden;
    margin-bottom: 2rem;
}

.hero-image {
    width: 100%;
    height: 380px;
    object-fit: cover;
}

.detail-content {
    padding: 2.5rem;
}

.title {
    font-size: 2.4rem;
    font-weight: 800;
    margin: 0 0 1rem;
    color: #0f172a;
}

.meta {
    color: #64748b;
    font-size: 1.05rem;
    margin-bottom: 1.8rem;
}

.stats-bar {
    display: flex;
    gap: 3rem;
    flex-wrap: wrap;
    margin: 2rem 0 3rem;
    padding: 1.8rem;
    background: #fdfaf7;
    border-radius: 12px;
}

.stat-block {
    flex: 1;
    min-width: 160px;
}

.stat-number {
    font-size: 2.6rem;
    font-weight: 800;
    color: #f59e0b;
}

.stat-label {
    color: #64748b;
    font-size: 1rem;
}

.progress-large {
    height: 14px;
    background: #e5e7eb;
    border-radius: 7px;
    overflow: hidden;
    margin: 1.2rem 0;
}

.progress-fill-large {
    height: 100%;
    background: linear-gradient(90deg, #f59e0b, #fbbf24);
}

.description {
    line-height: 1.7;
    color: #334155;
    font-size: 1.08rem;
    margin-bottom: 2.5rem;
}

.donors-section {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 8px 32px rgba(0,0,0,0.06);
}

.donor-row {
    display: flex;
    justify-content: space-between;
    padding: 1rem 0;
    border-bottom: 1px solid #e2e8f0;
}

.donor-name {
    font-weight: 600;
}

.donor-amount {
    color: #16a34a;
    font-weight: 700;
}

@media (max-width: 768px) {
    .stats-bar { gap: 1.5rem; justify-content: center; text-align: center; }
    .hero-image { height: 260px; }
}
</style>

<div class="campaign-detail-container">

    <a href="creator-dashboard.php" class="back-link">← Back to Dashboard</a>

    <div class="campaign-hero">
        <img src="<?= htmlspecialchars($campaign['thumbnail_url']) ?>"
             alt="<?= htmlspecialchars($campaign['title'] ?? 'Campaign image') ?>"
             class="hero-image"
             onerror="this.onerror=null; this.src='/assets/placeholder-large.jpg';">
    </div>

    <div class="detail-content">

        <h1 class="title"><?= htmlspecialchars($campaign['title'] ?? 'Untitled Campaign') ?></h1>

        <div class="meta">
            Created: <?= date('d M Y', strtotime($campaign['created_at'] ?? 'now')) ?> •
            Category: <?= htmlspecialchars($campaign['category'] ?? 'General') ?> •
            Status: <strong class="status-<?= htmlspecialchars($campaign['status'] ?? 'unknown') ?>">
                <?= ucfirst($campaign['status'] ?? 'Unknown') ?>
            </strong>
        </div>

        <div class="stats-bar">
            <div class="stat-block">
                <div class="stat-number">₹<?= number_format($raised) ?></div>
                <div class="stat-label">raised of ₹<?= number_format($goal) ?> goal</div>
            </div>
            <div class="stat-block">
                <div class="progress-large">
                    <div class="progress-fill-large" style="width: <?= $percent ?>%"></div>
                </div>
                <div class="stat-label"><?= round($percent) ?>% funded</div>
            </div>
            <div class="stat-block">
                <div class="stat-number"><?= count($donors) ?>+</div>
                <div class="stat-label">supporters</div>
            </div>
        </div>

        <div class="description">
            <?= nl2br(htmlspecialchars($campaign['description'] ?? 'No description provided.')) ?>
        </div>

        <?php if (!empty($campaign['reject_reason']) && $campaign['status'] === 'rejected'): ?>
        <div style="background:#fee2e2; padding:1.5rem; border-radius:12px; margin:2rem 0; color:#991b1b;">
            <strong>Rejection reason:</strong><br>
            <?= htmlspecialchars($campaign['reject_reason']) ?>
        </div>
        <?php endif; ?>

        <?php if ($donors): ?>
        <div class="donors-section">
            <h2 style="margin-top:0; font-size:1.5rem;">Recent Supporters</h2>
            <?php foreach ($donors as $d): ?>
            <div class="donor-row">
                <div class="donor-name"><?= htmlspecialchars($d['name'] ?? 'Anonymous') ?></div>
                <div class="donor-amount">₹<?= number_format($d['amount'] ?? 0) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>