<?php
session_start();
require_once __DIR__ . "/../includes/header.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trust & Safety - CrowdSpark</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-primary: #fafafa;
            --bg-secondary: #f1f5f9;
            --bg-card: rgba(255, 255, 255, 0.95);
            
            --text-primary: #0f172a;
            --text-secondary: #475569;
            
            --border-color: rgba(15, 23, 42, 0.08);
            
            --shadow-md: 0 8px 32px rgba(0, 0, 0, 0.08);
            
            --orb-opacity: 0.20;
        }

        [data-theme="dark"] {
            --bg-primary: #0f0f0f;
            --bg-secondary: #1a1a1a;
            --bg-card: rgba(20, 20, 30, 0.85);
            
            --text-primary: #ffffff;
            --text-secondary: #cbd5e1;
            
            --border-color: rgba(255, 255, 255, 0.1);
            
            --shadow-md: 0 8px 32px rgba(0, 0, 0, 0.4);
            
            --orb-opacity: 0.25;
        }

        :root, [data-theme="dark"] {
            --accent-primary: #10b981;
            --accent-secondary: #34d399;
            --accent-gradient: linear-gradient(135deg, #10b981, #34d399);
            --accent-glow: rgba(16, 185, 129, 0.4);
        }

        [data-theme="dark"] {
            --orb-1: linear-gradient(45deg, #10b981, #34d399);
            --orb-2: linear-gradient(45deg, #059669, #10b981);
            --orb-3: linear-gradient(45deg, #047857, #059669);
        }

        [data-theme="light"] {
            --orb-1: linear-gradient(45deg, #6ee7b7, #34d399);
            --orb-2: linear-gradient(45deg, #34d399, #10b981);
            --orb-3: linear-gradient(45deg, #10b981, #059669);
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

        .trust-page {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            padding: 140px 20px 80px;
        }

        .trust-container {
            max-width: 1100px;
            margin: 0 auto;
            animation: fadeInUp 0.8s ease;
        }

        .trust-hero {
            text-align: center;
            margin-bottom: 60px;
        }

        .trust-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.5rem, 6vw, 4rem);
            font-weight: 900;
            margin-bottom: 16px;
            background: linear-gradient(135deg, var(--text-primary), var(--accent-primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .trust-hero p {
            font-size: 1.25rem;
            color: var(--text-secondary);
            max-width: 700px;
            margin: 0 auto;
        }

        .trust-badges {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 24px;
            margin-bottom: 60px;
        }

        .badge {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .badge:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .badge-icon {
            font-size: 48px;
            color: var(--accent-primary);
            margin-bottom: 16px;
        }

        .badge h3 {
            font-size: 1.1rem;
            font-weight: 800;
            margin-bottom: 8px;
            color: var(--text-primary);
        }

        .badge p {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .section {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 50px;
            margin-bottom: 40px;
        }

        .section h2 {
            font-size: 2rem;
            font-weight: 900;
            margin-bottom: 24px;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .section h2::before {
            content: '';
            width: 5px;
            height: 40px;
            background: var(--accent-gradient);
            border-radius: 3px;
        }

        .section p {
            line-height: 1.8;
            color: var(--text-secondary);
            margin-bottom: 16px;
            font-size: 16px;
        }

        .section ul {
            margin-left: 24px;
            margin-top: 16px;
        }

        .section li {
            color: var(--text-secondary);
            line-height: 1.8;
            margin-bottom: 12px;
        }

        .section strong {
            color: var(--text-primary);
        }

        .safety-tips {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
        }

        .tip-card {
            background: var(--bg-secondary);
            border: 2px solid var(--border-color);
            border-radius: 16px;
            padding: 28px;
            transition: all 0.3s ease;
        }

        .tip-card:hover {
            border-color: var(--accent-primary);
            background: rgba(16, 185, 129, 0.05);
        }

        .tip-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: var(--accent-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 24px;
            margin-bottom: 16px;
        }

        .tip-card h4 {
            font-size: 1.1rem;
            font-weight: 800;
            margin-bottom: 12px;
            color: var(--text-primary);
        }

        .tip-card p {
            font-size: 15px;
            color: var(--text-secondary);
            line-height: 1.7;
        }

        .cta-section {
            background: var(--accent-gradient);
            border-radius: 24px;
            padding: 60px;
            text-align: center;
            color: #fff;
            margin-top: 60px;
        }

        .cta-section h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 900;
            margin-bottom: 16px;
        }

        .cta-section h2::before {
            display: none;
        }

        .cta-section p {
            font-size: 1.1rem;
            margin-bottom: 30px;
            opacity: 0.95;
        }

        .cta-btn {
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

        .cta-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3);
        }

        @media (max-width: 768px) {
            .trust-page {
                padding: 120px 15px 60px;
            }

            .section {
                padding: 35px 24px;
            }

            .cta-section {
                padding: 40px 24px;
            }

            .safety-tips {
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

    <div class="trust-page">
        <div class="trust-container">

            <div class="trust-hero">
                <h1>Trust & Safety</h1>
                <p>Your security is our top priority. Learn how we protect you and your donations.</p>
            </div>

            <div class="trust-badges">
                <div class="badge">
                    <div class="badge-icon">🔒</div>
                    <h3>Secure Payments</h3>
                    <p>Bank-level encryption for all transactions</p>
                </div>
                <div class="badge">
                    <div class="badge-icon">✅</div>
                    <h3>Verified Campaigns</h3>
                    <p>Every campaign is reviewed before going live</p>
                </div>
                <div class="badge">
                    <div class="badge-icon">🛡️</div>
                    <h3>Fraud Protection</h3>
                    <p>24/7 monitoring for suspicious activity</p>
                </div>
                <div class="badge">
                    <div class="badge-icon">💬</div>
                    <h3>Support Team</h3>
                    <p>Dedicated team ready to help anytime</p>
                </div>
            </div>

            <div class="section">
                <h2>How We Protect You</h2>
                <p>CrowdSpark employs multiple layers of security to ensure your donations reach the right people safely:</p>
                <ul>
                    <li><strong>Campaign Verification:</strong> Our team reviews every campaign before approval to ensure legitimacy</li>
                    <li><strong>Secure Payment Processing:</strong> We use Razorpay, a PCI-DSS compliant payment gateway with bank-level security</li>
                    <li><strong>Identity Verification:</strong> Campaign creators undergo identity verification processes</li>
                    <li><strong>Fraud Detection:</strong> Advanced algorithms monitor for suspicious activity 24/7</li>
                    <li><strong>Encrypted Data:</strong> All personal and payment information is encrypted with SSL/TLS</li>
                    <li><strong>Regular Audits:</strong> We conduct security audits to maintain the highest standards</li>
                </ul>
            </div>

            <div class="section">
                <h2>Our Commitment to Safety</h2>
                <p>We take safety seriously and have established comprehensive policies:</p>
                <p><strong>Campaign Guidelines:</strong> All campaigns must comply with our terms of service. We prohibit illegal activities, hate speech, weapons, drugs, and misleading information.</p>
                <p><strong>Transparent Reporting:</strong> Campaign creators must provide regular updates and be transparent about fund usage.</p>
                <p><strong>Fund Protection:</strong> Donations are held securely and transferred to verified bank accounts only.</p>
                <p><strong>User Privacy:</strong> We never sell your personal information and follow strict privacy policies.</p>
            </div>

            <div class="section">
                <h2>Safety Tips for Donors</h2>
                <div class="safety-tips">
                    <div class="tip-card">
                        <div class="tip-icon">
                            <i class="fa-solid fa-search"></i>
                        </div>
                        <h4>Research Campaigns</h4>
                        <p>Read the full campaign story, check for updates, and review the creator's profile before donating.</p>
                    </div>
                    <div class="tip-card">
                        <div class="tip-icon">
                            <i class="fa-solid fa-shield-check"></i>
                        </div>
                        <h4>Look for Verification</h4>
                        <p>Verified campaigns have been reviewed by our team and display a verification badge.</p>
                    </div>
                    <div class="tip-card">
                        <div class="tip-icon">
                            <i class="fa-solid fa-flag"></i>
                        </div>
                        <h4>Report Suspicious Activity</h4>
                        <p>If something doesn't feel right, report it immediately. We investigate all reports.</p>
                    </div>
                    <div class="tip-card">
                        <div class="tip-icon">
                            <i class="fa-solid fa-comments"></i>
                        </div>
                        <h4>Ask Questions</h4>
                        <p>Don't hesitate to contact campaign creators with questions before donating.</p>
                    </div>
                    <div class="tip-card">
                        <div class="tip-icon">
                            <i class="fa-solid fa-credit-card"></i>
                        </div>
                        <h4>Use Secure Payments</h4>
                        <p>Always donate through our secure platform. Never send money directly outside CrowdSpark.</p>
                    </div>
                    <div class="tip-card">
                        <div class="tip-icon">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <h4>Stay Informed</h4>
                        <p>Enable email notifications to receive updates about campaigns you've supported.</p>
                    </div>
                </div>
            </div>

            <div class="section">
                <h2>What We Don't Allow</h2>
                <p>To maintain a safe community, the following are strictly prohibited:</p>
                <ul>
                    <li>Campaigns promoting illegal activities or products</li>
                    <li>Fraudulent, misleading, or deceptive campaigns</li>
                    <li>Campaigns promoting hate, violence, or discrimination</li>
                    <li>Campaigns for weapons, drugs, or prohibited items</li>
                    <li>Campaigns violating intellectual property rights</li>
                    <li>Pyramid schemes or get-rich-quick schemes</li>
                    <li>Campaigns without clear goals or proper documentation</li>
                </ul>
                <p>Violations result in immediate campaign removal and potential account suspension.</p>
            </div>

            <div class="cta-section">
                <h2>Report a Concern</h2>
                <p>See something suspicious? Help us keep CrowdSpark safe for everyone.</p>
                <a href="/public/report-issue.php" class="cta-btn">
                    <i class="fa-solid fa-flag"></i>
                    Report an Issue
                </a>
            </div>

        </div>
    </div>

    <?php require_once __DIR__ . "/../includes/footer.php"; ?>

</body>
</html>