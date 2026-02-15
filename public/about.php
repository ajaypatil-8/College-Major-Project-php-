<?php
require_once __DIR__ . "/../includes/header.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - CrowdSpark</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    
    <style>
        /* ===== THEME VARIABLES ===== */
        :root {
            --bg-primary: #ffffff;
            --bg-secondary: #f8fafc;
            --bg-card: rgba(255, 255, 255, 0.9);
            --bg-card-hover: rgba(255, 255, 255, 0.95);
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-tertiary: #64748b;
            --border-color: rgba(15, 23, 42, 0.1);
            --border-hover: rgba(139, 92, 246, 0.3);
            --orb-opacity: 0.5;
        }

        [data-theme="dark"] {
            --bg-primary: #0f0f0f;
            --bg-secondary: #1a1a1a;
            --bg-card: rgba(20, 20, 30, 0.85);
            --bg-card-hover: rgba(30, 30, 40, 0.9);
            --text-primary: #ffffff;
            --text-secondary: #cbd5e1;
            --text-tertiary: #94a3b8;
            --border-color: rgba(255, 255, 255, 0.15);
            --border-hover: rgba(139, 92, 246, 0.4);
            --orb-opacity: 0.25;
        }

        /* Purple orbs - VISIBLE on both themes */
        [data-theme="dark"] {
            --orb-1: linear-gradient(45deg, #8b5cf6, #a78bfa);
            --orb-2: linear-gradient(45deg, #3b82f6, #60a5fa);
            --orb-3: linear-gradient(45deg, #ec4899, #8b5cf6);
        }

        [data-theme="light"] {
            --orb-1: linear-gradient(45deg, #c4b5fd, #a78bfa);
            --orb-2: linear-gradient(45deg, #93c5fd, #60a5fa);
            --orb-3: linear-gradient(45deg, #f9a8d4, #c084fc);
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

        .about-container {
            position: relative;
            z-index: 1;
            max-width: 1400px;
            margin: 0 auto;
            padding: 120px 40px 80px;
        }

        .about-hero {
            text-align: center;
            margin-bottom: 80px;
            animation: fadeInUp 0.8s ease;
        }

        .about-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(3rem, 8vw, 6rem);
            font-weight: 900;
            background: linear-gradient(135deg, #fff, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
            line-height: 1.1;
        }

        [data-theme="light"] .about-hero h1 {
            background: linear-gradient(135deg, #0f172a, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .about-hero p {
            font-size: 1.25rem;
            color: var(--text-secondary);
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .about-intro {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 32px;
            padding: 50px;
            margin-bottom: 80px;
            animation: fadeInUp 0.8s ease 0.2s both;
            position: relative;
            overflow: hidden;
        }

        .about-intro::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #8b5cf6, #3b82f6);
        }

        .about-intro h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #fff, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        [data-theme="light"] .about-intro h2 {
            background: linear-gradient(135deg, #0f172a, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .about-intro p {
            color: var(--text-secondary);
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 20px;
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 40px;
            margin-bottom: 100px;
        }

        .value-card {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 28px;
            padding: 50px 40px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            animation: fadeInLeft 0.8s ease both;
        }

        .value-card:nth-child(1) { animation-delay: 0.3s; }
        .value-card:nth-child(2) { animation-delay: 0.4s; }

        .value-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #8b5cf6, #3b82f6);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .value-card:hover::before {
            transform: scaleX(1);
        }

        .value-card:hover {
            background: var(--bg-card-hover);
            border-color: var(--border-hover);
            transform: translateY(-8px);
        }

        .value-card-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #8b5cf6, #3b82f6);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 24px;
            transition: all 0.4s ease;
        }

        .value-card:hover .value-card-icon {
            transform: rotate(10deg) scale(1.1);
        }

        .value-card h3 {
            font-size: 1.8rem;
            margin-bottom: 16px;
            font-weight: 800;
            color: var(--text-primary);
        }

        .value-card p {
            color: var(--text-secondary);
            font-size: 1.05rem;
            line-height: 1.8;
        }

        .trust-section {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 32px;
            padding: 60px 40px;
            margin-bottom: 100px;
            text-align: center;
            animation: fadeInUp 0.8s ease 0.5s both;
            position: relative;
            overflow: hidden;
        }

        .trust-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #8b5cf6, #3b82f6, #8b5cf6);
            background-size: 200% 100%;
            animation: gradientShift 3s ease infinite;
        }

        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .trust-section h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            margin-bottom: 40px;
            background: linear-gradient(135deg, #fff, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        [data-theme="light"] .trust-section h2 {
            background: linear-gradient(135deg, #0f172a, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .trust-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
            margin-top: 40px;
        }

        .trust-item {
            background: var(--bg-secondary);
            padding: 24px 28px;
            border-radius: 16px;
            font-size: 1.05rem;
            font-weight: 600;
            color: var(--text-primary);
            transition: all 0.3s ease;
            border: 2px solid transparent;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideInUp 0.5s ease both;
        }

        .trust-item:nth-child(1) { animation-delay: 0.6s; }
        .trust-item:nth-child(2) { animation-delay: 0.7s; }
        .trust-item:nth-child(3) { animation-delay: 0.8s; }
        .trust-item:nth-child(4) { animation-delay: 0.9s; }

        .trust-item:hover {
            background: rgba(139, 92, 246, 0.2);
            border-color: #8b5cf6;
            transform: translateX(8px);
        }

        .team-section {
            text-align: center;
            margin-bottom: 100px;
            animation: fadeInUp 0.8s ease 0.6s both;
        }

        .team-section h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #fff, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        [data-theme="light"] .team-section h2 {
            background: linear-gradient(135deg, #0f172a, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .team-section > p {
            font-size: 1.1rem;
            color: var(--text-secondary);
            margin-bottom: 50px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 32px;
        }

        .team-member {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            padding: 40px 32px;
            border-radius: 24px;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.6s ease both;
        }

        .team-member:nth-child(1) { animation-delay: 0.7s; }
        .team-member:nth-child(2) { animation-delay: 0.8s; }

        .team-member::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.1) 0%, transparent 100%);
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .team-member:hover {
            transform: translateY(-8px);
            border-color: rgba(139, 92, 246, 0.3);
        }

        .team-member:hover::before {
            opacity: 1;
        }

        .team-member strong {
            display: block;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .team-member span {
            display: block;
            font-size: 1rem;
            color: #8b5cf6;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }

        /* CTA - Keep purple gradient */
        .cta-section {
            background: linear-gradient(135deg, #8b5cf6 0%, #3b82f6 100%);
            border-radius: 32px;
            padding: 70px 40px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(139, 92, 246, 0.3);
            animation: fadeInUp 0.8s ease 0.9s both;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 30% 40%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 70% 70%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }

        .cta-section h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 900;
            margin-bottom: 16px;
            position: relative;
            z-index: 1;
        }

        .cta-section p {
            font-size: 1.15rem;
            opacity: 0.95;
            margin-bottom: 40px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            position: relative;
            z-index: 1;
        }

        .cta-actions {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            position: relative;
            z-index: 1;
        }

        .btn {
            padding: 16px 40px;
            border-radius: 999px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-size: 1rem;
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
            background: white;
            color: #8b5cf6;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.25);
        }

        .btn-secondary {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        .btn-secondary:hover {
            background: white;
            color: #8b5cf6;
            transform: translateY(-3px);
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

        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 968px) {
            .about-container {
                padding: 100px 20px 60px;
            }

            .about-hero h1 {
                font-size: 3rem;
            }

            .about-intro,
            .trust-section,
            .cta-section {
                padding: 40px 30px;
            }

            .values-grid,
            .team-grid {
                grid-template-columns: 1fr;
            }

            .trust-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .about-hero h1 {
                font-size: 2rem;
            }

            .about-intro h2,
            .trust-section h2,
            .team-section h2,
            .cta-section h2 {
                font-size: 2rem;
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

    <div class="about-container">
        
        <div class="about-hero">
            <h1>About CrowdSpark</h1>
            <p>Building trust, transparency, and impact through responsible crowdfunding.</p>
        </div>

        <div class="about-intro">
            <h2>Who We Are</h2>
            <p>
                CrowdSpark is a transparent and community-driven crowdfunding platform
                designed to connect people with genuine causes. Our goal is to empower
                individuals and organizations to raise funds responsibly while giving
                donors complete visibility into how their contributions create impact.
            </p>

            <p>
                We believe trust is the foundation of crowdfunding. Every campaign on
                CrowdSpark is reviewed before being published to ensure authenticity
                and accountability.
            </p>
        </div>

        <div class="values-grid">
            <div class="value-card">
                <div class="value-card-icon">
                    <i class="fas fa-rocket"></i>
                </div>
                <h3>Our Mission</h3>
                <p>
                    To make crowdfunding safe, transparent, and accessible for everyone
                    by enabling meaningful causes to receive the support they deserve.
                </p>
            </div>

            <div class="value-card">
                <div class="value-card-icon">
                    <i class="fas fa-eye"></i>
                </div>
                <h3>Our Vision</h3>
                <p>
                    To become a trusted global crowdfunding platform where compassion,
                    integrity, and impact drive every contribution.
                </p>
            </div>
        </div>

        <div class="trust-section">
            <h2>Why Trust CrowdSpark?</h2>

            <div class="trust-grid">
                <div class="trust-item">
                    <i class="fas fa-check-circle"></i>
                    Campaign review and approval process
                </div>
                <div class="trust-item">
                    <i class="fas fa-chart-line"></i>
                    Transparent fundraising progress
                </div>
                <div class="trust-item">
                    <i class="fas fa-shield-alt"></i>
                    Secure donation workflow
                </div>
                <div class="trust-item">
                    <i class="fas fa-users"></i>
                    Community-driven impact
                </div>
            </div>
        </div>

        <div class="team-section">
            <h2>Project Team</h2>
            <p>
                CrowdSpark is designed and developed as a full-stack web project
                with a focus on clean architecture, security, and real-world usability.
            </p>

            <div class="team-grid">
                <div class="team-member">
                    <strong>Ajay Patil</strong>
                    <span>Developer</span>
                </div>

                <div class="team-member">
                    <strong>Gautam Londhe</strong>
                    <span>Project Contributor</span>
                </div>
            </div>
        </div>

        <div class="cta-section">
            <h2>Join Us in Making a Difference</h2>
            <p>
                Whether you want to support a cause or start a campaign,
                CrowdSpark is here to help you create meaningful impact.
            </p>

            <div class="cta-actions">
                <a href="/CroudSpark-X/public/explore-campaigns.php" class="btn btn-primary">
                    <i class="fas fa-compass"></i> Explore Campaigns
                </a>
                <a href="/CroudSpark-X/creator/create-campaign.php" class="btn btn-secondary">
                    <i class="fas fa-plus-circle"></i> Start a campaign
                </a>
            </div>
        </div>

    </div>

    <?php require_once __DIR__ . "/../includes/footer.php"; ?>

</body>
</html>