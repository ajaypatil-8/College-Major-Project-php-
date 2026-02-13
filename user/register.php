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
    --dark: #0f172a;
    --gray-50: #f8fafc;
    --gray-100: #f1f5f9;
    --gray-200: #e2e8f0;
    --gray-300: #cbd5e1;
    --gray-400: #94a3b8;
    --gray-600: #475569;
    --gray-700: #334155;
    --gray-900: #0f172a;
    --error: #ef4444;
    --error-light: #fee2e2;
    --error-dark: #991b1b;
    --success: #10b981;
    --success-light: #d1fae5;
}

.auth-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 60px 20px;
    position: relative;
    overflow: hidden;
}

/* Animated Background Particles */
.auth-page::before {
    content: '';
    position: absolute;
    width: 500px;
    height: 500px;
    background: rgba(255, 255, 255, 0.08);
    border-radius: 50%;
    top: -150px;
    left: -150px;
    animation: float 25s infinite ease-in-out;
}

.auth-page::after {
    content: '';
    position: absolute;
    width: 400px;
    height: 400px;
    background: rgba(255, 255, 255, 0.06);
    border-radius: 50%;
    bottom: -120px;
    right: -120px;
    animation: float 20s infinite ease-in-out reverse;
}

@keyframes float {
    0%, 100% { transform: translate(0, 0) scale(1); }
    25% { transform: translate(20px, -30px) scale(1.05); }
    50% { transform: translate(-20px, -50px) scale(0.95); }
    75% { transform: translate(30px, -20px) scale(1.02); }
}

.auth-container {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 600px;
    animation: slideUp 0.7s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(40px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.auth-card {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(25px);
    padding: 50px 45px;
    border-radius: 36px;
    box-shadow: 0 50px 120px rgba(0, 0, 0, 0.25),
                0 0 0 1px rgba(255, 255, 255, 0.5) inset;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.auth-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 60px 140px rgba(0, 0, 0, 0.3),
                0 0 0 1px rgba(255, 255, 255, 0.5) inset;
}

/* Brand Section */
.auth-brand {
    text-align: center;
    margin-bottom: 40px;
    animation: fadeIn 0.8s ease-out 0.2s both;
}

@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}

.brand-icon {
    width: 75px;
    height: 75px;
    background: linear-gradient(135deg, var(--primary), var(--primary-hover));
    border-radius: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 18px;
    font-size: 40px;
    box-shadow: 0 12px 35px rgba(245, 158, 11, 0.35);
    animation: bounce 2.5s infinite;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-12px) rotate(2deg); }
}

.auth-card h2 {
    font-size: 34px;
    font-weight: 900;
    color: var(--gray-900);
    margin-bottom: 12px;
    letter-spacing: -1px;
}

.auth-sub {
    font-size: 16px;
    color: var(--gray-600);
    font-weight: 500;
    margin-bottom: 32px;
}

/* Alert Messages */
.alert {
    padding: 18px 24px;
    border-radius: 18px;
    font-size: 15px;
    font-weight: 600;
    text-align: center;
    margin-bottom: 28px;
    animation: shake 0.5s ease;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-12px); }
    50% { transform: translateX(12px); }
    75% { transform: translateX(-8px); }
}

.alert::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    to { left: 100%; }
}

