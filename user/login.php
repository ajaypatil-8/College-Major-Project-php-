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

/* Animated Background - Slate/Gray theme */
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
    background: linear-gradient(45deg, #64748b, #94a3b8);
    top: -10%;
    left: -10%;
    animation-delay: 0s;
}

.orb-2 {
    width: 400px;
    height: 400px;
    background: linear-gradient(45deg, #475569, #64748b);
    bottom: -10%;
    right: -10%;
    animation-delay: 5s;
}

.orb-3 {
    width: 350px;
    height: 350px;
    background: linear-gradient(45deg, #334155, #475569);
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

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
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
    max-width: 480px;
    animation: fadeInUp 0.8s ease;
}

.auth-card {
    background: rgba(20, 20, 30, 0.85);
    backdrop-filter: blur(20px);
    padding: 50px 45px;
    border-radius: 32px;
    box-shadow: 0 20px 60px rgba(100, 116, 139, 0.1);
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
    background: linear-gradient(90deg, #64748b, #94a3b8);
}

.auth-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 30px 80px rgba(100, 116, 139, 0.15);
}

/* ===== BRAND SECTION ===== */
.auth-brand {
    text-align: center;
    margin-bottom: 36px;
    animation: fadeIn 0.8s ease-out 0.2s both;
}

.brand-icon {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #64748b, #94a3b8);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    font-size: 36px;
    box-shadow: 0 10px 30px rgba(100, 116, 139, 0.3);
    animation: bounce 2s infinite;
}

.auth-card h2 {
    font-family: 'Playfair Display', serif;
    font-size: 2.5rem;
    font-weight: 900;
    margin-bottom: 10px;
    background: linear-gradient(135deg, #fff, #64748b);
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

/* ===== FORM ===== */
.auth-form {
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

.form-group label {
    display: block;
    font-size: 12px;
    font-weight: 800;
    color: #fff;
    margin-bottom: 10px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.form-group input {
    width: 100%;
    padding: 16px 20px;
    border-radius: 16px;
    border: 2px solid rgba(255, 255, 255, 0.15);
    font-size: 15px;
    background: rgba(10, 10, 20, 0.6);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-weight: 500;
    color: #fff;
    font-family: 'DM Sans', sans-serif;
}

.form-group input::placeholder {
    color: #94a3b8;
    font-weight: 400;
}

.form-group input:focus {
    outline: none;
    border-color: #64748b;
    background: rgba(20, 20, 30, 0.7);
    box-shadow: 0 0 0 4px rgba(100, 116, 139, 0.15);
    transform: translateY(-2px);
}

/* ===== PASSWORD TOGGLE ===== */
.pass-wrap {
    position: relative;
}

.pass-wrap .toggle-password {
    position: absolute;
    right: 20px;
    top: 50%;
    margin-top: 6px;
    cursor: pointer;
    color: #94a3b8;
    font-size: 18px;
    transition: all 0.3s ease;
    padding: 8px;
}

.pass-wrap .toggle-password:hover {
    color: #64748b;
    transform: scale(1.15);
}

/* ===== FORGOT PASSWORD ===== */
.forgot {
    text-align: right;
    margin-top: -12px;
    animation: slideIn 0.6s ease-out 0.3s both;
}

.forgot a {
    font-size: 14px;
    color: #64748b;
    text-decoration: none;
    font-weight: 700;
    transition: all 0.3s ease;
    position: relative;
}

.forgot a::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 2px;
    background: #64748b;
    transition: width 0.3s ease;
}

.forgot a:hover::after {
    width: 100%;
}

.forgot a:hover {
    color: #94a3b8;
}

/* ===== BUTTON ===== */
.btn-auth {
    width: 100%;
    padding: 18px;
    border: none;
    border-radius: 50px;
    background: linear-gradient(135deg, #64748b, #94a3b8);
    color: #fff;
    font-weight: 800;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 10px 30px rgba(100, 116, 139, 0.3);
    margin-top: 8px;
    position: relative;
    overflow: hidden;
    text-transform: uppercase;
    letter-spacing: 1px;
    animation: slideIn 0.6s ease-out 0.4s both;
}

.btn-auth::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s ease;
}

.btn-auth:hover::before {
    left: 100%;
}

.btn-auth:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 40px rgba(100, 116, 139, 0.4);
}

.btn-auth:active {
    transform: translateY(-1px);
}

/* ===== LOADING STATE ===== */
.btn-auth.loading {
    pointer-events: none;
    opacity: 0.8;
}

.btn-auth.loading::after {
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

/* ===== FOOTER ===== */
.auth-footer {
    text-align: center;
    margin-top: 32px;
    font-size: 15px;
    color: #94a3b8;
    font-weight: 500;
    animation: fadeIn 0.8s ease-out 0.6s both;
}

.auth-footer a {
    color: #64748b;
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
    background: #64748b;
    transition: width 0.3s ease;
}

.auth-footer a:hover::after {
    width: 100%;
}

.auth-footer a:hover {
    color: #94a3b8;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 640px) {
    .auth-page {
        padding: 100px 20px 60px;
    }
    
    .auth-card {
        padding: 40px 30px;
    }
    
    .auth-card h2 {
        font-size: 2rem;
    }
    
    .brand-icon {
        width: 60px;
        height: 60px;
        font-size: 30px;
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
                <h2>Welcome Back</h2>
                <p class="auth-sub">Login to continue your journey with CrowdSpark</p>
            </div>

            <?php if($msg): ?>
            <div class="alert alert-error">
                <i class="fa fa-exclamation-circle"></i> <?= $msg ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                
                <div class="form-group">
                    <label>Email Address</label>
                    <input 
                        type="email" 
                        name="email" 
                        required 
                        placeholder="you@example.com"
                        autocomplete="email"
                    >
                </div>

                <div class="form-group pass-wrap">
                    <label>Password</label>
                    <input 
                        type="password" 
                        name="password" 
                        id="pass" 
                        required 
                        placeholder="Enter your password"
                        autocomplete="current-password"
                    >
                    <i class="fa fa-eye toggle-password" onclick="togglePass()" id="toggleIcon"></i>
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
</div>

<script>
function togglePass() {
    const input = document.getElementById("pass");
    const icon = document.getElementById("toggleIcon");
    
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

// Add loading state on form submit
document.querySelector('.auth-form').addEventListener('submit', function() {
    const btn = document.querySelector('.btn-auth');
    btn.classList.add('loading');
    btn.textContent = '';
});

// Add focus animations
document.querySelectorAll('.form-group input').forEach(input => {
    input.addEventListener('focus', function() {
        this.parentElement.style.transform = 'scale(1.01)';
    });
    
    input.addEventListener('blur', function() {
        this.parentElement.style.transform = 'scale(1)';
    });
});
</script>

<?php require_once __DIR__."/../includes/footer.php"; ?>