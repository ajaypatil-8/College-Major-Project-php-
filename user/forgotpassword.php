<?php
session_start();
require_once __DIR__."/../config/db.php";
require_once __DIR__."/../config/env.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__."/../vendor/phpmailer/src/PHPMailer.php";
require __DIR__."/../vendor/phpmailer/src/SMTP.php";
require __DIR__."/../vendor/phpmailer/src/Exception.php";

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

        /* SEND EMAIL */
        $mail = new PHPMailer(true);

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
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 40px 20px;'>
        <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px; border-radius: 20px; text-align: center;'>
            <h1 style='color: white; margin: 0 0 20px 0; font-size: 32px;'>🔐 Password Reset</h1>
            <div style='background: white; padding: 30px; border-radius: 15px; margin: 20px 0;'>
                <p style='color: #64748b; margin: 0 0 20px 0; font-size: 16px;'>Your verification code is:</p>
                <h2 style='color: #f43f5e; font-size: 48px; margin: 0; letter-spacing: 8px; font-weight: 900;'>$otp</h2>
            </div>
            <p style='color: rgba(255,255,255,0.9); margin: 20px 0 0 0; font-size: 14px;'>This code will expire in 10 minutes</p>
            <p style='color: rgba(255,255,255,0.8); margin: 10px 0 0 0; font-size: 13px;'>If you didn't request this, please ignore this email</p>
        </div>
    </div>
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
    
    // Check if OTP session exists
    if(!isset($_SESSION['reset_otp'])){
        $msg="Session expired. Please start again";
        $step=1;
    }
    // Check OTP expiry (10 minutes)
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

        // Clear session
        unset($_SESSION['reset_email']);
        unset($_SESSION['reset_otp']);
        unset($_SESSION['otp_time']);

        $success="Password changed successfully! Redirecting to login...";

        echo "<script>
        setTimeout(()=>{
            window.location='login.php'
        }, 2000);
        </script>";

        $step=4;
    }
}
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@400;500;600;700;800;900&display=swap');

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'DM Sans', sans-serif;
    background: #0f0f0f;
    color: #fff;
    overflow-x: hidden;
    position: relative;
}

/* Animated Background - Rose/Pink theme */
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
    background: linear-gradient(45deg, #f43f5e, #fb7185);
    top: -10%;
    left: -10%;
    animation-delay: 0s;
}

.orb-2 {
    width: 400px;
    height: 400px;
    background: linear-gradient(45deg, #e11d48, #f43f5e);
    bottom: -10%;
    right: -10%;
    animation-delay: 5s;
}

.orb-3 {
    width: 350px;
    height: 350px;
    background: linear-gradient(45deg, #be123c, #e11d48);
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

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-10px); }
    75% { transform: translateX(10px); }
}

@keyframes shimmer {
    0% { background-position: -1000px 0; }
    100% { background-position: 1000px 0; }
}

