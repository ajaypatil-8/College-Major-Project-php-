<?php
session_start();
require_once __DIR__ . "/../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'creator') {
    header("Location: ../public/index.php");
    exit;
}

$creator_id = $_SESSION['user_id'];

// ─── Helper ────────────────────────────────────────────────────────────────
function queryScalar($pdo, $sql, array $params = []): int {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int) ($stmt->fetchColumn() ?? 0);
}

// ─── Stats ─────────────────────────────────────────────────────────────────
$total_campaigns   = queryScalar($pdo, "SELECT COUNT(*) FROM campaigns WHERE user_id = ?", [$creator_id]);
$approved          = queryScalar($pdo, "SELECT COUNT(*) FROM campaigns WHERE user_id = ? AND status = 'approved'", [$creator_id]);
$pending           = queryScalar($pdo, "SELECT COUNT(*) FROM campaigns WHERE user_id = ? AND status = 'pending'", [$creator_id]);
$rejected          = queryScalar($pdo, "SELECT COUNT(*) FROM campaigns WHERE user_id = ? AND status = 'rejected'", [$creator_id]);

$total_funds       = queryScalar($pdo, "
    SELECT COALESCE(SUM(d.amount), 0)
    FROM donations d JOIN campaigns c ON d.campaign_id = c.id
    WHERE c.user_id = ? AND d.status = 'success'
", [$creator_id]);

$total_donations   = queryScalar($pdo, "
    SELECT COUNT(*)
    FROM donations d JOIN campaigns c ON d.campaign_id = c.id
    WHERE c.user_id = ? AND d.status = 'success'
", [$creator_id]);

$success_rate = $total_campaigns > 0 ? round(($approved / $total_campaigns) * 100) : 0;

// ─── Campaigns & Recent Donations ──────────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT c.*,
           COALESCE((SELECT SUM(amount) FROM donations 
                     WHERE campaign_id = c.id AND status = 'success'), 0) AS raised,
           (SELECT media_url FROM campaign_media 
            WHERE campaign_id = c.id AND media_type = 'thumbnail' 
            LIMIT 1) AS thumbnail_url
    FROM campaigns c
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$creator_id]);
$campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT d.amount, u.name, c.title
    FROM donations d
    JOIN users u ON d.user_id = u.id
    JOIN campaigns c ON d.campaign_id = c.id
    WHERE c.user_id = ? AND d.status = 'success'
    ORDER BY d.created_at DESC
    LIMIT 8
");
$stmt->execute([$creator_id]);
$recent = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . "/../includes/header.php";
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@400;500;600;700;800;900&display=swap');

/* ===== BASE STYLES ===== */
* { 
    box-sizing: border-box; 
    margin: 0;
    padding: 0;
}

body {
    background: #0f0f0f;
    color: #fff;
    font-family: 'DM Sans', sans-serif;
    line-height: 1.6;
    position: relative;
    overflow-x: hidden;
}

/* Animated Background - Green/Emerald theme */
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
    background: linear-gradient(45deg, #10b981, #34d399);
    top: -10%;
    left: -10%;
    animation-delay: 0s;
}

