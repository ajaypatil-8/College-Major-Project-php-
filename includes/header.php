<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CrowdSpark - Support Dreams, Change Lives</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

/* ===== GLOBAL RESET ===== */
*{
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html, body{
    overflow-x: hidden;
    scroll-behavior: smooth;
}

/* ===== BODY ===== */
body{
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    background: #fafafa;
    padding-top: 100px;
    -webkit-font-smoothing: antialiased;
}

/* ===== FLOATING GLASS NAVBAR ===== */
.nav-wrap{
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    display: flex;
    justify-content: center;
    z-index: 999;
    padding: 20px 16px;
    animation: slideDown 0.5s ease;
}

@keyframes slideDown {
    from {
        transform: translateY(-100px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.navbar{
    max-width: 1180px;
    width: 100%;
    height: 68px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 32px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px) saturate(180%);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08),
                0 2px 8px rgba(0, 0, 0, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.8);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    overflow: hidden;
}

.navbar::before{
    content: "";
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(245, 158, 11, 0.1), transparent);
    transition: 0.8s;
}

.navbar:hover::before{
    left: 100%;
}

.navbar.scrolled{
    height: 60px;
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12),
                0 4px 12px rgba(0, 0, 0, 0.06);
}

/* ===== LOGO ===== */
.logo{
    font-weight: 900;
    font-size: 22px;
    text-decoration: none;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 8px;
    position: relative;
    transition: transform 0.3s ease;
}

.logo:hover{
    transform: scale(1.05);
}

