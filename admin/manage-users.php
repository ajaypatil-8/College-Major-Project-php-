<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT']."/config/db.php";

/* ===== ADMIN CHECK ===== */
if(!isset($_SESSION['user_id']) || $_SESSION['role']!='admin'){
    header("Location: ../login.php");
    exit;
}

$msg="";
$success="";

/* ===== CHANGE ROLE ===== */
if(isset($_POST['change_role'])){
    $uid = $_POST['uid'];
    $new_role = $_POST['role'];

    if($uid == $_SESSION['user_id']){
        $msg="You cannot change your own role";
    } else {
        $stmt=$pdo->prepare("UPDATE users SET role=? WHERE id=?");
        $stmt->execute([$new_role,$uid]);
        $success="User role updated";
    }
}

/* ===== DELETE USER ===== */
if(isset($_POST['delete_user'])){
    $uid=$_POST['uid'];

    if($uid == $_SESSION['user_id']){
        $msg="You cannot delete yourself";
    } else {
        $stmt=$pdo->prepare("DELETE FROM users WHERE id=?");
        $stmt->execute([$uid]);
        $success="User deleted successfully";
    }
}

/* ===== SEARCH ===== */
$search = isset($_GET['search']) ? trim($_GET['search']) : "";

/* ===== PAGINATION ===== */
$limit = 30;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

/* ===== QUERY USERS ===== */
if($search != ""){
    $stmt = $pdo->prepare("
    SELECT * FROM users 
    WHERE name LIKE :s OR email LIKE :s
    ORDER BY id DESC 
    LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':s', "%$search%");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    // count
    $countStmt=$pdo->prepare("SELECT COUNT(*) FROM users WHERE name LIKE ? OR email LIKE ?");
    $countStmt->execute(["%$search%","%$search%"]);
    $total = $countStmt->fetchColumn();

}else{

    $stmt = $pdo->prepare("
    SELECT * FROM users 
    ORDER BY id DESC 
    LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $total = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
}

$users=$stmt->fetchAll();
$total_pages = ceil($total / $limit);
?>

<?php require_once $_SERVER['DOCUMENT_ROOT']."/includes/header.php"; ?>

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

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-10px); }
    75% { transform: translateX(10px); }
}

@keyframes shimmer {
    0% { background-position: -1000px 0; }
    100% { background-position: 1000px 0; }
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

/* Alert Messages */
.alert {
    padding: 16px 24px;
    border-radius: 16px;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 24px;
    animation: shake 0.5s ease;
    position: relative;
    overflow: hidden;
}

.alert::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    animation: shimmer 2s infinite;
}

.alert-error {
    background: rgba(239, 68, 68, 0.2);
    color: #ef4444;
    border-left: 4px solid #ef4444;
}

[data-theme="light"] .alert-error {
    background: rgba(239, 68, 68, 0.15);
    color: #dc2626;
}

.alert-success {
    background: rgba(16, 185, 129, 0.2);
    color: #10b981;
    border-left: 4px solid #10b981;
}

[data-theme="light"] .alert-success {
    background: rgba(16, 185, 129, 0.15);
    color: #059669;
}

/* Search Card */
.search-card {
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

.search-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #ef4444, #dc2626);
}

.search-form {
    display: flex;
    gap: 12px;
    align-items: center;
}

.search-input {
    flex: 1;
    padding: 16px 20px;
    border-radius: 12px;
    border: 2px solid var(--border-color);
    background: var(--bg-secondary);
    color: var(--text-primary);
    font-size: 15px;
    font-weight: 500;
    transition: all 0.3s ease;
    font-family: 'DM Sans', sans-serif;
}

.search-input::placeholder {
    color: var(--text-tertiary);
}

.search-input:focus {
    outline: none;
    border-color: #ef4444;
    box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15);
}

