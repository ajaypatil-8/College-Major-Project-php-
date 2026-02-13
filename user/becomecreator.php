<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
require_once __DIR__."/../config/db.php";
require_once __DIR__."/../config/env.php";

/* LOGIN REQUIRED */
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* GET USER EMAIL FROM DB */
$stmt=$pdo->prepare("SELECT email,role FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user=$stmt->fetch();

$email = $user['email'];

$msg="";
$success="";
$step=1;

/* ================= SEND OTP ================= */
if(isset($_POST['send_otp'])){

    $otp = rand(100000,999999);

    $_SESSION['creator_otp']=$otp;

    $stmt=$pdo->prepare("UPDATE users SET creator_otp=? WHERE id=?");
    $stmt->execute([$otp,$user_id]);

    /* ===== SEND MAIL ===== */
    require __DIR__."/../vendor/phpmailer/src/PHPMailer.php";
    require __DIR__."/../vendor/phpmailer/src/SMTP.php";
    require __DIR__."/../vendor/phpmailer/src/Exception.php";

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
    $mail->Subject = "Creator Verification OTP";
    $mail->Body    = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 40px 20px;'>
        <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px; border-radius: 20px; text-align: center;'>
            <h1 style='color: white; margin: 0 0 20px 0; font-size: 32px;'>🚀 Creator Verification</h1>
            <div style='background: white; padding: 30px; border-radius: 15px; margin: 20px 0;'>
                <p style='color: #64748b; margin: 0 0 20px 0; font-size: 16px;'>Your verification code is:</p>
                <h2 style='color: #f59e0b; font-size: 48px; margin: 0; letter-spacing: 8px; font-weight: 900;'>$otp</h2>
            </div>
            <p style='color: rgba(255,255,255,0.9); margin: 20px 0 0 0; font-size: 14px;'>This code will expire in 10 minutes</p>
        </div>
    </div>
    ";

    $mail->send();

    $msg="OTP sent to your email";
    $step=2;

}catch(Exception $e){
    $msg="Mail not sent. Check SMTP configuration";
}

}

/* ================= VERIFY OTP ================= */
if(isset($_POST['verify_otp'])){

    $entered = trim($_POST['otp']);

    $stmt=$pdo->prepare("SELECT creator_otp FROM users WHERE id=?");
    $stmt->execute([$user_id]);
    $dbOtp = $stmt->fetchColumn();

    if($entered == $dbOtp){

        /* UPDATE ROLE */
        $stmt=$pdo->prepare("UPDATE users SET role='creator', creator_otp=NULL WHERE id=?");
        $stmt->execute([$user_id]);

        $_SESSION['role']="creator";

        $success="You are now a creator! Redirecting...";

        echo "<script>
        setTimeout(()=>{
        window.location='/CroudSpark-X/creator/create-campaign.php'
        },2000)
        </script>";

        $step=3;

    }else{
        $msg="Invalid OTP. Please try again";
        $step=2;
    }
}
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Inter', sans-serif;
}

:root {
    --primary: #f59e0b;
    --primary-hover: #d97706;
    --primary-light: #fef3c7;
    --purple: #667eea;
    --purple-dark: #764ba2;
    --gray-50: #f8fafc;
    --gray-100: #f1f5f9;
    --gray-200: #e2e8f0;
    --gray-300: #cbd5e1;
    --gray-600: #475569;
    --gray-700: #334155;
    --gray-900: #0f172a;
    --error: #ef4444;
    --error-light: #fee2e2;
    --success: #10b981;
    --success-light: #d1fae5;
}

.creator-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--purple) 0%, var(--purple-dark) 100%);
    padding: 60px 20px;
    position: relative;
    overflow: hidden;
}

/* Animated Background */
.creator-page::before {
    content: '';
    position: absolute;
    width: 500px;
    height: 500px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    top: -150px;
    right: -100px;
    animation: float 20s infinite ease-in-out;
}

.creator-page::after {
    content: '';
    position: absolute;
    width: 400px;
    height: 400px;
    background: rgba(255, 255, 255, 0.08);
    border-radius: 50%;
    bottom: -100px;
    left: -100px;
    animation: float 15s infinite ease-in-out reverse;
}

@keyframes float {
    0%, 100% { transform: translateY(0) scale(1); }
    50% { transform: translateY(-30px) scale(1.05); }
}

