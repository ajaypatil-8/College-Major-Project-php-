<?php
require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/../config/db.php";
?>

<style>
/* ===== HERO SECTION ===== */
.hero{
    padding: 160px 20px 120px;
    text-align: center;
    background: linear-gradient(135deg, #fff7ed 0%, #ffffff 50%, #fef3c7 100%);
    position: relative;
    overflow: hidden;
}

/* Animated gradient orbs */
.hero::before,
.hero::after{
    content: "";
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.4;
    animation: float 8s ease-in-out infinite;
}

.hero::before{
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, #f59e0b, transparent 70%);
    top: -150px;
    right: -100px;
}

.hero::after{
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, #fb923c, transparent 70%);
    bottom: -100px;
    left: -100px;
    animation-delay: 2s;
}

@keyframes float {
    0%, 100% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(30px, -30px) scale(1.1); }
}

.hero-content{
    position: relative;
    z-index: 10;
}

.hero h1{
    font-size: 62px;
    font-weight: 900;
    margin-bottom: 24px;
    background: linear-gradient(120deg, #f59e0b, #fb923c, #f59e0b);
    background-size: 200% auto;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: gradientShift 3s ease infinite;
    letter-spacing: -1px;
    line-height: 1.1;
}

@keyframes gradientShift {
    0%, 100% { background-position: 0% center; }
    50% { background-position: 100% center; }
}

.hero .subtitle{
    max-width: 680px;
    margin: 0 auto 40px;
    color: var(--text-muted);
    font-size: 20px;
    line-height: 1.7;
    font-weight: 500;
}

.hero-actions{
    margin-top: 50px;
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-explore{
    background: linear-gradient(135deg, #f59e0b, #fb923c);
    color: #fff;
    padding: 20px 48px;
    font-size: 18px;
    border-radius: 999px;
    font-weight: 700;
    text-decoration: none;
    box-shadow: 0 20px 40px rgba(245, 158, 11, 0.3);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    display: inline-flex;
    align-items: center;
    gap: 10px;
    position: relative;
    overflow: hidden;
}

.btn-explore::before{
    content: "";
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: 0.5s;
}

.btn-explore:hover::before{
    left: 100%;
}

.btn-explore:hover{
    transform: translateY(-5px) scale(1.05);
    box-shadow: 0 30px 60px rgba(245, 158, 11, 0.4);
}

.btn-secondary{
    background: #fff;
    color: #f59e0b;
    padding: 20px 48px;
    font-size: 18px;
    border-radius: 999px;
    font-weight: 700;
    text-decoration: none;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 2px solid #f59e0b;
}

.btn-secondary:hover{
    transform: translateY(-3px);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
    background: #f59e0b;
    color: #fff;
}

/* Floating stats */
.hero-stats{
    display: flex;
    justify-content: center;
    gap: 30px;
    margin-top: 80px;
    flex-wrap: wrap;
}

.stat{
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    padding: 24px 42px;
    border-radius: 20px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.08);
    font-weight: 700;
    font-size: 16px;
    border: 1px solid rgba(245, 158, 11, 0.1);
    transition: all 0.3s ease;
    animation: fadeInUp 0.6s ease forwards;
    opacity: 0;
}

.stat:nth-child(1){ animation-delay: 0.1s; }
.stat:nth-child(2){ animation-delay: 0.2s; }
.stat:nth-child(3){ animation-delay: 0.3s; }

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
    from {
        opacity: 0;
        transform: translateY(20px);
    }
}

.stat:hover{
    transform: translateY(-8px);
    box-shadow: 0 25px 60px rgba(245, 158, 11, 0.2);
}

/* ===== HOW IT WORKS ===== */
.how-it-works{
    padding: 120px 20px;
    text-align: center;
    background: var(--bg-main);
}

.how-it-works h2{
    font-size: 42px;
    font-weight: 900;
    margin-bottom: 16px;
    color: var(--text-main);
}

.section-subtitle{
    color: var(--text-muted);
    font-size: 18px;
    max-width: 600px;
    margin: 0 auto 60px;
}

.hiw-steps{
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 40px;
    margin-top: 70px;
    max-width: 1100px;
    margin-left: auto;
    margin-right: auto;
}

.hiw-step{
    background: var(--bg-card);
    padding: 50px 35px;
    border-radius: 24px;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.06);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: 1px solid var(--border);
    position: relative;
    overflow: hidden;
}