.alert-error {
    background: linear-gradient(135deg, var(--error-light), #fecaca);
    color: var(--error-dark);
    border-left: 5px solid var(--error);
}

.alert-success {
    background: linear-gradient(135deg, var(--success-light), #a7f3d0);
    color: #065f46;
    border-left: 5px solid var(--success);
}

/* Profile Upload Section */
.profile-upload {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 30px;
    animation: scaleIn 0.6s ease-out 0.3s both;
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

.profile-preview {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--gray-100), var(--gray-200));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 42px;
    color: var(--gray-400);
    overflow: hidden;
    margin-bottom: 16px;
    border: 5px solid #fff;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}

.profile-preview:hover {
    transform: scale(1.05) rotate(5deg);
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18);
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
    background: linear-gradient(135deg, transparent, rgba(245, 158, 11, 0.1));
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
    font-size: 14px;
    font-weight: 700;
    background: linear-gradient(135deg, var(--gray-50), #fff);
    border: 2px dashed var(--gray-300);
    padding: 12px 24px;
    border-radius: 999px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    color: var(--gray-700);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 12px;
}

.upload-btn:hover {
    background: linear-gradient(135deg, var(--primary-light), #fff);
    border-color: var(--primary);
    color: var(--primary-hover);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(245, 158, 11, 0.2);
}

.upload-btn i {
    font-size: 16px;
}

/* Form */
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

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.form-group label {
    display: block;
    font-size: 12px;
    font-weight: 800;
    color: var(--gray-700);
    margin-bottom: 10px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 16px 18px;
    border-radius: 16px;
    border: 2px solid var(--gray-200);
    font-size: 15px;
    background: var(--gray-50);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-weight: 500;
    color: var(--gray-900);
}

.form-group textarea {
    height: 90px;
    resize: vertical;
    font-family: inherit;
}

.form-group input::placeholder,
.form-group textarea::placeholder {
    color: var(--gray-400);
    font-weight: 400;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--primary);
    background: #fff;
    box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.12),
                0 12px 35px rgba(0, 0, 0, 0.06);
    transform: translateY(-2px);
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
    color: var(--gray-600);
    font-size: 18px;
    transition: all 0.3s ease;
    padding: 8px;
}

.pass-wrap .toggle-password:hover {
    color: var(--primary);
    transform: scale(1.15);
}

/* Submit Button */
.btn-auth {
    grid-column: 1 / -1;
    width: 100%;
    padding: 20px;
    border: none;
    border-radius: 18px;
    background: linear-gradient(135deg, var(--primary), var(--primary-hover));
    color: #fff;
    font-weight: 900;
    font-size: 17px;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 12px 35px rgba(245, 158, 11, 0.35);
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
    box-shadow: 0 18px 45px rgba(245, 158, 11, 0.45);
}

.btn-auth:active {
    transform: translateY(-2px);
    box-shadow: 0 10px 28px rgba(245, 158, 11, 0.35);
}

/* Footer */
.auth-footer {
    text-align: center;
    margin-top: 32px;
    font-size: 15px;
    color: var(--gray-600);
    font-weight: 500;
    animation: fadeIn 0.8s ease-out 0.7s both;
}

.auth-footer a {
    color: var(--primary);
    font-weight: 900;
    text-decoration: none;
    transition: all 0.3s ease;
    position: relative;
}

.auth-footer a::after {
    content: '';
    position: absolute;
    bottom: -3px;
    left: 0;
    width: 0;
    height: 3px;
    background: var(--primary);
    transition: width 0.3s ease;
    border-radius: 2px;
}

.auth-footer a:hover::after {
    width: 100%;
}

.auth-footer a:hover {
    color: var(--primary-hover);
}

/* Responsive Design */
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
        font-size: 28px;
    }
    
    .brand-icon {
        width: 65px;
        height: 65px;
        font-size: 34px;
    }
}

@media (max-width: 480px) {
    .auth-card {
        padding: 35px 25px;
    }
    
    .profile-preview {
        width: 95px;
        height: 95px;
        font-size: 36px;
    }
}

/* Loading State */
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

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Password Strength Indicator */
.password-strength {
    height: 4px;
    background: var(--gray-200);
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
</style>

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

// Add ripple effect to button
document.querySelector('.btn-auth').addEventListener('click', function(e) {
    const ripple = document.createElement('span');
    const rect = this.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = e.clientX - rect.left - size / 2;
    const y = e.clientY - rect.top - size / 2;
    
    ripple.style.width = ripple.style.height = size + 'px';
    ripple.style.left = x + 'px';
    ripple.style.top = y + 'px';
    ripple.classList.add('ripple');
    
    this.appendChild(ripple);
    
    setTimeout(() => ripple.remove(), 600);
});
</script>

<?php require_once __DIR__."/../includes/footer.php"; ?>