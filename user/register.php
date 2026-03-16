<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT']."/config/db.php";
require_once $_SERVER['DOCUMENT_ROOT']."/uploads/upload.php";

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

            echo "<script>setTimeout(()=>{window.location='login.php'},1500)</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - CrowdSpark</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Syne:wght@700;800;900&display=swap" rel="stylesheet">
<style>

:root {
    --bg: #0c0e16;
    --card-bg: rgba(18, 21, 36, 0.88);
    --card-border: rgba(255,255,255,0.08);
    --input-bg: rgba(255,255,255,0.05);
    --input-border: rgba(255,255,255,0.1);
    --text-1: #f0f2f8;
    --text-2: #8b93aa;
    --text-3: #555e78;
    --accent: #8b5cf6;
    --accent2: #a78bfa;
    --accent-grad: linear-gradient(135deg, #8b5cf6, #a78bfa);
    --accent-glow: rgba(139,92,246,0.25);
    --success-c: #10b981;
    --error-c: #ef4444;
    --orb-opacity: 0.18;
}

[data-theme="light"] {
    --bg: #f4f6fb;
    --card-bg: rgba(255,255,255,0.93);
    --card-border: rgba(15,23,42,0.08);
    --input-bg: rgba(15,23,42,0.03);
    --input-border: rgba(15,23,42,0.1);
    --text-1: #0d1117;
    --text-2: #5a6478;
    --text-3: #9aa3b2;
    --orb-opacity: 0.45;
}

*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: var(--bg);
    color: var(--text-1);
    min-height: 100vh;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: 40px 20px 60px;
    overflow-x: hidden;
    -webkit-font-smoothing: antialiased;
    transition: background .3s ease, color .3s ease;
    position: relative;
}

