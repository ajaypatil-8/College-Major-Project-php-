<?php
require_once __DIR__ . "/../includes/header.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - CrowdSpark</title>
    <style>
        /* Import Inter font */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

        /* Hero Section */
        .about-hero {
            background: linear-gradient(135deg, #f59e0b 0%, #fb923c 100%);
            padding: 120px 20px 80px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
            margin-top: -80px;
        }

        .about-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }

        .about-hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
            animation: fadeInDown 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            letter-spacing: -1px;
        }

        .about-hero p {
            font-size: 1.3rem;
            opacity: 0.95;
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.2s backwards;
            max-width: 700px;
            margin: 0 auto;
            font-weight: 500;
            line-height: 1.6;
        }

        /* About Intro Section */
        .about-intro {
            max-width: 900px;
            margin: 80px auto;
            padding: 0 20px;
            animation: fadeInUp 0.8s ease-out 0.3s backwards;
        }

        .about-content h2 {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 24px;
            text-align: center;
            letter-spacing: -0.5px;
        }

        .about-content p {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--text-muted);
            margin-bottom: 24px;
            font-weight: 500;
        }

        /* Mission & Vision Cards */
        .about-values {
            max-width: 1200px;
            margin: 100px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 40px;
        }

        .value-card {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            padding: 48px 40px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            animation: cardAppear 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) backwards;
        }

        .value-card:nth-child(1) {
            animation-delay: 0.4s;
        }

        .value-card:nth-child(2) {
            animation-delay: 0.5s;
        }

        .value-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(135deg, #f59e0b, #fb923c);
        }

        .value-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.03) 0%, transparent 100%);
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .value-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: rgba(245, 158, 11, 0.2);
        }

        .value-card:hover::after {
            opacity: 1;
        }

        .value-card h3 {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 16px;
            letter-spacing: -0.3px;
            position: relative;
            z-index: 1;
        }

        .value-card p {
            font-size: 1.05rem;
            line-height: 1.8;
            color: var(--text-muted);
            font-weight: 500;
            position: relative;
            z-index: 1;
        }

        /* Trust Section */
        .about-trust {
            max-width: 1000px;
            margin: 100px auto;
            padding: 60px 40px;
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border);
            text-align: center;
            animation: fadeInUp 0.8s ease-out 0.6s backwards;
            position: relative;
            overflow: hidden;
        }

        .about-trust::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #f59e0b, #fb923c, #f59e0b);
            background-size: 200% 100%;
            animation: gradientShift 3s ease infinite;
        }

        .about-trust h2 {
            font-size: 2.3rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 40px;
            letter-spacing: -0.5px;
        }

        .trust-points {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
            margin-top: 40px;
        }

        .trust-point {
            background: var(--bg-soft);
            padding: 24px 28px;
            border-radius: var(--radius-md);
            font-size: 1.05rem;
            font-weight: 600;
            color: var(--text-main);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 2px solid transparent;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideInUp 0.5s ease-out backwards;
        }

        .trust-point:nth-child(1) { animation-delay: 0.7s; }
        .trust-point:nth-child(2) { animation-delay: 0.8s; }
        .trust-point:nth-child(3) { animation-delay: 0.9s; }
        .trust-point:nth-child(4) { animation-delay: 1s; }

        .trust-point:hover {
            background: white;
            border-color: var(--primary);
            transform: translateX(8px);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.15);
        }

        /* Team Section */
        .about-team {
            max-width: 900px;
            margin: 100px auto;
            padding: 0 20px;
            text-align: center;
            animation: fadeInUp 0.8s ease-out 0.7s backwards;
        }

        .about-team h2 {
            font-size: 2.3rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 16px;
            letter-spacing: -0.5px;
        }

        .about-team > p {
            font-size: 1.05rem;
            line-height: 1.8;
            color: var(--text-muted);
            margin-bottom: 48px;
            font-weight: 500;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        .team-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 32px;
            margin-top: 48px;
        }

        .team-member {
            background: var(--bg-card);
            padding: 40px 32px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            animation: cardAppear 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) backwards;
        }

        .team-member:nth-child(1) { animation-delay: 0.8s; }
        .team-member:nth-child(2) { animation-delay: 0.9s; }

        .team-member::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.05) 0%, transparent 100%);
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .team-member:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: rgba(245, 158, 11, 0.3);
        }

        .team-member:hover::before {
            opacity: 1;
        }

        .team-member strong {
            display: block;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 8px;
            letter-spacing: -0.3px;
            position: relative;
            z-index: 1;
        }

        .team-member span {
            display: block;
            font-size: 1rem;
            color: var(--primary);
            font-weight: 600;
            letter-spacing: 0.3px;
            position: relative;
            z-index: 1;
        }

        /* CTA Section */
        .about-cta {
            max-width: 900px;
            margin: 100px auto 80px;
            padding: 70px 40px;
            background: linear-gradient(135deg, #f59e0b 0%, #fb923c 100%);
            border-radius: var(--radius-lg);
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(245, 158, 11, 0.3);
            animation: fadeInUp 0.8s ease-out 0.8s backwards;
        }

        .about-cta::before {
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

        .about-cta h2 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 16px;
            position: relative;
            z-index: 1;
            letter-spacing: -0.5px;
        }

        .about-cta p {
            font-size: 1.15rem;
            opacity: 0.95;
            margin-bottom: 40px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            position: relative;
            z-index: 1;
            font-weight: 500;
            line-height: 1.7;
        }

        .cta-actions {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            position: relative;
            z-index: 1;
            color: orange;
        }

        .btn-primary,
        .btn-secondary {
            padding: 16px 40px;
            border-radius: 999px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            font-size: 1rem;
            letter-spacing: 0.3px;
            position: relative;
            overflow: hidden;
            border: 2px solid transparent;
        }

        .btn-primary::before,
        .btn-secondary::before {
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

        .btn-primary:active::before,
        .btn-secondary:active::before {
            width: 300px;
            height: 300px;
        }

        .btn-primary {
            background: white;
            color: var(--primary);
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
            color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
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

        @keyframes gradientShift {
            0%, 100% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .about-hero {
                padding: 100px 20px 60px;
            }

            .about-hero h1 {
                font-size: 2.5rem;
            }

            .about-hero p {
                font-size: 1.1rem;
            }

            .about-intro {
                margin: 60px auto;
            }

            .about-content h2 {
                font-size: 2rem;
            }

            .about-values {
                grid-template-columns: 1fr;
                margin: 60px auto;
                gap: 24px;
            }

            .value-card {
                padding: 36px 28px;
            }

            .about-trust {
                padding: 40px 24px;
                margin: 60px 20px;
            }

            .about-trust h2 {
                font-size: 1.9rem;
            }

            .trust-points {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .about-team {
                margin: 60px auto;
            }

            .about-team h2 {
                font-size: 1.9rem;
            }

            .team-list {
                grid-template-columns: 1fr;
                gap: 24px;
            }

            .about-cta {
                margin: 60px 20px;
                padding: 50px 28px;
            }

            .about-cta h2 {
                font-size: 2rem;
            }

            .cta-actions {
                flex-direction: column;
                gap: 16px;
            }

            .btn-primary,
            .btn-secondary {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .about-hero h1 {
                font-size: 2rem;
            }

            .about-values {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <!-- Hero Section -->
    <section class="about-hero">
        <h1>About CrowdSpark</h1>
        <p>
            Building trust, transparency, and impact through responsible crowdfunding.
        </p>
    </section>

    <!-- About Intro Section -->
    <section class="about-intro">
        <div class="about-content">
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
    </section>

    <!-- Mission & Vision Cards -->
    <section class="about-values">
        <div class="value-card">
            <h3>Our Mission</h3>
            <p>
                To make crowdfunding safe, transparent, and accessible for everyone
                by enabling meaningful causes to receive the support they deserve.
            </p>
        </div>

        <div class="value-card">
            <h3>Our Vision</h3>
            <p>
                To become a trusted global crowdfunding platform where compassion,
                integrity, and impact drive every contribution.
            </p>
        </div>
    </section>

    <!-- Trust Section -->
    <section class="about-trust">
        <h2>Why Trust CrowdSpark?</h2>

        <div class="trust-points">
            <div class="trust-point">
                ✔ Campaign review and approval process
            </div>
            <div class="trust-point">
                ✔ Transparent fundraising progress
            </div>
            <div class="trust-point">
                ✔ Secure donation workflow
            </div>
            <div class="trust-point">
                ✔ Community-driven impact
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="about-team">
        <h2>Project Team</h2>
        <p>
            CrowdSpark is designed and developed as a full-stack web project
            with a focus on clean architecture, security, and real-world usability.
        </p>

        <div class="team-list">
            <div class="team-member">
                <strong>Ajay Patil</strong>
                <span>Developer</span>
            </div>

            <div class="team-member">
                <strong>Gautam Londhe</strong>
                <span>Project Contributor</span>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="about-cta">
        <h2>Join Us in Making a Difference</h2>
        <p>
            Whether you want to support a cause or start a fundraiser,
            CrowdSpark is here to help you create meaningful impact.
        </p>

        <div class="cta-actions">
            <a href="/CroudSpark-X/public/explore-campaigns.php" class="btn-primary">
                Explore Campaigns
            </a>
            <a href="/CroudSpark-X/public/start-fundraise.php" class="btn-secondary">
                Start a Fundraiser
            </a>
        </div>
    </section>

</body>
</html>

<?php
require_once __DIR__ . "/../includes/footer.php";
?>