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

    /* ===== SEND MAIL (same as forgot password) ===== */
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
    $mail->Body    = "<h2>Your OTP: $otp</h2>";

    $mail->send();

    $msg="OTP sent to your email";
    $step=2;

}catch(Exception $e){
    $msg="Mail not sent. Check SMTP";
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
        },1500)
        </script>";

        $step=3;

    }else{
        $msg="Invalid OTP";
        $step=2;
    }
}
?>



<style>
.creator-page{
min-height:85vh;
display:flex;
align-items:center;
justify-content:center;
background:linear-gradient(135deg,#f8fafc,#eef2ff);
padding:40px 20px;
}

.creator-card{
width:100%;
max-width:450px;
background:#fff;
padding:40px 34px;
border-radius:20px;
box-shadow:0 25px 60px rgba(0,0,0,0.08);
text-align:center;
}

.creator-card h2{
margin:0;
font-size:26px;
font-weight:700;
}

.creator-sub{
font-size:14px;
color:#64748b;
margin-bottom:25px;
}

/* email box */
.email-box{
background:#f1f5f9;
padding:12px;
border-radius:10px;
font-weight:600;
margin-bottom:18px;
}

/* input */
input{
width:100%;
padding:13px;
border-radius:12px;
border:1px solid #e2e8f0;
margin-bottom:14px;
font-size:14px;
}

/* button */
.btn{
width:100%;
padding:13px;
border:none;
border-radius:12px;
background:#f59e0b;
font-weight:700;
cursor:pointer;
margin-top:6px;
font-size:15px;
}

.btn:hover{background:#ffa726;}

.msg{margin-top:12px;color:#dc2626;}
.success{margin-top:12px;color:#16a34a;}
</style>

<div class="creator-page">
<div class="creator-card">

<h2>Become Creator 🚀</h2>
<p class="creator-sub">Verify your email to start campaigns</p>

<div class="email-box">
<?= htmlspecialchars($email) ?>
</div>

<?php if($msg): ?><div class="msg"><?= $msg ?></div><?php endif; ?>
<?php if($success): ?><div class="success"><?= $success ?></div><?php endif; ?>

<!-- STEP 1 SEND OTP -->
<?php if($step==1): ?>
<form method="POST">
<button name="send_otp" class="btn">Send OTP</button>
</form>
<?php endif; ?>

<!-- STEP 2 VERIFY -->
<?php if($step==2): ?>
<form method="POST">
<input type="text" name="otp" placeholder="Enter OTP" required>
<button name="verify_otp" class="btn">Verify & Become Creator</button>
</form>
<?php endif; ?>

</div>
</div>

<?php require_once __DIR__."/../includes/footer.php"; ?>