.hiw-step::before{
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, #f59e0b, #fb923c);
    transform: scaleX(0);
    transition: transform 0.4s ease;
}

.hiw-step:hover::before{
    transform: scaleX(1);
}

.hiw-step:hover{
    transform: translateY(-15px);
    box-shadow: 0 35px 80px rgba(245, 158, 11, 0.15);
}

.step-number{
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #f59e0b, #fb923c);
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    margin: 0 auto 25px;
    font-size: 28px;
    box-shadow: 0 15px 35px rgba(245, 158, 11, 0.3);
    transition: all 0.3s ease;
}

.hiw-step:hover .step-number{
    transform: scale(1.1) rotate(360deg);
}

.hiw-step h3{
    font-size: 22px;
    font-weight: 800;
    margin-bottom: 12px;
    color: var(--text-main);
}

.hiw-step p{
    color: var(--text-muted);
    font-size: 15px;
    line-height: 1.6;
}

/* ===== CAMPAIGNS ===== */
.campaign-preview{
    padding: 120px 20px;
    background: var(--bg-soft);
}

.campaign-preview h2{
    text-align: center;
    font-size: 42px;
    font-weight: 900;
    margin-bottom: 60px;
    color: var(--text-main);
}

.campaign-grid{
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 40px;
    margin-top: 60px;
    max-width: 1200px;
    margin-left: auto;
    margin-right: auto;
}

