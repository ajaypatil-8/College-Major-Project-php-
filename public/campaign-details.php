<?php
session_start();
require_once __DIR__."/../config/db.php";

$id = $_GET['id'] ?? null;
if(!$id){
    die("Campaign not found");
}

/* ===== GET CAMPAIGN WITH CREATOR INFO ===== */
$stmt=$pdo->prepare("
    SELECT c.*, u.name as creator_name, u.profile_image as creator_image 
    FROM campaigns c 
    LEFT JOIN users u ON c.user_id = u.id 
    WHERE c.id=? AND c.status='approved'
");
$stmt->execute([$id]);
$c=$stmt->fetch(PDO::FETCH_ASSOC);

if(!$c){
    die("Campaign not found or not approved");
}

/* ===== GET ALL DONATIONS & CALCULATE RAISED AMOUNT ===== */
$donationStmt = $pdo->prepare("SELECT IFNULL(SUM(amount),0) as raised, COUNT(*) as donors FROM donations WHERE campaign_id=? AND status='success'");
$donationStmt->execute([$id]);
$donationData = $donationStmt->fetch(PDO::FETCH_ASSOC);
$raised = $donationData['raised'];
$donors = $donationData['donors'];
$progress = $c['goal'] > 0 ? round(($raised / $c['goal']) * 100, 2) : 0;

/* ===== GET MEDIA ===== */
$m=$pdo->prepare("SELECT * FROM campaign_media WHERE campaign_id=? ORDER BY id ASC");
$m->execute([$id]);
$media=$m->fetchAll(PDO::FETCH_ASSOC);

/* ===== COMBINE ALL MEDIA FOR CAROUSEL (THUMBNAIL FIRST) ===== */
$allMedia = [];

// Add thumbnail first
if($c['thumbnail']) {
    $allMedia[] = ['type' => 'image', 'url' => $c['thumbnail']];
}

// Add other media
foreach($media as $mm){
    if($mm['media_type'] == "image" && $mm['media_url'] != $c['thumbnail']) {
        $allMedia[] = ['type' => 'image', 'url' => $mm['media_url']];
    }
    if($mm['media_type'] == "video") {
        $allMedia[] = ['type' => 'video', 'url' => $mm['media_url']];
    }
}
?>

<?php require_once __DIR__."/../includes/header.php"; ?>

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

/* Animated Background - Blue/Cyan theme */
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
    background: linear-gradient(45deg, #06b6d4, #22d3ee);
    top: -10%;
    left: -10%;
    animation-delay: 0s;
}

.orb-2 {
    width: 400px;
    height: 400px;
    background: linear-gradient(45deg, #0891b2, #06b6d4);
    bottom: -10%;
    right: -10%;
    animation-delay: 5s;
}

.orb-3 {
    width: 350px;
    height: 350px;
    background: linear-gradient(45deg, #0e7490, #0891b2);
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
    from { opacity: 0; transform: translateY(40px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes slideIn {
    from { opacity: 0; transform: translateX(-20px); }
    to { opacity: 1; transform: translateX(0); }
}

/* ===== PAGE CONTAINER ===== */
.campaign-page {
    position: relative;
    z-index: 1;
    min-height: 100vh;
    padding: 140px 20px 80px;
}

.campaign-container {
    max-width: 1400px;
    margin: 0 auto;
    animation: fadeInUp 0.8s ease;
}

.campaign-grid {
    display: grid;
    grid-template-columns: 1.3fr 0.7fr;
    gap: 50px;
    align-items: start;
}

/* ===== MEDIA CAROUSEL ===== */
.media-carousel-wrapper {
    position: relative;
    margin-bottom: 40px;
    border-radius: 24px;
    overflow: hidden;
    background: rgba(20, 20, 30, 0.6);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    box-shadow: 0 20px 60px rgba(6, 182, 212, 0.15);
    animation: slideIn 0.6s ease-out;
}

.media-carousel {
    position: relative;
    width: 100%;
    height: 550px;
    overflow: hidden;
}

.media-slides {
    display: flex;
    width: 100%;
    height: 100%;
    transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.media-slide {
    min-width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #000;
}

.media-slide img,
.media-slide video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Carousel Navigation */
.carousel-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 100%;
    display: flex;
    justify-content: space-between;
    padding: 0 20px;
    z-index: 10;
    pointer-events: none;
}

.carousel-btn {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: rgba(6, 182, 212, 0.9);
    backdrop-filter: blur(10px);
    border: none;
    color: #fff;
    font-size: 20px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: all;
}

.carousel-btn:hover {
    background: #06b6d4;
    transform: scale(1.1);
    box-shadow: 0 8px 20px rgba(6, 182, 212, 0.5);
}

/* Carousel Indicators */
.carousel-indicators {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 10px;
    z-index: 10;
}

.carousel-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.4);
    cursor: pointer;
    transition: all 0.3s ease;
}

.carousel-dot.active {
    width: 36px;
    border-radius: 6px;
    background: #06b6d4;
    box-shadow: 0 0 15px rgba(6, 182, 212, 0.6);
}

.carousel-counter {
    position: absolute;
    top: 20px;
    right: 20px;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(10px);
    padding: 10px 18px;
    border-radius: 999px;
    font-size: 14px;
    font-weight: 700;
    color: #06b6d4;
    z-index: 10;
}

/* ===== CAMPAIGN INFO ===== */
.campaign-title {
    font-family: 'Playfair Display', serif;
    font-size: 3.2rem;
    font-weight: 900;
    margin: 0 0 20px;
    background: linear-gradient(135deg, #fff, #06b6d4);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    line-height: 1.2;
}

.creator-info {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 30px;
    padding: 16px;
    background: rgba(20, 20, 30, 0.4);
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.creator-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #06b6d4;
}

.creator-name {
    font-weight: 700;
    color: #fff;
}

.creator-label {
    font-size: 12px;
    color: #94a3b8;
}

.campaign-meta {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 30px;
    padding: 24px;
    background: rgba(20, 20, 30, 0.6);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.meta-item {
    text-align: center;
}

.meta-label {
    font-size: 12px;
    font-weight: 800;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
    display: block;
}

.meta-value {
    font-size: 20px;
    font-weight: 900;
    color: #fff;
    display: block;
}

.meta-value.highlight {
    color: #06b6d4;
    font-size: 24px;
}

.section-card {
    background: rgba(20, 20, 30, 0.6);
    backdrop-filter: blur(10px);
    padding: 32px;
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    margin-bottom: 24px;
}

.section-card h3 {
    font-size: 24px;
    font-weight: 900;
    color: #fff;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-card h3::before {
    content: '';
    width: 4px;
    height: 24px;
    background: linear-gradient(135deg, #06b6d4, #22d3ee);
    border-radius: 2px;
}

.section-card p {
    line-height: 1.8;
    color: #cbd5e1;
    font-size: 16px;
}

/* ===== DONATE SIDEBAR ===== */
.donate-sidebar {
    position: sticky;
    top: 140px;
    animation: slideIn 0.6s ease-out 0.2s both;
}

.donate-card {
    background: rgba(20, 20, 30, 0.9);
    backdrop-filter: blur(20px);
    padding: 36px;
    border-radius: 24px;
    box-shadow: 0 20px 60px rgba(6, 182, 212, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.15);
    transition: all 0.4s ease;
    position: relative;
    overflow: hidden;
}

.donate-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #06b6d4, #22d3ee);
}

.progress-section {
    margin-bottom: 30px;
}

.progress-stats {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
}

.stat-raised {
    font-size: 28px;
    font-weight: 900;
    color: #06b6d4;
}

.stat-goal {
    font-size: 14px;
    color: #94a3b8;
    margin-top: 4px;
}

.progress-bar-wrapper {
    height: 12px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 999px;
    overflow: hidden;
    margin-bottom: 16px;
}

.progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #06b6d4, #22d3ee);
    border-radius: 999px;
    transition: width 1s ease;
    box-shadow: 0 0 10px rgba(6, 182, 212, 0.5);
}

.progress-info {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    text-align: center;
}

.info-item {
    padding: 12px;
    background: rgba(6, 182, 212, 0.1);
    border-radius: 12px;
}

.info-value {
    font-size: 20px;
    font-weight: 900;
    color: #06b6d4;
}

.info-label {
    font-size: 12px;
    color: #94a3b8;
    margin-top: 4px;
}

.donate-section h3 {
    font-size: 22px;
    font-weight: 900;
    color: #fff;
    margin-bottom: 20px;
    text-align: center;
}

.login-prompt {
    text-align: center;
    padding: 20px;
    color: #cbd5e1;
    line-height: 1.6;
    margin-bottom: 20px;
}

.donate-form label {
    display: block;
    font-size: 12px;
    font-weight: 800;
    color: #fff;
    margin-bottom: 10px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.donate-form input {
    width: 100%;
    padding: 16px 20px;
    border-radius: 12px;
    border: 2px solid rgba(255, 255, 255, 0.15);
    font-size: 20px;
    background: rgba(10, 10, 20, 0.6);
    transition: all 0.3s ease;
    font-weight: 700;
    color: #fff;
    font-family: 'DM Sans', sans-serif;
    margin-bottom: 20px;
    text-align: center;
}

.donate-form input:focus {
    outline: none;
    border-color: #06b6d4;
    box-shadow: 0 0 0 4px rgba(6, 182, 212, 0.15);
}

.donate-btn {
    width: 100%;
    padding: 18px;
    border: none;
    border-radius: 50px;
    background: linear-gradient(135deg, #06b6d4, #22d3ee);
    color: #fff;
    font-weight: 900;
    font-size: 17px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 12px 30px rgba(6, 182, 212, 0.4);
    text-transform: uppercase;
    letter-spacing: 1px;
    text-decoration: none;
    display: block;
    text-align: center;
}

.donate-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 16px 40px rgba(6, 182, 212, 0.5);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 1200px) {
    .campaign-grid {
        grid-template-columns: 1fr;
    }
    
    .donate-sidebar {
        position: static;
        max-width: 600px;
        margin: 0 auto;
    }
}

@media (max-width: 768px) {
    .campaign-page {
        padding: 120px 15px 60px;
    }
    
    .campaign-title {
        font-size: 2.2rem;
    }
    
    .media-carousel {
        height: 350px;
    }
    
    .campaign-meta {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    
    .carousel-btn {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }
}
</style>

<!-- Background Animation -->
<div class="bg-animation">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
</div>

<div class="campaign-page">
    <div class="campaign-container">
        <div class="campaign-grid">
            
            <!-- LEFT COLUMN - CAMPAIGN DETAILS -->
            <div class="campaign-content">
                
                <!-- SWIPEABLE MEDIA CAROUSEL -->
                <?php if(!empty($allMedia)): ?>
                <div class="media-carousel-wrapper">
                    <div class="media-carousel">
                        
                        <!-- Counter -->
                        <div class="carousel-counter">
                            <span id="currentSlide">1</span> / <?= count($allMedia) ?>
                        </div>
                        
                        <!-- Slides -->
                        <div class="media-slides" id="mediaSlides">
                            <?php foreach($allMedia as $item): ?>
                            <div class="media-slide">
                                <?php if($item['type'] === 'image'): ?>
                                    <img src="<?= htmlspecialchars($item['url']) ?>" alt="Campaign Media">
                                <?php else: ?>
                                    <video src="<?= htmlspecialchars($item['url']) ?>" controls></video>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Navigation Arrows -->
                        <div class="carousel-nav">
                            <button class="carousel-btn" onclick="changeSlide(-1)">
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>
                            <button class="carousel-btn" onclick="changeSlide(1)">
                                <i class="fa-solid fa-chevron-right"></i>
                            </button>
                        </div>
                        
                        <!-- Indicators -->
                        <div class="carousel-indicators">
                            <?php foreach($allMedia as $index => $item): ?>
                            <span class="carousel-dot <?= $index === 0 ? 'active' : '' ?>" 
                                  onclick="goToSlide(<?= $index ?>)"></span>
                            <?php endforeach; ?>
                        </div>
                        
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Title -->
                <h1 class="campaign-title"><?= htmlspecialchars($c['title']) ?></h1>
                
                <!-- Creator Info -->
                <div class="creator-info">
                    <img src="<?= $c['creator_image'] ?: 'https://via.placeholder.com/48' ?>" 
                         alt="Creator" class="creator-avatar">
                    <div>
                        <div class="creator-label">Created by</div>
                        <div class="creator-name"><?= htmlspecialchars($c['creator_name']) ?></div>
                    </div>
                </div>
                
                <!-- Meta Information -->
                <div class="campaign-meta">
                    <div class="meta-item">
                        <span class="meta-label">📁 Category</span>
                        <span class="meta-value"><?= htmlspecialchars($c['category']) ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">📍 Location</span>
                        <span class="meta-value"><?= htmlspecialchars($c['location']) ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">🎯 Goal</span>
                        <span class="meta-value highlight">₹<?= number_format($c['goal']) ?></span>
                    </div>
                </div>
                
                <!-- Short Description -->
                <?php if($c['short_desc']): ?>
                <div class="section-card">
                    <h3>Overview</h3>
                    <p><?= nl2br(htmlspecialchars($c['short_desc'])) ?></p>
                </div>
                <?php endif; ?>
                
                <!-- Full Story -->
                <?php if($c['story']): ?>
                <div class="section-card">
                    <h3>Campaign Story</h3>
                    <p><?= nl2br(htmlspecialchars($c['story'])) ?></p>
                </div>
                <?php endif; ?>
                
            </div>
            
            <!-- RIGHT COLUMN - DONATE CARD -->
            <div class="donate-sidebar">
                <div class="donate-card">
                    
                    <!-- Progress Section -->
                    <div class="progress-section">
                        <div class="progress-stats">
                            <div>
                                <div class="stat-raised">₹<?= number_format($raised) ?></div>
                                <div class="stat-goal">raised of ₹<?= number_format($c['goal']) ?></div>
                            </div>
                            <div style="text-align: right;">
                                <div class="stat-raised"><?= $progress ?>%</div>
                                <div class="stat-goal">funded</div>
                            </div>
                        </div>
                        
                        <div class="progress-bar-wrapper">
                            <div class="progress-bar" style="width: <?= min($progress, 100) ?>%"></div>
                        </div>
                        
                        <div class="progress-info">
                            <div class="info-item">
                                <div class="info-value"><?= $donors ?></div>
                                <div class="info-label">Backers</div>
                            </div>
                            <div class="info-item">
                                <div class="info-value"><?= ceil((strtotime($c['end_date']) - time()) / 86400) ?></div>
                                <div class="info-label">Days Left</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Donate Section -->
                    <div class="donate-section">
                        <h3>💰 Support This Campaign</h3>
                        
                        <?php if(!isset($_SESSION['user_id'])): ?>
                        
                        <div class="login-prompt">
                            <p>Please login to support this campaign and help make a difference!</p>
                        </div>
                        <a href="/CroudSpark-X/user/login.php" class="donate-btn">
                            <i class="fa fa-sign-in-alt"></i> Login to Donate
                        </a>
                        
                        <?php else: ?>
                        
                        <form action="/CroudSpark-X/public/fake-payment.php" method="POST" class="donate-form">
                            <input type="hidden" name="campaign_id" value="<?= $c['id'] ?>">
                            
                            <label>Enter Amount (₹)</label>
                            <input 
                                type="number" 
                                name="amount" 
                                required
                                placeholder="₹ 1,000"
                                min="1"
                            >
                            
                            <button type="submit" class="donate-btn">
                                <i class="fa fa-heart"></i> Donate Now
                            </button>
                        </form>
                        
                        <?php endif; ?>
                    </div>
                    
                </div>
            </div>
            
        </div>
    </div>
</div>

<script>
// Carousel functionality
let currentIndex = 0;
const totalSlides = <?= count($allMedia) ?>;

function updateCarousel() {
    const slides = document.getElementById('mediaSlides');
    const dots = document.querySelectorAll('.carousel-dot');
    const counter = document.getElementById('currentSlide');
    
    slides.style.transform = `translateX(-${currentIndex * 100}%)`;
    counter.textContent = currentIndex + 1;
    
    dots.forEach((dot, index) => {
        dot.classList.toggle('active', index === currentIndex);
    });
    
    // Pause all videos
    document.querySelectorAll('.media-slide video').forEach(v => v.pause());
}

function changeSlide(direction) {
    currentIndex += direction;
    if (currentIndex < 0) currentIndex = totalSlides - 1;
    if (currentIndex >= totalSlides) currentIndex = 0;
    updateCarousel();
}

function goToSlide(index) {
    currentIndex = index;
    updateCarousel();
}

// Keyboard navigation
document.addEventListener('keydown', e => {
    if (e.key === 'ArrowLeft') changeSlide(-1);
    if (e.key === 'ArrowRight') changeSlide(1);
});

// Touch swipe
let touchStart = 0;
let touchEnd = 0;
const carousel = document.querySelector('.media-carousel');

carousel.addEventListener('touchstart', e => {
    touchStart = e.changedTouches[0].screenX;
});

carousel.addEventListener('touchend', e => {
    touchEnd = e.changedTouches[0].screenX;
    if (touchStart - touchEnd > 50) changeSlide(1);
    if (touchEnd - touchStart > 50) changeSlide(-1);
});
</script>

<?php require_once __DIR__."/../includes/footer.php"; ?>