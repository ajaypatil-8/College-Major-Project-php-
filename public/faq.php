<?php
session_start();
require_once __DIR__ . "/../includes/header.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQs - CrowdSpark</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
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
            --border-hover: rgba(245, 158, 11, 0.3);
            
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.04);
            --shadow-md: 0 8px 32px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 20px 60px rgba(0, 0, 0, 0.15);
            
            --orb-opacity: 0.20;
        }

        [data-theme="dark"] {
            --bg-primary: #0f0f0f;
            --bg-secondary: #1a1a1a;
            --bg-card: rgba(20, 20, 30, 0.85);
            --bg-card-hover: rgba(30, 30, 40, 0.95);
            
            --text-primary: #ffffff;
            --text-secondary: #cbd5e1;
            --text-tertiary: #94a3b8;
            
            --border-color: rgba(255, 255, 255, 0.1);
            --border-hover: rgba(245, 158, 11, 0.4);
            
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.3);
            --shadow-md: 0 8px 32px rgba(0, 0, 0, 0.4);
            --shadow-lg: 0 20px 60px rgba(0, 0, 0, 0.6);
            
            --orb-opacity: 0.25;
        }

        /* Orange accent colors */
        :root, [data-theme="dark"] {
            --accent-primary: #f59e0b;
            --accent-secondary: #fb923c;
            --accent-gradient: linear-gradient(135deg, #f59e0b, #fb923c);
            --accent-glow: rgba(245, 158, 11, 0.4);
        }

        /* Orb colors */
        [data-theme="dark"] {
            --orb-1: linear-gradient(45deg, #f59e0b, #fb923c);
            --orb-2: linear-gradient(45deg, #ea580c, #f59e0b);
            --orb-3: linear-gradient(45deg, #dc2626, #ea580c);
        }

        [data-theme="light"] {
            --orb-1: linear-gradient(45deg, #fbbf24, #fb923c);
            --orb-2: linear-gradient(45deg, #fb923c, #f59e0b);
            --orb-3: linear-gradient(45deg, #f59e0b, #ea580c);
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

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Page Container */
        .faq-page {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            padding: 140px 20px 80px;
        }

        .faq-container {
            max-width: 1000px;
            margin: 0 auto;
            animation: fadeInUp 0.8s ease;
        }

        /* Hero Section */
        .faq-hero {
            text-align: center;
            margin-bottom: 60px;
        }

        .faq-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.5rem, 6vw, 4rem);
            font-weight: 900;
            margin-bottom: 16px;
            background: linear-gradient(135deg, var(--text-primary), var(--accent-primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: fadeInUp 0.6s ease;
        }

        .faq-hero p {
            font-size: 1.25rem;
            color: var(--text-secondary);
            max-width: 600px;
            margin: 0 auto;
            animation: fadeInUp 0.6s ease 0.1s both;
        }

        /* Search Box */
        .faq-search {
            max-width: 600px;
            margin: 0 auto 50px;
            animation: fadeInUp 0.6s ease 0.2s both;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 18px 24px 18px 56px;
            border-radius: 999px;
            border: 2px solid var(--border-color);
            background: var(--bg-card);
            backdrop-filter: blur(10px);
            font-size: 16px;
            font-weight: 500;
            color: var(--text-primary);
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 4px var(--accent-glow);
        }

        .search-box i {
            position: absolute;
            left: 24px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--accent-primary);
            font-size: 18px;
        }

        /* Category Tabs */
        .faq-categories {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 50px;
            animation: fadeIn 0.6s ease 0.3s both;
        }

        .category-btn {
            padding: 12px 24px;
            border-radius: 999px;
            border: 2px solid var(--border-color);
            background: var(--bg-card);
            color: var(--text-secondary);
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .category-btn:hover {
            border-color: var(--accent-primary);
            color: var(--accent-primary);
            transform: translateY(-2px);
        }

        .category-btn.active {
            background: var(--accent-gradient);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 4px 12px var(--accent-glow);
        }

        /* FAQ Accordion */
        .faq-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .faq-item {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
            animation: fadeInUp 0.6s ease both;
        }

        .faq-item:nth-child(1) { animation-delay: 0.1s; }
        .faq-item:nth-child(2) { animation-delay: 0.15s; }
        .faq-item:nth-child(3) { animation-delay: 0.2s; }
        .faq-item:nth-child(4) { animation-delay: 0.25s; }
        .faq-item:nth-child(5) { animation-delay: 0.3s; }

        .faq-item:hover {
            border-color: var(--border-hover);
            box-shadow: var(--shadow-md);
        }

        .faq-item.active {
            border-color: var(--accent-primary);
            box-shadow: 0 8px 24px var(--accent-glow);
        }

        .faq-question {
            padding: 24px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            gap: 20px;
        }

        .faq-question h3 {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--text-primary);
            flex: 1;
        }

        .faq-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--bg-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent-primary);
            font-size: 18px;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }

        .faq-item.active .faq-icon {
            background: var(--accent-gradient);
            color: #fff;
            transform: rotate(180deg);
        }

        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease;
        }

        .faq-item.active .faq-answer {
            max-height: 500px;
        }

        .faq-answer-content {
            padding: 0 28px 28px;
            color: var(--text-secondary);
            line-height: 1.8;
            font-size: 15px;
        }

        .faq-answer-content p {
            margin-bottom: 12px;
        }

        .faq-answer-content ul {
            margin-left: 20px;
            margin-top: 12px;
        }

        .faq-answer-content li {
            margin-bottom: 8px;
        }

        /* Still Need Help Section */
        .help-section {
            margin-top: 60px;
            padding: 50px;
            background: var(--accent-gradient);
            border-radius: 24px;
            text-align: center;
            color: #fff;
            animation: fadeInUp 0.6s ease 0.4s both;
        }

        .help-section h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 900;
            margin-bottom: 16px;
        }

        .help-section p {
            font-size: 1.1rem;
            margin-bottom: 30px;
            opacity: 0.95;
        }

        .help-buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .help-btn {
            padding: 16px 32px;
            border-radius: 999px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            color: #fff;
            font-weight: 800;
            font-size: 16px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .help-btn:hover {
            background: #fff;
            color: var(--accent-primary);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .faq-page {
                padding: 120px 15px 60px;
            }

            .faq-hero h1 {
                font-size: 2.5rem;
            }

            .faq-categories {
                gap: 8px;
            }

            .category-btn {
                padding: 10px 20px;
                font-size: 13px;
            }

            .faq-question {
                padding: 20px;
            }

            .faq-question h3 {
                font-size: 1rem;
            }

            .help-section {
                padding: 40px 24px;
            }

            .help-section h2 {
                font-size: 2rem;
            }

            .help-buttons {
                flex-direction: column;
            }

            .help-btn {
                width: 100%;
                justify-content: center;
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

    <div class="faq-page">
        <div class="faq-container">

            <!-- Hero -->
            <div class="faq-hero">
                <h1>Frequently Asked Questions</h1>
                <p>Find answers to common questions about CrowdSpark</p>
            </div>

            <!-- Search -->
            <div class="faq-search">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="faqSearch" placeholder="Search for answers...">
                </div>
            </div>

            <!-- Categories -->
            <div class="faq-categories">
                <button class="category-btn active" onclick="filterCategory('all')">All</button>
                <button class="category-btn" onclick="filterCategory('general')">General</button>
                <button class="category-btn" onclick="filterCategory('donations')">Donations</button>
                <button class="category-btn" onclick="filterCategory('campaigns')">Campaigns</button>
                <button class="category-btn" onclick="filterCategory('account')">Account</button>
            </div>

            <!-- FAQ List -->
            <div class="faq-list" id="faqList">

                <!-- General FAQs -->
                <div class="faq-item" data-category="general">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <h3>What is CrowdSpark?</h3>
                        <div class="faq-icon">
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            <p>CrowdSpark is a crowdfunding platform that connects people who need financial support with those who want to help. We enable individuals, nonprofits, and organizations to raise funds for causes they care about.</p>
                            <p>Our platform makes it easy to create campaigns, share your story, and receive donations from supporters around the world.</p>
                        </div>
                    </div>
                </div>

                <div class="faq-item" data-category="general">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <h3>How does CrowdSpark work?</h3>
                        <div class="faq-icon">
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            <p>CrowdSpark works in simple steps:</p>
                            <ul>
                                <li><strong>Create:</strong> Sign up and create your campaign with a compelling story</li>
                                <li><strong>Share:</strong> Share your campaign with friends, family, and social networks</li>
                                <li><strong>Receive:</strong> Accept donations through secure payment processing</li>
                                <li><strong>Update:</strong> Keep your supporters informed with regular updates</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="faq-item" data-category="general">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <h3>Is CrowdSpark free to use?</h3>
                        <div class="faq-icon">
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            <p>It's free to start a campaign on CrowdSpark. We charge a small platform fee on donations received to cover operational costs and payment processing fees.</p>
                            <p>There are no upfront costs, hidden fees, or charges if you don't reach your goal.</p>
                        </div>
                    </div>
                </div>

                <!-- Donation FAQs -->
                <div class="faq-item" data-category="donations">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <h3>How do I donate to a campaign?</h3>
                        <div class="faq-icon">
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            <p>Donating is simple:</p>
                            <ul>
                                <li>Browse campaigns or search for a specific cause</li>
                                <li>Click on the campaign you want to support</li>
                                <li>Click the "Donate Now" button</li>
                                <li>Enter your donation amount</li>
                                <li>Complete the secure payment process</li>
                            </ul>
                            <p>You'll receive an email confirmation once your donation is processed.</p>
                        </div>
                    </div>
                </div>

                <div class="faq-item" data-category="donations">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <h3>Are donations tax-deductible?</h3>
                        <div class="faq-icon">
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            <p>Tax deductibility depends on the campaign type and your location. Donations to registered nonprofits may be tax-deductible, while personal campaigns typically are not.</p>
                            <p>We recommend consulting with a tax professional regarding your specific situation.</p>
                        </div>
                    </div>
                </div>

                <div class="faq-item" data-category="donations">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <h3>Can I get a refund for my donation?</h3>
                        <div class="faq-icon">
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            <p>All donations are final. However, if you believe there's been fraudulent activity or a mistake, please contact our support team immediately.</p>
                            <p>We take fraud seriously and investigate all reports thoroughly.</p>
                        </div>
                    </div>
                </div>

                <!-- Campaign FAQs -->
                <div class="faq-item" data-category="campaigns">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <h3>How do I start a campaign?</h3>
                        <div class="faq-icon">
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            <p>To start a campaign:</p>
                            <ul>
                                <li>Sign up for a CrowdSpark account</li>
                                <li>Click "Start Project" or "Become Creator"</li>
                                <li>Fill in your campaign details (title, goal, story, images)</li>
                                <li>Submit for review</li>
                                <li>Once approved, your campaign goes live!</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="faq-item" data-category="campaigns">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <h3>How long does campaign approval take?</h3>
                        <div class="faq-icon">
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            <p>Most campaigns are reviewed within 24-48 hours. We review each campaign to ensure it meets our guidelines and terms of service.</p>
                            <p>You'll receive an email notification once your campaign is approved or if we need additional information.</p>
                        </div>
                    </div>
                </div>

                <div class="faq-item" data-category="campaigns">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <h3>What happens if I don't reach my goal?</h3>
                        <div class="faq-icon">
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            <p>CrowdSpark uses a flexible funding model. This means you keep all the funds you raise, even if you don't reach your goal.</p>
                            <p>You can continue your campaign or extend the deadline to reach more supporters.</p>
                        </div>
                    </div>
                </div>

                <!-- Account FAQs -->
                <div class="faq-item" data-category="account">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <h3>How do I create an account?</h3>
                        <div class="faq-icon">
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            <p>Click the "Sign Up" or "Login" button in the navigation bar. Fill in your name, email, and create a secure password. You'll receive a verification email to activate your account.</p>
                        </div>
                    </div>
                </div>

                <div class="faq-item" data-category="account">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <h3>How do I reset my password?</h3>
                        <div class="faq-icon">
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            <p>Click "Forgot Password" on the login page. Enter your email address, and we'll send you a link to reset your password. Follow the instructions in the email to create a new password.</p>
                        </div>
                    </div>
                </div>

                <div class="faq-item" data-category="account">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <h3>Can I delete my account?</h3>
                        <div class="faq-icon">
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            <p>Yes, you can delete your account from your profile settings. Please note that active campaigns must be closed before account deletion, and this action cannot be undone.</p>
                            <p>Contact support if you need assistance with account deletion.</p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Still Need Help -->
            <div class="help-section">
                <h2>Still Need Help?</h2>
                <p>Can't find what you're looking for? Our support team is here to help!</p>
                <div class="help-buttons">
                    <a href="/public/helpcenter.php" class="help-btn">
                        <i class="fa-solid fa-life-ring"></i> Help Center
                    </a>
                    <a href="/public/contact.php" class="help-btn">
                        <i class="fa-solid fa-envelope"></i> Contact Support
                    </a>
                </div>
            </div>

        </div>
    </div>

    <script>
        // Toggle FAQ
        function toggleFAQ(element) {
            const item = element.closest('.faq-item');
            const allItems = document.querySelectorAll('.faq-item');
            
            // Close all other items
            allItems.forEach(i => {
                if (i !== item) {
                    i.classList.remove('active');
                }
            });
            
            // Toggle current item
            item.classList.toggle('active');
        }

        // Filter by category
        function filterCategory(category) {
            const items = document.querySelectorAll('.faq-item');
            const buttons = document.querySelectorAll('.category-btn');
            
            // Update active button
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            // Filter items
            items.forEach(item => {
                if (category === 'all' || item.dataset.category === category) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
                item.classList.remove('active');
            });
        }

        // Search functionality
        document.getElementById('faqSearch').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const items = document.querySelectorAll('.faq-item');
            
            items.forEach(item => {
                const question = item.querySelector('h3').textContent.toLowerCase();
                const answer = item.querySelector('.faq-answer-content').textContent.toLowerCase();
                
                if (question.includes(searchTerm) || answer.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    </script>

    <?php require_once __DIR__ . "/../includes/footer.php"; ?>

</body>
</html>