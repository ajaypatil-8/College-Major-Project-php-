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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - CrowdSpark</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
    <style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@400;500;600;700;800;900&display=swap');

/* ===== THEME VARIABLES ===== */
:root {
    --bg-primary: #ffffff;
    --bg-secondary: #f8fafc;
    --bg-card: rgba(255, 255, 255, 0.9);
    --text-primary: #0f172a;
    --text-secondary: #475569;
    --text-tertiary: #64748b;
    --border-color: rgba(15, 23, 42, 0.1);
    --orb-opacity: 0.25;
}

[data-theme="dark"] {
    --bg-primary: #0f0f0f;
    --bg-secondary: #1a1a1a;
    --bg-card: rgba(20, 20, 30, 0.85);
    --text-primary: #ffffff;
    --text-secondary: #cbd5e1;
    --text-tertiary: #94a3b8;
    --border-color: rgba(255, 255, 255, 0.15);
    --orb-opacity: 0.25;
    --orb-1: linear-gradient(45deg, #8b5cf6, #a78bfa);
    --orb-2: linear-gradient(45deg, #7c3aed, #8b5cf6);
    --orb-3: linear-gradient(45deg, #6d28d9, #7c3aed);
}

[data-theme="light"] {
    --orb-1: linear-gradient(45deg, #c4b5fd, #a78bfa);
    --orb-2: linear-gradient(45deg, #a78bfa, #8b5cf6);
    --orb-3: linear-gradient(45deg, #8b5cf6, #c4b5fd);
}

* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg-primary);
    color: var(--text-primary);
    overflow-x: hidden;
    position: relative;
    transition: background-color 0.3s ease, color 0.3s ease;
}

.bg-animation {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 0;
    overflow: hidden;
    opacity: var(--orb-opacity);
    transition: opacity 0.3s ease;
}

.orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    animation: float 20s infinite ease-in-out;
}

.orb-1 { width: 500px; height: 500px; background: var(--orb-1); top: -10%; left: -10%; }
.orb-2 { width: 400px; height: 400px; background: var(--orb-2); bottom: -10%; right: -10%; animation-delay: 5s; }
.orb-3 { width: 350px; height: 350px; background: var(--orb-3); top: 50%; left: 50%; animation-delay: 10s; }

@keyframes float {
    0%, 100% { transform: translate(0, 0) scale(1); }
    25% { transform: translate(50px, 50px) scale(1.1); }
    50% { transform: translate(-30px, 80px) scale(0.9); }
    75% { transform: translate(40px, -40px) scale(1.05); }
}

@keyframes fadeInUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }
@keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
@keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-10px); } 75% { transform: translateX(10px); } }
@keyframes shimmer { 0% { background-position: -1000px 0; } 100% { background-position: 1000px 0; } }
@keyframes bounce { 0%, 100% { transform: translateY(0) rotate(0deg); } 50% { transform: translateY(-12px) rotate(2deg); } }
@keyframes slideIn { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }
@keyframes scaleIn { from { opacity: 0; transform: scale(0.8); } to { opacity: 1; transform: scale(1); } }
@keyframes spin { to { transform: rotate(360deg); } }