.logo-icon{
    font-size: 26px;
    background: linear-gradient(135deg, #f59e0b, #fb923c);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: pulse 2s ease infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.logo span{
    background: linear-gradient(135deg, #f59e0b, #fb923c);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* ===== CENTER LINKS ===== */
.nav-links{
    display: flex;
    align-items: center;
    gap: 8px;
    list-style: none;
}

.nav-links a{
    position: relative;
    padding: 10px 18px;
    border-radius: 999px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    color: #64748b;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    display: flex;
    align-items: center;
    gap: 6px;
}

.nav-links a i{
    font-size: 14px;
    transition: transform 0.3s ease;
}

.nav-links a:hover i{
    transform: translateY(-2px);
}

.nav-links a::before{
    content: "";
    position: absolute;
    bottom: 0;
    left: 50%;
    width: 0;
    height: 2px;
    background: linear-gradient(90deg, #f59e0b, #fb923c);
    transform: translateX(-50%);
    transition: width 0.3s ease;
    border-radius: 2px;
}

.nav-links a:hover{
    color: #f59e0b;
    transform: translateY(-2px);
}

.nav-links a:hover::before{
    width: 70%;
}

/* Active page */
.nav-links a.active{
    background: linear-gradient(135deg, #f59e0b, #fb923c);
    color: #fff;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
}

.nav-links a.active::before{
    display: none;
}

.nav-links a.active:hover{
    transform: translateY(-2px) scale(1.05);
    box-shadow: 0 6px 16px rgba(245, 158, 11, 0.5);
}

/* ===== RIGHT SECTION ===== */
.nav-right{
    display: flex;
    align-items: center;
    gap: 12px;
}

/* ===== BUTTONS ===== */
.btn-nav{
    padding: 10px 20px;
    border-radius: 999px;
    font-size: 14px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    display: inline-flex;
    align-items: center;
    gap: 6px;
    position: relative;
    overflow: hidden;
}

.btn-nav::before{
    content: "";
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.4);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.btn-nav:active::before{
    width: 300px;
    height: 300px;
}

.btn-login{
    border: 2px solid #f59e0b;
    color: #f59e0b;
    background: transparent;
}

.btn-login:hover{
    background: #f59e0b;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(245, 158, 11, 0.3);
}

.btn-creator{
    background: linear-gradient(135deg, #f59e0b, #fb923c);
    color: #fff;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
}

.btn-creator:hover{
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(245, 158, 11, 0.4);
}

/* ===== THEME TOGGLE ===== */
.theme-btn{
    width: 42px;
    height: 42px;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    background: #fff;
    color: #f59e0b;
    font-size: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.theme-btn:hover{
    transform: scale(1.1) rotate(20deg);
    box-shadow: 0 6px 16px rgba(245, 158, 11, 0.3);
}

.theme-btn:active{
    transform: scale(0.95);
}

/* ===== AVATAR ===== */
.avatar{
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f59e0b, #fb923c);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 800;
    cursor: pointer;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
}

.avatar::after{
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border-radius: 50%;
    border: 2px solid #fff;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.avatar:hover{
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(245, 158, 11, 0.5);
}

.avatar:hover::after{
    opacity: 1;
    animation: ripple 0.6s ease;
}

@keyframes ripple {
    0% { transform: scale(1); opacity: 1; }
    100% { transform: scale(1.5); opacity: 0; }
}

.avatar img{
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* ===== PROFILE OVERLAY ===== */
.profile-overlay{
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.4s ease;
    z-index: 998;
}

.profile-overlay.active{
    opacity: 1;
    pointer-events: auto;
}

/* ===== PROFILE SIDEBAR ===== */
.profile-sidebar{
    position: fixed;
    top: 0;
    right: -400px;
    width: 360px;
    height: 100vh;
    background: #fff;
    box-shadow: -20px 0 60px rgba(0, 0, 0, 0.3);
    transition: right 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    z-index: 999;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
}

.profile-sidebar.active{
    right: 0;
}

/* Close button */
.sidebar-close{
    position: absolute;
    top: 20px;
    right: 20px;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #f1f5f9;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: #64748b;
    transition: all 0.3s ease;
    z-index: 10;
}

.sidebar-close:hover{
    background: #f59e0b;
    color: #fff;
    transform: rotate(90deg);
}

/* Header */
.sidebar-header{
    padding: 32px 24px;
    background: linear-gradient(135deg, #f59e0b, #fb923c);
    display: flex;
    align-items: center;
    gap: 14px;
    position: relative;
    overflow: hidden;
}

.sidebar-header::before{
    content: "";
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(255,255,255,0.2), transparent 70%);
    border-radius: 50%;
}

.sidebar-avatar{
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: #fff;
    color: #f59e0b;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    font-weight: 900;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    overflow: hidden;
}

.sidebar-avatar img{
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.sidebar-user-info h4{
    color: #fff;
    font-size: 18px;
    font-weight: 800;
    margin-bottom: 4px;
}

.sidebar-user-info p{
    color: rgba(255, 255, 255, 0.9);
    font-size: 13px;
    text-transform: capitalize;
    background: rgba(255, 255, 255, 0.2);
    padding: 4px 12px;
    border-radius: 999px;
    display: inline-block;
}

/* Links */
.sidebar-links{
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.sidebar-links a{
    text-decoration: none;
    color: #0f172a;
    padding: 14px 16px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    display: flex;
    align-items: center;
    gap: 12px;
    position: relative;
    overflow: hidden;
}

.sidebar-links a i{
    font-size: 16px;
    width: 20px;
    transition: transform 0.3s ease;
}

.sidebar-links a::before{
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 4px;
    background: linear-gradient(135deg, #f59e0b, #fb923c);
    transform: scaleY(0);
    transition: transform 0.3s ease;
    border-radius: 0 4px 4px 0;
}

.sidebar-links a:hover{
    background: #fff7ed;
    color: #f59e0b;
    transform: translateX(8px);
}

.sidebar-links a:hover::before{
    transform: scaleY(1);
}

.sidebar-links a:hover i{
    transform: scale(1.2);
}

/* Logout button */
.logout-btn{
    margin: 24px;
    padding: 16px;
    text-align: center;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #fff;
    border-radius: 14px;
    text-decoration: none;
    font-weight: 700;
    font-size: 15px;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.logout-btn:hover{
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4);
}

.logout-btn i{
    font-size: 16px;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 968px) {
    .nav-links{
        display: none;
    }
    
    .navbar{
        padding: 0 20px;
    }
    
    .profile-sidebar{
        width: 100%;
        right: -100%;
    }
}

@media (max-width: 480px) {
    .navbar{
        height: 60px;
        padding: 0 16px;
    }
    
    .logo{
        font-size: 18px;
    }
    
    .btn-nav{
        padding: 8px 14px;
        font-size: 13px;
    }
    
    .nav-right{
        gap: 8px;
    }
}

/* ===== SCROLLBAR ===== */
.profile-sidebar::-webkit-scrollbar{
    width: 6px;
}

.profile-sidebar::-webkit-scrollbar-track{
    background: #f1f5f9;
}

.profile-sidebar::-webkit-scrollbar-thumb{
    background: #cbd5e1;
    border-radius: 10px;
}

.profile-sidebar::-webkit-scrollbar-thumb:hover{
    background: #f59e0b;
}

</style>

</head>

<body>

<!-- NAVBAR -->
<div class="nav-wrap">
    <nav class="navbar" id="navbar">
        
        <a href="/CroudSpark-X/public/index.php" class="logo">
            <span class="logo-icon">✨</span>
            Crowd<span>Spark</span>
        </a>

        <div class="nav-links">
            <a class="<?= $current=='index.php'?'active':'' ?>" href="/CroudSpark-X/public/index.php">
                <i class="fa-solid fa-house"></i> Home
            </a>

            <a href="/CroudSpark-X/public/explore-campaigns.php">
                <i class="fa-solid fa-layer-group"></i> Projects
            </a>

            <a href="/CroudSpark-X/public/about.php">
                <i class="fa-solid fa-circle-info"></i> About
            </a>

            <a href="/CroudSpark-X/public/contact.php">
                <i class="fa-solid fa-phone"></i> Contact
            </a>
        </div>

        <div class="nav-right">
            
            <!-- Theme Toggle (Optional) -->
            <!-- <button class="theme-btn" onclick="toggleTheme()">
                <i class="fa-solid fa-moon"></i>
            </button> -->

            <?php if(!isset($_SESSION['user_id'])): ?>

                <a href="/CroudSpark-X/user/login.php" class="btn-nav btn-login">
                    <i class="fa-solid fa-sign-in"></i> Login
                </a>
                <a href="/CroudSpark-X/user/login.php" class="btn-nav btn-creator">
                    <i class="fa-solid fa-rocket"></i> Start Project
                </a>

            <?php else: ?>

                <?php if($_SESSION['role']=="creator"): ?>
                    <a href="/CroudSpark-X/creator/create-campaign.php" class="btn-nav btn-creator">
                        <i class="fa-solid fa-plus"></i> New Campaign
                    </a>
                <?php elseif($_SESSION['role']=="admin"): ?>
                    <a href="/CroudSpark-X/admin/admin-dashboard.php" class="btn-nav btn-creator">
                        <i class="fa-solid fa-shield"></i> Admin
                    </a>
                <?php else: ?>
                    <a href="/CroudSpark-X/user/becomecreator.php" class="btn-nav btn-creator">
                        <i class="fa-solid fa-star"></i> Become Creator
                    </a>
                <?php endif; ?>

                <div class="avatar" onclick="openSidebar()">
                    <?php if(!empty($_SESSION['profile_image'])): ?>
                        <img src="<?= $_SESSION['profile_image'] ?>" alt="Profile">
                    <?php else: ?>
                        <?= strtoupper(substr($_SESSION['name'], 0, 1)); ?>
                    <?php endif; ?>
                </div>

            <?php endif; ?>

        </div>
    </nav>
</div>

<!-- SIDEBAR -->
<?php if(isset($_SESSION['user_id'])): ?>

<div id="overlay" class="profile-overlay" onclick="closeSidebar()"></div>

<div id="sidebar" class="profile-sidebar">
    
    <button class="sidebar-close" onclick="closeSidebar()">
        <i class="fa-solid fa-times"></i>
    </button>

    <div class="sidebar-header">
        <div class="sidebar-avatar">
            <?php if(!empty($_SESSION['profile_image'])): ?>
                <img src="<?= $_SESSION['profile_image'] ?>" alt="Profile">
            <?php else: ?>
                <?= strtoupper(substr($_SESSION['name'], 0, 1)); ?>
            <?php endif; ?>
        </div>

        <div class="sidebar-user-info">
            <h4><?= htmlspecialchars($_SESSION['name']); ?></h4>
            <p><?= ucfirst($_SESSION['role']); ?></p>
        </div>
    </div>

    <div class="sidebar-links">

        <?php if($_SESSION['role']=="admin"): ?>
            <a href="/CroudSpark-X/admin/admin-dashboard.php">
                <i class="fa-solid fa-shield"></i> Admin Dashboard
            </a>
        <?php endif; ?>

        <?php if($_SESSION['role']=="creator"): ?>
            <a href="/CroudSpark-X/creator/creator-dashboard.php">
                <i class="fa-solid fa-chart-line"></i> Creator Dashboard
            </a>
            <a href="/CroudSpark-X/creator/my-campaigns.php">
                <i class="fa-solid fa-layer-group"></i> My Campaigns
            </a>
        <?php endif; ?>

        <a href="/CroudSpark-X/dashboard/user-dashboard.php">
            <i class="fa-solid fa-user"></i> My Dashboard
        </a>
        <a href="/CroudSpark-X/dashboard/edit-profile.php">
            <i class="fa-solid fa-pen"></i> Edit Profile
        </a>
        <a href="/CroudSpark-X/dashboard/change-password.php">
            <i class="fa-solid fa-lock"></i> Change Password
        </a>
        <a href="/CroudSpark-X/dashboard/my-donations.php">
            <i class="fa-solid fa-heart"></i> My Donations
        </a>

    </div>

    <a href="/CroudSpark-X/user/logout.php" class="logout-btn">
        <i class="fa-solid fa-sign-out"></i> Logout
    </a>

</div>

<?php endif; ?>

<script>
// Sidebar controls
function openSidebar(){
    document.getElementById("sidebar").classList.add("active");
    document.getElementById("overlay").classList.add("active");
    document.body.style.overflow = "hidden";
}

function closeSidebar(){
    document.getElementById("sidebar").classList.remove("active");
    document.getElementById("overlay").classList.remove("active");
    document.body.style.overflow = "auto";
}

// Navbar scroll effect
let lastScroll = 0;
window.addEventListener('scroll', () => {
    const navbar = document.getElementById('navbar');
    const currentScroll = window.pageYOffset;
    
    if (currentScroll > 50) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
    
    lastScroll = currentScroll;
});

// Theme toggle (optional)
function toggleTheme(){
    document.body.classList.toggle('dark');
    const icon = document.querySelector('.theme-btn i');
    if(document.body.classList.contains('dark')){
        icon.className = 'fa-solid fa-sun';
    } else {
        icon.className = 'fa-solid fa-moon';
    }
}
</script>

</body>
</html>