.creator-container {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 520px;
    animation: slideUp 0.6s ease-out;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.creator-card {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(20px);
    padding: 50px 45px;
    border-radius: 32px;
    box-shadow: 0 40px 100px rgba(0, 0, 0, 0.25),
                0 0 0 1px rgba(255, 255, 255, 0.5) inset;
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.creator-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 50px 120px rgba(0, 0, 0, 0.3),
                0 0 0 1px rgba(255, 255, 255, 0.5) inset;
}

/* Icon Section */
.creator-icon {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, var(--primary), var(--primary-hover));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 24px;
    font-size: 50px;
    box-shadow: 0 15px 40px rgba(245, 158, 11, 0.3);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { 
        transform: scale(1);
        box-shadow: 0 15px 40px rgba(245, 158, 11, 0.3);
    }
    50% { 
        transform: scale(1.05);
        box-shadow: 0 20px 50px rgba(245, 158, 11, 0.4);
    }
}

.creator-card h2 {
    font-size: 32px;
    font-weight: 900;
    color: var(--gray-900);
    margin-bottom: 12px;
    letter-spacing: -0.5px;
}

.creator-sub {
    font-size: 16px;
    color: var(--gray-600);
    margin-bottom: 32px;
    font-weight: 500;
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
    background: var(--gray-200);
    transition: all 0.3s ease;
}

.step-dot.active {
    background: var(--primary);
    width: 40px;
    border-radius: 6px;
}

.step-dot.completed {
    background: var(--success);
}

/* Email Display */
.email-box {
    background: linear-gradient(135deg, var(--gray-100), var(--gray-50));
    padding: 18px 24px;
    border-radius: 16px;
    font-weight: 700;
    color: var(--gray-900);
    margin-bottom: 28px;
    border: 2px solid var(--gray-200);
    font-size: 15px;
    letter-spacing: 0.3px;
    position: relative;
    overflow: hidden;
}

.email-box::before {
    content: '📧';
    position: absolute;
    left: 15px;
    font-size: 20px;
}

.email-box {
    padding-left: 50px;
}

/* Alert Messages */
.alert {
    padding: 16px 20px;
    border-radius: 16px;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 24px;
    animation: shake 0.5s ease;
    position: relative;
    overflow: hidden;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-10px); }
    75% { transform: translateX(10px); }
}

.alert::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    to { left: 100%; }
}

