<?php
session_start();
require_once __DIR__ . "/../includes/header.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help Center - CrowdSpark</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        /* ===== THEME VARIABLES ===== */
        :root {
            --bg-primary: #fafafa;
            --bg-secondary: #f1f5f9;
            --bg-card: rgba(255, 255, 255, 0.95);
            
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-tertiary: #64748b;
            
            --border-color: rgba(15, 23, 42, 0.08);
            
            --shadow-md: 0 8px 32px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 20px 60px rgba(0, 0, 0, 0.15);
            
            --orb-opacity: 0.20;
        }

        [data-theme="dark"] {
            --bg-primary: #0f0f0f;
            --bg-secondary: #1a1a1a;
            --bg-card: rgba(20, 20, 30, 0.85);
            
            --text-primary: #ffffff;
            --text-secondary: #cbd5e1;
            --text-tertiary: #94a3b8;
            
            --border-color: rgba(255, 255, 255, 0.1);
            
            --shadow-md: 0 8px 32px rgba(0, 0, 0, 0.4);
            --shadow-lg: 0 20px 60px rgba(0, 0, 0, 0.6);
            
            --orb-opacity: 0.25;
        }

        /* Purple accent */
        :root, [data-theme="dark"] {
            --accent-primary: #8b5cf6;
            --accent-secondary: #3b82f6;
            --accent-gradient: linear-gradient(135deg, #8b5cf6, #3b82f6);
            --accent-glow: rgba(139, 92, 246, 0.4);
        }

        [data-theme="dark"] {
            --orb-1: linear-gradient(45deg, #8b5cf6, #a78bfa);
            --orb-2: linear-gradient(45deg, #7c3aed, #8b5cf6);
            --orb-3: linear-gradient(45deg, #6d28d9, #7c3aed);
        }

        [data-theme="light"] {
            --orb-1: linear-gradient(45deg, #c4b5fd, #a78bfa);
            --orb-2: linear-gradient(45deg, #a78bfa, #8b5cf6);
            --orb-3: linear-gradient(45deg, #8b5cf6, #7c3aed);
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
            transition: background-color 0.3s ease;
        }

        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
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

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .help-page {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            padding: 140px 20px 80px;
        }

        .help-container {
            max-width: 1200px;
            margin: 0 auto;
            animation: fadeInUp 0.8s ease;
        }

        .help-hero {
            text-align: center;
            margin-bottom: 60px;
        }

        .help-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.5rem, 6vw, 4rem);
            font-weight: 900;
            margin-bottom: 16px;
            background: linear-gradient(135deg, var(--text-primary), var(--accent-primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .help-hero p {
            font-size: 1.25rem;
            color: var(--text-secondary);
        }

        .help-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }

        .help-card {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 40px;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .help-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: var(--accent-primary);
        }

        .help-card-icon {
            width: 70px;
            height: 70px;
            border-radius: 20px;
            background: var(--accent-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: #fff;
            margin-bottom: 24px;
            box-shadow: 0 8px 20px var(--accent-glow);
        }

        .help-card h3 {
            font-size: 1.5rem;
            font-weight: 900;
            margin-bottom: 12px;
            color: var(--text-primary);
        }

        .help-card p {
            color: var(--text-secondary);
            line-height: 1.7;
            margin-bottom: 20px;
        }

        .help-card-link {
            color: var(--accent-primary);
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .help-card-link i {
            transition: transform 0.3s ease;
        }

        .help-card:hover .help-card-link i {
            transform: translateX(5px);
        }

        .quick-links {
            background: var(--bg-card);
            border-radius: 24px;
            padding: 50px;
            border: 1px solid var(--border-color);
            margin-bottom: 60px;
        }

        .quick-links h2 {
            font-size: 2rem;
            font-weight: 900;
            margin-bottom: 30px;
            color: var(--text-primary);
        }

        .links-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 16px;
        }

        .quick-link-item {
            padding: 16px 20px;
            background: var(--bg-secondary);
            border-radius: 12px;
            border: 2px solid var(--border-color);
            text-decoration: none;
            color: var(--text-primary);
            font-weight: 700;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .quick-link-item:hover {
            border-color: var(--accent-primary);
            background: rgba(139, 92, 246, 0.1);
            transform: translateX(5px);
        }

        .quick-link-item i {
            color: var(--accent-primary);
            font-size: 20px;
        }

        .contact-section {
            background: var(--accent-gradient);
            border-radius: 24px;
            padding: 60px;
            text-align: center;
            color: #fff;
        }

        .contact-section h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 900;
            margin-bottom: 16px;
        }

        .contact-section p {
            font-size: 1.1rem;
            margin-bottom: 30px;
            opacity: 0.95;
        }

        .contact-btn {
            padding: 18px 36px;
            background: #fff;
            color: var(--accent-primary);
            border-radius: 999px;
            text-decoration: none;
            font-weight: 900;
            font-size: 16px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .contact-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3);
        }

        @media (max-width: 768px) {
            .help-page {
                padding: 120px 15px 60px;
            }

            .help-grid {
                grid-template-columns: 1fr;
            }

            .quick-links,
            .contact-section {
                padding: 40px 24px;
            }

            .links-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <div class="bg-animation">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <div class="help-page">
        <div class="help-container">

            <div class="help-hero">
                <h1>How Can We Help You?</h1>
                <p>Browse our help resources or get in touch with support</p>
            </div>

            <div class="help-grid">
                <a href="/public/faq.php" class="help-card">
                    <div class="help-card-icon">
                        <i class="fa-solid fa-circle-question"></i>
                    </div>
                    <h3>FAQs</h3>
                    <p>Find quick answers to the most commonly asked questions about using CrowdSpark.</p>
                    <span class="help-card-link">
                        Browse FAQs <i class="fa-solid fa-arrow-right"></i>
                    </span>
                </a>

                <a href="/public/trust-safety.php" class="help-card">
                    <div class="help-card-icon">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <h3>Trust & Safety</h3>
                    <p>Learn about our security measures and how we keep your donations safe.</p>
                    <span class="help-card-link">
                        Learn More <i class="fa-solid fa-arrow-right"></i>
                    </span>
                </a>

                <a href="/public/report-issue.php" class="help-card">
                    <div class="help-card-icon">
                        <i class="fa-solid fa-flag"></i>
                    </div>
                    <h3>Report an Issue</h3>
                    <p>Report suspicious campaigns or technical problems to our team.</p>
                    <span class="help-card-link">
                        Report Now <i class="fa-solid fa-arrow-right"></i>
                    </span>
                </a>
            </div>

            <div class="quick-links">
                <h2>Popular Help Topics</h2>
                <div class="links-grid">
                    <a href="/public/faq.php" class="quick-link-item">
                        <i class="fa-solid fa-rocket"></i>
                        Getting Started
                    </a>
                    <a href="/public/faq.php" class="quick-link-item">
                        <i class="fa-solid fa-heart"></i>
                        Making Donations
                    </a>
                    <a href="/public/faq.php" class="quick-link-item">
                        <i class="fa-solid fa-bullhorn"></i>
                        Creating Campaigns
                    </a>
                    <a href="/public/faq.php" class="quick-link-item">
                        <i class="fa-solid fa-credit-card"></i>
                        Payment Methods
                    </a>
                    <a href="/public/faq.php" class="quick-link-item">
                        <i class="fa-solid fa-user-shield"></i>
                        Account Security
                    </a>
                    <a href="/public/faq.php" class="quick-link-item">
                        <i class="fa-solid fa-share-nodes"></i>
                        Sharing Campaigns
                    </a>
                </div>
            </div>

            <div class="contact-section">
                <h2>Still Need Help?</h2>
                <p>Our support team is here to assist you with any questions or concerns.</p>
                <a href="/public/contact.php" class="contact-btn">
                    <i class="fa-solid fa-envelope"></i>
                    Contact Support
                </a>
            </div>

        </div>
    </div>

    <?php require_once __DIR__ . "/../includes/footer.php"; ?>

</body>
</html>