.campaign-card{
    background: #fff;
    border-radius: 28px;
    overflow: hidden;
    box-shadow: 0 30px 70px rgba(0, 0, 0, 0.08);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.campaign-card:hover{
    transform: translateY(-16px) scale(1.02);
    box-shadow: 0 40px 90px rgba(245, 158, 11, 0.2);
}

.campaign-img-wrapper{
    position: relative;
    overflow: hidden;
    height: 220px;
}

.campaign-card img{
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.campaign-card:hover img{
    transform: scale(1.1);
}

.campaign-body{
    padding: 28px;
}

.campaign-body h3{
    font-size: 20px;
    font-weight: 800;
    margin-bottom: 10px;
    color: #111;
    line-height: 1.3;
}

.campaign-body p{
    color: #64748b;
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 16px;
}

.progress-bar{
    height: 12px;
    background: #f1f5f9;
    border-radius: 20px;
    overflow: hidden;
    margin: 18px 0;
    position: relative;
}

.progress-fill{
    height: 100%;
    background: linear-gradient(90deg, #f59e0b, #fb923c);
    border-radius: 20px;
    transition: width 0.8s ease;
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

.campaign-btn{
    display: block;
    text-align: center;
    background: linear-gradient(135deg, #111, #333);
    color: #fff;
    padding: 15px;
    border-radius: 14px;
    text-decoration: none;
    font-weight: 700;
    margin-top: 16px;
    transition: all 0.3s ease;
    font-size: 15px;
}

.campaign-btn:hover{
    background: linear-gradient(135deg, #f59e0b, #fb923c);
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(245, 158, 11, 0.3);
}

/* ===== TRUST SECTION ===== */
.trust-section{
    padding: 120px 20px;
    text-align: center;
    background: var(--bg-main);
}

.trust-section h2{
    font-size: 42px;
    font-weight: 900;
    margin-bottom: 70px;
    color: var(--text-main);
}

.trust-grid{
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 35px;
    margin-top: 60px;
    max-width: 1100px;
    margin-left: auto;
    margin-right: auto;
}

.trust-card{
    background: var(--bg-card);
    padding: 45px 35px;
    border-radius: 24px;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.06);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: 1px solid var(--border);
    position: relative;
}

.trust-card::before{
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #f59e0b, #fb923c);
    border-radius: 24px 24px 0 0;
    transform: scaleX(0);
    transition: transform 0.4s ease;
}

.trust-card:hover::before{
    transform: scaleX(1);
}

.trust-card:hover{
    transform: translateY(-12px);
    box-shadow: 0 35px 80px rgba(245, 158, 11, 0.15);
}

.trust-icon{
    font-size: 52px;
    margin-bottom: 18px;
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.trust-card:hover .trust-icon{
    animation: none;
    transform: scale(1.2);
    transition: transform 0.3s ease;
}

.trust-card h3{
    font-size: 20px;
    font-weight: 800;
    margin-bottom: 8px;
    color: var(--text-main);
}

.trust-card p{
    color: var(--text-muted);
    font-size: 15px;
}

/* Responsive */
@media (max-width: 768px) {
    .hero h1{
        font-size: 42px;
    }
    
    .hero .subtitle{
        font-size: 17px;
    }
    
    .hero-actions{
        flex-direction: column;
        align-items: center;
    }
    
    .btn-explore, .btn-secondary{
        width: 100%;
        max-width: 300px;
    }
    
    .how-it-works h2,
    .campaign-preview h2,
    .trust-section h2{
        font-size: 32px;
    }
}
</style>

<!-- HERO -->
<section class="hero">
    <div class="hero-content">
        <h1>Support Dreams. Change Lives.</h1>
        <p class="subtitle">Discover verified campaigns, support real people and make an impact with secure, transparent crowdfunding.</p>

        <div class="hero-actions">
            <a href="/CroudSpark-X/public/explore-campaigns.php" class="btn-explore">
                🚀 Explore Campaigns
            </a>
            <a href="/CroudSpark-X/creator/create-campaign.php" class="btn-secondary">
                Start Campaign
            </a>
        </div>

        <div class="hero-stats">
            <div class="stat">🚀 1,200+ Live Fundraisers</div>
            <div class="stat">💳 100% Secure Donations</div>
            <div class="stat">❤️ Real Impact Stories</div>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section class="how-it-works">
    <h2>How CrowdSpark Works</h2>
    <p class="section-subtitle">Start making a difference in three simple steps</p>

    <div class="hiw-steps">
        <div class="hiw-step">
            <div class="step-number">1</div>
            <h3>Browse Campaigns</h3>
            <p>Explore verified campaigns across India. Every campaign is admin-approved for authenticity.</p>
        </div>

        <div class="hiw-step">
            <div class="step-number">2</div>
            <h3>Donate Securely</h3>
            <p>Support causes with safe, encrypted payments. Your contribution goes directly to those in need.</p>
        </div>

        <div class="hiw-step">
            <div class="step-number">3</div>
            <h3>Track Impact</h3>
            <p>See how your donation helps people in real-time. Receive updates and impact reports.</p>
        </div>
    </div>
</section>

<!-- CAMPAIGNS -->
<section class="campaign-preview">
    <h2>Trending Campaigns</h2>

    <div class="campaign-grid">
        <?php
        $stmt = $pdo->prepare("SELECT * FROM campaigns WHERE status='approved' ORDER BY id DESC LIMIT 6");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($data){
            foreach($data as $row){
        ?>
        <div class="campaign-card">
            <div class="campaign-img-wrapper">
                <img src="<?= $row['media_url'] ?? '/CroudSpark-X/assets/noimg.jpg' ?>" 
                     alt="<?= htmlspecialchars($row['title']) ?>">
            </div>

            <div class="campaign-body">
                <h3><?= htmlspecialchars($row['title']) ?></h3>
                <p><?= substr($row['short_desc'] ?? 'Help make a difference', 0, 90) ?>...</p>

                <div class="progress-bar">
                    <div class="progress-fill" style="width:40%"></div>
                </div>

                <a class="campaign-btn" href="/CroudSpark-X/public/campaign-details.php?id=<?= $row['id'] ?>">
                    View Campaign →
                </a>
            </div>
        </div>
        <?php 
            }
        } else {
            echo '<p style="text-align:center;color:var(--text-muted);">No campaigns available yet. Check back soon!</p>';
        }
        ?>
    </div>
</section>

<!-- TRUST -->
<section class="trust-section">
    <h2>Why Choose CrowdSpark</h2>

    <div class="trust-grid">
        <div class="trust-card">
            <div class="trust-icon">🔒</div>
            <h3>Bank-Level Security</h3>
            <p>Protected donations with SSL encryption</p>
        </div>
        
        <div class="trust-card">
            <div class="trust-icon">✔</div>
            <h3>100% Verified</h3>
            <p>All campaigns admin-approved & validated</p>
        </div>
        
        <div class="trust-card">
            <div class="trust-icon">📊</div>
            <h3>Full Transparency</h3>
            <p>Track fund utilization in real-time</p>
        </div>
        
        <div class="trust-card">
            <div class="trust-icon">👥</div>
            <h3>Trusted Community</h3>
            <p>Join thousands of active donors</p>
        </div>
    </div>
</section>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>