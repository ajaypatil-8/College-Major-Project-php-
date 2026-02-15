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

/* ===== SEARCH FILTERS ===== */
$search = $_GET['search'] ?? "";
$category = $_GET['category'] ?? "";
$status = $_GET['status'] ?? "";

/* ===== QUERY ===== */
$sql = "
SELECT c.*, u.name as creator_name,
(
  SELECT media_url FROM campaign_media 
  WHERE campaign_id=c.id AND media_type='thumbnail' 
  LIMIT 1
) as thumbnail
FROM campaigns c
JOIN users u ON c.user_id = u.id
WHERE 1
";

$params=[];

if($search){
    $sql.=" AND (c.title LIKE ? OR u.name LIKE ?)";
    $params[]="%$search%";
    $params[]="%$search%";
}

if($category){
    $sql.=" AND c.category=?";
    $params[]=$category;
}

if($status){
    $sql.=" AND c.status=?";
    $params[]=$status;
}

$sql.=" ORDER BY c.id DESC LIMIT $limit OFFSET $offset";

$stmt=$pdo->prepare($sql);
$stmt->execute($params);
$campaigns=$stmt->fetchAll();

/* ===== COUNT TOTAL ===== */
$countSql="SELECT COUNT(*) FROM campaigns c JOIN users u ON c.user_id=u.id WHERE 1";
$countParams=[];

if($search){
    $countSql.=" AND (c.title LIKE ? OR u.name LIKE ?)";
    $countParams[]="%$search%";
    $countParams[]="%$search%";
}
if($category){
    $countSql.=" AND c.category=?";
    $countParams[]=$category;
}
if($status){
    $countSql.=" AND c.status=?";
    $countParams[]=$status;
}

$countStmt=$pdo->prepare($countSql);
$countStmt->execute($countParams);
$total=$countStmt->fetchColumn();
$total_pages=ceil($total/$limit);
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
    max-width: 1400px;
    margin: 0 auto;
    padding: 120px 40px 80px;
}

/* Page Header */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    flex-wrap: wrap;
    gap: 20px;
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

.result-count {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(239, 68, 68, 0.2);
    color: #ef4444;
    padding: 8px 16px;
    border-radius: 999px;
    font-size: 14px;
    font-weight: 700;
}

/* Filters Section */
.filters-card {
    background: var(--bg-card);
    backdrop-filter: blur(20px);
    border: 1px solid var(--border-color);
    border-radius: 24px;
    padding: 32px;
    margin-bottom: 40px;
    position: relative;
    overflow: hidden;
    animation: fadeInUp 0.6s ease 0.2s both;
}

.filters-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #ef4444, #dc2626);
}

.filters-title {
    font-size: 18px;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.filters-title i {
    color: #ef4444;
}

.filters-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr auto;
    gap: 16px;
    align-items: end;
}

.filter-group label {
    display: block;
    font-size: 12px;
    font-weight: 800;
    color: var(--text-secondary);
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.filter-group input,
.filter-group select {
    width: 100%;
    padding: 14px 18px;
    border-radius: 12px;
    border: 2px solid var(--border-color);
    background: var(--bg-secondary);
    color: var(--text-primary);
    font-size: 15px;
    font-weight: 500;
    transition: all 0.3s ease;
    font-family: 'DM Sans', sans-serif;
}

.filter-group input::placeholder {
    color: var(--text-tertiary);
}

.filter-group input:focus,
.filter-group select:focus {
    outline: none;
    border-color: #ef4444;
    box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15);
}

.btn-filter {
    padding: 14px 32px;
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #fff;
    font-weight: 800;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-filter:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4);
}

