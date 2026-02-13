<?php
require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/../config/db.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CrowdSpark - Support Dreams, Change Lives</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    
    <style>
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
            --border-hover: rgba(245, 158, 11, 0.3);
            
            --orb-opacity: 0.15;
            
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
            --border-hover: rgba(245, 158, 11, 0.4);
            
            --orb-opacity: 0.25;
            
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.3);
            --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.4);
            --shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.5);
        }

        /* Orb colors for Home page */
        [data-theme="dark"] {
            --orb-1: linear-gradient(45deg, #f59e0b, #fb923c);
            --orb-2: linear-gradient(45deg, #ea580c, #f59e0b);
            --orb-3: linear-gradient(45deg, #fbbf24, #fb923c);
        }

        [data-theme="light"] {
            --orb-1: linear-gradient(45deg, #f59e0b, #fb923c);
            --orb-2: linear-gradient(45deg, #ea580c, #f59e0b);
            --orb-3: linear-gradient(45deg, #fbbf24, #fb923c);
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

        /* Animated Background - Orange theme for Home */
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

        /* Hero Section */
        .hero {
            position: relative;
            z-index: 1;
            padding: 110px 40px 30px;
            text-align: center;
            max-width: 1400px;
            margin: 0 auto;
        }

        .hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(3rem, 8vw, 6rem);
            font-weight: 900;
            margin-bottom: 24px;
            background: linear-gradient(135deg, #fff, #f59e0b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1.1;
            animation: fadeInDown 0.8s ease;
        }

        [data-theme="light"] .hero h1 {
            background: linear-gradient(135deg, #0f172a, #f59e0b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero .subtitle {
            max-width: 700px;
            margin: 0 auto 50px;
            color: var(--text-secondary);
            font-size: 1.25rem;
            line-height: 1.7;
            font-weight: 500;
            animation: fadeInUp 0.8s ease 0.2s both;
        }

        .hero-actions {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 0.8s ease 0.4s both;
        }

        .btn {
            padding: 18px 40px;
            font-size: 1.1rem;
            border-radius: 999px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn:active::before {
            width: 300px;
            height: 300px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #f59e0b, #fb923c);
            color: #fff;
            box-shadow: 0 20px 40px rgba(245, 158, 11, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 25px 50px rgba(245, 158, 11, 0.4);
        }

        .btn-secondary {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            color: var(--text-primary);
            border: 2px solid rgba(245, 158, 11, 0.3);
        }

        .btn-secondary:hover {
            background: var(--bg-card-hover);
            border-color: #f59e0b;
            transform: translateY(-3px);
        }

        /* Hero Stats */
        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 80px;
            flex-wrap: wrap;
            animation: fadeInUp 0.8s ease 0.6s both;
        }

        .stat {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            padding: 24px 40px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s ease;
            color: var(--text-primary);
        }

        .stat:hover {
            transform: translateY(-5px);
            border-color: var(--border-hover);
            box-shadow: var(--shadow-md);
        }

        /* How It Works */
        .how-it-works {
            position: relative;
            z-index: 1;
            padding: 80px 40px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            margin-bottom: 70px;
            animation: fadeInUp 0.8s ease;
        }

        .section-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.5rem, 6vw, 4rem);
            font-weight: 900;
            margin-bottom: 16px;
            background: linear-gradient(135deg, #fff, #f59e0b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        [data-theme="light"] .section-header h2 {
            background: linear-gradient(135deg, #0f172a, #f59e0b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .section-header p {
            color: var(--text-secondary);
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .steps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
        }

        .step-card {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            padding: 50px 35px;
            border-radius: 24px;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            animation: cardAppear 0.6s ease both;
        }

        .step-card:nth-child(1) { animation-delay: 0.1s; }
        .step-card:nth-child(2) { animation-delay: 0.2s; }
        .step-card:nth-child(3) { animation-delay: 0.3s; }

        .step-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #f59e0b, #fb923c);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .step-card:hover::before {
            transform: scaleX(1);
        }

        .step-card:hover {
            transform: translateY(-10px);
            border-color: var(--border-hover);
            background: var(--bg-card-hover);
        }

        .step-number {
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

        .step-card:hover .step-number {
            transform: scale(1.1) rotate(360deg);
        }

        .step-card h3 {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 12px;
            color: var(--text-primary);
            text-align: center;
        }

        .step-card p {
            color: var(--text-secondary);
            font-size: 1rem;
            line-height: 1.7;
            text-align: center;
        }

        /* Campaign Card */
        .campaign-preview{
            position: relative;
            z-index: 1;
            padding: 60px 40px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .campaigns-grid{
            display:grid;
            grid-template-columns:repeat(auto-fill,minmax(320px,1fr));
            gap:35px;
            margin-top:60px;
        }

        .campaign-card{
            position:relative;
            border-radius:24px;
            overflow:hidden;
            cursor:pointer;
            transition:all .45s cubic-bezier(.4,0,.2,1);
            background: var(--bg-card);
            backdrop-filter:blur(18px);
            -webkit-backdrop-filter:blur(18px);
            border:1px solid var(--border-color);
            box-shadow: var(--shadow-md);
        }

        .campaign-card::before{
            content:"";
            position:absolute;
            top:0;
            left:-120%;
            width:60%;
            height:100%;
            background:linear-gradient(
            120deg,
            transparent,
            rgba(255,255,255,0.25),
            transparent
            );
            transform:skewX(-25deg);
            transition:.7s;
        }

        .campaign-card:hover::before{
            left:130%;
        }

        .campaign-card:hover{
            transform:translateY(-12px) scale(1.02);
            border:1px solid rgba(241,150,4,0.5);
            box-shadow: var(--shadow-lg);
        }

        .card-image {
            width: 100%;
            height: 240px;
            overflow: hidden;
            position: relative;
        }

        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .card-category {
            position: absolute;
            top: 16px;
            left: 16px;
            background: linear-gradient(135deg, #f19604, #86520a);
            color: white;
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }

        .card-body {
            padding: 24px;
        }

        .card-title {
            font-size: 1.3rem;
            font-weight: 800;
            margin-bottom: 10px;
            color: var(--text-primary);
        }

        .card-description {
            color: var(--text-secondary);
            font-size: 0.95rem;
            margin-bottom: 18px;
        }

        .card-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 0.85rem;
            color: var(--text-tertiary);
        }

        .stat-raised {
            color: #f19604;
            font-weight: 800;
        }

        .progress-bar-container {
            width: 100%;
            height: 8px;
            background: var(--border-color);
            border-radius: 20px;
            margin-bottom: 18px;
        }

        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg,#06b6d4,#14b8a6);
            border-radius: 20px;
        }

        .view-btn {
            background: linear-gradient(135deg, #f19604, #86520a);
            padding: 10px 18px;
            border-radius: 50px;
            color: #fff;
            text-decoration: none;
            font-weight: 700;
            font-size: 13px;
        }

        .card-stats{
            display:flex;
            justify-content:space-between;
            margin:12px 0;
            font-size:14px;
        }

        .card-meta{
            display:flex;
            justify-content:space-between;
            font-size:13px;
            color: var(--text-tertiary);
            margin-bottom:10px;
        }

        .card-location{
            display:flex;
            gap:6px;
            align-items:center;
        }

        .card-date{
            opacity:.8;
        }

        .card-footer{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-top:12px;
        }

        .percentage{
            font-weight:700;
            color:#f19604;
            font-size:14px;
        }

        .stat-goal{
            color: var(--text-secondary);
            font-size:13px;
        }

        /* Trust Section */
        .trust-section {
            position: relative;
            z-index: 1;
            padding: 70px 40px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .trust-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 35px;
            margin-top: 60px;
        }

        .trust-card {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            padding: 45px 35px;
            border-radius: 24px;
            transition: all 0.4s ease;
            text-align: center;
            position: relative;
            overflow: hidden;
            animation: cardAppear 0.6s ease both;
        }

        .trust-card:nth-child(1) { animation-delay: 0.1s; }
        .trust-card:nth-child(2) { animation-delay: 0.2s; }
        .trust-card:nth-child(3) { animation-delay: 0.3s; }
        .trust-card:nth-child(4) { animation-delay: 0.4s; }

        .trust-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #f59e0b, #fb923c);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .trust-card:hover::before {
            transform: scaleX(1);
        }

        .trust-card:hover {
            transform: translateY(-10px);
            border-color: var(--border-hover);
            background: var(--bg-card-hover);
        }

        .trust-icon {
            font-size: 3.5rem;
            margin-bottom: 18px;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .trust-card:hover .trust-icon {
            animation: none;
            transform: scale(1.2);
        }

        .trust-card h3 {
            font-size: 1.3rem;
            font-weight: 800;
            margin-bottom: 8px;
            color: var(--text-primary);
        }

        .trust-card p {
            color: var(--text-secondary);
            font-size: 1rem;
        }

        /* Animations */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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

        @keyframes cardAppear {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Responsive */
        @media (max-width: 968px) {
            .hero {
                padding: 120px 20px 80px;
            }

            .how-it-works,
            .campaign-preview,
            .trust-section {
                padding: 80px 20px;
            }

            .steps-grid,
            .campaigns-grid,
            .trust-grid {
                grid-template-columns: 1fr;
            }

            .hero-actions {
                flex-direction: column;
                align-items: center;
            }

            .btn {
                width: 100%;
                max-width: 300px;
            }
        }

        @media (max-width: 480px) {
            .hero h1 {
                font-size: 2.5rem;
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

    <!-- HERO -->
    <section class="hero">
        <h1>Support Dreams. Change Lives.</h1>
        <p class="subtitle">Discover verified campaigns, support real people and make an impact with secure, transparent crowdfunding.</p>

        <div class="hero-actions">
            <a href="/CroudSpark-X/public/explore-campaigns.php" class="btn btn-primary">
                <i class="fas fa-compass"></i> Explore Campaigns
            </a>
            <a href="/CroudSpark-X/creator/create-campaign.php" class="btn btn-secondary">
                <i class="fas fa-plus-circle"></i> Start Campaign
            </a>
        </div>

        <div class="hero-stats">
            <div class="stat">🚀 1,200+ Live Fundraisers</div>
            <div class="stat">💳 100% Secure Donations</div>
            <div class="stat">❤️ Real Impact Stories</div>
        </div>
    </section>

    <!-- HOW IT WORKS -->
    <section class="how-it-works">
        <div class="section-header">
            <h2>How CrowdSpark Works</h2>
            <p>Start making a difference in three simple steps</p>
        </div>

        <div class="steps-grid">
            <div class="step-card">
                <div class="step-number">1</div>
                <h3>Browse Campaigns</h3>
                <p>Explore verified campaigns across India. Every campaign is admin-approved for authenticity.</p>
            </div>

            <div class="step-card">
                <div class="step-number">2</div>
                <h3>Donate Securely</h3>
                <p>Support causes with safe, encrypted payments. Your contribution goes directly to those in need.</p>
            </div>

            <div class="step-card">
                <div class="step-number">3</div>
                <h3>Track Impact</h3>
                <p>See how your donation helps people in real-time. Receive updates and impact reports.</p>
            </div>
        </div>
    </section>

    <!-- CAMPAIGNS -->
    <section class="campaign-preview">
        <div class="section-header">
            <h2>Trending Campaigns</h2>
        </div>

        <div class="campaigns-grid">

        <?php
        $stmt = $pdo->prepare("
        SELECT c.*,
        COALESCE(SUM(d.amount),0) as raised_amount,
        (
        SELECT media_url FROM campaign_media 
        WHERE campaign_id = c.id 
        AND media_type='thumbnail'
        LIMIT 1
        ) as thumbnail
        FROM campaigns c
        LEFT JOIN donations d ON c.id=d.campaign_id
        WHERE c.status='approved'
        GROUP BY c.id
        ORDER BY RAND()
        LIMIT 6
        ");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($data){
        foreach($data as $row):

        $raised = $row['raised_amount'];
        $goal   = $row['goal'];
        $percent = ($goal>0) ? min(($raised/$goal)*100,100) : 0;

        $thumb = $row['thumbnail'] ?: "https://via.placeholder.com/400x250";
        ?>

<div class="campaign-card">

    <div class="card-image">
        <span class="card-category"><?= htmlspecialchars($row['category']) ?></span>
        <img src="<?= $thumb ?>" alt="<?= htmlspecialchars($row['title']) ?>">
    </div>

    <div class="card-body">
        <h3 class="card-title"><?= htmlspecialchars($row['title']) ?></h3>

        <p class="card-description">
            <?= substr(htmlspecialchars($row['short_desc'] ?? 'Support this campaign'),0,100) ?>...
        </p>

        <div class="card-meta">
            <span class="card-location">
                <i class="fas fa-map-marker-alt"></i> 
                <?= htmlspecialchars($row['location'] ?? 'India') ?>
            </span>

            <span class="card-date">
                <?= date('d M Y',strtotime($row['created_at'])) ?>
            </span>
        </div>

        <div class="card-stats">
            <span class="stat-raised">₹<?= number_format($raised) ?></span>
            <span class="stat-goal">Goal: ₹<?= number_format($goal) ?></span>
        </div>

        <div class="progress-bar-container">
            <div class="progress-bar-fill" style="width:<?= $percent ?>%"></div>
        </div>

        <div class="card-footer">
            <span class="percentage"><?= number_format($percent,1) ?>%</span>

            <a href="/CroudSpark-X/public/campaign-details.php?id=<?= $row['id'] ?>" class="view-btn">
                View Campaign <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

</div>

<?php 
endforeach;
}else{
echo "<p style='color:#aaa;text-align:center'>No campaigns yet</p>";
}
?>

</div>

    </section>

    <!-- TRUST -->
    <section class="trust-section">
        <div class="section-header">
            <h2>Why Choose CrowdSpark</h2>
        </div>

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

</body>
</html>