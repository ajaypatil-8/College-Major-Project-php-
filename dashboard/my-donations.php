<?php
session_start();
require_once __DIR__ . "/../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: /CroudSpark-X/user/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Pagination setup
$records_per_page = 30;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Get total count for pagination
$countQuery = "SELECT COUNT(*) FROM donations WHERE user_id = :user_id";
$params = ['user_id' => $user_id];

// Add status filter
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $countQuery .= " AND status = :status";
    $params['status'] = $_GET['status'];
}

$countStmt = $pdo->prepare($countQuery);
foreach ($params as $key => $value) {
    $countStmt->bindValue(":$key", $value);
}
$countStmt->execute();
$total_records = $countStmt->fetchColumn();
$total_pages = ceil($total_records / $records_per_page);

// Build main query
$query = "
    SELECT 
        d.*,
        c.title as campaign_title,
        c.id as campaign_id,
        c.category
    FROM donations d
    LEFT JOIN campaigns c ON d.campaign_id = c.id
    WHERE d.user_id = :user_id
";

// Add status filter to main query
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $query .= " AND d.status = :status";
}

// Add sorting
$orderBy = "d.created_at DESC";
if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'oldest':
            $orderBy = "d.created_at ASC";
            break;
        case 'highest':
            $orderBy = "d.amount DESC";
            break;
        case 'lowest':
            $orderBy = "d.amount ASC";
            break;
        default:
            $orderBy = "d.created_at DESC";
    }
}

$query .= " ORDER BY $orderBy LIMIT :limit_val OFFSET :offset_val";