/* Theme Toggle Button */
.theme-toggle {
    position: fixed;
    top: 20px;
    right: 20px;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: var(--bg-card);
    border: 2px solid var(--border-color);
    backdrop-filter: blur(20px);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    transition: all 0.3s ease;
    z-index: 1000;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.theme-toggle:hover {
    transform: scale(1.1) rotate(10deg);
    box-shadow: 0 8px 20px rgba(139, 92, 246, 0.2);
}

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
    background: var(--bg-card);
    backdrop-filter: blur(20px);
    padding: 50px 45px;
    border-radius: 32px;
    box-shadow: 0 20px 60px rgba(139, 92, 246, 0.1);
    border: 1px solid var(--border-color);
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
    background: linear-gradient(135deg, var(--text-primary), #8b5cf6);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.auth-sub {
    font-size: 1rem;
    color: var(--text-secondary);
    margin-bottom: 32px;
    font-weight: 500;
}

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

.alert-error { background: rgba(239, 68, 68, 0.2); color: #ef4444; border-left: 4px solid #ef4444; }
[data-theme="light"] .alert-error { color: #dc2626; }

.alert-success { background: rgba(16, 185, 129, 0.2); color: #10b981; border-left: 4px solid #10b981; }
[data-theme="light"] .alert-success { color: #059669; }

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
    color: var(--text-primary);
    margin-bottom: 10px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 16px 18px;
    border-radius: 16px;
    border: 2px solid var(--border-color);
    font-size: 15px;
    background: var(--bg-secondary);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-weight: 500;
    color: var(--text-primary);
    font-family: 'DM Sans', sans-serif;
}

.form-group textarea {
    height: 90px;
    resize: vertical;
}

.form-group input::placeholder,
.form-group textarea::placeholder {
    color: var(--text-tertiary);
    font-weight: 400;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #8b5cf6;
    box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.15);
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
    color: var(--text-tertiary);
    font-size: 18px;
    transition: all 0.3s ease;
    padding: 8px;
}

.pass-wrap .toggle-password:hover {
    color: #8b5cf6;
    transform: scale(1.15);
}

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

.auth-footer {
    text-align: center;
    margin-top: 32px;
    font-size: 15px;
    color: var(--text-tertiary);
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

.password-strength {
    height: 4px;
    background: var(--border-color);
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

/* Image Cropper Modal */
.crop-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.95);
    z-index: 10000;
    align-items: center;
    justify-content: center;
    padding: 20px;
    animation: fadeIn 0.3s ease;
}

.crop-modal.active {
    display: flex;
}

.crop-container {
    background: var(--bg-card);
    border-radius: 20px;
    max-width: 650px;
    width: 100%;
    overflow: hidden;
    box-shadow: 0 25px 80px rgba(0, 0, 0, 0.5);
    animation: scaleIn 0.3s ease;
}

.crop-header {
    padding: 24px 28px;
    border-bottom: 2px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.crop-header h3 {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    font-weight: 900;
    color: var(--text-primary);
    background: linear-gradient(135deg, var(--text-primary), #8b5cf6);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.close-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: none;
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    font-size: 24px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.close-btn:hover {
    background: rgba(239, 68, 68, 0.2);
    transform: rotate(90deg);
}

.crop-body {
    padding: 24px;
    max-height: 450px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bg-secondary);
}

#cropImage {
    max-width: 100%;
    max-height: 400px;
}

.crop-controls {
    padding: 20px 28px;
    display: flex;
    gap: 12px;
    justify-content: center;
    border-top: 2px solid var(--border-color);
    border-bottom: 2px solid var(--border-color);
    background: var(--bg-secondary);
}

.crop-btn {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    border: 2px solid var(--border-color);
    background: var(--bg-card);
    color: var(--text-primary);
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.crop-btn:hover {
    background: rgba(139, 92, 246, 0.2);
    border-color: #8b5cf6;
    color: #8b5cf6;
    transform: scale(1.1);
}

.crop-footer {
    padding: 24px 28px;
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}

.btn-cancel, .btn-apply {
    padding: 14px 32px;
    border-radius: 50px;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-size: 14px;
}

.btn-cancel {
    background: var(--bg-secondary);
    border: 2px solid var(--border-color);
    color: var(--text-secondary);
}

.btn-cancel:hover {
    background: var(--bg-card);
    border-color: var(--text-tertiary);
    transform: translateY(-2px);
}

.btn-apply {
    background: linear-gradient(135deg, #8b5cf6, #a78bfa);
    border: none;
    color: #fff;
    box-shadow: 0 8px 20px rgba(139, 92, 246, 0.3);
}

.btn-apply:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(139, 92, 246, 0.4);
}

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
    
    .crop-controls {
        flex-wrap: wrap;
    }
    
    .crop-btn {
        width: 42px;
        height: 42px;
        font-size: 16px;
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
    
    .crop-header {
        padding: 20px;
    }
    
    .crop-header h3 {
        font-size: 1.2rem;
    }
    
    .crop-footer {
        flex-direction: column;
    }
    
    .btn-cancel, .btn-apply {
        width: 100%;
    }
}
    </style>
</head>
<body>

<!-- Theme Toggle Button -->
<button class="theme-toggle" onclick="toggleTheme()" id="themeToggle">
    <i class="fas fa-moon"></i>
</button>

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

            <form method="POST" enctype="multipart/form-data" class="auth-form" id="registerForm">

                <div class="profile-upload full-width">
                    <div class="profile-preview" id="preview">👤</div>
                    <label class="upload-btn">
                        <i class="fa fa-camera"></i>
                        Choose Profile Image
                        <input type="file" name="profile" accept="image/*" hidden onchange="openCropper(event)" id="profileInput">
                    </label>
                </div>

                <div class="form-group full-width">
                    <label>Full Name</label>
                    <input type="text" name="name" required placeholder="Enter your full name" autocomplete="name">
                </div>

                <div class="form-group full-width">
                    <label>Email Address</label>
                    <input type="email" name="email" required placeholder="you@example.com" autocomplete="email">
                </div>

                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" placeholder="+1 (555) 000-0000" autocomplete="tel">
                </div>

                <div class="form-group">
                    <label>City</label>
                    <input type="text" name="city" placeholder="Your city" autocomplete="address-level2">
                </div>

                <div class="form-group full-width">
                    <label>Bio</label>
                    <textarea name="bio" placeholder="Tell us a bit about yourself and your interests..."></textarea>
                </div>

                <div class="form-group pass-wrap">
                    <label>Password</label>
                    <input type="password" name="password" id="pass1" required placeholder="Minimum 6 characters" autocomplete="new-password" oninput="checkPasswordStrength(this.value)">
                    <i class="fa fa-eye toggle-password" onclick="togglePass('pass1', this)" id="toggleIcon1"></i>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="strengthBar"></div>
                    </div>
                </div>

                <div class="form-group pass-wrap">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" id="pass2" required placeholder="Re-enter password" autocomplete="new-password">
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

<!-- Image Cropper Modal -->
<div class="crop-modal" id="cropModal">
    <div class="crop-container">
        <div class="crop-header">
            <h3>✨ Adjust Your Profile Picture</h3>
            <button onclick="closeCropModal()" class="close-btn" type="button">&times;</button>
        </div>
        <div class="crop-body">
            <img id="cropImage" src="">
        </div>
        <div class="crop-controls">
            <button onclick="cropper.zoom(0.1)" class="crop-btn" type="button" title="Zoom In"><i class="fas fa-search-plus"></i></button>
            <button onclick="cropper.zoom(-0.1)" class="crop-btn" type="button" title="Zoom Out"><i class="fas fa-search-minus"></i></button>
            <button onclick="cropper.rotate(90)" class="crop-btn" type="button" title="Rotate Right"><i class="fas fa-redo"></i></button>
            <button onclick="cropper.rotate(-90)" class="crop-btn" type="button" title="Rotate Left"><i class="fas fa-undo"></i></button>
            <button onclick="cropper.scaleX(-cropper.getData().scaleX || -1)" class="crop-btn" type="button" title="Flip Horizontal"><i class="fas fa-arrows-alt-h"></i></button>
        </div>
        <div class="crop-footer">
            <button onclick="closeCropModal()" class="btn-cancel" type="button">Cancel</button>
            <button onclick="applyCrop()" class="btn-apply" type="button">Apply & Save</button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
<script>
// Theme Toggle
function toggleTheme() {
    const html = document.documentElement;
    const currentTheme = html.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    
    const icon = document.querySelector('#themeToggle i');
    icon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
}

// Load saved theme
document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
    const icon = document.querySelector('#themeToggle i');
    icon.className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
});

// Image Cropper
let cropper = null;
let croppedImageBlob = null;

function openCropper(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(ev) {
            document.getElementById('cropImage').src = ev.target.result;
            document.getElementById('cropModal').classList.add('active');
            document.body.style.overflow = 'hidden';
            
            if (cropper) {
                cropper.destroy();
            }
            
            cropper = new Cropper(document.getElementById('cropImage'), {
                aspectRatio: 1,
                viewMode: 2,
                dragMode: 'move',
                autoCropArea: 1,
                restore: false,
                guides: true,
                center: true,
                highlight: false,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false,
                minCropBoxWidth: 100,
                minCropBoxHeight: 100,
            });
        }
        reader.readAsDataURL(file);
    }
}

function closeCropModal() {
    document.getElementById('cropModal').classList.remove('active');
    document.body.style.overflow = 'auto';
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }
    document.getElementById('profileInput').value = '';
}

function applyCrop() {
    cropper.getCroppedCanvas({
        width: 400,
        height: 400,
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
    }).toBlob((blob) => {
        croppedImageBlob = blob;
        
        const url = URL.createObjectURL(blob);
        const preview = document.getElementById("preview");
        preview.innerHTML = `<img src="${url}">`;
        preview.style.transform = "scale(1.1)";
        setTimeout(() => {
            preview.style.transform = "scale(1)";
        }, 300);
        
        closeCropModal();
    }, 'image/jpeg', 0.92);
}

// Form submission with cropped image
document.getElementById('registerForm').addEventListener('submit', function(e) {
    if (croppedImageBlob) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.delete('profile');
        formData.append('profile', croppedImageBlob, 'profile.jpg');
        
        const btn = document.querySelector('.btn-auth');
        btn.classList.add('loading');
        btn.textContent = '';
        
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        }).then(response => response.text())
        .then(html => {
            document.open();
            document.write(html);
            document.close();
        }).catch(error => {
            console.error('Error:', error);
            btn.classList.remove('loading');
            btn.textContent = 'CREATE ACCOUNT';
        });
    } else {
        const btn = document.querySelector('.btn-auth');
        const pass1 = document.getElementById('pass1').value;
        const pass2 = document.getElementById('pass2').value;
        
        if (pass1 === pass2 && pass1.length >= 6) {
            btn.classList.add('loading');
            btn.textContent = '';
        }
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

// Validate password match in real-time
const pass1 = document.getElementById('pass1');
const pass2 = document.getElementById('pass2');

if (pass2) {
    pass2.addEventListener('input', function() {
        if (this.value && pass1.value && this.value !== pass1.value) {
            this.style.borderColor = '#ef4444';
        } else {
            this.style.borderColor = '';
        }
    });
}

// Escape key to close modal
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.getElementById('cropModal').classList.contains('active')) {
        closeCropModal();
    }
});
</script>

</body>
</html>