@keyframes pulse {
    0%, 100% { 
        transform: scale(1);
        box-shadow: 0 15px 40px rgba(244, 63, 94, 0.3);
    }
    50% { 
        transform: scale(1.05);
        box-shadow: 0 20px 50px rgba(244, 63, 94, 0.4);
    }
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* ===== PAGE CONTAINER ===== */
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
    background: rgba(20, 20, 30, 0.85);
    backdrop-filter: blur(20px);
    padding: 50px 45px;
    border-radius: 32px;
    box-shadow: 0 20px 60px rgba(244, 63, 94, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.15);
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

/* ===== ICON SECTION ===== */
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
    background: linear-gradient(135deg, #fff, #f43f5e);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.auth-sub {
    font-size: 1rem;
    color: #cbd5e1;
    text-align: center;
    margin-bottom: 32px;
    font-weight: 500;
    line-height: 1.5;
}

/* Progress Steps */
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
    background: rgba(255, 255, 255, 0.2);
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

/* Alert Messages */
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

.alert-error {
    background: rgba(239, 68, 68, 0.2);
    color: #fca5a5;
    border-left: 4px solid #ef4444;
}

.alert-success {
    background: rgba(16, 185, 129, 0.2);
    color: #6ee7b7;
    border-left: 4px solid #10b981;
}

/* Form Elements */
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
    color: #fff;
    margin-bottom: 10px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.form-group input {
    width: 100%;
    padding: 16px 20px;
    border-radius: 16px;
    border: 2px solid rgba(255, 255, 255, 0.15);
    font-size: 15px;
    background: rgba(10, 10, 20, 0.6);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-weight: 500;
    color: #fff;
    font-family: 'DM Sans', sans-serif;
}

.form-group input::placeholder {
    color: #94a3b8;
    font-weight: 400;
}

.form-group input:focus {
    outline: none;
    border-color: #f43f5e;
    background: rgba(20, 20, 30, 0.7);
    box-shadow: 0 0 0 4px rgba(244, 63, 94, 0.15);
    transform: translateY(-2px);
}

/* OTP Input */
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

/* Password Toggle */
.pass-wrap {
    position: relative;
}

.pass-wrap .toggle-password {
    position: absolute;
    right: 18px;
    top: 50%;
    margin-top: 6px;
    cursor: pointer;
    color: #94a3b8;
    font-size: 18px;
    transition: all 0.3s ease;
    padding: 8px;
}

.pass-wrap .toggle-password:hover {
    color: #f43f5e;
    transform: scale(1.15);
}

/* Buttons */
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

.btn:hover::before {
    left: 100%;
}

.btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 40px rgba(244, 63, 94, 0.4);
}

.btn:active {
    transform: translateY(-1px);
}

.btn-secondary {
    background: rgba(30, 30, 40, 0.6);
    border: 2px solid rgba(255, 255, 255, 0.15);
}

.btn-secondary:hover {
    background: rgba(244, 63, 94, 0.2);
    border-color: #f43f5e;
}

/* Loading State */
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

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Info Box */
.info-box {
    background: rgba(30, 30, 40, 0.6);
    border: 2px dashed rgba(244, 63, 94, 0.3);
    border-radius: 16px;
    padding: 16px;
    margin-top: 20px;
    font-size: 13px;
    color: #cbd5e1;
    line-height: 1.6;
}

.info-box strong {
    color: #fff;
    display: block;
    margin-bottom: 6px;
    font-size: 14px;
}