// Fetch donations with campaign info
$stmt = $pdo->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue(":$key", $value);
}
$stmt->bindValue(':limit_val', $records_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset_val', $offset, PDO::PARAM_INT);
$stmt->execute();
$donations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics (with filter applied)
$statsQuery = "
    SELECT 
        COUNT(*) as total_donations,
        COALESCE(SUM(CASE WHEN status = 'success' THEN amount ELSE 0 END), 0) as total_amount,
        COUNT(CASE WHEN status = 'success' THEN 1 END) as successful_donations,
        COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_donations,
        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_donations
    FROM donations 
    WHERE user_id = :user_id
";
$statsStmt = $pdo->prepare($statsQuery);
$statsStmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
$statsStmt->execute();
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

require_once __DIR__ . "/../includes/header.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Donations - CrowdSpark</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        /* ===== THEME VARIABLES ===== */
        :root {
            --bg-primary: #fafafa;
            --bg-secondary: #f1f5f9;
            --bg-card: rgba(255, 255, 255, 0.95);
            --bg-card-hover: rgba(255, 255, 255, 1);
            
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-tertiary: #64748b;
            
            --border-color: rgba(15, 23, 42, 0.08);
            --border-hover: rgba(20, 184, 166, 0.3);
            
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.04);
            --shadow-md: 0 8px 32px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 20px 60px rgba(0, 0, 0, 0.15);
            
            --orb-opacity: 0.25;
        }

        [data-theme="dark"] {
            --bg-primary: #0f0f0f;
            --bg-secondary: #1a1a1a;
            --bg-card: rgba(20, 20, 30, 0.95);
            --bg-card-hover: rgba(30, 30, 40, 0.95);
            
            --text-primary: #ffffff;
            --text-secondary: #cbd5e1;
            --text-tertiary: #94a3b8;
            
            --border-color: rgba(255, 255, 255, 0.1);
            --border-hover: rgba(20, 184, 166, 0.4);
            
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.3);
            --shadow-md: 0 8px 32px rgba(0, 0, 0, 0.4);
            --shadow-lg: 0 20px 60px rgba(0, 0, 0, 0.6);
            
            --orb-opacity: 0.25;
        }

        /* Teal/Dark Cyan Accent Colors */
        :root {
            --accent-primary: #14b8a6;
            --accent-secondary: #0d9488;
            --accent-dark: #115e59;
            --accent-light: #5eead4;
            --accent-gradient: linear-gradient(135deg, #14b8a6, #0d9488);
            --accent-glow: rgba(20, 184, 166, 0.4);
        }

        /* Orb colors - Teal/Cyan mix */
        [data-theme="dark"] {
            --orb-1: linear-gradient(45deg, #14b8a6, #06b6d4);
            --orb-2: linear-gradient(45deg, #0d9488, #0891b2);
            --orb-3: linear-gradient(45deg, #2dd4bf, #22d3ee);
        }

        [data-theme="light"] {
            --orb-1: linear-gradient(45deg, #99f6e4, #a5f3fc);
            --orb-2: linear-gradient(45deg, #5eead4, #67e8f9);
            --orb-3: linear-gradient(45deg, #2dd4bf, #22d3ee);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            overflow-x: hidden;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Background Animation */
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

        /* Hero Section */
        .explore-hero {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 40px 20px 60px;
            animation: fadeInUp 0.8s ease;
        }

        .explore-hero h1 {
            font-size: clamp(2.5rem, 6vw, 4rem);
            font-weight: 900;
            margin-bottom: 16px;
            background: linear-gradient(135deg, var(--text-primary), var(--accent-primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .explore-hero p {
            font-size: 1.25rem;
            color: var(--text-secondary);
            max-width: 600px;
            margin: 0 auto;
        }

        /* Main Container */
        .donation-history {
            position: relative;
            z-index: 1;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px 80px;
        }

        /* Stats Grid */
        .donation-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
            margin-bottom: 50px;
            animation: fadeIn 0.8s ease 0.2s both;
        }

        .stat-card {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 28px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
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
            border-color: var(--border-hover);
            box-shadow: var(--shadow-md);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 12px;
        }

        .stat-label {
            display: block;
            color: var(--text-secondary);
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-value {
            display: block;
            font-size: 2rem;
            font-weight: 900;
            color: var(--accent-primary);
        }

        /* Filters Section */
        .filters-section {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 24px;
            margin-bottom: 30px;
            box-shadow: var(--shadow-sm);
            animation: fadeIn 0.8s ease 0.3s both;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }

        .filter-item label {
            display: block;
            color: var(--text-secondary);
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-item select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            background: var(--bg-secondary);
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .filter-item select:focus {
            outline: none;
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 4px var(--accent-glow);
        }

        /* Donations Table */
        .donations-table-wrapper {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: var(--shadow-md);
            animation: fadeInUp 0.8s ease 0.4s both;
        }

        .donations-table {
            width: 100%;
            border-collapse: collapse;
        }

        .donations-table thead {
            background: var(--accent-gradient);
            color: #fff;
        }

        .donations-table thead th {
            padding: 20px;
            text-align: left;
            font-weight: 800;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .donations-table tbody tr {
            border-bottom: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .donations-table tbody tr:hover {
            background: var(--bg-secondary);
        }

        .donations-table tbody td {
            padding: 24px 20px;
            color: var(--text-primary);
            font-weight: 500;
        }

        .campaign-info {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .campaign-title {
            font-weight: 700;
            color: var(--text-primary);
            font-size: 15px;
        }

        .campaign-category {
            display: inline-block;
            background: var(--accent-gradient);
            color: #fff;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            width: fit-content;
        }

        .amount {
            font-size: 1.25rem;
            font-weight: 900;
            color: var(--accent-primary);
        }

        .payment-info {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .payment-method {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--bg-secondary);
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-secondary);
            width: fit-content;
        }

        .payment-id {
            font-size: 12px;
            color: var(--text-tertiary);
            font-family: 'Courier New', monospace;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-success {
            background: rgba(34, 197, 94, 0.15);
            color: #22c55e;
            border: 2px solid rgba(34, 197, 94, 0.3);
        }

        .status-failed {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            border: 2px solid rgba(239, 68, 68, 0.3);
        }

        .status-pending {
            background: rgba(251, 191, 36, 0.15);
            color: #fbbf24;
            border: 2px solid rgba(251, 191, 36, 0.3);
        }

        .date-info {
            color: var(--text-secondary);
            font-size: 14px;
            font-weight: 600;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            margin-top: 40px;
            animation: fadeIn 0.8s ease 0.5s both;
        }

        .pagination a,
        .pagination span {
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 2px solid var(--border-color);
            background: var(--bg-card);
            color: var(--text-primary);
        }

        .pagination a:hover {
            background: var(--accent-gradient);
            color: #fff;
            border-color: transparent;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px var(--accent-glow);
        }

        .pagination .current {
            background: var(--accent-gradient);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 4px 12px var(--accent-glow);
        }

        .pagination .disabled {
            opacity: 0.5;
            pointer-events: none;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: var(--text-secondary);
        }

        .empty-state i {
            font-size: 5rem;
            margin-bottom: 24px;
            opacity: 0.3;
        }

        .empty-state h3 {
            font-size: 1.75rem;
            margin-bottom: 12px;
            color: var(--text-primary);
            font-weight: 800;
        }

        .empty-state p {
            font-size: 1.1rem;
            margin-bottom: 30px;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 32px;
            background: var(--accent-gradient);
            color: #fff;
            text-decoration: none;
            border-radius: 999px;
            font-weight: 700;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px var(--accent-glow);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px var(--accent-glow);
        }

        /* Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .donations-table {
                display: block;
                overflow-x: auto;
            }
        }

        @media (max-width: 768px) {
            .donation-stats {
                grid-template-columns: 1fr;
            }

            .filters-grid {
                grid-template-columns: 1fr;
            }

            .donations-table thead {
                display: none;
            }

            .donations-table tbody tr {
                display: block;
                margin-bottom: 20px;
                border-radius: 16px;
                overflow: hidden;
                border: 1px solid var(--border-color);
            }

            .donations-table tbody td {
                display: flex;
                justify-content: space-between;
                padding: 16px 20px;
                border-bottom: 1px solid var(--border-color);
            }

            .donations-table tbody td:last-child {
                border-bottom: none;
            }

            .donations-table tbody td::before {
                content: attr(data-label);
                font-weight: 700;
                color: var(--text-secondary);
                text-transform: uppercase;
                font-size: 12px;
                letter-spacing: 0.5px;
            }
        }
    </style>
</head>
<body>

    <!-- Background Animation -->
    <div class="bg-animation">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <!-- Hero -->
    <section class="explore-hero">
        <h1>My Donations</h1>
        <p>Complete history of your contributions and impact.</p>
    </section>

    <!-- Main Content -->
    <section class="donation-history">

        <!-- Statistics -->
        <div class="donation-stats">
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <span class="stat-label">Total Donations</span>
                <span class="stat-value"><?= $stats['total_donations'] ?></span>
            </div>

            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <span class="stat-label">Successful</span>
                <span class="stat-value"><?= $stats['successful_donations'] ?></span>
            </div>

            <div class="stat-card">
                <div class="stat-icon">💵</div>
                <span class="stat-label">Total Amount</span>
                <span class="stat-value">₹<?= number_format($stats['total_amount']) ?></span>
            </div>

            <div class="stat-card">
                <div class="stat-icon">⏳</div>
                <span class="stat-label">Pending</span>
                <span class="stat-value"><?= $stats['pending_donations'] ?></span>
            </div>

            <div class="stat-card">
                <div class="stat-icon">❌</div>
                <span class="stat-label">Failed</span>
                <span class="stat-value"><?= $stats['failed_donations'] ?></span>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-section">
            <form method="GET" action="" class="filters-grid">
                <div class="filter-item">
                    <label>Status Filter</label>
                    <select name="status" onchange="this.form.submit()">
                        <option value="">All Donations</option>
                        <option value="success" <?= isset($_GET['status']) && $_GET['status'] == 'success' ? 'selected' : '' ?>>Success</option>
                        <option value="pending" <?= isset($_GET['status']) && $_GET['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="failed" <?= isset($_GET['status']) && $_GET['status'] == 'failed' ? 'selected' : '' ?>>Failed</option>
                    </select>
                </div>

                <div class="filter-item">
                    <label>Sort By</label>
                    <select name="sort" onchange="this.form.submit()">
                        <option value="newest" <?= !isset($_GET['sort']) || $_GET['sort'] == 'newest' ? 'selected' : '' ?>>Newest First</option>
                        <option value="oldest" <?= isset($_GET['sort']) && $_GET['sort'] == 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                        <option value="highest" <?= isset($_GET['sort']) && $_GET['sort'] == 'highest' ? 'selected' : '' ?>>Highest Amount</option>
                        <option value="lowest" <?= isset($_GET['sort']) && $_GET['sort'] == 'lowest' ? 'selected' : '' ?>>Lowest Amount</option>
                    </select>
                </div>
                
                <!-- Hidden field to preserve page when filtering -->
                <?php if (isset($_GET['page'])): ?>
                    <input type="hidden" name="page" value="1">
                <?php endif; ?>
            </form>
        </div>

        <!-- Donations Table -->
        <?php if (count($donations) > 0): ?>
        <div class="donations-table-wrapper">
            <table class="donations-table">
                <thead>
                    <tr>
                        <th>Campaign</th>
                        <th>Amount</th>
                        <th>Payment Details</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($donations as $d): ?>
                    <tr>
                        <td data-label="Campaign">
                            <div class="campaign-info">
                                <span class="campaign-title"><?= htmlspecialchars($d['campaign_title']) ?></span>
                                <?php if (!empty($d['category'])): ?>
                                    <span class="campaign-category"><?= htmlspecialchars($d['category']) ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        
                        <td data-label="Amount">
                            <span class="amount">₹<?= number_format($d['amount']) ?></span>
                        </td>
                        
                        <td data-label="Payment">
                            <div class="payment-info">
                                <span class="payment-method">
                                    <i class="fa-solid fa-credit-card"></i>
                                    <?= ucfirst(htmlspecialchars($d['payment_method'] ?? 'razorpay')) ?>
                                </span>
                                <?php if (!empty($d['razorpay_payment_id'])): ?>
                                    <span class="payment-id" title="Payment ID">
                                        <?= htmlspecialchars(substr($d['razorpay_payment_id'], 0, 20)) ?>...
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($d['razorpay_order_id'])): ?>
                                    <span class="payment-id" title="Order ID">
                                        Order: <?= htmlspecialchars(substr($d['razorpay_order_id'], 0, 15)) ?>...
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>
                        
                        <td data-label="Date">
                            <span class="date-info">
                                <?= date('d M Y', strtotime($d['created_at'])) ?><br>
                                <small style="opacity: 0.7;"><?= date('h:i A', strtotime($d['created_at'])) ?></small>
                            </span>
                        </td>
                        
                        <td data-label="Status">
                            <?php 
                            $status = strtolower($d['status']);
                            $statusClass = 'status-' . $status;
                            $statusIcon = $status == 'success' ? 'check-circle' : ($status == 'failed' ? 'times-circle' : 'clock');
                            ?>
                            <span class="status-badge <?= $statusClass ?>">
                                <i class="fa-solid fa-<?= $statusIcon ?>"></i>
                                <?= ucfirst($status) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): 
            // Build query string for pagination
            $queryParams = [];
            if (isset($_GET['status']) && !empty($_GET['status'])) {
                $queryParams[] = 'status=' . urlencode($_GET['status']);
            }
            if (isset($_GET['sort']) && !empty($_GET['sort'])) {
                $queryParams[] = 'sort=' . urlencode($_GET['sort']);
            }
            $queryString = !empty($queryParams) ? '&' . implode('&', $queryParams) : '';
        ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?><?= $queryString ?>">
                    <i class="fa-solid fa-chevron-left"></i> Previous
                </a>
            <?php else: ?>
                <span class="disabled">
                    <i class="fa-solid fa-chevron-left"></i> Previous
                </span>
            <?php endif; ?>

            <span class="current">Page <?= $page ?> of <?= $total_pages ?></span>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?><?= $queryString ?>">
                    Next <i class="fa-solid fa-chevron-right"></i>
                </a>
            <?php else: ?>
                <span class="disabled">
                    Next <i class="fa-solid fa-chevron-right"></i>
                </span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <!-- Empty State -->
        <div class="empty-state">
            <i class="fa-solid fa-heart-crack"></i>
            <h3>No Donations Yet</h3>
            <p>You haven't made any donations yet. Start supporting campaigns to make an impact!</p>
            <a href="/CroudSpark-X/public/explore-campaigns.php" class="btn-primary">
                <i class="fa-solid fa-compass"></i> Explore Campaigns
            </a>
        </div>
        <?php endif; ?>

    </section>

    <?php require_once __DIR__ . "/../includes/footer.php"; ?>

</body>
</html>