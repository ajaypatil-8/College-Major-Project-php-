<?php
session_start();
require_once __DIR__."/../config/db.php";

/* LOGIN REQUIRED */
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$msg="";
$success="";

/* CHANGE PASSWORD */
if(isset($_POST['change_password'])){
    $old_pass = trim($_POST['old_password']);
    $new_pass = trim($_POST['new_password']);
    $confirm_pass = trim($_POST['confirm_password']);

    $stmt = $pdo->prepare("SELECT password FROM users WHERE id=?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    $current_hash = $user['password'];

    if(!password_verify($old_pass, $current_hash)){
        $msg="Current password is incorrect";
    }
    elseif(strlen($new_pass) < 6){
        $msg="New password must be at least 6 characters";
    }
    elseif($new_pass !== $confirm_pass){
        $msg="New passwords do not match";
    }
    elseif($old_pass === $new_pass){
        $msg="New password must be different from current password";
    }
    else{
        $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->execute([$new_hash, $user_id]);
        $success="Password changed successfully!";
        echo "<script>setTimeout(()=>{ window.location.href = window.location.pathname; }, 2000);</script>";
    }
}
?>

<?php require_once __DIR__."/../includes/header.php"; ?>

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
    --orb-opacity: 0.15;
    --accent-color: #6366f1;
    --accent-light: #818cf8;
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
    --orb-1: linear-gradient(45deg, #6366f1, #818cf8);
    --orb-2: linear-gradient(45deg, #4f46e5, #6366f1);
    --orb-3: linear-gradient(45deg, #4338ca, #4f46e5);
}

[data-theme="light"] {
    --orb-1: linear-gradient(45deg, #a5b4fc, #818cf8);
    --orb-2: linear-gradient(45deg, #818cf8, #6366f1);
    --orb-3: linear-gradient(45deg, #6366f1, #a5b4fc);
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

.orb-1 { width: 500px; height: 500px; background: var(--orb-1); top: -10%; left: -10%; animation-delay: 0s; }
.orb-2 { width: 400px; height: 400px; background: var(--orb-2); bottom: -10%; right: -10%; animation-delay: 5s; }
.orb-3 { width: 350px; height: 350px; background: var(--orb-3); top: 50%; left: 50%; animation-delay: 10s; }

@keyframes float {
    0%, 100% { transform: translate(0, 0) scale(1); }
    25% { transform: translate(50px, 50px) scale(1.1); }
    50% { transform: translate(-30px, 80px) scale(0.9); }
    75% { transform: translate(40px, -40px) scale(1.05); }
}

@keyframes fadeInUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }
@keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-10px); } 75% { transform: translateX(10px); } }
@keyframes shimmer { 0% { background-position: -1000px 0; } 100% { background-position: 1000px 0; } }
@keyframes pulse {
    0%, 100% { transform: scale(1); box-shadow: 0 15px 40px rgba(99, 102, 241, 0.3); }
    50% { transform: scale(1.05); box-shadow: 0 20px 50px rgba(99, 102, 241, 0.4); }
}
@keyframes slideIn { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }
@keyframes spin { to { transform: rotate(360deg); } }

.change-pass-page {
    position: relative;
    z-index: 1;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 120px 20px 80px;
}

.change-pass-container {
    width: 100%;
    max-width: 520px;
    animation: fadeInUp 0.8s ease;
}

.change-pass-card {
    background: var(--bg-card);
    backdrop-filter: blur(20px);
    padding: 50px 45px;
    border-radius: 32px;
    box-shadow: 0 20px 60px rgba(99, 102, 241, 0.1);
    border: 1px solid var(--border-color);
    transition: all 0.4s ease;
    position: relative;
    overflow: hidden;
}

.change-pass-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #6366f1, #818cf8);
}

.change-pass-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 30px 80px rgba(99, 102, 241, 0.15);
}

.change-pass-icon {
    width: 90px;
    height: 90px;
    background: linear-gradient(135deg, #6366f1, #818cf8);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 24px;
    font-size: 45px;
    box-shadow: 0 15px 40px rgba(99, 102, 241, 0.3);
    animation: pulse 2s infinite;
}

.change-pass-card h2 {
    font-family: 'Playfair Display', serif;
    font-size: 2.5rem;
    font-weight: 900;
    margin-bottom: 12px;
    text-align: center;
    background: linear-gradient(135deg, var(--text-primary), #6366f1);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.change-pass-sub {
    font-size: 1rem;
    color: var(--text-secondary);
    text-align: center;
    margin-bottom: 32px;
    font-weight: 500;
    line-height: 1.5;
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
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
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

.change-pass-form {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.form-group {
    position: relative;
    animation: slideIn 0.6s ease-out;
    animation-fill-mode: both;
}

.form-group:nth-child(1) { animation-delay: 0.1s; }
.form-group:nth-child(2) { animation-delay: 0.2s; }
.form-group:nth-child(3) { animation-delay: 0.3s; }

.form-group label {
    display: block;
    font-size: 12px;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: 10px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.pass-wrap {
    position: relative;
}

.form-group input {
    width: 100%;
    padding: 16px 50px 16px 20px;
    border-radius: 16px;
    border: 2px solid var(--border-color);
    font-size: 15px;
    background: var(--bg-secondary);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-weight: 500;
    color: var(--text-primary);
    font-family: 'DM Sans', sans-serif;
}

.form-group input::placeholder {
    color: var(--text-tertiary);
    font-weight: 400;
}

.form-group input:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
    transform: translateY(-2px);
}

.toggle-password {
    position: absolute;
    right: 18px;
    top: 50%;
    margin-top: 6px;
    cursor: pointer;
    color: var(--text-tertiary);
    font-size: 18px;
    transition: all 0.3s ease;
    padding: 8px;
    z-index: 10;
}

.toggle-password:hover {
    color: #6366f1;
    transform: scale(1.15);
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

.btn {
    width: 100%;
    padding: 18px;
    border: none;
    border-radius: 50px;
    background: linear-gradient(135deg, #6366f1, #818cf8);
    color: #fff;
    font-weight: 800;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
    position: relative;
    overflow: hidden;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-top: 8px;
    animation: slideIn 0.6s ease-out 0.4s both;
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

.btn:hover::before { left: 100%; }

.btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 40px rgba(99, 102, 241, 0.4);
}

.btn:active { transform: translateY(-1px); }

.forgot-pass-link {
    text-align: center;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 2px dashed var(--border-color);
    animation: fadeInUp 0.8s ease-out 0.5s both;
}

.forgot-pass-link p {
    font-size: 14px;
    color: var(--text-tertiary);
    margin-bottom: 12px;
    font-weight: 500;
}

.btn-forgot {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 28px;
    background: var(--bg-secondary);
    border: 2px solid var(--border-color);
    border-radius: 50px;
    color: var(--text-secondary);
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-forgot:hover {
    background: rgba(99, 102, 241, 0.2);
    border-color: #6366f1;
    color: #6366f1;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(99, 102, 241, 0.2);
}

.info-box {
    background: var(--bg-secondary);
    border: 2px dashed rgba(99, 102, 241, 0.3);
    border-radius: 16px;
    padding: 16px;
    margin-top: 24px;
    font-size: 13px;
    color: var(--text-secondary);
    line-height: 1.6;
    animation: fadeInUp 0.8s ease-out 0.6s both;
}

.info-box strong {
    color: var(--text-primary);
    display: block;
    margin-bottom: 6px;
    font-size: 14px;
}

.info-box ul {
    margin: 8px 0 0 20px;
    padding: 0;
}

.info-box li {
    margin: 4px 0;
}

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

@media (max-width: 640px) {
    .change-pass-page { padding: 100px 20px 60px; }
    .change-pass-card { padding: 40px 30px; }
    .change-pass-card h2 { font-size: 2rem; }
    .change-pass-icon { width: 75px; height: 75px; font-size: 38px; }
}
</style>

<div class="bg-animation">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
</div>

<div class="change-pass-page">
    <div class="change-pass-container">
        <div class="change-pass-card">
            <div class="change-pass-icon">🔑</div>
            <h2>Change Password</h2>
            <p class="change-pass-sub">Update your password to keep your account secure</p>

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

            <form method="POST" class="change-pass-form" id="changePassForm">
                <div class="form-group pass-wrap">
                    <label>Current Password</label>
                    <input type="password" name="old_password" id="oldPass" placeholder="Enter current password" required autocomplete="current-password">
                    <i class="fa fa-eye toggle-password" onclick="togglePass('oldPass', this)"></i>
                </div>

                <div class="form-group pass-wrap">
                    <label>New Password</label>
                    <input type="password" name="new_password" id="newPass" placeholder="Minimum 6 characters" required autocomplete="new-password" oninput="checkPasswordStrength(this.value)">
                    <i class="fa fa-eye toggle-password" onclick="togglePass('newPass', this)"></i>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="strengthBar"></div>
                    </div>
                </div>

                <div class="form-group pass-wrap">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" id="confirmPass" placeholder="Re-enter new password" required autocomplete="new-password">
                    <i class="fa fa-eye toggle-password" onclick="togglePass('confirmPass', this)"></i>
                </div>

                <button type="submit" name="change_password" class="btn">
                    <i class="fa fa-lock"></i> Update Password
                </button>
            </form>

            <div class="forgot-pass-link">
                <p>Can't remember your current password?</p>
                <a href="../user/forgotpassword.php" class="btn-forgot">
                    <i class="fa fa-unlock-alt"></i> Forgot Password
                </a>
            </div>

            <div class="info-box">
                <strong>🔒 Password Security Tips</strong>
                <ul>
                    <li>Use at least 8 characters with mixed case letters</li>
                    <li>Include numbers and special characters</li>
                    <li>Avoid common words or personal information</li>
                    <li>Don't reuse passwords from other accounts</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
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

const newPass = document.getElementById('newPass');
const confirmPass = document.getElementById('confirmPass');

confirmPass.addEventListener('input', function() {
    if (this.value && newPass.value && this.value !== newPass.value) {
        this.style.borderColor = '#ef4444';
    } else if (this.value === newPass.value && this.value.length > 0) {
        this.style.borderColor = '#10b981';
    } else {
        this.style.borderColor = '';
    }
});

newPass.addEventListener('input', function() {
    const oldPass = document.getElementById('oldPass').value;
    if (this.value && oldPass && this.value === oldPass) {
        this.style.borderColor = '#ef4444';
    } else {
        this.style.borderColor = '';
    }
});

document.getElementById('changePassForm').addEventListener('submit', function(e) {
    const newPassVal = document.getElementById('newPass').value;
    const confirmPassVal = document.getElementById('confirmPass').value;
    const oldPassVal = document.getElementById('oldPass').value;
    
    if (newPassVal.length < 6) {
        e.preventDefault();
        alert('New password must be at least 6 characters long');
        return;
    }
    if (newPassVal !== confirmPassVal) {
        e.preventDefault();
        alert('New passwords do not match');
        return;
    }
    if (oldPassVal === newPassVal) {
        e.preventDefault();
        alert('New password must be different from current password');
        return;
    }
    
    const btn = this.querySelector('.btn');
    btn.classList.add('loading');
    btn.innerHTML = '';
});
</script>

<?php require_once __DIR__."/../includes/footer.php"; ?>