<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/config/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/header.php";

// Handle form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error_message = "Please fill in all fields";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$name, $email, $subject, $message]);

            $success_message = "Message sent successfully! We'll get back to you soon.";
            $_POST = array();
        } catch (PDOException $e) {
            $error_message = "Database error. Try again.";
        }
    }
}
?>


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    
    <style>

                    :root {
                --bg-primary: #ffffff;
                --bg-secondary: #f8fafc;
                --bg-card: rgba(255, 255, 255, 0.9);
                --bg-card-hover: rgba(255, 255, 255, 0.95);
                --text-primary: #0f172a;
                --text-secondary: #475569;
                --text-tertiary: #64748b;
                --border-color: rgba(15, 23, 42, 0.1);
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
                --orb-opacity: 0.25;
            }

            /* Keep these orbs VISIBLE on both themes */
            [data-theme="dark"] {
                --orb-1: linear-gradient(45deg, #f59e0b, #fb923c);
                --orb-2: linear-gradient(45deg, #3b82f6, #8b5cf6);
                --orb-3: linear-gradient(45deg, #ec4899, #f59e0b);
            }

            [data-theme="light"] {
                --orb-1: linear-gradient(45deg, #fbbf24, #fb923c);
                --orb-2: linear-gradient(45deg, #60a5fa, #a78bfa);
                --orb-3: linear-gradient(45deg, #f472b6, #fb923c);
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

        /* Animated Background */
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
            background: linear-gradient(45deg, #f59e0b, #fb923c);
            top: -10%;
            left: -10%;
            animation-delay: 0s;
        }

        .orb-2 {
            width: 400px;
            height: 400px;
            background: linear-gradient(45deg, #3b82f6, #8b5cf6);
            bottom: -10%;
            right: -10%;
            animation-delay: 5s;
        }

        .orb-3 {
            width: 350px;
            height: 350px;
            background: linear-gradient(45deg, #ec4899, #f59e0b);
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

        /* Container */
        .contact-container {
            position: relative;
            z-index: 1;
            max-width: 1400px;
            margin: 0 auto;
            padding: 120px 40px 80px;
        }

        /* Header Section */
        .contact-header {
            text-align: center;
            margin-bottom: 80px;
            animation: fadeInUp 0.8s ease;
        }

        .contact-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(3rem, 8vw, 6rem);
            font-weight: 900;
            background: linear-gradient(135deg, var(--text-primary), #f59e0b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
            line-height: 1.1;
        }

        .contact-header p {
            font-size: 1.25rem;
            color: var(--text-secondary);
            max-width: 600px;
            margin: 0 auto;
        }

        /* Main Grid */
        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 60px;
            margin-bottom: 100px;
        }

        /* Contact Info Cards */
        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        .info-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 40px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            animation: fadeInLeft 0.8s ease;
            animation-fill-mode: both;
        }

        .info-card:nth-child(1) { animation-delay: 0.1s; }
        .info-card:nth-child(2) { animation-delay: 0.2s; }
        .info-card:nth-child(3) { animation-delay: 0.3s; }

        .info-card::before {
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

        .info-card:hover::before {
            transform: scaleX(1);
        }

        .info-card:hover {
            background: rgba(30, 30, 40, 0.9);
            border-color: rgba(245, 158, 11, 0.4);
            transform: translateY(-5px);
        }

        .info-card-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #f59e0b, #fb923c);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 20px;
            transition: all 0.4s ease;
        }

        .info-card:hover .info-card-icon {
            transform: rotate(10deg) scale(1.1);
        }

        .info-card h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .info-card p {
            color: var(--text-secondary);
            line-height: 1.8;
            margin-bottom: 8px;
            font-size: 1rem;
        }

        .info-card a {
            color: #f59e0b;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .info-card a:hover {
            color: #fb923c;
            transform: translateX(5px);
        }

        /* Message Form */
        .message-form-container {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 32px;
            padding: 50px;
            animation: fadeInRight 0.8s ease 0.2s both;
            position: relative;
            overflow: hidden;
        }

        .message-form-container::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.1) 0%, transparent 70%);
            pointer-events: none;
        }

        .form-header {
            margin-bottom: 40px;
            position: relative;
            z-index: 1;
        }

        .form-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            margin-bottom: 10px;
           background: linear-gradient(135deg, var(--text-primary), #f59e0b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .form-header p {
             color: var(--text-secondary);
            font-size: 1.05rem;
        }

        /* Form Elements */
        .form-group {
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
        }

        .form-label {
            display: block;
            margin-bottom: 12px;
            font-weight: 600;
           color: var(--text-primary);
            font-size: 0.95rem;
            letter-spacing: 0.5px;
        }

        .form-input,
        .form-textarea {
            width: 100%;
            padding: 18px 24px;
            background: var(--bg-secondary);
            border: 2px solid var(--border-color);
            border-radius: 16px;
            color: var(--text-primary);
            font-size: 1rem;
            font-family: 'DM Sans', sans-serif;
            transition: all 0.3s ease;
            outline: none;
        }

        .form-input:focus,
        .form-textarea:focus {
            background: var(--bg-secondary);
            border-color: #f59e0b;
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.15);
        }

        .form-textarea {
            resize: vertical;
            min-height: 180px;
            line-height: 1.6;
        }

        .form-input::placeholder,
        .form-textarea::placeholder {
            color: #94a3b8;
        }

        /* Submit Button */
        .submit-btn {
            width: 100%;
            padding: 20px;
            background: linear-gradient(135deg, #f59e0b, #fb923c);
            border: none;
            border-radius: 16px;
            color: #fff;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .submit-btn::before {
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

        .submit-btn:hover::before {
            width: 400px;
            height: 400px;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 40px rgba(245, 158, 11, 0.4);
        }

        .submit-btn:active {
            transform: translateY(-1px);
        }

        .submit-btn span {
            position: relative;
            z-index: 1;
        }

        /* Alert Messages */
        .alert {
            padding: 20px 24px;
            border-radius: 16px;
            margin-bottom: 30px;
            animation: slideDown 0.5s ease;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            border: 2px solid rgba(34, 197, 94, 0.3);
            color: #22c55e;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 2px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }

        /* Quick Contact Section */
        .quick-contact {
            text-align: center;
            padding: 80px 40px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 32px;
            animation: fadeInUp 0.8s ease 0.4s both;
        }

        .quick-contact h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            margin-bottom: 20px;
             background: linear-gradient(135deg, var(--text-primary), #f59e0b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .quick-contact p {
            color: var(--text-secondary);
            font-size: 1.1rem;
            margin-bottom: 40px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .quick-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .quick-link {
            padding: 16px 32px;
            background: var(--bg-secondary);
            border: 2px solid var(--border-color);
            color: var(--text-primary);
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .quick-link:hover {
            background: linear-gradient(135deg, #f59e0b, #fb923c);
            border-color: transparent;
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(245, 158, 11, 0.3);
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
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

        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 968px) {
            .contact-grid {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .contact-header h1 {
                font-size: 3rem;
            }

            .message-form-container {
                padding: 35px;
            }

            .quick-contact {
                padding: 60px 30px;
            }
        }

        @media (max-width: 480px) {
            .contact-container {
                padding: 100px 20px 60px;
            }

            .info-card {
                padding: 30px;
            }

            .message-form-container {
                padding: 25px;
            }

            .form-header h2 {
                font-size: 2rem;
            }

            .quick-contact h2 {
                font-size: 2rem;
            }
        }
    </style>



   
    

    <!-- Background Animation -->
    <div class="bg-animation">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <div class="contact-container">
        
        <!-- Header -->
        <div class="contact-header">
            <h1>Let's Connect</h1>
            <p>Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
        </div>

        <!-- Main Grid -->
        <div class="contact-grid">
            
            <!-- Contact Info -->
            <div class="contact-info">
                
                <div class="info-card">
                    <div class="info-card-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3>Email Us</h3>
                    <p>Our team is here to help</p>
                    <a href="mailto:crowdspark.business@gmail.com">crowdspark.business@gmail.com</a><br>
                </div>

                <div class="info-card">
                    <div class="info-card-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <h3>Call Us</h3>
                    <p>Mon-Fri from 9am to 6pm</p>
                    <a href="tel:+1234567890">+91 8544###2210</a><br>
                </div>

                <div class="info-card">
                    <div class="info-card-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h3>Visit Us</h3>
                    <p>Come say hello at our office</p>
                    <p>255 main steet<br>Palus , Maharashtra 416310<br>India</p>
                </div>

            </div>

            <!-- Message Form -->
            <div class="message-form-container">
                <div class="form-header">
                    <h2>Send us a Message</h2>
                    <p>Fill out the form below and our team will get back to you within 24 hours</p>
                </div>

                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span><?= htmlspecialchars($success_message) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?= htmlspecialchars($error_message) ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label" for="name">Full Name</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            class="form-input" 
                            placeholder="John Doe"
                            value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-input" 
                            placeholder="john@example.com"
                            value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="subject">Subject</label>
                        <input 
                            type="text" 
                            id="subject" 
                            name="subject" 
                            class="form-input" 
                            placeholder="How can we help you?"
                            value="<?= isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : '' ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="message">Message</label>
                        <textarea 
                            id="message" 
                            name="message" 
                            class="form-textarea" 
                            placeholder="Tell us more about your inquiry..."
                            required
                        ><?= isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '' ?></textarea>
                    </div>

                    <button type="submit" name="send_message" class="submit-btn">
                        <span>Send Message</span>
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>

        </div>

        <!-- Quick Contact -->
        <div class="quick-contact">
            <h2>Need Quick Help?</h2>
            <p>For immediate assistance, reach out through our social channels or explore our help center</p>
            <div class="quick-links">
                <a href="#" class="quick-link">
                    <i class="fab fa-twitter"></i>
                    Twitter
                </a>
                <a href="#" class="quick-link">
                    <i class="fab fa-facebook"></i>
                    Facebook
                </a>
                <a href="#" class="quick-link">
                    <i class="fab fa-linkedin"></i>
                    LinkedIn
                </a>
                <a href="#" class="quick-link">
                    <i class="fas fa-question-circle"></i>
                    Help Center
                </a>
            </div>
        </div>

    </div>

    <?php include 'includes/footer.php'; ?>