.orb-2 {
    width: 400px;
    height: 400px;
    background: linear-gradient(45deg, #059669, #10b981);
    bottom: -10%;
    right: -10%;
    animation-delay: 5s;
}

.orb-3 {
    width: 350px;
    height: 350px;
    background: linear-gradient(45deg, #047857, #059669);
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
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
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

@keyframes shimmer {
    0% { background-position: -1000px 0; }
    100% { background-position: 1000px 0; }
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.8; }
}

@keyframes scaleIn {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

/* ===== DASHBOARD CONTAINER ===== */
.dashboard-container {
    position: relative;
    z-index: 1;
    max-width: 1440px;
    margin: 0 auto;
    padding: 2.5rem 1.5rem 5rem;
    animation: fadeInUp 0.6s ease-out;
}

/* ===== HEADER ===== */
.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 3rem;
    flex-wrap: wrap;
    gap: 1.5rem;
    animation: slideInRight 0.7s ease-out;
    padding: 2rem 2.5rem;
    background: rgba(20, 20, 30, 0.85);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    border: 1px solid rgba(255, 255, 255, 0.15);
    box-shadow: 0 20px 60px rgba(16, 185, 129, 0.12);
}

.dashboard-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #10b981, #34d399);
    border-radius: 24px 24px 0 0;
}

.dashboard-header h1 {
    margin: 0;
    font-family: 'Playfair Display', serif;
    font-size: 3rem;
    font-weight: 900;
    letter-spacing: -0.05em;
    background: linear-gradient(45deg, #10b981, #34d399);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* ===== PRIMARY BUTTON ===== */
.btn-primary {
    background: linear-gradient(45deg, #10b981, #34d399);
    color: white;
    padding: 1.2rem 2.5rem;
    border-radius: 50px;
    font-weight: 700;
    font-size: 1.05rem;
    text-decoration: none;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 10px 40px rgba(16, 185, 129, 0.35);
    border: none;
    position: relative;
    overflow: hidden;
    display: inline-block;
}

.btn-primary::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    transition: left 0.5s;
}

.btn-primary:hover::before {
    left: 100%;
}

.btn-primary:hover {
    transform: translateY(-4px) scale(1.05);
    box-shadow: 0 20px 60px rgba(16, 185, 129, 0.5);
}

.btn-primary:active {
    transform: translateY(-2px) scale(1.02);
}

/* ===== STATS GRID ===== */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.stat-card {
    background: rgba(20, 20, 30, 0.85);
    backdrop-filter: blur(20px);
    padding: 2.5rem 2rem;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(16, 185, 129, 0.08);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: 1px solid rgba(255, 255, 255, 0.15);
    position: relative;
    overflow: hidden;
    animation: fadeInUp 0.6s ease-out backwards;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(45deg, #10b981, #34d399);
    transform: scaleX(0);
    transition: transform 0.4s ease;
}

.stat-card:hover::before {
    transform: scaleX(1);
}

.stat-card:nth-child(1) { animation-delay: 0.1s; }
.stat-card:nth-child(2) { animation-delay: 0.2s; }
.stat-card:nth-child(3) { animation-delay: 0.3s; }
.stat-card:nth-child(4) { animation-delay: 0.4s; }
.stat-card:nth-child(5) { animation-delay: 0.5s; }
.stat-card:nth-child(6) { animation-delay: 0.6s; }
.stat-card:nth-child(7) { animation-delay: 0.7s; }

.stat-card:hover {
    transform: translateY(-12px) scale(1.02);
    box-shadow: 0 20px 60px rgba(16, 185, 129, 0.2);
}

.stat-label {
    color: #cbd5e1;
    font-size: 0.95rem;
    font-weight: 600;
    margin-bottom: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.stat-value {
    font-size: 3rem;
    font-weight: 900;
    line-height: 1;
    background: linear-gradient(45deg, #10b981, #34d399);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.stat-raised .stat-value { 
    background: linear-gradient(45deg, #10b981, #34d399); 
    -webkit-background-clip: text; 
    -webkit-text-fill-color: transparent; 
}

.stat-approved .stat-value { 
    background: linear-gradient(45deg, #10b981, #34d399); 
    -webkit-background-clip: text; 
    -webkit-text-fill-color: transparent; 
}

.stat-pending .stat-value { 
    background: linear-gradient(45deg, #f59e0b, #fbbf24); 
    -webkit-background-clip: text; 
    -webkit-text-fill-color: transparent; 
}

.stat-rejected .stat-value { 
    background: linear-gradient(45deg, #ef4444, #f87171); 
    -webkit-background-clip: text; 
    -webkit-text-fill-color: transparent; 
}

/* ===== CONTENT LAYOUT ===== */
.content-layout {
    display: grid;
    grid-template-columns: 2.1fr 1fr;
    gap: 2.5rem;
}

/* ===== CARD ===== */
.card {
    background: rgba(20, 20, 30, 0.85);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 2.5rem;
    box-shadow: 0 10px 30px rgba(16, 185, 129, 0.08);
    margin-bottom: 2.5rem;
    border: 1px solid rgba(255, 255, 255, 0.15);
    transition: all 0.3s ease;
    animation: fadeInUp 0.8s ease-out;
    position: relative;
    overflow: hidden;
}

.card::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(16, 185, 129, 0.03) 0%, transparent 70%);
    pointer-events: none;
}

.card:hover {
    box-shadow: 0 20px 60px rgba(16, 185, 129, 0.15);
    transform: translateY(-4px);
}

.card h2 {
    margin: 0 0 2rem;
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
    font-weight: 800;
    background: linear-gradient(45deg, #10b981, #34d399);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* ===== CAMPAIGN TABLE ===== */
.campaign-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 1rem;
}

.campaign-table th {
    text-align: left;
    padding: 1.2rem 1.5rem;
    background: rgba(16, 185, 129, 0.08);
    color: #fff;
    font-size: 0.9rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    border: none;
}

.campaign-table th:first-child { border-radius: 16px 0 0 16px; }
.campaign-table th:last-child { border-radius: 0 16px 16px 0; }

.campaign-table td {
    padding: 1.8rem 1.5rem;
    border: none;
    vertical-align: middle;
    background: rgba(20, 20, 30, 0.6);
    transition: all 0.3s ease;
}

.campaign-row {
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.campaign-row td:first-child { border-radius: 16px 0 0 16px; }
.campaign-row td:last-child { border-radius: 0 16px 16px 0; }

.campaign-row:hover {
    transform: scale(1.02);
    box-shadow: 0 10px 40px rgba(16, 185, 129, 0.12);
}

.campaign-row:hover td {
    background: rgba(16, 185, 129, 0.03);
}

/* ===== CAMPAIGN PREVIEW ===== */
.campaign-preview {
    display: flex;
    gap: 1.5rem;
    align-items: center;
}

.thumb {
    width: 90px;
    height: 68px;
    object-fit: cover;
    border-radius: 16px;
    background: rgba(16, 185, 129, 0.1);
    box-shadow: 0 8px 24px rgba(16, 185, 129, 0.15);
    transition: all 0.3s ease;
    border: 2px solid rgba(16, 185, 129, 0.15);
}

.thumb:hover {
    transform: scale(1.1) rotate(2deg);
    box-shadow: 0 12px 32px rgba(16, 185, 129, 0.25);
}

.campaign-title {
    font-weight: 700;
    font-size: 1.1rem;
    margin: 0 0 0.8rem;
    color: #fff;
}

/* ===== PROGRESS BAR ===== */
.progress-bar {
    height: 12px;
    background: rgba(16, 185, 129, 0.1);
    border-radius: 20px;
    overflow: hidden;
    margin: 0.8rem 0;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #10b981, #34d399);
    transition: width 1s cubic-bezier(0.65, 0, 0.35, 1);
    box-shadow: 0 0 20px rgba(16, 185, 129, 0.4);
    position: relative;
    overflow: hidden;
}

.progress-fill::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    animation: shimmer 2s infinite;
}

/* ===== REJECT NOTICE ===== */
.reject-notice {
    margin-top: 1rem;
    padding: 0.8rem 1.2rem;
    background: rgba(239, 68, 68, 0.1);
    color: #fca5a5;
    border-radius: 12px;
    font-size: 0.92rem;
    line-height: 1.5;
    border-left: 4px solid #ef4444;
    animation: pulse 2s infinite;
}

/* ===== STATUS BADGES ===== */
.status-badge {
    padding: 0.6em 1.4em;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.status-badge:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(0,0,0,0.12);
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

/* ===== DONATIONS ===== */
.donation-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
    animation: fadeInUp 0.5s ease-out backwards;
}

.donation-item:nth-child(1) { animation-delay: 0.1s; }
.donation-item:nth-child(2) { animation-delay: 0.2s; }
.donation-item:nth-child(3) { animation-delay: 0.3s; }
.donation-item:nth-child(4) { animation-delay: 0.4s; }
.donation-item:nth-child(5) { animation-delay: 0.5s; }

.donation-item:hover {
    background: rgba(16, 185, 129, 0.03);
    padding-left: 1rem;
    padding-right: 1rem;
    border-radius: 12px;
    transform: translateX(8px);
}

.donor-info strong {
    display: block;
    margin-bottom: 0.4rem;
    font-weight: 700;
    color: #fff;
    font-size: 1.05rem;
}

.donor-info small {
    color: #cbd5e1;
    font-size: 0.9rem;
    font-weight: 500;
}

.amount {
    font-weight: 900;
    background: linear-gradient(45deg, #10b981, #34d399);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-size: 1.4rem;
}

/* ===== TIPS LIST ===== */
.tips-list {
    padding-left: 1.5rem;
    margin: 0;
    color: #cbd5e1;
    line-height: 2;
}

.tips-list li {
    margin-bottom: 1.2rem;
    font-weight: 500;
    transition: all 0.3s ease;
    animation: fadeInUp 0.5s ease-out backwards;
}

.tips-list li:nth-child(1) { animation-delay: 0.1s; }
.tips-list li:nth-child(2) { animation-delay: 0.2s; }
.tips-list li:nth-child(3) { animation-delay: 0.3s; }
.tips-list li:nth-child(4) { animation-delay: 0.4s; }
.tips-list li:nth-child(5) { animation-delay: 0.5s; }

.tips-list li:hover {
    color: #10b981;
    transform: translateX(8px);
}

.tips-list li::marker {
    color: #10b981;
    font-size: 1.2em;
}

/* ===== EMPTY STATE ===== */
.empty-state {
    text-align: center;
    color: #94a3b8;
    padding: 4rem 2rem;
    font-size: 1.2rem;
    font-weight: 500;
    animation: pulse 2s infinite;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 1100px) {
    .content-layout {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 640px) {
    .dashboard-header {
        padding: 1.5rem;
    }
    
    .dashboard-header h1 {
        font-size: 2.2rem;
    }
    
    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
    }
    
    .campaign-preview {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .stat-value {
        font-size: 2.2rem;
    }
    
    .card {
        padding: 1.5rem;
    }
}
</style>

<!-- Background Animation -->
<div class="bg-animation">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
</div>

<div class="dashboard-container">

    <header class="dashboard-header">
        <h1>Creator Dashboard</h1>
        <a href="/CroudSpark-X/creator/create-campaign.php" class="btn-primary">+ Create New Campaign</a>
    </header>

    <section class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Campaigns</div>
            <div class="stat-value"><?= $total_campaigns ?></div>
        </div>
        <div class="stat-card stat-approved">
            <div class="stat-label">Approved</div>
            <div class="stat-value"><?= $approved ?></div>
        </div>
        <div class="stat-card stat-pending">
            <div class="stat-label">Pending</div>
            <div class="stat-value"><?= $pending ?></div>
        </div>
        <div class="stat-card stat-rejected">
            <div class="stat-label">Rejected</div>
            <div class="stat-value"><?= $rejected ?></div>
        </div>
        <div class="stat-card stat-raised">
            <div class="stat-label">Total Raised</div>
            <div class="stat-value">₹<?= number_format($total_funds) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Donations</div>
            <div class="stat-value"><?= number_format($total_donations) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Success Rate</div>
            <div class="stat-value"><?= $success_rate ?>%</div>
        </div>
    </section>

    <div class="content-layout">

        <!-- Main column -->
        <div>

            <div class="card">
                <h2>📊 Your Campaigns</h2>

                <?php if ($campaigns): ?>
                <table class="campaign-table">
                    <thead>
                        <tr>
                            <th>Campaign</th>
                            <th>Goal</th>
                            <th>Raised</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($campaigns as $c):
                        $raised  = $c['raised'] ?? 0;
                        $percent = $c['goal'] > 0 ? min(100, ($raised / $c['goal']) * 100) : 0;
                        
                        // Get thumbnail URL - use placeholder if not available
                        $thumbnail = !empty($c['thumbnail_url']) ? htmlspecialchars($c['thumbnail_url']) : 'https://via.placeholder.com/90x68?text=No+Image';
                    ?>
                    <tr class="campaign-row" style="cursor:pointer;"
                        onclick="window.location='view-campaign.php?id=<?= $c['id'] ?>'">
                        <td>
                            <div class="campaign-preview">
                                <img src="<?= $thumbnail ?>"
                                     alt="<?= htmlspecialchars($c['title'] ?? 'Campaign image') ?>"
                                     class="thumb"
                                     onerror="this.src='https://via.placeholder.com/90x68?text=No+Image'">
                                <div>
                                    <div class="campaign-title"><?= htmlspecialchars($c['title']) ?></div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?= $percent ?>%"></div>
                                    </div>
                                    <?php if ($c['status'] === 'rejected' && !empty($c['reject_reason'])): ?>
                                        <div class="reject-notice">
                                            <?= htmlspecialchars($c['reject_reason']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>₹<?= number_format($c['goal']) ?></td>
                        <td>₹<?= number_format($raised) ?></td>
                        <td>
                            <span class="status-badge status-<?= $c['status'] ?>">
                                <?= ucfirst($c['status']) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    You haven't created any campaigns yet.<br>
                    Start your first one today!
                </div>
                <?php endif; ?>
            </div>

        </div>

        <!-- Sidebar -->
        <aside>

            <div class="card">
                <h2>💰 Recent Donations</h2>

                <?php if ($recent): ?>
                    <?php foreach ($recent as $r): ?>
                    <div class="donation-item">
                        <div class="donor-info">
                            <strong><?= htmlspecialchars($r['name']) ?></strong>
                            <small><?= htmlspecialchars($r['title']) ?></small>
                        </div>
                        <div class="amount">₹<?= number_format($r['amount']) ?></div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state" style="padding:2rem 0;">
                        No donations yet — keep sharing!
                    </div>
                <?php endif; ?>
            </div>

            <div class="card">
                <h2>✨ Quick Tips</h2>
                <ul class="tips-list">
                    <li>Use real photos of people & impact</li>
                    <li>Tell an emotional, honest story</li>
                    <li>Share in 5–10 relevant WhatsApp groups daily</li>
                    <li>Post updates every 3–5 days</li>
                    <li>Thank every donor personally</li>
                </ul>
            </div>

        </aside>

    </div>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>