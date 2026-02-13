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
        <div style='background: linear-gradient(135deg, #10b981 0%, #34d399 100%); padding: 40px; border-radius: 20px; text-align: center;'>
            <h1 style='color: white; margin: 0 0 20px 0; font-size: 32px;'>🚀 Creator Verification</h1>
            <div style='background: white; padding: 30px; border-radius: 15px; margin: 20px 0;'>
                <p style='color: #64748b; margin: 0 0 20px 0; font-size: 16px;'>Your verification code is:</p>
                <h2 style='color: #10b981; font-size: 48px; margin: 0; letter-spacing: 8px; font-weight: 900;'>$otp</h2>
            </div>
            <p style='color: rgba(255,255,255,0.9); margin: 20px 0 0 0; font-size: 14px;'>This code will expire in 10 minutes</p>
        </div>
    </div>
    ";

    $mail->send();

    $success="OTP sent to your email";
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

/* Animated Background - Emerald/Green theme */
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
    background: linear-gradient(45deg, #10b981, #34d399);
    top: -10%;
    left: -10%;
    animation-delay: 0s;
}

.orb-2 {
    width: 400px;
    height: 400px;
    background: linear-gradient(45deg, #059669, #10b981);
    bottom: -10%;
    right: -10%;
    animation-delay: 5s;
}

.orb-3 {
    width: 350px;
    height: 350px;
    background: linear-gradient(45deg, #047857, #059669);
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

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
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
        box-shadow: 0 15px 40px rgba(16, 185, 129, 0.3);
    }
    50% { 
        transform: scale(1.05);
        box-shadow: 0 20px 50px rgba(16, 185, 129, 0.4);
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

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* ===== PAGE CONTAINER ===== */
.creator-page {
    position: relative;
    z-index: 1;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 120px 20px 80px;
}

.creator-container {
    width: 100%;
    max-width: 520px;
    animation: fadeInUp 0.8s ease;
}

.creator-card {
    background: rgba(20, 20, 30, 0.85);
    backdrop-filter: blur(20px);
    padding: 50px 45px;
    border-radius: 32px;
    box-shadow: 0 20px 60px rgba(16, 185, 129, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.15);
    transition: all 0.4s ease;
    position: relative;
    overflow: hidden;
    text-align: center;
}

.creator-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #10b981, #34d399);
}

.creator-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 30px 80px rgba(16, 185, 129, 0.15);
}

/* ===== ICON SECTION ===== */
.creator-icon {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, #10b981, #34d399);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 24px;
    font-size: 50px;
    box-shadow: 0 15px 40px rgba(16, 185, 129, 0.3);
    animation: pulse 2s infinite;
}

.creator-card h2 {
    font-family: 'Playfair Display', serif;
    font-size: 2.5rem;
    font-weight: 900;
    margin-bottom: 12px;
    background: linear-gradient(135deg, #fff, #10b981);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.creator-sub {
    font-size: 1rem;
    color: #cbd5e1;
    margin-bottom: 32px;
    font-weight: 500;
}

/* ===== PROGRESS STEPS ===== */
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
    background: #10b981;
    width: 40px;
    border-radius: 6px;
}

.step-dot.completed {
    background: #10b981;
}

/* ===== EMAIL BOX ===== */
.email-box {
    background: rgba(16, 185, 129, 0.1);
    padding: 18px 24px;
    border-radius: 16px;
    font-weight: 700;
    color: #6ee7b7;
    margin-bottom: 28px;
    border: 2px solid rgba(16, 185, 129, 0.3);
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

/* ===== ALERT MESSAGES ===== */
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

/* ===== OTP INPUT ===== */
.otp-input-container {
    margin-bottom: 24px;
}

.otp-input {
    width: 100%;
    padding: 18px 20px;
    border-radius: 16px;
    border: 2px solid rgba(255, 255, 255, 0.15);
    font-size: 24px;
    font-weight: 800;
    text-align: center;
    letter-spacing: 12px;
    background: rgba(10, 10, 20, 0.6);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    color: #fff;
    font-family: 'DM Sans', sans-serif;
}

.otp-input::placeholder {
    font-size: 16px;
    letter-spacing: normal;
    color: #94a3b8;
    font-weight: 400;
}

.otp-input:focus {
    outline: none;
    border-color: #10b981;
    background: rgba(20, 20, 30, 0.7);
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
    transform: translateY(-2px);
}

/* ===== BUTTONS ===== */
.btn {
    width: 100%;
    padding: 18px;
    border: none;
    border-radius: 50px;
    background: linear-gradient(135deg, #10b981, #34d399);
    color: #fff;
    font-weight: 800;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
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
    box-shadow: 0 15px 40px rgba(16, 185, 129, 0.4);
}

.btn:active {
    transform: translateY(-1px);
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.btn-secondary {
    background: rgba(30, 30, 40, 0.6);
    border: 2px solid rgba(255, 255, 255, 0.15);
}

.btn-secondary:hover {
    background: rgba(16, 185, 129, 0.2);
    border-color: #10b981;
}

/* ===== LOADING STATE ===== */
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

/* ===== INFO BOX ===== */
.info-box {
    background: rgba(30, 30, 40, 0.6);
    border: 2px dashed rgba(16, 185, 129, 0.3);
    border-radius: 16px;
    padding: 16px;
    margin-top: 24px;
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

/* ===== RESPONSIVE ===== */
@media (max-width: 640px) {
    .creator-page {
        padding: 100px 20px 60px;
    }
    
    .creator-card {
        padding: 40px 30px;
    }
    
    .creator-card h2 {
        font-size: 2rem;
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

/* ===== FADE IN ===== */
.fade-in {
    animation: fadeIn 0.5s ease-out;
}

/* ===== SUCCESS CONFETTI ===== */
.confetti {
    position: fixed;
    width: 10px;
    height: 10px;
    background: #10b981;
    position: absolute;
    animation: confetti-fall 3s linear;
}

@keyframes confetti-fall {
    to {
        transform: translateY(100vh) rotate(360deg);
        opacity: 0;
    }
}
</style>

<!-- Background Animation -->
<div class="bg-animation">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
</div>

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
                        autofocus
                    >
                </div>
                <button name="verify_otp" class="btn" type="submit">
                    <i class="fa fa-check-circle"></i> Verify & Become Creator
                </button>
            </form>
            
            <div class="info-box">
                <strong>💡 Didn't receive the code?</strong>
                Check your spam folder or click the button below to resend.
            </div>
            
            <!-- Resend OTP -->
            <form method="POST" style="margin-top: 16px;">
                <button name="send_otp" class="btn btn-secondary" type="submit">
                    <i class="fa fa-redo"></i> Resend Code
                </button>
            </form>
            <?php endif; ?>

            <!-- STEP 3: SUCCESS -->
            <?php if($step == 3): ?>
            <div class="fade-in" style="text-align: center;">
                <div style="font-size: 80px; margin-bottom: 20px;">🎉</div>
                <h3 style="color: #10b981; font-size: 24px; margin-bottom: 12px;">Congratulations!</h3>
                <p style="color: #94a3b8; font-size: 16px;">You are now a verified creator. Redirecting to create your first campaign...</p>
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
    const colors = ['#10b981', '#34d399', '#6ee7b7', '#a7f3d0', '#d1fae5'];
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