/* Table Card */
.table-card {
    background: var(--bg-card);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    overflow: hidden;
    border: 1px solid var(--border-color);
    margin-bottom: 40px;
    animation: fadeInUp 0.6s ease 0.3s both;
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

/* Campaign Cell */
.campaign-cell {
    display: flex;
    align-items: center;
    gap: 14px;
}

.campaign-thumb {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    object-fit: cover;
    border: 2px solid rgba(239, 68, 68, 0.3);
    transition: all 0.3s ease;
}

.campaign-thumb:hover {
    transform: scale(1.1);
    border-color: #ef4444;
}

.campaign-title {
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1.4;
}

/* Badges */
.badge {
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    display: inline-block;
}

.badge-approved {
    background: rgba(16, 185, 129, 0.2);
    color: #10b981;
}

.badge-pending {
    background: rgba(245, 158, 11, 0.2);
    color: #f59e0b;
}

.badge-rejected {
    background: rgba(239, 68, 68, 0.2);
    color: #ef4444;
}

.badge-draft {
    background: rgba(100, 116, 139, 0.2);
    color: #94a3b8;
}

/* Pagination */
.pagination-container {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    margin-top: 40px;
    animation: fadeInUp 0.6s ease 0.4s both;
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
    .filters-grid {
        grid-template-columns: 1fr;
    }
    
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
    
    .table-card th,
    .table-card td {
        padding: 12px;
        font-size: 13px;
    }
    
    .campaign-thumb {
        width: 50px;
        height: 50px;
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
            <h1 class="page-title">🚀 All Campaigns</h1>
            <p class="page-subtitle">
                <span class="result-count">
                    <i class="fa-solid fa-layer-group"></i>
                    <?= number_format($total) ?> Total Campaigns
                </span>
            </p>
        </div>
    </div>

    <!-- FILTERS -->
    <div class="filters-card">
        <h3 class="filters-title">
            <i class="fa-solid fa-filter"></i>
            Filter Campaigns
        </h3>
        
        <form method="GET">
            <div class="filters-grid">
                
                <div class="filter-group">
                    <label>Search</label>
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Search campaign or creator"
                        value="<?= htmlspecialchars($search) ?>"
                    >
                </div>

                <div class="filter-group">
                    <label>Category</label>
                    <select name="category">
                        <option value="">All Categories</option>
                        <option <?=($category=='Startup')?'selected':''?>>Startup</option>
                        <option <?=($category=='Education')?'selected':''?>>Education</option>
                        <option <?=($category=='Community')?'selected':''?>>Community</option>
                        <option <?=($category=='Health')?'selected':''?>>Health</option>
                        <option <?=($category=='Technology')?'selected':''?>>Technology</option>
                        <option <?=($category=='Environment')?'selected':''?>>Environment</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="">All Status</option>
                        <option value="approved" <?=($status=='approved')?'selected':''?>>Approved</option>
                        <option value="pending" <?=($status=='pending')?'selected':''?>>Pending</option>
                        <option value="rejected" <?=($status=='rejected')?'selected':''?>>Rejected</option>
                        <option value="draft" <?=($status=='draft')?'selected':''?>>Draft</option>
                    </select>
                </div>

                <button type="submit" class="btn-filter">
                    <i class="fa-solid fa-search"></i> Filter
                </button>

            </div>
        </form>
    </div>

    <!-- TABLE -->
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
                <?php if(empty($campaigns)): ?>
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <i class="fa-solid fa-layer-group"></i>
                            <h3>No campaigns found</h3>
                            <p>Try adjusting your filters</p>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                
                <?php foreach($campaigns as $c): ?>
                <tr>
                    <td>
                        <div class="campaign-cell">
                            <img 
                                class="campaign-thumb" 
                                src="<?= $c['thumbnail'] ?: 'https://via.placeholder.com/60' ?>"
                                alt="Campaign"
                            >
                            <div class="campaign-title">
                                <?= htmlspecialchars($c['title']) ?>
                            </div>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($c['creator_name']) ?></td>
                    <td><strong>₹<?= number_format($c['goal']) ?></strong></td>
                    <td><?= htmlspecialchars($c['category']) ?></td>
                    <td>
                        <span class="badge badge-<?= $c['status'] ?>">
                            <?= ucfirst($c['status']) ?>
                        </span>
                    </td>
                    <td><?= date("d M Y", strtotime($c['created_at'])) ?></td>
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
            class="pagination-btn <?=($i==$page)?'active':''?>"
            href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&status=<?= urlencode($status) ?>"
        >
            <?= $i ?>
        </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__."/../includes/footer.php"; ?>