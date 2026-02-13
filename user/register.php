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

/* Animated Background - Purple/Violet theme */
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
    background: linear-gradient(45deg, #8b5cf6, #a78bfa);
    top: -10%;
    left: -10%;
    animation-delay: 0s;
}

.orb-2 {
    width: 400px;
    height: 400px;
    background: linear-gradient(45deg, #7c3aed, #8b5cf6);
    bottom: -10%;
    right: -10%;
    animation-delay: 5s;
}

.orb-3 {
    width: 350px;
    height: 350px;
    background: linear-gradient(45deg, #6d28d9, #7c3aed);
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
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
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

@keyframes bounce {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-12px) rotate(2deg); }
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

@keyframes scaleIn {
    from {
        opacity: 0;
        transform: scale(0.8);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes spin {
    to { transform: rotate(360deg); }
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
    max-width: 600px;
    animation: fadeInUp 0.8s ease;
}

.auth-card {
    background: rgba(20, 20, 30, 0.85);
    backdrop-filter: blur(20px);
    padding: 50px 45px;
    border-radius: 32px;
    box-shadow: 0 20px 60px rgba(139, 92, 246, 0.1);
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
    background: linear-gradient(90deg, #8b5cf6, #a78bfa);
}

.auth-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 30px 80px rgba(139, 92, 246, 0.15);
}

/* ===== BRAND SECTION ===== */
.auth-brand {
    text-align: center;
    margin-bottom: 36px;
    animation: fadeIn 0.8s ease-out 0.2s both;
}

.brand-icon {
    width: 75px;
    height: 75px;
    background: linear-gradient(135deg, #8b5cf6, #a78bfa);
    border-radius: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 18px;
    font-size: 40px;
    box-shadow: 0 15px 40px rgba(139, 92, 246, 0.3);
    animation: bounce 2.5s infinite;
}

.auth-card h2 {
    font-family: 'Playfair Display', serif;
    font-size: 2.5rem;
    font-weight: 900;
    margin-bottom: 10px;
    background: linear-gradient(135deg, #fff, #8b5cf6);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.auth-sub {
    font-size: 1rem;
    color: #cbd5e1;
    margin-bottom: 32px;
    font-weight: 500;
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

/* ===== PROFILE UPLOAD ===== */
.profile-upload {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 30px;
    animation: scaleIn 0.6s ease-out 0.3s both;
}

.profile-preview {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(139, 92, 246, 0.2), rgba(167, 139, 250, 0.2));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 42px;
    color: #a78bfa;
    overflow: hidden;
    margin-bottom: 16px;
    border: 5px solid rgba(139, 92, 246, 0.3);
    box-shadow: 0 15px 40px rgba(139, 92, 246, 0.2);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}

.profile-preview:hover {
    transform: scale(1.05) rotate(5deg);
    box-shadow: 0 20px 50px rgba(139, 92, 246, 0.3);
}

.profile-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-preview::after {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 50%;
    background: linear-gradient(135deg, transparent, rgba(139, 92, 246, 0.2));
    opacity: 0;
    transition: opacity 0.3s ease;
}

.profile-preview:hover::after {
    opacity: 1;
}

.upload-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    font-weight: 700;
    background: rgba(139, 92, 246, 0.1);
    border: 2px dashed rgba(139, 92, 246, 0.3);
    padding: 12px 24px;
    border-radius: 999px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    color: #a78bfa;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.upload-btn:hover {
    background: rgba(139, 92, 246, 0.2);
    border-color: #8b5cf6;
    color: #c4b5fd;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(139, 92, 246, 0.2);
}

.upload-btn i {
    font-size: 16px;
}

/* ===== FORM ===== */
.auth-form {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
}

.form-group {
    position: relative;
    animation: slideIn 0.6s ease-out;
    animation-fill-mode: both;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group:nth-child(1) { animation-delay: 0.1s; }
.form-group:nth-child(2) { animation-delay: 0.15s; }
.form-group:nth-child(3) { animation-delay: 0.2s; }
.form-group:nth-child(4) { animation-delay: 0.25s; }
.form-group:nth-child(5) { animation-delay: 0.3s; }
.form-group:nth-child(6) { animation-delay: 0.35s; }
.form-group:nth-child(7) { animation-delay: 0.4s; }

.form-group label {
    display: block;
    font-size: 12px;
    font-weight: 800;
    color: #fff;
    margin-bottom: 10px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 16px 18px;
    border-radius: 16px;
    border: 2px solid rgba(255, 255, 255, 0.15);
    font-size: 15px;
    background: rgba(10, 10, 20, 0.6);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-weight: 500;
    color: #fff;
    font-family: 'DM Sans', sans-serif;
}

.form-group textarea {
    height: 90px;
    resize: vertical;
}

.form-group input::placeholder,
.form-group textarea::placeholder {
    color: #94a3b8;
    font-weight: 400;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #8b5cf6;
    background: rgba(20, 20, 30, 0.7);
    box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.15);
    transform: translateY(-2px);
}

/* ===== PASSWORD TOGGLE ===== */
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
    color: #8b5cf6;
    transform: scale(1.15);
}

/* ===== BUTTON ===== */
.btn-auth {
    grid-column: 1 / -1;
    width: 100%;
    padding: 20px;
    border: none;
    border-radius: 50px;
    background: linear-gradient(135deg, #8b5cf6, #a78bfa);
    color: #fff;
    font-weight: 900;
    font-size: 17px;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 12px 35px rgba(139, 92, 246, 0.35);
    margin-top: 12px;
    position: relative;
    overflow: hidden;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    animation: slideIn 0.6s ease-out 0.45s both;
}

.btn-auth::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.6s ease;
}

.btn-auth:hover::before {
    left: 100%;
}

.btn-auth:hover {
    transform: translateY(-4px);
    box-shadow: 0 18px 45px rgba(139, 92, 246, 0.45);
}

.btn-auth:active {
    transform: translateY(-2px);
}

/* ===== LOADING STATE ===== */
.btn-auth.loading {
    pointer-events: none;
    opacity: 0.8;
}

.btn-auth.loading::after {
    content: '';
    position: absolute;
    width: 22px;
    height: 22px;
    top: 50%;
    left: 50%;
    margin-left: -11px;
    margin-top: -11px;
    border: 3px solid rgba(255,255,255,0.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 0.7s linear infinite;
}

/* ===== FOOTER ===== */
.auth-footer {
    text-align: center;
    margin-top: 32px;
    font-size: 15px;
    color: #94a3b8;
    font-weight: 500;
    animation: fadeIn 0.8s ease-out 0.7s both;
}

.auth-footer a {
    color: #8b5cf6;
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
    background: #8b5cf6;
    transition: width 0.3s ease;
}

.auth-footer a:hover::after {
    width: 100%;
}

.auth-footer a:hover {
    color: #a78bfa;
}

/* ===== PASSWORD STRENGTH ===== */
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

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .auth-form {
        grid-template-columns: 1fr;
    }
    
    .form-group {
        grid-column: 1 / -1 !important;
    }
    
    .auth-card {
        padding: 40px 30px;
    }
    
    .auth-card h2 {
        font-size: 2rem;
    }
    
    .brand-icon {
        width: 65px;
        height: 65px;
        font-size: 34px;
    }
}

@media (max-width: 480px) {
    .auth-page {
        padding: 100px 20px 60px;
    }
    
    .auth-card {
        padding: 35px 25px;
    }
    
    .profile-preview {
        width: 95px;
        height: 95px;
        font-size: 36px;
    }
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
            
            <div class="auth-brand">
                <div class="brand-icon">🚀</div>
                <h2>Create Account</h2>
                <p class="auth-sub">Join CrowdSpark and start funding amazing projects</p>
            </div>

            <?php if($msg): ?>
            <div class="alert alert-error">
                <i class="fa fa-exclamation-circle"></i> <?= $msg ?>
            </div>
            <?php endif; ?>

            <?php if($success): ?>
            <div class="alert alert-success">
                <i class="fa fa-check-circle"></i> <?= $success ?>
            </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="auth-form">

                <!-- Profile Image Upload -->
                <div class="profile-upload full-width">
                    <div class="profile-preview" id="preview">👤</div>
                    <label class="upload-btn">
                        <i class="fa fa-camera"></i>
                        Choose Profile Image
                        <input type="file" name="profile" accept="image/*" hidden onchange="previewImage(event)">
                    </label>
                </div>

                <!-- Form Fields -->
                <div class="form-group full-width">
                    <label>Full Name</label>
                    <input 
                        type="text" 
                        name="name" 
                        required 
                        placeholder="Enter your full name"
                        autocomplete="name"
                    >
                </div>

                <div class="form-group full-width">
                    <label>Email Address</label>
                    <input 
                        type="email" 
                        name="email" 
                        required 
                        placeholder="you@example.com"
                        autocomplete="email"
                    >
                </div>

                <div class="form-group">
                    <label>Phone Number</label>
                    <input 
                        type="text" 
                        name="phone" 
                        placeholder="+1 (555) 000-0000"
                        autocomplete="tel"
                    >
                </div>

                <div class="form-group">
                    <label>City</label>
                    <input 
                        type="text" 
                        name="city" 
                        placeholder="Your city"
                        autocomplete="address-level2"
                    >
                </div>

                <div class="form-group full-width">
                    <label>Bio</label>
                    <textarea 
                        name="bio" 
                        placeholder="Tell us a bit about yourself and your interests..."
                    ></textarea>
                </div>

                <div class="form-group pass-wrap">
                    <label>Password</label>
                    <input 
                        type="password" 
                        name="password" 
                        id="pass1" 
                        required 
                        placeholder="Minimum 6 characters"
                        autocomplete="new-password"
                        oninput="checkPasswordStrength(this.value)"
                    >
                    <i class="fa fa-eye toggle-password" onclick="togglePass('pass1', this)" id="toggleIcon1"></i>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="strengthBar"></div>
                    </div>
                </div>

                <div class="form-group pass-wrap">
                    <label>Confirm Password</label>
                    <input 
                        type="password" 
                        name="confirm_password" 
                        id="pass2" 
                        required 
                        placeholder="Re-enter password"
                        autocomplete="new-password"
                    >
                    <i class="fa fa-eye toggle-password" onclick="togglePass('pass2', this)" id="toggleIcon2"></i>
                </div>

                <button type="submit" class="btn-auth">Create Account</button>

            </form>

            <div class="auth-footer">
                Already have an account?
                <a href="login.php">Login here</a>
            </div>

        </div>
    </div>
</div>

<script>
// Image Preview
function previewImage(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(ev) {
            const preview = document.getElementById("preview");
            preview.innerHTML = `<img src="${ev.target.result}">`;
            preview.style.transform = "scale(1.1)";
            setTimeout(() => {
                preview.style.transform = "scale(1)";
            }, 300);
        }
        reader.readAsDataURL(file);
    }
}

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
document.querySelector('.auth-form').addEventListener('submit', function(e) {
    const btn = document.querySelector('.btn-auth');
    const pass1 = document.getElementById('pass1').value;
    const pass2 = document.getElementById('pass2').value;
    
    if (pass1 === pass2 && pass1.length >= 6) {
        btn.classList.add('loading');
        btn.textContent = '';
    }
});

// Add input focus animations
document.querySelectorAll('.form-group input, .form-group textarea').forEach(input => {
    input.addEventListener('focus', function() {
        this.parentElement.style.zIndex = '10';
    });
    
    input.addEventListener('blur', function() {
        this.parentElement.style.zIndex = '1';
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