/* Orbs */
.bg-scene { position:fixed; inset:0; pointer-events:none; overflow:hidden; opacity:var(--orb-opacity); transition:opacity .3s ease; }
.orb { position:absolute; border-radius:50%; filter:blur(90px); animation:orbFloat 20s ease-in-out infinite; }
.o1 { width:520px; height:520px; background:linear-gradient(135deg,#8b5cf6,#a78bfa); top:-15%; left:-15%; }
.o2 { width:380px; height:380px; background:linear-gradient(135deg,#a78bfa,#8b5cf6); bottom:-12%; right:-12%; animation-delay:7s; }
.o3 { width:260px; height:260px; background:linear-gradient(135deg,#8b5cf688,#a78bfa88); top:40%; left:60%; animation-delay:13s; }

@keyframes orbFloat {
    0%,100% { transform:translate(0,0) scale(1); }
    33%      { transform:translate(45px,55px) scale(1.07); }
    66%      { transform:translate(-25px,20px) scale(.93); }
}

/* Theme btn */
.theme-btn {
    position:fixed; top:20px; right:20px;
    width:44px; height:44px;
    border-radius:12px;
    background:var(--card-bg);
    border:1px solid var(--card-border);
    backdrop-filter:blur(20px);
    color:var(--text-2); font-size:17px;
    cursor:pointer; display:flex; align-items:center; justify-content:center;
    transition:all .25s ease; z-index:999;
    box-shadow:0 4px 16px rgba(0,0,0,.2);
}
.theme-btn:hover { border-color:var(--accent); color:var(--accent); transform:rotate(15deg) scale(1.08); }
[data-theme="dark"] .theme-btn .fa-moon { display:block; }
[data-theme="dark"] .theme-btn .fa-sun  { display:none; }
[data-theme="light"] .theme-btn .fa-moon { display:none; }
[data-theme="light"] .theme-btn .fa-sun  { display:block; }

/* Card */
.card {
    position:relative; z-index:1;
    width:100%; max-width:560px;
    background:var(--card-bg);
    border:1px solid var(--card-border);
    border-radius:28px;
    padding:44px 42px;
    backdrop-filter:blur(24px) saturate(180%);
    -webkit-backdrop-filter:blur(24px) saturate(180%);
    box-shadow:0 24px 64px rgba(0,0,0,.35), 0 0 0 1px rgba(255,255,255,0.04);
    animation:cardIn .7s cubic-bezier(.22,1,.36,1) both;
    margin-top:20px;
}
.card::before {
    content:''; position:absolute;
    top:0; left:40px; right:40px; height:1px;
    background:linear-gradient(90deg,transparent,rgba(139,92,246,.65),transparent);
    border-radius:1px;
}
@keyframes cardIn {
    from { opacity:0; transform:translateY(32px) scale(.97); }
    to   { opacity:1; transform:translateY(0) scale(1); }
}

/* Brand */
.brand { text-align:center; margin-bottom:32px; }
.brand-icon {
    width:62px; height:62px;
    border-radius:18px;
    background:var(--accent-grad);
    display:flex; align-items:center; justify-content:center;
    font-size:28px; margin:0 auto 16px;
    box-shadow:0 8px 28px var(--accent-glow);
    animation:iconBounce 3s ease infinite;
}
@keyframes iconBounce {
    0%,100% { transform:translateY(0); }
    50%      { transform:translateY(-6px); }
}
.brand h1 {
    font-family:'Syne',sans-serif;
    font-size:1.9rem; font-weight:900; letter-spacing:-.4px;
    background:var(--accent-grad);
    -webkit-background-clip:text; -webkit-text-fill-color:transparent;
    margin-bottom:8px;
}
.brand p { font-size:14px; color:var(--text-2); font-weight:500; line-height:1.5; }

/* Alert */
.alert {
    padding:14px 18px; border-radius:12px;
    font-size:13.5px; font-weight:600;
    margin-bottom:24px;
    display:flex; align-items:center; gap:10px;
    animation:shake .45s ease;
}
.alert-error  { background:rgba(239,68,68,.12);  border:1px solid rgba(239,68,68,.25);  color:#f87171; }
.alert-success{ background:rgba(16,185,129,.12); border:1px solid rgba(16,185,129,.25); color:#34d399; }
[data-theme="light"] .alert-error  { color:#dc2626; }
[data-theme="light"] .alert-success{ color:#059669; }
@keyframes shake { 0%,100%{transform:translateX(0);} 25%{transform:translateX(-8px);} 75%{transform:translateX(8px);} }

/* Profile upload */
.avatar-section {
    display:flex; flex-direction:column; align-items:center; gap:14px;
    margin-bottom:28px;
}
.avatar-ring {
    width:100px; height:100px;
    border-radius:50%;
    background:rgba(139,92,246,.15);
    border:2.5px solid rgba(139,92,246,.3);
    display:flex; align-items:center; justify-content:center;
    font-size:40px; overflow:hidden;
    box-shadow:0 10px 32px var(--accent-glow);
    transition:all .3s ease; cursor:pointer;
}
.avatar-ring:hover { border-color:var(--accent); transform:scale(1.05); box-shadow:0 14px 40px var(--accent-glow); }
.avatar-ring img { width:100%; height:100%; object-fit:cover; }

.upload-label {
    display:inline-flex; align-items:center; gap:8px;
    font-size:12px; font-weight:700; letter-spacing:.4px; text-transform:uppercase;
    color:var(--accent);
    background:rgba(139,92,246,.1);
    border:1.5px dashed rgba(139,92,246,.35);
    padding:10px 20px; border-radius:999px;
    cursor:pointer; transition:all .25s ease;
}
.upload-label:hover { background:rgba(139,92,246,.18); border-color:var(--accent); }

/* Grid form */
.form {
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:18px;
}
.full { grid-column:1/-1; }

.field { display:flex; flex-direction:column; gap:8px; }

.field label {
    font-size:11px; font-weight:800;
    letter-spacing:.6px; text-transform:uppercase;
    color:var(--text-2);
}

/* Input wrap — eye is relative to THIS */
.input-wrap { position:relative; }

.field input,
.field textarea {
    width:100%;
    padding:13px 16px;
    background:var(--input-bg);
    border:1.5px solid var(--input-border);
    border-radius:13px;
    font-size:14px; font-weight:500;
    color:var(--text-1);
    font-family:'Plus Jakarta Sans',sans-serif;
    transition:all .25s ease;
    outline:none;
}
.field textarea { height:88px; resize:vertical; padding:13px 16px; }
.field input::placeholder,
.field textarea::placeholder { color:var(--text-3); font-weight:400; }
.field input:focus,
.field textarea:focus {
    border-color:var(--accent);
    background:rgba(139,92,246,.06);
    box-shadow:0 0 0 4px var(--accent-glow);
}

/* ===== EYE ICON FIX =====
   Eye is inside .input-wrap (NOT inside .field which also has the label).
   top: 50% + translateY(-50%) = perfectly centered on the input height. */
.eye-toggle {
    position:absolute;
    right:15px;
    top:50%;
    transform:translateY(-50%);
    color:var(--text-3);
    font-size:15px;
    cursor:pointer;
    padding:4px;
    line-height:1;
    transition:color .2s ease, transform .2s ease;
}
.eye-toggle:hover {
    color:var(--accent);
    transform:translateY(-50%) scale(1.15);
}
/* Push text left so it doesn't go under the icon */
.input-wrap input { padding-right:44px; }

/* Password strength bar */
.strength-track {
    height:3px;
    background:var(--input-border);
    border-radius:2px;
    margin-top:6px;
    overflow:hidden;
}
.strength-fill {
    height:100%; width:0;
    border-radius:2px;
    transition:width .3s ease, background .3s ease;
}

/* Submit */
.btn-submit {
    grid-column:1/-1;
    width:100%; padding:15px;
    border:none; border-radius:13px;
    background:var(--accent-grad);
    color:#fff;
    font-family:'Plus Jakarta Sans',sans-serif;
    font-size:15px; font-weight:800; letter-spacing:.3px;
    cursor:pointer;
    box-shadow:0 6px 24px var(--accent-glow);
    transition:all .25s ease;
    position:relative; overflow:hidden;
    margin-top:4px;
}
.btn-submit::after {
    content:''; position:absolute; inset:0;
    background:linear-gradient(90deg,transparent,rgba(255,255,255,.18),transparent);
    transform:translateX(-100%); transition:transform .5s ease;
}
.btn-submit:hover { transform:translateY(-2px); box-shadow:0 10px 32px var(--accent-glow); }
.btn-submit:hover::after { transform:translateX(100%); }
.btn-submit.loading { pointer-events:none; opacity:.75; }
.btn-submit.loading::before {
    content:''; position:absolute;
    top:50%; left:50%;
    width:20px; height:20px; margin:-10px 0 0 -10px;
    border:2.5px solid rgba(255,255,255,.3);
    border-top-color:#fff; border-radius:50%;
    animation:spin .7s linear infinite;
}
@keyframes spin { to { transform:rotate(360deg); } }

.card-footer {
    text-align:center; margin-top:26px;
    font-size:14px; color:var(--text-2); font-weight:500;
    grid-column:1/-1;
}
.card-footer a { font-weight:800; color:var(--accent); text-decoration:none; transition:opacity .2s ease; }
.card-footer a:hover { opacity:.8; text-decoration:underline; }

/* Crop modal */
.crop-modal {
    display:none; position:fixed; inset:0;
    background:rgba(0,0,0,.92); z-index:10000;
    align-items:center; justify-content:center;
    padding:20px;
}
.crop-modal.active { display:flex; }
.crop-box {
    background:var(--card-bg);
    border:1px solid var(--card-border);
    border-radius:22px;
    max-width:620px; width:100%;
    overflow:hidden;
    box-shadow:0 32px 80px rgba(0,0,0,.6);
    animation:cardIn .3s ease both;
}
.crop-head {
    padding:22px 26px;
    border-bottom:1px solid var(--card-border);
    display:flex; justify-content:space-between; align-items:center;
}
.crop-head h3 {
    font-family:'Syne',sans-serif; font-size:1.2rem; font-weight:800;
    background:var(--accent-grad); -webkit-background-clip:text; -webkit-text-fill-color:transparent;
}
.crop-close {
    width:34px; height:34px; border-radius:9px;
    background:rgba(239,68,68,.1); border:none;
    color:#ef4444; font-size:20px; cursor:pointer;
    display:flex; align-items:center; justify-content:center;
    transition:all .25s ease;
}
.crop-close:hover { background:rgba(239,68,68,.2); transform:rotate(90deg); }
.crop-canvas {
    padding:20px;
    background:var(--input-bg);
    display:flex; align-items:center; justify-content:center;
    max-height:420px;
}
#cropImage { max-width:100%; max-height:380px; }
.crop-tools {
    padding:16px 24px;
    display:flex; gap:10px; justify-content:center;
    background:var(--input-bg);
    border-top:1px solid var(--card-border);
    border-bottom:1px solid var(--card-border);
}
.crop-btn {
    width:44px; height:44px; border-radius:10px;
    border:1.5px solid var(--card-border);
    background:transparent;
    color:var(--text-2); font-size:16px;
    cursor:pointer; display:flex; align-items:center; justify-content:center;
    transition:all .25s ease;
}
.crop-btn:hover { background:rgba(139,92,246,.15); border-color:var(--accent); color:var(--accent); }
.crop-foot {
    padding:20px 26px;
    display:flex; gap:10px; justify-content:flex-end;
}
.btn-cancel {
    padding:11px 24px; border-radius:10px;
    background:transparent; border:1.5px solid var(--card-border);
    color:var(--text-2); font-family:'Plus Jakarta Sans',sans-serif;
    font-size:13.5px; font-weight:700; cursor:pointer;
    transition:all .25s ease;
}
.btn-cancel:hover { border-color:var(--text-2); color:var(--text-1); }
.btn-apply {
    padding:11px 24px; border-radius:10px;
    background:var(--accent-grad); border:none;
    color:#fff; font-family:'Plus Jakarta Sans',sans-serif;
    font-size:13.5px; font-weight:700; cursor:pointer;
    box-shadow:0 4px 16px var(--accent-glow);
    transition:all .25s ease;
}
.btn-apply:hover { transform:translateY(-1px); box-shadow:0 8px 24px var(--accent-glow); }

@media (max-width:600px) {
    .form { grid-template-columns:1fr; }
    .full, .field { grid-column:1/-1 !important; }
    .card { padding:36px 24px; border-radius:22px; }
    .brand h1 { font-size:1.7rem; }
    .crop-tools { flex-wrap:wrap; }
}
</style>
</head>
<body>

<div class="bg-scene">
    <div class="orb o1"></div>
    <div class="orb o2"></div>
    <div class="orb o3"></div>
</div>

<button class="theme-btn" onclick="toggleTheme()">
    <i class="fa-solid fa-moon"></i>
    <i class="fa-solid fa-sun"></i>
</button>

<div class="card">

    <div class="brand">
        <div class="brand-icon">🚀</div>
        <h1>Create Account</h1>
        <p>Join CrowdSpark and start funding amazing projects</p>
    </div>

    <?php if($msg): ?>
    <div class="alert alert-error full">
        <i class="fa-solid fa-circle-exclamation"></i><?= htmlspecialchars($msg) ?>
    </div>
    <?php endif; ?>

    <?php if($success): ?>
    <div class="alert alert-success full">
        <i class="fa-solid fa-circle-check"></i><?= htmlspecialchars($success) ?>
    </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="form" id="registerForm">

        <!-- Profile upload -->
        <div class="avatar-section full">
            <div class="avatar-ring" id="avatarPreview">👤</div>
            <label class="upload-label">
                <i class="fa-solid fa-camera"></i> Choose Profile Image
                <input type="file" name="profile" accept="image/*" hidden onchange="openCropper(event)" id="profileInput">
            </label>
        </div>

        <div class="field full">
            <label>Full Name</label>
            <div class="input-wrap">
                <input type="text" name="name" required placeholder="Enter your full name" autocomplete="name">
            </div>
        </div>

        <div class="field full">
            <label>Email Address</label>
            <div class="input-wrap">
                <input type="email" name="email" required placeholder="you@example.com" autocomplete="email">
            </div>
        </div>

        <div class="field">
            <label>Phone Number</label>
            <div class="input-wrap">
                <input type="text" name="phone" placeholder="+91 98765 43210" autocomplete="tel">
            </div>
        </div>

        <div class="field">
            <label>City</label>
            <div class="input-wrap">
                <input type="text" name="city" placeholder="Your city" autocomplete="address-level2">
            </div>
        </div>

        <div class="field full">
            <label>Bio</label>
            <div class="input-wrap">
                <textarea name="bio" placeholder="Tell us a bit about yourself..."></textarea>
            </div>
        </div>

        <!-- Password — eye icon inside .input-wrap = perfectly centered -->
        <div class="field">
            <label>Password</label>
            <div class="input-wrap">
                <input type="password" name="password" id="pass1" required placeholder="Min. 6 characters" autocomplete="new-password" oninput="checkStrength(this.value)">
                <i class="fa-solid fa-eye eye-toggle" id="eye1" onclick="togglePass('pass1','eye1')"></i>
            </div>
            <div class="strength-track"><div class="strength-fill" id="strengthFill"></div></div>
        </div>

        <div class="field">
            <label>Confirm Password</label>
            <div class="input-wrap">
                <input type="password" name="confirm_password" id="pass2" required placeholder="Re-enter password" autocomplete="new-password">
                <i class="fa-solid fa-eye eye-toggle" id="eye2" onclick="togglePass('pass2','eye2')"></i>
            </div>
        </div>

        <button type="submit" class="btn-submit" id="submitBtn">Create Account</button>

    </form>

    <div class="card-footer">
        Already have an account? <a href="login.php">Sign in</a>
    </div>

</div>

<!-- Crop Modal -->
<div class="crop-modal" id="cropModal">
    <div class="crop-box">
        <div class="crop-head">
            <h3>✨ Crop Profile Picture</h3>
            <button onclick="closeCropModal()" class="crop-close" type="button">×</button>
        </div>
        <div class="crop-canvas">
            <img id="cropImage" src="">
        </div>
        <div class="crop-tools">
            <button onclick="cropper.zoom(.1)"          class="crop-btn" type="button" title="Zoom In"><i class="fas fa-search-plus"></i></button>
            <button onclick="cropper.zoom(-.1)"         class="crop-btn" type="button" title="Zoom Out"><i class="fas fa-search-minus"></i></button>
            <button onclick="cropper.rotate(90)"        class="crop-btn" type="button" title="Rotate Right"><i class="fas fa-redo"></i></button>
            <button onclick="cropper.rotate(-90)"       class="crop-btn" type="button" title="Rotate Left"><i class="fas fa-undo"></i></button>
            <button onclick="cropper.scaleX(-cropper.getData().scaleX||(-1))" class="crop-btn" type="button" title="Flip"><i class="fas fa-arrows-alt-h"></i></button>
        </div>
        <div class="crop-foot">
            <button onclick="closeCropModal()" class="btn-cancel" type="button">Cancel</button>
            <button onclick="applyCrop()"      class="btn-apply"  type="button">Apply & Save</button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
<script>
// Theme
function toggleTheme(){
    const html=document.documentElement;
    const next=html.getAttribute('data-theme')==='dark'?'light':'dark';
    html.setAttribute('data-theme',next);
    localStorage.setItem('theme',next);
}
(function(){
    const t=localStorage.getItem('theme')||'dark';
    document.documentElement.setAttribute('data-theme',t);
})();

// Eye toggle — works on any input+icon pair by ID
function togglePass(inputId, iconId){
    const inp=document.getElementById(inputId);
    const ico=document.getElementById(iconId);
    if(inp.type==='password'){
        inp.type='text';
        ico.classList.replace('fa-eye','fa-eye-slash');
    }else{
        inp.type='password';
        ico.classList.replace('fa-eye-slash','fa-eye');
    }
}

// Password strength
function checkStrength(val){
    const bar=document.getElementById('strengthFill');
    if(!val){ bar.style.width='0'; return; }
    if(val.length<6){ bar.style.cssText='width:33%;background:#ef4444'; }
    else if(val.length<10){ bar.style.cssText='width:66%;background:#f59e0b'; }
    else { bar.style.cssText='width:100%;background:#10b981'; }
}

// Confirm password live check
document.getElementById('pass2').addEventListener('input',function(){
    const match=this.value===document.getElementById('pass1').value;
    this.style.borderColor=this.value?(match?'':'#ef4444'):'';
});

// Cropper
let cropper=null, croppedBlob=null;

function openCropper(e){
    const file=e.target.files[0];
    if(!file) return;
    const reader=new FileReader();
    reader.onload=function(ev){
        document.getElementById('cropImage').src=ev.target.result;
        document.getElementById('cropModal').classList.add('active');
        document.body.style.overflow='hidden';
        if(cropper){ cropper.destroy(); }
        cropper=new Cropper(document.getElementById('cropImage'),{
            aspectRatio:1, viewMode:2, dragMode:'move', autoCropArea:1,
            restore:false, guides:true, center:true, highlight:false,
            cropBoxMovable:true, cropBoxResizable:true, toggleDragModeOnDblclick:false,
        });
    };
    reader.readAsDataURL(file);
}

function closeCropModal(){
    document.getElementById('cropModal').classList.remove('active');
    document.body.style.overflow='';
    if(cropper){ cropper.destroy(); cropper=null; }
    document.getElementById('profileInput').value='';
}

function applyCrop(){
    cropper.getCroppedCanvas({width:400,height:400,imageSmoothingQuality:'high'}).toBlob(blob=>{
        croppedBlob=blob;
        const url=URL.createObjectURL(blob);
        const av=document.getElementById('avatarPreview');
        av.innerHTML=`<img src="${url}">`;
        closeCropModal();
    },'image/jpeg',.92);
}

// Submit
document.getElementById('registerForm').addEventListener('submit',function(e){
    if(croppedBlob){
        e.preventDefault();
        const fd=new FormData(this);
        fd.delete('profile');
        fd.append('profile',croppedBlob,'profile.jpg');
        const btn=document.getElementById('submitBtn');
        btn.classList.add('loading'); btn.textContent='';
        fetch(window.location.href,{method:'POST',body:fd})
            .then(r=>r.text()).then(html=>{ document.open(); document.write(html); document.close(); })
            .catch(()=>{ btn.classList.remove('loading'); btn.textContent='Create Account'; });
    } else {
        const btn=document.getElementById('submitBtn');
        btn.classList.add('loading'); btn.textContent='';
    }
});

// Escape closes crop modal
document.addEventListener('keydown',e=>{
    if(e.key==='Escape'&&document.getElementById('cropModal').classList.contains('active')) closeCropModal();
});
</script>

</body>
</html>