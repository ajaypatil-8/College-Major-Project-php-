<?php
session_start();
require_once __DIR__."/../config/env.php";

$campaign_id = $_GET['id'] ?? 0;

// Fetch campaign details
require_once __DIR__."/../config/db.php";

$stmt = $pdo->prepare("
    SELECT c.*,
           (SELECT media_url FROM campaign_media 
            WHERE campaign_id = c.id AND media_type = 'thumbnail' 
            LIMIT 1) AS thumbnail_url
    FROM campaigns c
    WHERE c.id = ?
");
$stmt->execute([$campaign_id]);
$campaign = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$campaign) {
    die("Campaign not found");
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Donate - <?= htmlspecialchars($campaign['title']) ?> | CrowdSpark</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@400;500;600;700;800;900&display=swap');

/* ===== THEME VARIABLES ===== */
:root {
    /* Light Theme - White */
    --bg-primary: #ffffff;
    --bg-secondary: #fafafa;
    --bg-card: rgba(255, 255, 255, 0.95);
    --bg-card-hover: rgba(255, 255, 255, 0.98);
    
    --text-primary: #0f172a;
    --text-secondary: #475569;
    --text-tertiary: #64748b;
    
    --border-color: rgba(15, 23, 42, 0.1);
    --border-hover: rgba(236, 72, 153, 0.3);
    
    --orb-opacity: 0.3;
    
    --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
    --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.15);
    --shadow-xl: 0 20px 60px rgba(0, 0, 0, 0.2);
}

[data-theme="dark"] {
    /* Dark Theme - Black */
    --bg-primary: #000000;
    --bg-secondary: #0a0a0a;
    --bg-card: rgba(15, 15, 20, 0.95);
    --bg-card-hover: rgba(20, 20, 30, 0.98);
    
    --text-primary: #ffffff;
    --text-secondary: #cbd5e1;
    --text-tertiary: #94a3b8;
    
    --border-color: rgba(255, 255, 255, 0.12);
    --border-hover: rgba(236, 72, 153, 0.4);
    
    --orb-opacity: 0.25;
    
    --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.4);
    --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.5);
    --shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.6);
    --shadow-xl: 0 20px 60px rgba(0, 0, 0, 0.8);
}

