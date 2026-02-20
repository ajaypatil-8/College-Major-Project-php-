<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

require_once $_SERVER['DOCUMENT_ROOT']."/config/db.php";
require_once $_SERVER['DOCUMENT_ROOT']."/config/env.php";

/* Load PHPMailer manually */
require $_SERVER['DOCUMENT_ROOT']."/vendor/phpmailer/src/PHPMailer.php";
require $_SERVER['DOCUMENT_ROOT']."/vendor/phpmailer/src/SMTP.php";
require $_SERVER['DOCUMENT_ROOT']."/vendor/phpmailer/src/Exception.php";

$msg="";
$success="";
$step=1;

/* STEP 1 — SEND OTP */
if(isset($_POST['send_otp'])){

    $email = trim($_POST['email']);
    $stmt=$pdo->prepare("SELECT id FROM users WHERE email=?");
    $stmt->execute([$email]);

    if($stmt->rowCount()==0){
        $msg="Email not found in our system";
    }else{

        $otp = rand(100000,999999);
        $_SESSION['reset_email']=$email;
        $_SESSION['reset_otp']=$otp;
        $_SESSION['otp_time']=time();

        /* IMPORTANT: full namespace */
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try{
            $mail->isSMTP();
            $mail->Host       = $_ENV['MAIL_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['MAIL_USER'];
            $mail->Password   = $_ENV['MAIL_PASS'];
            $mail->SMTPSecure = 'tls';
            $mail->Port       = $_ENV['MAIL_PORT'];

            $mail->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME']);
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = "Password Reset OTP - CrowdSpark";
            $mail->Body    = "
                <h2>Your OTP is: $otp</h2>
                <p>This code expires in 10 minutes.</p>
            ";

            $mail->send();
            $success="OTP sent successfully! Check your email";
            $step=2;

        }catch(Exception $e){
    $msg="Failed to send email. Please try again later";
}
    }
}

/* STEP 2 — VERIFY OTP */
if(isset($_POST['verify_otp'])){
    $entered = trim($_POST['otp']);
    
    if(!isset($_SESSION['reset_otp'])){
        $msg="Session expired. Please start again";
        $step=1;
    }
    elseif(isset($_SESSION['otp_time']) && (time() - $_SESSION['otp_time']) > 600){
        $msg="OTP expired. Please request a new one";
        unset($_SESSION['reset_otp']);
        unset($_SESSION['otp_time']);
        $step=1;
    }
    elseif($entered == $_SESSION['reset_otp']){
        $success="OTP verified! Now set your new password";
        $step=3;
    }else{
        $msg="Invalid OTP. Please try again";
        $step=2;
    }
}

/* STEP 3 — CHANGE PASSWORD */
if(isset($_POST['change_pass'])){
    $pass = trim($_POST['password']);
    $cpass = trim($_POST['confirm']);

    if(strlen($pass) < 6){
        $msg="Password must be at least 6 characters";
        $step=3;
    }
    elseif($pass !== $cpass){
        $msg="Passwords do not match";
        $step=3;
    }else{
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $stmt=$pdo->prepare("UPDATE users SET password=? WHERE email=?");
        $stmt->execute([$hash, $_SESSION['reset_email']]);

        unset($_SESSION['reset_email']);
        unset($_SESSION['reset_otp']);
        unset($_SESSION['otp_time']);

        $success="Password changed successfully! Redirecting to login...";
        echo "<script>setTimeout(()=>{ window.location='login.php' }, 2000);</script>";
        $step=4;
    }
}
?>

<html lang="en">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - CrowdSpark</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@400;500;600;700;800;900&display=swap');

/* ===== THEME VARIABLES ===== */
:root {
    --bg-primary: #ffffff;
    --bg-secondary: #f8fafc;
    --bg-card: rgba(255, 255, 255, 0.9);
    --text-primary: #0f172a;
    --text-secondary: #475569;
    --text-tertiary: #64748b;
    --border-color: rgba(15, 23, 42, 0.1);
    --orb-opacity: 0.25;
}

[data-theme="dark"] {
    --bg-primary: #0f0f0f;
    --bg-secondary: #1a1a1a;
    --bg-card: rgba(20, 20, 30, 0.85);
    --text-primary: #ffffff;
    --text-secondary: #cbd5e1;
    --text-tertiary: #94a3b8;
    --border-color: rgba(255, 255, 255, 0.15);
    --orb-opacity: 0.25;
    --orb-1: linear-gradient(45deg, #f43f5e, #fb7185);
    --orb-2: linear-gradient(45deg, #e11d48, #f43f5e);
    --orb-3: linear-gradient(45deg, #be123c, #e11d48);
}

[data-theme="light"] {
    --orb-1: linear-gradient(45deg, #fda4af, #fb7185);
    --orb-2: linear-gradient(45deg, #fb7185, #f43f5e);
    --orb-3: linear-gradient(45deg, #f43f5e, #fda4af);
}

* { margin: 0; padding: 0; box-sizing: border-box; }

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

.orb-1 { width: 500px; height: 500px; background: var(--orb-1); top: -10%; left: -10%; }
.orb-2 { width: 400px; height: 400px; background: var(--orb-2); bottom: -10%; right: -10%; animation-delay: 5s; }
.orb-3 { width: 350px; height: 350px; background: var(--orb-3); top: 50%; left: 50%; animation-delay: 10s; }

@keyframes float {
    0%, 100% { transform: translate(0, 0) scale(1); }
    25% { transform: translate(50px, 50px) scale(1.1); }
    50% { transform: translate(-30px, 80px) scale(0.9); }
    75% { transform: translate(40px, -40px) scale(1.05); }
}

@keyframes fadeInUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-10px); } 75% { transform: translateX(10px); } }
@keyframes shimmer { 0% { background-position: -1000px 0; } 100% { background-position: 1000px 0; } }
@keyframes pulse {
    0%, 100% { transform: scale(1); box-shadow: 0 15px 40px rgba(244, 63, 94, 0.3); }
    50% { transform: scale(1.05); box-shadow: 0 20px 50px rgba(244, 63, 94, 0.4); }
}
@keyframes slideIn { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }
@keyframes spin { to { transform: rotate(360deg); } }

/* Theme Toggle Button */
.theme-toggle {
    position: fixed;
    top: 20px;
    right: 20px;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: var(--bg-card);
    border: 2px solid var(--border-color);
    backdrop-filter: blur(20px);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    transition: all 0.3s ease;
    z-index: 1000;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.theme-toggle:hover {
    transform: scale(1.1) rotate(10deg);
    box-shadow: 0 8px 20px rgba(244, 63, 94, 0.2);
}

.auth-page {
    position: relative;
    z-index: 1;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 120px 20px 80px;
}

.auth-container {
    width: 100%;
    max-width: 520px;
    animation: fadeInUp 0.8s ease;
}

.auth-card {
    background: var(--bg-card);
    backdrop-filter: blur(20px);
    padding: 50px 45px;
    border-radius: 32px;
    box-shadow: 0 20px 60px rgba(244, 63, 94, 0.1);
    border: 1px solid var(--border-color);
    transition: all 0.4s ease;
    position: relative;
    overflow: hidden;
}

.auth-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #f43f5e, #fb7185);
}

.auth-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 30px 80px rgba(244, 63, 94, 0.15);
}

.auth-icon {
    width: 90px;
    height: 90px;
    background: linear-gradient(135deg, #f43f5e, #fb7185);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 24px;
    font-size: 45px;
    box-shadow: 0 15px 40px rgba(244, 63, 94, 0.3);
    animation: pulse 2s infinite;
}

.auth-card h2 {
    font-family: 'Playfair Display', serif;
    font-size: 2.5rem;
    font-weight: 900;
    margin-bottom: 12px;
    text-align: center;
    background: linear-gradient(135deg, var(--text-primary), #f43f5e);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.auth-sub {
    font-size: 1rem;
    color: var(--text-secondary);
    text-align: center;
    margin-bottom: 32px;
    font-weight: 500;
    line-height: 1.5;
}

.progress-steps {
    display: flex;
    justify-content: center;
    gap: 12px;
    margin-bottom: 32px;
}

.step-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: var(--border-color);
    transition: all 0.3s ease;
}

.step-dot.active {
    background: #f43f5e;
    width: 40px;
    border-radius: 6px;
}

.step-dot.completed {
    background: #10b981;
}

.alert {
    padding: 16px 20px;
    border-radius: 16px;
    font-size: 14px;
    font-weight: 600;
    text-align: center;
    margin-bottom: 24px;
    animation: shake 0.5s ease;
    position: relative;
    overflow: hidden;
}

.alert::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    animation: shimmer 2s infinite;
}

.alert-error { background: rgba(239, 68, 68, 0.2); color: #ef4444; border-left: 4px solid #ef4444; }
[data-theme="light"] .alert-error { color: #dc2626; }

.alert-success { background: rgba(16, 185, 129, 0.2); color: #10b981; border-left: 4px solid #10b981; }
[data-theme="light"] .alert-success { color: #059669; }

.form-group {
    margin-bottom: 20px;
    position: relative;
    animation: slideIn 0.6s ease-out;
    animation-fill-mode: both;
}

.form-group:nth-child(1) { animation-delay: 0.1s; }
.form-group:nth-child(2) { animation-delay: 0.2s; }

.form-group label {
    display: block;
    font-size: 12px;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: 10px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.form-group input {
    width: 100%;
    padding: 16px 20px;
    border-radius: 16px;
    border: 2px solid var(--border-color);
    font-size: 15px;
    background: var(--bg-secondary);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-weight: 500;
    color: var(--text-primary);
    font-family: 'DM Sans', sans-serif;
}

.form-group input::placeholder {
    color: var(--text-tertiary);
    font-weight: 400;
}

.form-group input:focus {
    outline: none;
    border-color: #f43f5e;
    box-shadow: 0 0 0 4px rgba(244, 63, 94, 0.15);
    transform: translateY(-2px);
}

.otp-input {
    text-align: center;
    letter-spacing: 12px;
    font-size: 24px !important;
    font-weight: 800 !important;
}

.otp-input::placeholder {
    letter-spacing: normal !important;
    font-size: 16px !important;
}

.pass-wrap {
    position: relative;
}

.pass-wrap .toggle-password {
    position: absolute;
    right: 18px;
    top: 50%;
    margin-top: 6px;
    cursor: pointer;
    color: var(--text-tertiary);
    font-size: 18px;
    transition: all 0.3s ease;
    padding: 8px;
}

.pass-wrap .toggle-password:hover {
    color: #f43f5e;
    transform: scale(1.15);
}

.btn {
    width: 100%;
    padding: 18px;
    border: none;
    border-radius: 50px;
    background: linear-gradient(135deg, #f43f5e, #fb7185);
    color: #fff;
    font-weight: 800;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 10px 30px rgba(244, 63, 94, 0.3);
    position: relative;
    overflow: hidden;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-top: 8px;
}

.btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s ease;
}

.btn:hover::before { left: 100%; }

.btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 40px rgba(244, 63, 94, 0.4);
}

.btn:active { transform: translateY(-1px); }

.btn-secondary {
    background: var(--bg-secondary);
    border: 2px solid var(--border-color);
    color: var(--text-secondary);
}

.btn-secondary:hover {
    background: rgba(244, 63, 94, 0.2);
    border-color: #f43f5e;
    color: #f43f5e;
}

.btn.loading {
    pointer-events: none;
    opacity: 0.8;
}

.btn.loading::after {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    top: 50%;
    left: 50%;
    margin-left: -10px;
    margin-top: -10px;
    border: 3px solid rgba(255,255,255,0.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 0.8s linear infinite;
}

.info-box {
    background: var(--bg-secondary);
    border: 2px dashed rgba(244, 63, 94, 0.3);
    border-radius: 16px;
    padding: 16px;
    margin-top: 20px;
    font-size: 13px;
    color: var(--text-secondary);
    line-height: 1.6;
}

.info-box strong {
    color: var(--text-primary);
    display: block;
    margin-bottom: 6px;
    font-size: 14px;
}

.auth-footer {
    text-align: center;
    margin-top: 28px;
    font-size: 15px;
    color: var(--text-tertiary);
    font-weight: 500;
}

.auth-footer a {
    color: #f43f5e;
    font-weight: 800;
    text-decoration: none;
    transition: all 0.3s ease;
    position: relative;
}

.auth-footer a::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 2px;
    background: #f43f5e;
    transition: width 0.3s ease;
}

.auth-footer a:hover::after {
    width: 100%;
}

.password-strength {
    height: 4px;
    background: var(--border-color);
    border-radius: 2px;
    margin-top: 8px;
    overflow: hidden;
    position: relative;
}

.password-strength-bar {
    height: 100%;
    width: 0;
    transition: all 0.3s ease;
    border-radius: 2px;
}

.strength-weak { width: 33%; background: #ef4444; }
.strength-medium { width: 66%; background: #f59e0b; }
.strength-strong { width: 100%; background: #10b981; }

@media (max-width: 640px) {
    .auth-page { padding: 100px 20px 60px; }
    .auth-card { padding: 40px 30px; }
    .auth-card h2 { font-size: 2rem; }
    .auth-icon { width: 75px; height: 75px; font-size: 38px; }
}

.fade-in {
    animation: fadeIn 0.5s ease-out;
}
    </style>



<!-- Theme Toggle Button -->
<button class="theme-toggle" onclick="toggleTheme()" id="themeToggle">
    <i class="fas fa-moon"></i>
</button>

<div class="bg-animation">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
</div>

<div class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-icon">🔐</div>
            <h2>Reset Password</h2>
            <p class="auth-sub">
                <?php if($step == 1): ?>
                Enter your email to receive a verification code
                <?php elseif($step == 2): ?>
                Enter the 6-digit code sent to your email
                <?php elseif($step == 3): ?>
                Create your new password
                <?php else: ?>
                Password updated successfully!
                <?php endif; ?>
            </p>
            
            <div class="progress-steps">
                <div class="step-dot <?= $step >= 1 ? 'active' : '' ?> <?= $step > 1 ? 'completed' : '' ?>"></div>
                <div class="step-dot <?= $step >= 2 ? 'active' : '' ?> <?= $step > 2 ? 'completed' : '' ?>"></div>
                <div class="step-dot <?= $step >= 3 ? 'active' : '' ?> <?= $step > 3 ? 'completed' : '' ?>"></div>
            </div>

            <?php if($msg): ?>
            <div class="alert alert-error fade-in">
                <i class="fa fa-exclamation-circle"></i> <?= $msg ?>
            </div>
            <?php endif; ?>

            <?php if($success): ?>
            <div class="alert alert-success fade-in">
                <i class="fa fa-check-circle"></i> <?= $success ?>
            </div>
            <?php endif; ?>

            <?php if($step == 1): ?>
            <form method="POST" class="fade-in">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="you@example.com" required autocomplete="email" autofocus>
                </div>
                <button name="send_otp" class="btn" type="submit">
                    <i class="fa fa-paper-plane"></i> Send Verification Code
                </button>
            </form>
            <div class="info-box">
                <strong>🔒 Your account is safe</strong>
                We'll send a verification code to your email. This code expires in 10 minutes.
            </div>
            <?php endif; ?>

            <?php if($step == 2): ?>
            <form method="POST" class="fade-in" id="otpForm">
                <div class="form-group">
                    <label>Verification Code</label>
                    <input type="text" name="otp" class="otp-input" placeholder="000000" required maxlength="6" pattern="[0-9]{6}" autocomplete="off" id="otpInput" autofocus>
                </div>
                <button name="verify_otp" class="btn" type="submit">
                    <i class="fa fa-check-circle"></i> Verify Code
                </button>
            </form>
            <div class="info-box">
                <strong>💡 Didn't receive the code?</strong>
                Check your spam folder or request a new code below.
            </div>
            <form method="POST" style="margin-top: 16px;">
                <input type="hidden" name="email" value="<?= htmlspecialchars($_SESSION['reset_email'] ?? '') ?>">
                <button name="send_otp" class="btn btn-secondary" type="submit">
                    <i class="fa fa-redo"></i> Resend Code
                </button>
            </form>
            <?php endif; ?>

            <?php if($step == 3): ?>
            <form method="POST" class="fade-in">
                <div class="form-group pass-wrap">
                    <label>New Password</label>
                    <input type="password" name="password" id="pass1" placeholder="Minimum 6 characters" required autocomplete="new-password" oninput="checkPasswordStrength(this.value)" autofocus>
                    <i class="fa fa-eye toggle-password" onclick="togglePass('pass1', this)"></i>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="strengthBar"></div>
                    </div>
                </div>
                
                <div class="form-group pass-wrap">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm" id="pass2" placeholder="Re-enter password" required autocomplete="new-password">
                    <i class="fa fa-eye toggle-password" onclick="togglePass('pass2', this)"></i>
                </div>
                
                <button name="change_pass" class="btn" type="submit">
                    <i class="fa fa-lock"></i> Reset Password
                </button>
            </form>
            <div class="info-box">
                <strong>⚡ Password Requirements</strong>
                Choose a strong password with at least 6 characters. Mix letters, numbers, and symbols for better security.
            </div>
            <?php endif; ?>

            <?php if($step == 4): ?>
            <div class="fade-in" style="text-align: center;">
                <div style="font-size: 80px; margin-bottom: 20px;">✅</div>
                <h3 style="color: #10b981; font-size: 24px; margin-bottom: 12px;">All Set!</h3>
                <p style="color: var(--text-tertiary); font-size: 16px;">Your password has been reset successfully. Redirecting to login...</p>
            </div>
            <?php endif; ?>

            <?php if($step < 4): ?>
            <div class="auth-footer">
                Remember your password?
                <a href="login.php">Back to Login</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Theme Toggle
function toggleTheme() {
    const html = document.documentElement;
    const currentTheme = html.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    
    const icon = document.querySelector('#themeToggle i');
    icon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
}

// Load saved theme
document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
    const icon = document.querySelector('#themeToggle i');
    icon.className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';

    const otpInput = document.getElementById('otpInput');
    if (otpInput) {
        otpInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }
});

function togglePass(inputId, icon) {
    const input = document.getElementById(inputId);
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        input.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}

function checkPasswordStrength(password) {
    const strengthBar = document.getElementById("strengthBar");
    const length = password.length;
    strengthBar.className = "password-strength-bar";
    if (length === 0) {
        strengthBar.style.width = "0";
    } else if (length < 6) {
        strengthBar.classList.add("strength-weak");
    } else if (length < 10) {
        strengthBar.classList.add("strength-medium");
    } else {
        strengthBar.classList.add("strength-strong");
    }
}

document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function() {
        const btn = this.querySelector('.btn');
        if (btn && !btn.classList.contains('loading')) {
            btn.classList.add('loading');
            btn.innerHTML = '';
        }
    });
});

const pass1 = document.getElementById('pass1');
const pass2 = document.getElementById('pass2');

if (pass2) {
    pass2.addEventListener('input', function() {
        if (this.value && pass1.value && this.value !== pass1.value) {
            this.style.borderColor = '#ef4444';
        } else {
            this.style.borderColor = '';
        }
    });
}
</script>
