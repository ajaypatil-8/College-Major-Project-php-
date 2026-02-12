<?php
session_start();
require_once __DIR__."/../config/db.php";

$msg = "";

if($_SERVER["REQUEST_METHOD"]=="POST"){

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if(empty($email) || empty($password)){
        $msg="Please fill all fields";
    }else{

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email=?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if($user && password_verify($password,$user['password'])){

            /* STORE SESSION */
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['role']    = $user['role'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['profile_image'] = $user['profile_image']; 

            /* ADMIN REDIRECT */
            if($user['role']=="admin"){
                header("Location: /CroudSpark-X/admin/admin-dashboard.php");
                exit;
            }

            header("Location: /CroudSpark-X/public/index.php");
            exit;

        }else{
            $msg="Invalid email or password";
        }
    }
}
?>


<style>
.auth-page{
min-height:85vh;
display:flex;
align-items:center;
justify-content:center;
background:linear-gradient(135deg,#f8fafc,#eef2ff);
padding:40px 20px;
}

.auth-card{
width:100%;
max-width:450px;
background:#fff;
padding:40px 36px;
border-radius:22px;
box-shadow:0 25px 60px rgba(0,0,0,0.08);
}

.auth-card h2{
margin:0;
font-size:26px;
font-weight:700;
text-align:center;
}

.auth-sub{
font-size:14px;
color:#64748b;
margin:8px 0 26px;
text-align:center;
}

.auth-form{
display:flex;
flex-direction:column;
gap:18px;
}

.form-group{
display:flex;
flex-direction:column;
gap:6px;
}

.form-group label{
font-size:13px;
font-weight:600;
}

.form-group input{
padding:13px 14px;
border-radius:12px;
border:1px solid #e2e8f0;
font-size:14px;
background:#f8fafc;
}

.form-group input:focus{
outline:none;
border-color:#f59e0b;
background:#fff;
box-shadow:0 0 0 3px rgba(245,158,11,.15);
}

.pass-wrap{position:relative;}
.pass-wrap i{
position:absolute;
right:14px;
top:40px;
cursor:pointer;
color:#64748b;
}

.forgot{
text-align:right;
margin-top:-8px;
}
.forgot a{
font-size:13px;
color:#f59e0b;
text-decoration:none;
font-weight:600;
}

.btn-auth{
margin-top:6px;
padding:14px;
border:none;
border-radius:14px;
background:#f59e0b;
color:#000;
font-weight:700;
font-size:15px;
cursor:pointer;
}
.btn-auth:hover{background:#ffa726;}

.alert{
background:#fee2e2;
color:#991b1b;
padding:12px;
border-radius:12px;
font-size:14px;
text-align:center;
}

.auth-footer{
text-align:center;
margin-top:18px;
font-size:14px;
color:#64748b;
}
.auth-footer a{
color:#f59e0b;
font-weight:600;
text-decoration:none;
}
</style>

<div class="auth-page">
<div class="auth-card">

<h2>Welcome back</h2>
<p class="auth-sub">Login to continue to CrowdSpark</p>

<?php if($msg): ?>
<div class="alert"><?= $msg ?></div>
<?php endif; ?>

<form method="POST" class="auth-form">

<div class="form-group">
<label>Email address</label>
<input type="email" name="email" required placeholder="Enter your email">
</div>

<div class="form-group pass-wrap">
<label>Password</label>
<input type="password" name="password" id="pass" required placeholder="Enter password">
<i class="fa fa-eye" onclick="togglePass()"></i>
</div>

<div class="forgot">
<a href="forgotpassword.php">Forgot password?</a>
</div>

<button type="submit" class="btn-auth">Login</button>

</form>

<div class="auth-footer">
New to CrowdSpark?
<a href="register.php">Create account</a>
</div>

</div>
</div>

<script>
function togglePass(){
const input=document.getElementById("pass");
input.type = input.type === "password" ? "text" : "password";
}
</script>

<?php require_once __DIR__."/../includes/footer.php"; ?>