/* Footer Link */
.auth-footer {
    text-align: center;
    margin-top: 28px;
    font-size: 15px;
    color: #94a3b8;
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

/* Password Strength */
.password-strength {
    height: 4px;
    background: rgba(255, 255, 255, 0.1);
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

/* Responsive */
@media (max-width: 640px) {
    .auth-page {
        padding: 100px 20px 60px;
    }
    
    .auth-card {
        padding: 40px 30px;
    }
    
    .auth-card h2 {
        font-size: 2rem;
    }
    
    .auth-icon {
        width: 75px;
        height: 75px;
        font-size: 38px;
    }
}

.fade-in {
    animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
</style>

<!-- Background Animation -->
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
            
            <!-- Progress Steps -->
            <div class="progress-steps">
                <div class="step-dot <?= $step >= 1 ? 'active' : '' ?> <?= $step > 1 ? 'completed' : '' ?>"></div>
                <div class="step-dot <?= $step >= 2 ? 'active' : '' ?> <?= $step > 2 ? 'completed' : '' ?>"></div>
                <div class="step-dot <?= $step >= 3 ? 'active' : '' ?> <?= $step > 3 ? 'completed' : '' ?>"></div>
            </div>

            <!-- Alert Messages -->
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

            <!-- STEP 1: EMAIL -->
            <?php if($step == 1): ?>
            <form method="POST" class="fade-in">
                <div class="form-group">
                    <label>Email Address</label>
                    <input 
                        type="email" 
                        name="email" 
                        placeholder="you@example.com" 
                        required
                        autocomplete="email"
                        autofocus
                    >
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

            <!-- STEP 2: OTP VERIFICATION -->
            <?php if($step == 2): ?>
            <form method="POST" class="fade-in" id="otpForm">
                <div class="form-group">
                    <label>Verification Code</label>
                    <input 
                        type="text" 
                        name="otp" 
                        class="otp-input" 
                        placeholder="000000" 
                        required 
                        maxlength="6"
                        pattern="[0-9]{6}"
                        autocomplete="off"
                        id="otpInput"
                        autofocus
                    >
                </div>
                <button name="verify_otp" class="btn" type="submit">
                    <i class="fa fa-check-circle"></i> Verify Code
                </button>
            </form>
            
            <div class="info-box">
                <strong>💡 Didn't receive the code?</strong>
                Check your spam folder or request a new code below.
            </div>
            
            <!-- Resend OTP -->
            <form method="POST" style="margin-top: 16px;">
                <input type="hidden" name="email" value="<?= htmlspecialchars($_SESSION['reset_email'] ?? '') ?>">
                <button name="send_otp" class="btn btn-secondary" type="submit">
                    <i class="fa fa-redo"></i> Resend Code
                </button>
            </form>
            <?php endif; ?>

            <!-- STEP 3: NEW PASSWORD -->
            <?php if($step == 3): ?>
            <form method="POST" class="fade-in">
                <div class="form-group pass-wrap">
                    <label>New Password</label>
                    <input 
                        type="password" 
                        name="password" 
                        id="pass1" 
                        placeholder="Minimum 6 characters" 
                        required
                        autocomplete="new-password"
                        oninput="checkPasswordStrength(this.value)"
                        autofocus
                    >
                    <i class="fa fa-eye toggle-password" onclick="togglePass('pass1', this)"></i>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="strengthBar"></div>
                    </div>
                </div>
                
                <div class="form-group pass-wrap">
                    <label>Confirm Password</label>
                    <input 
                        type="password" 
                        name="confirm" 
                        id="pass2" 
                        placeholder="Re-enter password" 
                        required
                        autocomplete="new-password"
                    >
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

            <!-- STEP 4: SUCCESS -->
            <?php if($step == 4): ?>
            <div class="fade-in" style="text-align: center;">
                <div style="font-size: 80px; margin-bottom: 20px;">✅</div>
                <h3 style="color: #10b981; font-size: 24px; margin-bottom: 12px;">All Set!</h3>
                <p style="color: #94a3b8; font-size: 16px;">Your password has been reset successfully. Redirecting to login...</p>
            </div>
            <?php endif; ?>

            <!-- Back to Login -->
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
// Auto-focus and validate OTP input
document.addEventListener('DOMContentLoaded', function() {
    const otpInput = document.getElementById('otpInput');
    if (otpInput) {
        // Only allow numbers
        otpInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        
        // Auto-submit when 6 digits entered
        otpInput.addEventListener('input', function() {
            if (this.value.length === 6) {
                setTimeout(() => {
                    document.getElementById('otpForm').submit();
                }, 500);
            }
        });
    }
});

// Toggle Password Visibility
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

// Password Strength Checker
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

// Add loading state on form submit
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function() {
        const btn = this.querySelector('.btn');
        if (btn && !btn.classList.contains('loading')) {
            btn.classList.add('loading');
            btn.innerHTML = '';
        }
    });
});

// Validate password match in real-time
const pass1 = document.getElementById('pass1');
const pass2 = document.getElementById('pass2');

if (pass2) {
    pass2.addEventListener('input', function() {
        if (this.value && pass1.value && this.value !== pass1.value) {
            this.style.borderColor = '#ef4444';
        } else {
            this.style.borderColor = 'rgba(255, 255, 255, 0.15)';
        }
    });
}
</script>

<?php require_once __DIR__."/../includes/footer.php"; ?>