.btn-search {
    padding: 16px 32px;
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

.btn-search:hover {
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

/* Role Badge */
.role-badge {
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    display: inline-block;
    text-transform: uppercase;
}

.role-user {
    background: rgba(100, 116, 139, 0.2);
    color: #94a3b8;
}

.role-creator {
    background: rgba(139, 92, 246, 0.2);
    color: #a78bfa;
}

.role-admin {
    background: rgba(239, 68, 68, 0.2);
    color: #ef4444;
}

/* Action Forms */
.action-form {
    display: flex;
    gap: 8px;
    align-items: center;
}

.action-form select {
    padding: 8px 12px;
    border-radius: 8px;
    border: 2px solid var(--border-color);
    background: var(--bg-secondary);
    color: var(--text-primary);
    font-size: 13px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.action-form select:focus {
    outline: none;
    border-color: #ef4444;
}

.btn-update {
    padding: 8px 16px;
    border: none;
    border-radius: 8px;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #fff;
    font-weight: 700;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
}

.btn-update:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
}

.btn-delete {
    padding: 8px 16px;
    border: none;
    border-radius: 8px;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #fff;
    font-weight: 700;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
}

.btn-delete:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
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


.user-letter{
    width:48px;
    height:48px;
    border-radius:50%;
    background:linear-gradient(135deg,#ec4899,#ef4444);
    color:#fff;
    font-weight:900;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:18px;
    text-transform:uppercase;
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
    
    .search-form {
        flex-direction: column;
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
        <div>
            <h1 class="page-title"> 👑 Manage Users</h1>
            <p class="page-subtitle">
                <span class="result-count">
                    <i class="fa-solid fa-users"></i>
                    <?= number_format($total) ?> Total Users
                </span>
            </p>
        </div>
    </div>

    <!-- ALERT MESSAGES -->
    <?php if($msg): ?>
    <div class="alert alert-error">
        <i class="fa fa-exclamation-circle"></i> <?= $msg ?>
    </div>
    <?php endif; ?>

    <?php if($success): ?>
    <div class="alert alert-success">
        <i class="fa fa-check-circle"></i> <?= $success ?>
    </div>
    <?php endif; ?>

    <!-- SEARCH -->
    <div class="search-card">
        <form method="GET" class="search-form">
            <input 
                type="text" 
                name="search" 
                class="search-input"
                placeholder="🔍 Search by name or email" 
                value="<?= htmlspecialchars($search) ?>"
            >
            <button type="submit" class="btn-search">
                <i class="fa-solid fa-search"></i> Search
            </button>
        </form>
    </div>

    <!-- TABLE -->
    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>City</th>
                    <th>Role</th>
                    <th>Change Role</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($users)): ?>
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <i class="fa-solid fa-users"></i>
                            <h3>No users found</h3>
                            <p>Try a different search term</p>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                
                <?php foreach($users as $u): ?>
                <tr>
                    <td><strong>#<?= $u['id'] ?></strong></td>
                    
                    <td>
                        <div class="user-cell">
                           <?php if(!empty($u['profile_image'])): ?>

                                    <img 
                                    class="user-avatar"
                                    src="<?= htmlspecialchars($u['profile_image']) ?>"
                                    alt="User"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                    >

                                    <!-- fallback if image broken -->
                                    <div class="user-letter" style="display:none;">
                                        <?= strtoupper(substr($u['name'],0,1)) ?>
                                    </div>

                                <?php else: ?>

                                    <!-- if no image uploaded -->
                                    <div class="user-letter">
                                        <?= strtoupper(substr($u['name'],0,1)) ?>
                                    </div>

                                <?php endif; ?>

                            <div class="user-name">
                                <?= htmlspecialchars($u['name']) ?>
                            </div>
                        </div>
                    </td>
                    
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['phone']) ?></td>
                    <td><?= htmlspecialchars($u['city']) ?></td>
                    
                    <td>
                        <span class="role-badge role-<?= $u['role'] ?>">
                            <?= ucfirst($u['role']) ?>
                        </span>
                    </td>
                    
                    <td>
                        <form method="POST" class="action-form">
                            <input type="hidden" name="uid" value="<?= $u['id'] ?>">
                            <select name="role">
                                <option value="user" <?= $u['role']=='user'?'selected':'' ?>>User</option>
                                <option value="creator" <?= $u['role']=='creator'?'selected':'' ?>>Creator</option>
                                <option value="admin" <?= $u['role']=='admin'?'selected':'' ?>>Admin</option>
                            </select>
                            <button class="btn-update" name="change_role">
                                <i class="fa-solid fa-sync"></i> Update
                            </button>
                        </form>
                    </td>
                    
                    <td>
                        <form method="POST" onsubmit="return confirm('Delete this user?')">
                            <input type="hidden" name="uid" value="<?= $u['id'] ?>">
                            <button class="btn-delete" name="delete_user">
                                <i class="fa-solid fa-trash"></i> Delete
                            </button>
                        </form>
                    </td>
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
            href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"
        >
            <?= $i ?>
        </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>

</div>

<?php require_once $_SERVER['DOCUMENT_ROOT']."/includes/footer.php"; ?>