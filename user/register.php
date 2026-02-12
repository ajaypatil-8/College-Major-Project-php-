<?php
session_start();
require_once __DIR__."/../config/db.php";
require_once __DIR__."/../uploads/upload.php";

$msg="";
$success="";

if($_SERVER["REQUEST_METHOD"]=="POST"){

    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $city  = trim($_POST['city']);
    $bio   = trim($_POST['bio']);
    $pass  = $_POST['password'];
    $cpass = $_POST['confirm_password'];

    if(strlen($name) < 3){
        $msg="Name must be at least 3 characters";
    }
    elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $msg="Enter valid email address";
    }
    elseif(strlen($pass) < 6){
        $msg="Password must be minimum 6 characters";
    }
    elseif($pass !== $cpass){
        $msg="Passwords do not match";
    }
    else{

        $check=$pdo->prepare("SELECT id FROM users WHERE email=?");
        $check->execute([$email]);

        if($check->rowCount()>0){
            $msg="Email already registered";
        }else{

            $profileImg=null;

            if(!empty($_FILES['profile']['name'])){
                $profileImg = uploadToCloudinary($_FILES['profile']['tmp_name'],"profiles");
            }

            $hash=password_hash($pass,PASSWORD_DEFAULT);

            $stmt=$pdo->prepare("
            INSERT INTO users(name,email,password,role,phone,city,bio,profile_image)
            VALUES(?,?,?,'user',?,?,?,?)
            ");

            $stmt->execute([$name,$email,$hash,$phone,$city,$bio,$profileImg]);

            $success="Account created successfully 🚀";

            echo "<script>
            setTimeout(()=>{window.location='login.php'},1500)
            </script>";
        }
    }
}
?>

<style>
.auth-page{
min-height:92vh;
display:flex;
align-items:center;
justify-content:center;
background:linear-gradient(135deg,#f8fafc,#eef2ff);
padding:40px 20px;
}

.auth-card{
width:100%;
max-width:520px;
background:#fff;
padding:44px 40px;
border-radius:24px;
box-shadow:0 30px 70px rgba(0,0,0,0.08);
}

/* title */
.auth-card h2{
text-align:center;
margin-bottom:6px;
font-size:28px;
font-weight:800;
}

.auth-sub{
text-align:center;
font-size:14px;
color:#64748b;
margin-bottom:28px;
}

/* image upload */
.profile-upload{
display:flex;
flex-direction:column;
align-items:center;
margin-bottom:22px;
}

.profile-preview{
width:90px;
height:90px;
border-radius:50%;
background:#f1f5f9;
display:flex;
align-items:center;
justify-content:center;
font-size:28px;
color:#94a3b8;
overflow:hidden;
margin-bottom:10px;
border:3px solid #f1f5f9;
}

.profile-preview img{
width:100%;
height:100%;
object-fit:cover;
}

.upload-btn{
font-size:13px;
background:#f8fafc;
border:1px dashed #cbd5e1;
padding:8px 14px;
border-radius:999px;
cursor:pointer;
}

.upload-btn:hover{
background:#eef2ff;
}

/* form */
form{
display:flex;
flex-direction:column;
gap:14px;
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

.form-group input,
.form-group textarea{
width:100%;
padding:13px 14px;
border-radius:12px;
border:1px solid #e2e8f0;
font-size:14px;
background:#f8fafc;
transition:.2s;
}

.form-group textarea{height:70px;resize:none;}

.form-group input:focus,
.form-group textarea:focus{
outline:none;
border-color:#f59e0b;
background:#fff;
box-shadow:0 0 0 3px rgba(245,158,11,.15);
}

/* button */
.btn-auth{
margin-top:8px;
width:100%;
padding:15px;
border:none;
border-radius:14px;
background:#f59e0b;
color:#000;
font-weight:800;
font-size:15px;
cursor:pointer;
transition:.25s;
}

.btn-auth:hover{
background:#ffa726;
transform:translateY(-1px);
box-shadow:0 10px 25px rgba(245,158,11,.35);
}

/* alerts */
.alert{
padding:12px;
border-radius:12px;
font-size:14px;
text-align:center;
}

.alert-error{background:#fee2e2;color:#991b1b;}
.alert-success{background:#dcfce7;color:#166534;}

.auth-footer{
text-align:center;
margin-top:16px;
font-size:13px;
color:#64748b;
}

.auth-footer a{
color:#f59e0b;
font-weight:700;
text-decoration:none;
}
</style>

<div class="auth-page">
<div class="auth-card">

<h2>Create account</h2>
<p class="auth-sub">Join CrowdSpark and start funding 🚀</p>

<?php if($msg): ?>

<div class="alert alert-error"><?= $msg ?></div>
<?php endif; ?>

<?php if($success): ?>

<div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

<!-- IMAGE -->

<div class="profile-upload">
<div class="profile-preview" id="preview">👤</div>

<label class="upload-btn">
Choose Profile Image
<input type="file" name="profile" accept="image/*" hidden onchange="previewImage(event)">
</label>
</div>

<div class="form-group">
<label>Full Name</label>
<input type="text" name="name" required>
</div>

<div class="form-group">
<label>Email</label>
<input type="email" name="email" required>
</div>

<div class="form-group">
<label>Phone</label>
<input type="text" name="phone">
</div>

<div class="form-group">
<label>City</label>
<input type="text" name="city">
</div>

<div class="form-group">
<label>Bio</label>
<textarea name="bio" placeholder="Tell something about you..."></textarea>
</div>

<div class="form-group">
<label>Password</label>
<input type="password" name="password" required>
</div>

<div class="form-group">
<label>Confirm Password</label>
<input type="password" name="confirm_password" required>
</div>

<button class="btn-auth">Create Account</button>

</form>

<div class="auth-footer">
Already have account?
<a href="login.php">Login</a>
</div>

</div>
</div>

<script>
function previewImage(e){
const file=e.target.files[0];
if(file){
const reader=new FileReader();
reader.onload=function(ev){
document.getElementById("preview").innerHTML=
`<img src="${ev.target.result}">`;
}
reader.readAsDataURL(file);
}
}
</script>

<?php require_once __DIR__."/../includes/footer.php"; ?>
