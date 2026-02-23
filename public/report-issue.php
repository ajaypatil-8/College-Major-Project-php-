<?php
session_start();
require_once __DIR__ . "/../config/db.php";

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category = $_POST['category'] ?? '';
    $campaign_url = trim($_POST['campaign_url'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $reporter_email = trim($_POST['reporter_email'] ?? '');
    $reporter_name = trim($_POST['reporter_name'] ?? '');
    
    // Validation
    if (empty($category) || empty($description) || empty($reporter_email)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($reporter_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($description) < 20) {
        $error = "Please provide more details (at least 20 characters).";
    } else {
        try {
            // Save report to database
            $stmt = $pdo->prepare("
                INSERT INTO reports 
                (category, reporter_name, reporter_email, campaign_url, description, status, created_at) 
                VALUES (?, ?, ?, ?, ?, 'pending', NOW())
            ");
            
            $stmt->execute([
                $category, 
                $reporter_name, 
                $reporter_email, 
                $campaign_url, 
                $description
            ]);
            
            $success = "✓ Report submitted successfully! Our team will review it within 24 hours. Reference ID: #" . str_pad($pdo->lastInsertId(), 6, '0', STR_PAD_LEFT);
            
            // Clear form data
            $_POST = array();
            
        } catch (PDOException $e) {
            $error = "Failed to submit report. Please try again or contact support.";
            error_log("Report submission error: " . $e->getMessage());
        }
    }
}

require_once __DIR__ . "/../includes/header.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report an Issue - CrowdSpark</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-primary: #fafafa;
            --bg-secondary: #f1f5f9;
            --bg-card: rgba(255, 255, 255, 0.95);
            --bg-input: #ffffff;
            
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-tertiary: #64748b;
            
            --border-color: rgba(15, 23, 42, 0.08);
            
            --shadow-md: 0 8px 32px rgba(0, 0, 0, 0.08);
            
            --orb-opacity: 0.20;
        }

        [data-theme="dark"] {
            --bg-primary: #0f0f0f;
            --bg-secondary: #1a1a1a;
            --bg-card: rgba(20, 20, 30, 0.85);
            --bg-input: rgba(10, 10, 20, 0.6);
            
            --text-primary: #ffffff;
            --text-secondary: #cbd5e1;
            --text-tertiary: #94a3b8;
            
            --border-color: rgba(255, 255, 255, 0.1);
            
            --shadow-md: 0 8px 32px rgba(0, 0, 0, 0.4);
            
            --orb-opacity: 0.25;
        }

        :root, [data-theme="dark"] {
            --accent-primary: #ef4444;
            --accent-secondary: #f87171;
            --accent-gradient: linear-gradient(135deg, #ef4444, #f87171);
            --accent-glow: rgba(239, 68, 68, 0.4);
        }

        [data-theme="dark"] {
            --orb-1: linear-gradient(45deg, #ef4444, #f87171);
            --orb-2: linear-gradient(45deg, #dc2626, #ef4444);
            --orb-3: linear-gradient(45deg, #b91c1c, #dc2626);
        }

        [data-theme="light"] {
            --orb-1: linear-gradient(45deg, #fca5a5, #f87171);
            --orb-2: linear-gradient(45deg, #f87171, #ef4444);
            --orb-3: linear-gradient(45deg, #ef4444, #dc2626);
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

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            50% { transform: translateX(5px); }
            75% { transform: translateX(-5px); }
        }

        .report-page {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            padding: 140px 20px 80px;
        }

        .report-container {
            max-width: 800px;
            margin: 0 auto;
            animation: fadeInUp 0.8s ease;
        }

        .report-hero {
            text-align: center;
            margin-bottom: 50px;
        }

        .report-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.5rem, 6vw, 4rem);
            font-weight: 900;
            margin-bottom: 16px;
            background: linear-gradient(135deg, var(--text-primary), var(--accent-primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .report-hero p {
            font-size: 1.1rem;
            color: var(--text-secondary);
            line-height: 1.7;
        }

        .alert {
            padding: 20px 28px;
            border-radius: 16px;
            margin-bottom: 30px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 14px;
            font-size: 15px;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
            border: 2px solid rgba(16, 185, 129, 0.3);
            animation: fadeInUp 0.5s ease;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            border: 2px solid rgba(239, 68, 68, 0.3);
            animation: shake 0.5s ease;
        }

        .alert i {
            font-size: 20px;
        }

        .report-form {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 50px;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
        }

        .form-group {
            margin-bottom: 28px;
        }

        .form-group label {
            display: block;
            font-weight: 800;
            margin-bottom: 10px;
            color: var(--text-primary);
            font-size: 15px;
            transition: color 0.3s ease;
        }

        .form-group label .required {
            color: var(--accent-primary);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid var(--border-color);
            border-radius: 14px;
            font-size: 15px;
            font-weight: 500;
            background: var(--bg-input);
            color: var(--text-primary);
            transition: all 0.3s ease;
            font-family: 'DM Sans', sans-serif;
        }

        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 4px var(--accent-glow);
            transform: translateY(-2px);
        }

        .form-group select {
            cursor: pointer;
        }

        .form-hint {
            font-size: 13px;
            color: var(--text-tertiary);
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .form-hint i {
            color: var(--accent-primary);
            font-size: 12px;
        }

        .char-counter {
            font-size: 12px;
            color: var(--text-tertiary);
            text-align: right;
            margin-top: 6px;
        }

        .submit-btn {
            width: 100%;
            padding: 18px;
            background: var(--accent-gradient);
            color: #fff;
            border: none;
            border-radius: 999px;
            font-weight: 900;
            font-size: 17px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px var(--accent-glow);
            text-transform: uppercase;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px var(--accent-glow);
        }

        .submit-btn:active {
            transform: translateY(-1px);
        }

        .info-box {
            background: var(--bg-secondary);
            border: 2px solid var(--border-color);
            border-radius: 16px;
            padding: 28px;
            margin-bottom: 40px;
            transition: all 0.3s ease;
        }

        .info-box:hover {
            border-color: var(--accent-primary);
        }

        .info-box h3 {
            font-size: 1.2rem;
            font-weight: 800;
            margin-bottom: 16px;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-box h3 i {
            color: var(--accent-primary);
            font-size: 22px;
        }

        .info-box ul {
            margin-left: 20px;
        }

        .info-box li {
            color: var(--text-secondary);
            line-height: 1.8;
            margin-bottom: 8px;
        }

        @media (max-width: 768px) {
            .report-page {
                padding: 120px 15px 60px;
            }

            .report-form {
                padding: 35px 24px;
            }

            .info-box {
                padding: 24px 20px;
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

    <div class="report-page">
        <div class="report-container">

            <div class="report-hero">
                <h1>Report an Issue</h1>
                <p>Help us maintain a safe and trustworthy community. Report suspicious campaigns, technical problems, or policy violations.</p>
            </div>

            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <span><?= $success ?></span>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?= $error ?></span>
            </div>
            <?php endif; ?>

            <?php if (!$success): ?>
            <div class="info-box">
                <h3><i class="fa-solid fa-info-circle"></i> What you can report:</h3>
                <ul>
                    <li><strong>Fraudulent campaigns</strong> - Fake stories, misleading information, or scams</li>
                    <li><strong>Inappropriate content</strong> - Offensive language, hate speech, or violations</li>
                    <li><strong>Technical issues</strong> - Bugs, errors, or problems with the platform</li>
                    <li><strong>Payment problems</strong> - Issues with donations or transactions</li>
                    <li><strong>Policy violations</strong> - Breaking our terms of service or community guidelines</li>
                </ul>
            </div>

            <form method="POST" class="report-form" id="reportForm">
                
                <div class="form-group">
                    <label>
                        Report Category <span class="required">*</span>
                    </label>
                    <select name="category" required>
                        <option value="">Select a category</option>
                        <option value="fraud" <?= isset($_POST['category']) && $_POST['category'] == 'fraud' ? 'selected' : '' ?>>Fraudulent Campaign</option>
                        <option value="inappropriate" <?= isset($_POST['category']) && $_POST['category'] == 'inappropriate' ? 'selected' : '' ?>>Inappropriate Content</option>
                        <option value="technical" <?= isset($_POST['category']) && $_POST['category'] == 'technical' ? 'selected' : '' ?>>Technical Issue</option>
                        <option value="payment" <?= isset($_POST['category']) && $_POST['category'] == 'payment' ? 'selected' : '' ?>>Payment Problem</option>
                        <option value="violation" <?= isset($_POST['category']) && $_POST['category'] == 'violation' ? 'selected' : '' ?>>Terms Violation</option>
                        <option value="other" <?= isset($_POST['category']) && $_POST['category'] == 'other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Your Name</label>
                    <input type="text" name="reporter_name" placeholder="Enter your name (optional)" value="<?= htmlspecialchars($_POST['reporter_name'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>
                        Your Email <span class="required">*</span>
                    </label>
                    <input type="email" name="reporter_email" placeholder="your@email.com" required value="<?= htmlspecialchars($_POST['reporter_email'] ?? '') ?>">
                    <div class="form-hint">
                        <i class="fa-solid fa-lock"></i>
                        We'll use this to contact you about your report
                    </div>
                </div>

                <div class="form-group">
                    <label>Campaign URL (if applicable)</label>
                    <input type="url" name="campaign_url" placeholder="https://crowdspark.com/campaign/..." value="<?= htmlspecialchars($_POST['campaign_url'] ?? '') ?>">
                    <div class="form-hint">
                        <i class="fa-solid fa-link"></i>
                        Copy and paste the URL of the campaign in question
                    </div>
                </div>

                <div class="form-group">
                    <label>
                        Detailed Description <span class="required">*</span>
                    </label>
                    <textarea name="description" id="description" placeholder="Please provide detailed information about the issue..." required minlength="20"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    <div class="form-hint">
                        <i class="fa-solid fa-pen"></i>
                        Include as much detail as possible to help us investigate (minimum 20 characters)
                    </div>
                    <div class="char-counter">
                        <span id="charCount">0</span> / 20 characters minimum
                    </div>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fa-solid fa-paper-plane"></i> Submit Report
                </button>

            </form>
            <?php endif; ?>

        </div>
    </div>

    <script>
        // Character counter
        const textarea = document.getElementById('description');
        const charCount = document.getElementById('charCount');
        
        if (textarea && charCount) {
            textarea.addEventListener('input', function() {
                charCount.textContent = this.value.length;
                charCount.parentElement.style.color = this.value.length >= 20 ? '#10b981' : 'var(--text-tertiary)';
            });
            
            // Initialize count
            charCount.textContent = textarea.value.length;
        }

        // Form submission loading state
        document.getElementById('reportForm')?.addEventListener('submit', function() {
            const btn = this.querySelector('.submit-btn');
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Submitting...';
            btn.disabled = true;
        });
    </script>

    <?php require_once __DIR__ . "/../includes/footer.php"; ?>

</body>
</html>