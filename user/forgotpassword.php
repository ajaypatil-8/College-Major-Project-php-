<?php
session_start();
require_once __DIR__."/../config/db.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__."/../vendor/phpmailer/src/PHPMailer.php";
require __DIR__."/../vendor/phpmailer/src/SMTP.php";
require __DIR__."/../vendor/phpmailer/src/Exception.php";

$msg="";
$step=1;

/* STEP 1 — SEND OTP */
if(isset($_POST['send_otp'])){

    $email = trim($_POST['email']);

    $stmt=$pdo->prepare("SELECT id FROM users WHERE email=?");
    $stmt->execute([$email]);

    if($stmt->rowCount()==0){
        $msg="Email not found";
    }else{

        $otp = rand(100000,999999);

        $_SESSION['reset_email']=$email;
        $_SESSION['reset_otp']=$otp;

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
    $mail->Subject = "Forgot Password OTP";
    $mail->Body    = "<h2>Your OTP: $otp</h2>";

    $mail->send();

    $msg="OTP sent to your email";
    $step=2;

}catch(Exception $e){
    $msg="Mail not sent. Check SMTP";
}

    }
}

/* STEP 2 — VERIFY OTP */
if(isset($_POST['verify_otp'])){

    if($_POST['otp']==$_SESSION['reset_otp']){
        $step=3;
    }else{
        $msg="Invalid OTP";
        $step=2;
    }
}

/* STEP 3 — CHANGE PASSWORD */
if(isset($_POST['change_pass'])){

    $pass=$_POST['password'];
    $cpass=$_POST['confirm'];

    if($pass!=$cpass){
        $msg="Passwords do not match";
        $step=3;
    }else{

        $hash=password_hash($pass,PASSWORD_DEFAULT);

        $stmt=$pdo->prepare("UPDATE users SET password=? WHERE email=?");
        $stmt->execute([$hash,$_SESSION['reset_email']]);

        unset($_SESSION['reset_email']);
        unset($_SESSION['reset_otp']);

        echo "<script>alert('Password changed. Login now');window.location='login.php'</script>";
        exit;
    }
}
?>

<?php require_once __DIR__."/../includes/header.php"; ?>

<style>
.auth-box{
max-width:420px;
margin:120px auto;
background:#fff;
padding:35px;
border-radius:16px;
box-shadow:0 20px 50px rgba(0,0,0,0.08);
}
.auth-box h2{text-align:center;margin-bottom:20px;}
.auth-box input{
width:100%;
padding:12px;
margin-bottom:12px;
border:1px solid #ddd;
border-radius:8px;
}
.auth-box button{
width:100%;
padding:12px;
background:#f59e0b;
border:none;
border-radius:10px;
font-weight:700;
cursor:pointer;
}
.msg{text-align:center;color:red;margin-bottom:10px;}
</style>

<div class="auth-box">
<h2>Forgot Password</h2>

<?php if($msg): ?>
<p class="msg"><?= $msg ?></p>
<?php endif; ?>

<!-- STEP 1 EMAIL -->
<?php if($step==1): ?>
<form method="POST">
<input type="email" name="email" placeholder="Enter email" required>
<button name="send_otp">Send OTP</button>
</form>
<?php endif; ?>

<!-- STEP 2 OTP -->
<?php if($step==2): ?>
<form method="POST">
<input type="text" name="otp" placeholder="Enter OTP" required>
<button name="verify_otp">Verify OTP</button>
</form>
<?php endif; ?>

<!-- STEP 3 NEW PASS -->
<?php if($step==3): ?>
<form method="POST">
<input type="password" name="password" placeholder="New password" required>
<input type="password" name="confirm" placeholder="Confirm password" required>
<button name="change_pass">Change Password</button>
</form>
<?php endif; ?>

</div>

<?php require_once __DIR__."/../includes/footer.php"; ?>