.alert-error {
    background: linear-gradient(135deg, var(--error-light), #fecaca);
    color: #991b1b;
    border-left: 4px solid var(--error);
}

.alert-success {
    background: linear-gradient(135deg, var(--success-light), #a7f3d0);
    color: #065f46;
    border-left: 4px solid var(--success);
}

/* OTP Input */
.otp-input-container {
    margin-bottom: 24px;
}

.otp-input {
    width: 100%;
    padding: 18px 20px;
    border-radius: 16px;
    border: 2px solid var(--gray-200);
    font-size: 24px;
    font-weight: 800;
    text-align: center;
    letter-spacing: 12px;
    background: var(--gray-50);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    color: var(--gray-900);
}

.otp-input::placeholder {
    font-size: 16px;
    letter-spacing: normal;
    color: var(--gray-400);
}

.otp-input:focus {
    outline: none;
    border-color: var(--primary);
    background: #fff;
    box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1),
                0 10px 30px rgba(0, 0, 0, 0.05);
    transform: translateY(-2px);
}

/* Buttons */
.btn {
    width: 100%;
    padding: 18px;
    border: none;
    border-radius: 16px;
    background: linear-gradient(135deg, var(--primary), var(--primary-hover));
    color: #fff;
    font-weight: 800;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 10px 30px rgba(245, 158, 11, 0.3);
    position: relative;
    overflow: hidden;
    text-transform: uppercase;
    letter-spacing: 1px;
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
    box-shadow: 0 15px 40px rgba(245, 158, 11, 0.4);
}

.btn:active {
    transform: translateY(-1px);
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
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
    background: var(--gray-50);
    border: 2px dashed var(--gray-300);
    border-radius: 16px;
    padding: 16px;
    margin-top: 24px;
    font-size: 13px;
    color: var(--gray-600);
    line-height: 1.6;
}

.info-box strong {
    color: var(--gray-900);
    display: block;
    margin-bottom: 6px;
    font-size: 14px;
}

/* Success Confetti Animation */
.confetti {
    position: fixed;
    width: 10px;
    height: 10px;
    background: var(--primary);
    position: absolute;
    animation: confetti-fall 3s linear;
}

@keyframes confetti-fall {
    to {
        transform: translateY(100vh) rotate(360deg);
        opacity: 0;
    }
}

/* Responsive */
@media (max-width: 640px) {
    .creator-card {
        padding: 40px 30px;
    }
    
    .creator-card h2 {
        font-size: 28px;
    }
    
    .creator-icon {
        width: 80px;
        height: 80px;
        font-size: 40px;
    }
    
    .otp-input {
        font-size: 20px;
        letter-spacing: 8px;
    }
}

/* Fade In Animation */
.fade-in {
    animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
</style>

<div class="creator-page">
    <div class="creator-container">
        <div class="creator-card">
            
            <div class="creator-icon">🚀</div>
            
            <h2>Become a Creator</h2>
            <p class="creator-sub">Verify your email to unlock creator privileges</p>
            
            <!-- Progress Steps -->
            <div class="progress-steps">
                <div class="step-dot <?= $step >= 1 ? 'active' : '' ?> <?= $step > 1 ? 'completed' : '' ?>"></div>
                <div class="step-dot <?= $step >= 2 ? 'active' : '' ?> <?= $step > 2 ? 'completed' : '' ?>"></div>
                <div class="step-dot <?= $step >= 3 ? 'active' : '' ?>"></div>
            </div>
            
            <!-- Email Display -->
            <div class="email-box">
                <?= htmlspecialchars($email) ?>
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

            <!-- STEP 1: SEND OTP -->
            <?php if($step == 1): ?>
            <form method="POST" class="fade-in">
                <button name="send_otp" class="btn" type="submit">
                    <i class="fa fa-paper-plane"></i> Send Verification Code
                </button>
            </form>
            
            <div class="info-box">
                <strong>📌 What happens next?</strong>
                We'll send a 6-digit verification code to your email. Please check your inbox and spam folder.
            </div>
            <?php endif; ?>

            <!-- STEP 2: VERIFY OTP -->
            <?php if($step == 2): ?>
            <form method="POST" class="fade-in" id="otpForm">
                <div class="otp-input-container">
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
                    >
                </div>
                <button name="verify_otp" class="btn" type="submit">
                    <i class="fa fa-check-circle"></i> Verify & Become Creator
                </button>
            </form>
            
            <div class="info-box">
                <strong>💡 Didn't receive the code?</strong>
                Check your spam folder or click the button above to resend.
            </div>
            
            <!-- Resend OTP -->
            <form method="POST" style="margin-top: 16px;">
                <button name="send_otp" class="btn" style="background: var(--gray-600); box-shadow: 0 10px 30px rgba(0,0,0,0.15);" type="submit">
                    <i class="fa fa-redo"></i> Resend Code
                </button>
            </form>
            <?php endif; ?>

            <!-- STEP 3: SUCCESS -->
            <?php if($step == 3): ?>
            <div class="fade-in" style="text-align: center;">
                <div style="font-size: 80px; margin-bottom: 20px;">🎉</div>
                <h3 style="color: var(--success); font-size: 24px; margin-bottom: 12px;">Congratulations!</h3>
                <p style="color: var(--gray-600); font-size: 16px;">You are now a verified creator. Redirecting to create your first campaign...</p>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
// Auto-focus OTP input
document.addEventListener('DOMContentLoaded', function() {
    const otpInput = document.getElementById('otpInput');
    if (otpInput) {
        otpInput.focus();
        
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

// Create confetti on success
<?php if($step == 3): ?>
function createConfetti() {
    const colors = ['#f59e0b', '#10b981', '#3b82f6', '#ec4899', '#8b5cf6'];
    for (let i = 0; i < 50; i++) {
        setTimeout(() => {
            const confetti = document.createElement('div');
            confetti.className = 'confetti';
            confetti.style.left = Math.random() * 100 + '%';
            confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
            confetti.style.animationDelay = Math.random() * 2 + 's';
            document.querySelector('.creator-card').appendChild(confetti);
            
            setTimeout(() => confetti.remove(), 3000);
        }, i * 30);
    }
}
createConfetti();
<?php endif; ?>
</script>

<?php require_once __DIR__."/../includes/footer.php"; ?>