/* Pink/Magenta accent colors - STAY CONSTANT */
:root,
[data-theme="dark"] {
    --accent-primary: #ec4899;
    --accent-secondary: #f472b6;
    --accent-gradient: linear-gradient(135deg, #ec4899, #f472b6);
    --orb-1: linear-gradient(45deg, #ec4899, #f472b6);
    --orb-2: linear-gradient(45deg, #db2777, #ec4899);
    --orb-3: linear-gradient(45deg, #be185d, #db2777);
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
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
}

/* Animated Background */
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
    filter: blur(100px);
    animation: float 25s infinite ease-in-out;
}

.orb-1 {
    width: 600px;
    height: 600px;
    background: var(--orb-1);
    top: -15%;
    left: -15%;
    animation-delay: 0s;
}

.orb-2 {
    width: 500px;
    height: 500px;
    background: var(--orb-2);
    bottom: -15%;
    right: -15%;
    animation-delay: 7s;
}

.orb-3 {
    width: 400px;
    height: 400px;
    background: var(--orb-3);
    top: 50%;
    left: 50%;
    animation-delay: 14s;
}

@keyframes float {
    0%, 100% { transform: translate(0, 0) scale(1); }
    25% { transform: translate(60px, 60px) scale(1.1); }
    50% { transform: translate(-40px, 100px) scale(0.9); }
    75% { transform: translate(50px, -50px) scale(1.05); }
}

/* ===== ANIMATIONS ===== */
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

@keyframes scaleIn {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes shimmer {
    0% { background-position: -1000px 0; }
    100% { background-position: 1000px 0; }
}

@keyframes heartbeat {
    0%, 100% { transform: scale(1); }
    10%, 30% { transform: scale(1.15); }
    20%, 40% { transform: scale(1); }
}

/* ===== THEME TOGGLE ===== */
.theme-toggle {
    position: fixed;
    top: 30px;
    right: 30px;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: var(--bg-card);
    border: 2px solid var(--border-color);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: var(--accent-primary);
    box-shadow: var(--shadow-md);
    transition: all 0.3s ease;
    z-index: 1000;
}

.theme-toggle:hover {
    transform: scale(1.1) rotate(20deg);
    border-color: var(--accent-primary);
    box-shadow: var(--shadow-lg);
}

[data-theme="light"] .theme-toggle .fa-moon { display: block; }
[data-theme="light"] .theme-toggle .fa-sun { display: none; }
[data-theme="dark"] .theme-toggle .fa-moon { display: none; }
[data-theme="dark"] .theme-toggle .fa-sun { display: block; }

/* ===== CONTAINER ===== */
.donate-container {
    position: relative;
    z-index: 1;
    max-width: 900px;
    width: 100%;
    animation: fadeInUp 0.8s ease;
}

/* ===== BACK LINK ===== */
.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 30px;
    color: var(--accent-primary);
    font-weight: 700;
    text-decoration: none;
    padding: 12px 24px;
    border-radius: 50px;
    background: rgba(236, 72, 153, 0.1);
    border: 2px solid var(--border-hover);
    transition: all 0.3s ease;
}

.back-link:hover {
    background: rgba(236, 72, 153, 0.2);
    transform: translateX(-4px);
}

/* ===== DONATION CARD ===== */
.donation-card {
    background: var(--bg-card);
    backdrop-filter: blur(30px);
    border-radius: 32px;
    box-shadow: var(--shadow-xl);
    border: 1px solid var(--border-color);
    overflow: hidden;
    transition: all 0.3s ease;
    animation: scaleIn 0.8s ease 0.2s both;
}

.donation-card:hover {
    box-shadow: 0 30px 80px rgba(236, 72, 153, 0.2);
}

/* ===== CAMPAIGN INFO ===== */
.campaign-info {
    padding: 40px;
    border-bottom: 1px solid var(--border-color);
    background: linear-gradient(135deg, rgba(236, 72, 153, 0.03) 0%, transparent 100%);
}

.campaign-header {
    display: flex;
    gap: 24px;
    align-items: flex-start;
}

.campaign-thumbnail {
    width: 120px;
    height: 120px;
    border-radius: 20px;
    object-fit: cover;
    box-shadow: var(--shadow-md);
    border: 3px solid var(--border-color);
    transition: all 0.3s ease;
}

.campaign-thumbnail:hover {
    transform: scale(1.05) rotate(2deg);
    box-shadow: var(--shadow-lg);
}

.campaign-details {
    flex: 1;
}

.campaign-title {
    font-family: 'Playfair Display', serif;
    font-size: 28px;
    font-weight: 900;
    margin-bottom: 12px;
    background: linear-gradient(135deg, var(--text-primary), var(--accent-primary));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    line-height: 1.2;
}

.campaign-category {
    display: inline-block;
    padding: 6px 16px;
    background: rgba(236, 72, 153, 0.1);
    color: var(--accent-primary);
    border-radius: 999px;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 16px;
}

.campaign-description {
    color: var(--text-secondary);
    font-size: 15px;
    line-height: 1.6;
}

/* ===== DONATION FORM ===== */
.donation-form {
    padding: 50px 40px;
}

.form-header {
    text-align: center;
    margin-bottom: 40px;
}

.form-header h2 {
    font-family: 'Playfair Display', serif;
    font-size: 32px;
    font-weight: 900;
    margin-bottom: 12px;
    background: linear-gradient(135deg, var(--text-primary), var(--accent-primary));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.form-header p {
    color: var(--text-secondary);
    font-size: 16px;
}

.form-group {
    margin-bottom: 28px;
    animation: fadeInUp 0.5s ease backwards;
}

.form-group:nth-child(1) { animation-delay: 0.3s; }
.form-group:nth-child(2) { animation-delay: 0.4s; }
.form-group:nth-child(3) { animation-delay: 0.5s; }

label {
    display: block;
    font-weight: 700;
    margin-bottom: 10px;
    color: var(--text-primary);
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.required {
    color: var(--accent-primary);
    margin-left: 4px;
}

input {
    width: 100%;
    padding: 18px 20px;
    border-radius: 16px;
    border: 2px solid var(--border-color);
    background: var(--bg-secondary);
    font-family: 'DM Sans', sans-serif;
    font-size: 16px;
    color: var(--text-primary);
    transition: all 0.3s ease;
}

input:focus {
    outline: none;
    border-color: var(--accent-primary);
    background: var(--bg-card-hover);
    box-shadow: 0 0 0 4px rgba(236, 72, 153, 0.1);
    transform: translateY(-2px);
}

input:hover {
    border-color: var(--border-hover);
}

input::placeholder {
    color: var(--text-tertiary);
}

/* ===== QUICK AMOUNTS ===== */
.quick-amounts {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-top: 12px;
    margin-bottom: 24px;
}

.amount-btn {
    padding: 14px;
    border-radius: 12px;
    border: 2px solid var(--border-color);
    background: var(--bg-secondary);
    color: var(--text-primary);
    font-weight: 700;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
}

.amount-btn:hover {
    border-color: var(--accent-primary);
    color: var(--accent-primary);
    transform: translateY(-2px);
}

.amount-btn.active {
    background: var(--accent-gradient);
    border-color: transparent;
    color: #fff;
    box-shadow: 0 4px 12px rgba(236, 72, 153, 0.3);
}

/* ===== SUBMIT BUTTON ===== */
.submit-btn {
    width: 100%;
    padding: 20px;
    border-radius: 16px;
    border: none;
    background: var(--accent-gradient);
    color: #fff;
    font-family: 'DM Sans', sans-serif;
    font-size: 18px;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 10px 40px rgba(236, 72, 153, 0.35);
    position: relative;
    overflow: hidden;
    margin-top: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.submit-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s;
}

.submit-btn:hover::before {
    left: 100%;
}

.submit-btn:hover {
    transform: translateY(-4px) scale(1.02);
    box-shadow: 0 20px 60px rgba(236, 72, 153, 0.5);
}

.submit-btn:active {
    transform: translateY(-2px) scale(1);
}

.submit-btn i {
    font-size: 20px;
    animation: heartbeat 2s infinite;
}

/* ===== TRUST BADGES ===== */
.trust-section {
    padding: 30px 40px;
    background: linear-gradient(135deg, rgba(236, 72, 153, 0.02) 0%, transparent 100%);
    border-top: 1px solid var(--border-color);
}

.trust-badges {
    display: flex;
    justify-content: center;
    gap: 40px;
    flex-wrap: wrap;
}

.trust-badge {
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--text-secondary);
    font-size: 14px;
    font-weight: 600;
}

.trust-badge i {
    font-size: 20px;
    color: var(--accent-primary);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .theme-toggle {
        top: 20px;
        right: 20px;
        width: 45px;
        height: 45px;
    }

    .donate-container {
        padding: 0;
    }
    
    .campaign-info {
        padding: 30px 24px;
    }
    
    .campaign-header {
        flex-direction: column;
    }
    
    .campaign-thumbnail {
        width: 100%;
        height: 200px;
    }
    
    .campaign-title {
        font-size: 24px;
    }
    
    .donation-form {
        padding: 40px 24px;
    }
    
    .form-header h2 {
        font-size: 26px;
    }
    
    .quick-amounts {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .trust-section {
        padding: 24px;
    }
    
    .trust-badges {
        gap: 20px;
    }
}

@media (max-width: 480px) {
    body {
        padding: 20px 10px;
    }
    
    .back-link {
        padding: 10px 20px;
        font-size: 14px;
    }
    
    .donation-card {
        border-radius: 24px;
    }
    
    .campaign-info {
        padding: 24px 20px;
    }
    
    .donation-form {
        padding: 30px 20px;
    }
    
    .quick-amounts {
        gap: 8px;
    }
    
    .amount-btn {
        padding: 12px;
        font-size: 14px;
    }
    
    input {
        padding: 16px 18px;
    }
    
    .submit-btn {
        padding: 18px;
        font-size: 16px;
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

<!-- Theme Toggle -->
<button class="theme-toggle" onclick="toggleTheme()" title="Toggle theme">
    <i class="fa-solid fa-moon"></i>
    <i class="fa-solid fa-sun"></i>
</button>

<div class="donate-container">

    <a href="/CroudSpark-X/public/campaign-details.php?id=<?= $campaign_id ?>" class="back-link">
        <i class="fa-solid fa-arrow-left"></i> Back to Campaign
    </a>

    <div class="donation-card">
        
        <!-- Campaign Info -->
        <div class="campaign-info">
            <div class="campaign-header">
                <img src="<?= htmlspecialchars($campaign['thumbnail_url'] ?? 'https://via.placeholder.com/120') ?>" 
                     alt="<?= htmlspecialchars($campaign['title']) ?>" 
                     class="campaign-thumbnail"
                     onerror="this.src='https://via.placeholder.com/120'">
                
                <div class="campaign-details">
                    <h1 class="campaign-title"><?= htmlspecialchars($campaign['title']) ?></h1>
                    <div class="campaign-category">
                        <i class="fa-solid fa-tag"></i> <?= htmlspecialchars($campaign['category'] ?? 'General') ?>
                    </div>
                    <p class="campaign-description">
                        <?= htmlspecialchars(substr($campaign['short_desc'] ?? $campaign['story'], 0, 150)) ?>...
                    </p>
                </div>
            </div>
        </div>

        <!-- Donation Form -->
        <form class="donation-form" id="donationForm">
            
            <div class="form-header">
                <h2>Make a Difference Today</h2>
                <p>Your contribution will help make this campaign a success</p>
            </div>

            <input type="hidden" id="campaign_id" value="<?= htmlspecialchars($campaign_id) ?>">

            <div class="form-group">
                <label for="donor_name">Your Name</label>
                <input type="text" 
                       id="donor_name" 
                       placeholder="Enter your full name (or remain anonymous)"
                       autocomplete="name">
            </div>

            <div class="form-group">
                <label for="donor_email">Email Address <span class="required">*</span></label>
                <input type="email" 
                       id="donor_email" 
                       placeholder="your.email@example.com"
                       required
                       autocomplete="email">
            </div>

            <div class="form-group">
                <label for="amount">Donation Amount (₹) <span class="required">*</span></label>
                <input type="number" 
                       id="amount" 
                       placeholder="Enter amount"
                       min="1"
                       required>
                
                <div class="quick-amounts">
                    <button type="button" class="amount-btn" onclick="setAmount(500)">₹500</button>
                    <button type="button" class="amount-btn" onclick="setAmount(1000)">₹1,000</button>
                    <button type="button" class="amount-btn" onclick="setAmount(2500)">₹2,500</button>
                    <button type="button" class="amount-btn" onclick="setAmount(5000)">₹5,000</button>
                </div>
            </div>

            <button type="button" class="submit-btn" onclick="payNow()">
                <i class="fa-solid fa-heart"></i>
                Donate Now
            </button>

        </form>

        <!-- Trust Section -->
        <div class="trust-section">
            <div class="trust-badges">
                <div class="trust-badge">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>Secure Payment</span>
                </div>
                <div class="trust-badge">
                    <i class="fa-solid fa-lock"></i>
                    <span>SSL Encrypted</span>
                </div>
                <div class="trust-badge">
                    <i class="fa-solid fa-heart-circle-check"></i>
                    <span>100% Verified</span>
                </div>
            </div>
        </div>

    </div>

</div>

<script>
// Theme System
function getTheme() {
    return localStorage.getItem('crowdspark-theme') || 'light';
}

function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('crowdspark-theme', theme);
}

function toggleTheme() {
    const currentTheme = getTheme();
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    setTheme(newTheme);
}

// Initialize theme
(function() {
    const savedTheme = getTheme();
    setTheme(savedTheme);
})();

// Expose globally
window.CrowdSparkTheme = {
    toggle: toggleTheme,
    set: setTheme,
    get: getTheme
};

// Quick amount selection
function setAmount(value) {
    document.getElementById('amount').value = value;
    
    // Update active state
    document.querySelectorAll('.amount-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
}

// Payment function
function payNow() {
    let name = document.getElementById("donor_name").value.trim();
    let email = document.getElementById("donor_email").value.trim();
    let amount = document.getElementById("amount").value;
    let campaignId = document.getElementById("campaign_id").value;

    // Validation
    if (!email) {
        alert("Please enter your email address");
        document.getElementById("donor_email").focus();
        return;
    }

    if (!amount || amount < 1) {
        alert("Please enter a valid donation amount");
        document.getElementById("amount").focus();
        return;
    }

    if (name == "") name = "Anonymous";

    // Disable button during processing
    const btn = document.querySelector('.submit-btn');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
    btn.disabled = true;

    /* STEP 1: CREATE ORDER */
    fetch('/CroudSpark-X/public/create-order.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: "amount=" + amount
    })
    .then(res => res.json())
    .then(order => {
        
        if (order.status != "success") {
            alert("Order creation failed. Please try again.");
            console.log(order);
            btn.innerHTML = originalText;
            btn.disabled = false;
            return;
        }

        /* STEP 2: OPEN RAZORPAY */
        var options = {
            "key": "<?= $_ENV['RAZORPAY_KEY_ID'] ?>",
            "amount": amount * 100,
            "currency": "INR",
            "name": "CrowdSpark Donation",
            "description": "Support <?= htmlspecialchars($campaign['title']) ?>",
            "order_id": order.order_id,
            "image": "<?= htmlspecialchars($campaign['thumbnail_url'] ?? '') ?>",

            "prefill": {
                "name": name,
                "email": email
            },

            "theme": {
                "color": "#ec4899"
            },

            "handler": function (response) {
                /* STEP 3: SAVE PAYMENT */
                fetch('/CroudSpark-X/public/verify-payment.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body:
                        "razorpay_payment_id=" + response.razorpay_payment_id +
                        "&razorpay_order_id=" + response.razorpay_order_id +
                        "&campaign_id=" + campaignId +
                        "&amount=" + amount +
                        "&donor_name=" + encodeURIComponent(name) +
                        "&donor_email=" + encodeURIComponent(email)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status == "success") {
                        window.location = "/CroudSpark-X/dashboard/user-dashboard.php";
                    } else {
                        alert("Payment verification failed. Please contact support.");
                        console.log(data);
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    alert("An error occurred. Please try again.");
                    console.error(err);
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
            },

            "modal": {
                "ondismiss": function() {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            }
        };

        var rzp = new Razorpay(options);
        rzp.open();
    })
    .catch(err => {
        alert("An error occurred. Please try again.");
        console.error(err);
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}
</script>

</body>
</html>