<?php
session_start();
require_once __DIR__."/../config/db.php";
require_once __DIR__."/../uploads/upload.php";

// Login required
if(!isset($_SESSION['user_id'])){
    header("Location: /CroudSpark-X/user/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$msg = "";
$success = "";

// Fetch current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$user){
    header("Location: /CroudSpark-X/user/login.php");
    exit;
}

// Handle form submission
if($_SERVER["REQUEST_METHOD"] == "POST"){

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $city = trim($_POST['city']);
    $bio = trim($_POST['bio']);

    // Validation
    if(strlen($name) < 3){
        $msg = "Name must be at least 3 characters";
    }
    elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $msg = "Enter valid email address";
    }
    else{
        // Check if email is already taken by another user
        $checkEmail = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $checkEmail->execute([$email, $user_id]);

        if($checkEmail->rowCount() > 0){
            $msg = "Email already registered by another user";
        }
        else{
            // Handle profile image upload
            $profileImg = $user['profile_image']; // Keep existing image by default

            if(!empty($_FILES['profile']['name'])){
                try {
                    $uploadedImage = uploadToCloudinary($_FILES['profile']['tmp_name'], "profiles");
                    if($uploadedImage){
                        $profileImg = $uploadedImage;
                    }
                } catch (Exception $e) {
                    $msg = "Image upload failed. Please try again.";
                }
            }

            // Update user data
            if(empty($msg)){
                $updateStmt = $pdo->prepare("
                    UPDATE users 
                    SET name = ?, email = ?, phone = ?, city = ?, bio = ?, profile_image = ?
                    WHERE id = ?
                ");

                $updateStmt->execute([$name, $email, $phone, $city, $bio, $profileImg, $user_id]);

                // Update session data
                $_SESSION['name'] = $name;
                $_SESSION['email'] = $email;
                $_SESSION['profile_image'] = $profileImg;

                $success = "Profile updated successfully! 🎉";

                // Refresh user data
                $stmt->execute([$user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        }
    }
}

require_once __DIR__."/../includes/header.php";
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@400;500;600;700;800;900&display=swap');

:root {
            --bg-primary: #fafafa;
            --bg-secondary: #f1f5f9;
            --bg-card: rgba(255, 255, 255, 0.95);
            --bg-card-hover: rgba(255, 255, 255, 1);
            
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-tertiary: #64748b;
            
            --border-color: rgba(15, 23, 42, 0.08);
            --border-hover: rgba(20, 184, 166, 0.3);
            
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.04);
            --shadow-md: 0 8px 32px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 20px 60px rgba(0, 0, 0, 0.15);
            
            --orb-opacity: 0.25;
        }

        [data-theme="dark"] {
            --bg-primary: #0f0f0f;
            --bg-secondary: #1a1a1a;
            --bg-card: rgba(20, 20, 30, 0.95);
            --bg-card-hover: rgba(30, 30, 40, 0.95);
            
            --text-primary: #ffffff;
            --text-secondary: #cbd5e1;
            --text-tertiary: #94a3b8;
            
            --border-color: rgba(255, 255, 255, 0.1);
            --border-hover: rgba(20, 184, 166, 0.4);
            
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.3);
            --shadow-md: 0 8px 32px rgba(0, 0, 0, 0.4);
            --shadow-lg: 0 20px 60px rgba(0, 0, 0, 0.6);
            
            --orb-opacity: 0.25;
        }

        /* Teal/Dark Cyan Accent Colors */
        :root {
            --accent-primary: #14b8a6;
            --accent-secondary: #0d9488;
            --accent-dark: #115e59;
            --accent-light: #5eead4;
            --accent-gradient: linear-gradient(135deg, #14b8a6, #0d9488);
            --accent-glow: rgba(20, 184, 166, 0.4);
        }

        /* Orb colors - Teal/Cyan mix */
        [data-theme="dark"] {
            --orb-1: linear-gradient(45deg, #14b8a6, #06b6d4);
            --orb-2: linear-gradient(45deg, #0d9488, #0891b2);
            --orb-3: linear-gradient(45deg, #2dd4bf, #22d3ee);
        }

        [data-theme="light"] {
            --orb-1: linear-gradient(45deg, #99f6e4, #a5f3fc);
            --orb-2: linear-gradient(45deg, #5eead4, #67e8f9);
            --orb-3: linear-gradient(45deg, #2dd4bf, #22d3ee);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            overflow-x: hidden;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Background Animation */
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

        .orb-1 {
            width: 500px;
            height: 500px;
            background: var(--orb-1);
            top: -10%;
            left: -10%;
        }

        .orb-2 {
            width: 400px;
            height: 400px;
            background: var(--orb-2);
            bottom: -10%;
            right: -10%;
            animation-delay: 5s;
        }

        .orb-3 {
            width: 350px;
            height: 350px;
            background: var(--orb-3);
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

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-30px);
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

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-10px); }
    50% { transform: translateX(10px); }
    75% { transform: translateX(-8px); }
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* ===== PROFILE PAGE ===== */
.profile-page {
    position: relative;
    z-index: 1;
    min-height: 100vh;
    padding: 120px 20px 80px;
}

.profile-container {
    max-width: 900px;
    margin: 0 auto;
}

/* ===== PAGE HEADER ===== */
.page-header {
    text-align: center;
    margin-bottom: 50px;
    animation: fadeInDown 0.8s ease;
}

.page-header h1 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2.5rem, 6vw, 4rem);
    font-weight: 900;
    background: linear-gradient(135deg, #fff, #14b8a6);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 12px;
    letter-spacing: -1px;
}

.page-header p {
    font-size: 1.1rem;
    color: #cbd5e1;
    font-weight: 500;
}

/* ===== PROFILE CARD ===== */
.profile-card {
    background: rgba(20, 20, 30, 0.85);
    backdrop-filter: blur(20px);
    border-radius: 32px;
    padding: 50px;
    box-shadow: 0 20px 60px rgba(20, 184, 166, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.15);
    animation: fadeInUp 0.8s ease;
    position: relative;
    overflow: hidden;
}

.profile-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #14b8a6, #5eead4);
}

/* ===== ALERT MESSAGES ===== */
.alert {
    padding: 18px 24px;
    border-radius: 18px;
    font-size: 15px;
    font-weight: 600;
    text-align: center;
    margin-bottom: 30px;
    animation: shake 0.5s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.alert-error {
    background: rgba(239, 68, 68, 0.2);
    color: #fca5a5;
    border-left: 5px solid #ef4444;
}

.alert-success {
    background: rgba(16, 185, 129, 0.2);
    color: #6ee7b7;
    border-left: 5px solid #10b981;
}

/* ===== PROFILE IMAGE SECTION ===== */
.profile-image-section {
    text-align: center;
    margin-bottom: 40px;
    animation: scaleIn 0.6s ease 0.2s both;
}

.current-image {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    margin: 0 auto 24px;
    overflow: hidden;
    border: 4px solid rgba(20, 184, 166, 0.3);
    box-shadow: 0 15px 40px rgba(20, 184, 166, 0.2);
    position: relative;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.current-image:hover {
    transform: scale(1.08) rotate(3deg);
    box-shadow: 0 20px 50px rgba(20, 184, 166, 0.3);
    border-color: #14b8a6;
}

.current-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #14b8a6, #5eead4);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 56px;
    color: #fff;
    font-weight: 900;
}

.change-photo-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 14px 28px;
    background: linear-gradient(135deg, #14b8a6, #5eead4);
    color: #fff;
    border: none;
    border-radius: 999px;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 8px 20px rgba(20, 184, 166, 0.3);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    position: relative;
    overflow: hidden;
}

.change-photo-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s;
}

.change-photo-btn:hover::before {
    left: 100%;
}

.change-photo-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(20, 184, 166, 0.4);
}

.change-photo-btn i {
    font-size: 16px;
}

/* ===== FORM ===== */
.profile-form {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
}

.form-group {
    position: relative;
    animation: slideInLeft 0.6s ease both;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group:nth-child(1) { animation-delay: 0.1s; }
.form-group:nth-child(2) { animation-delay: 0.15s; }
.form-group:nth-child(3) { animation-delay: 0.2s; }
.form-group:nth-child(4) { animation-delay: 0.25s; }
.form-group:nth-child(5) { animation-delay: 0.3s; }

.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 800;
    color: #fff;
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
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
    min-height: 120px;
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
    border-color: #14b8a6;
    background: rgba(20, 20, 30, 0.7);
    box-shadow: 0 0 0 4px rgba(20, 184, 166, 0.15);
    transform: translateY(-2px);
}

.form-group input:disabled {
    background: rgba(30, 30, 40, 0.6);
    color: #94a3b8;
    cursor: not-allowed;
}

.input-hint {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 8px;
    font-size: 12px;
    color: #94a3b8;
}

.input-hint i {
    color: #14b8a6;
}

/* ===== ROLE BADGE ===== */
.role-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 700;
    text-transform: capitalize;
    margin-top: 8px;
}

.role-user {
    background: rgba(59, 130, 246, 0.2);
    color: #60a5fa;
}

.role-creator {
    background: rgba(245, 158, 11, 0.2);
    color: #fbbf24;
}

.role-admin {
    background: rgba(239, 68, 68, 0.2);
    color: #fca5a5;
}

/* ===== BUTTONS ===== */
.form-actions {
    grid-column: 1 / -1;
    display: flex;
    gap: 16px;
    margin-top: 20px;
    animation: slideInLeft 0.6s ease 0.35s both;
}

.btn {
    flex: 1;
    padding: 18px;
    border: none;
    border-radius: 50px;
    font-weight: 800;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    text-transform: uppercase;
    letter-spacing: 1px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    position: relative;
    overflow: hidden;
}

.btn-primary {
    background: linear-gradient(135deg, #14b8a6, #5eead4);
    color: #fff;
    box-shadow: 0 12px 35px rgba(20, 184, 166, 0.35);
}

.btn-primary::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s;
}

.btn-primary:hover::before {
    left: 100%;
}

.btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 16px 45px rgba(20, 184, 166, 0.45);
}

.btn-secondary {
    background: rgba(30, 30, 40, 0.6);
    color: #cbd5e1;
    border: 2px solid rgba(255, 255, 255, 0.15);
}

.btn-secondary:hover {
    border-color: #14b8a6;
    background: rgba(20, 184, 166, 0.1);
    color: #14b8a6;
    transform: translateY(-2px);
}

/* ===== QUICK LINKS ===== */
.quick-links {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-top: 30px;
}

.quick-link {
    padding: 18px 24px;
    background: rgba(30, 30, 40, 0.6);
    border-radius: 16px;
    border: 2px solid rgba(255, 255, 255, 0.15);
    text-decoration: none;
    color: #cbd5e1;
    font-weight: 700;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 12px;
}

.quick-link:hover {
    border-color: #14b8a6;
    background: rgba(20, 184, 166, 0.1);
    color: #14b8a6;
    transform: translateX(5px);
}

.quick-link i {
    font-size: 20px;
}

/* ===== LOADING STATE ===== */
.btn-primary.loading {
    pointer-events: none;
    opacity: 0.8;
}

.btn-primary.loading::after {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255,255,255,0.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 0.7s linear infinite;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .profile-page {
        padding: 100px 20px 60px;
    }
    
    .profile-card {
        padding: 35px 25px;
    }
    
    .profile-form {
        grid-template-columns: 1fr;
    }
    
    .form-group {
        grid-column: 1 / -1 !important;
    }
    
    .page-header h1 {
        font-size: 2.5rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .quick-links {
        grid-template-columns: 1fr;
    }
    
    .current-image {
        width: 120px;
        height: 120px;
    }
}

@media (max-width: 480px) {
    .current-image {
        width: 100px;
        height: 100px;
    }
    
    .avatar-placeholder {
        font-size: 48px;
    }
}
</style>

<!-- Background Animation -->
<div class="bg-animation">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
</div>

<div class="profile-page">
    <div class="profile-container">

        <!-- PAGE HEADER -->
        <div class="page-header">
            <h1>Edit Profile</h1>
            <p>Update your personal information and preferences</p>
        </div>

        <!-- PROFILE CARD -->
        <div class="profile-card">

            <!-- ALERTS -->
            <?php if($msg): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-exclamation-circle"></i> <?= $msg ?>
            </div>
            <?php endif; ?>

            <?php if($success): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-check-circle"></i> <?= $success ?>
            </div>
            <?php endif; ?>

            <!-- PROFILE IMAGE SECTION -->
            <div class="profile-image-section">
                <div class="current-image" id="profilePreview">
                    <?php if($user['profile_image']): ?>
                        <img src="<?= htmlspecialchars($user['profile_image']) ?>" alt="Profile">
                    <?php else: ?>
                        <div class="avatar-placeholder">
                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <label class="change-photo-btn">
                    <i class="fa-solid fa-camera"></i>
                    Change Profile Photo
                    <input type="file" id="profileInput" accept="image/*" hidden>
                </label>
            </div>

            <!-- FORM -->
            <form method="POST" enctype="multipart/form-data" class="profile-form" id="profileForm">

                <!-- Hidden file input for form submission -->
                <input type="file" name="profile" id="hiddenProfileInput" hidden>

                <!-- Full Name -->
                <div class="form-group full-width">
                    <label>
                        <i class="fa-solid fa-user"></i> Full Name
                    </label>
                    <input 
                        type="text" 
                        name="name" 
                        value="<?= htmlspecialchars($user['name']) ?>"
                        required 
                        placeholder="Enter your full name"
                        autocomplete="name"
                    >
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label>
                        <i class="fa-solid fa-envelope"></i> Email Address
                    </label>
                    <input 
                        type="email" 
                        name="email" 
                        value="<?= htmlspecialchars($user['email']) ?>"
                        required 
                        placeholder="you@example.com"
                        autocomplete="email"
                    >
                </div>

                <!-- Role (Read-only) -->
                <div class="form-group">
                    <label>
                        <i class="fa-solid fa-shield"></i> Account Role
                    </label>
                    <input 
                        type="text" 
                        value="<?= ucfirst($user['role']) ?>"
                        disabled
                    >
                    <div class="role-badge role-<?= $user['role'] ?>">
                        <i class="fa-solid fa-<?= $user['role'] == 'admin' ? 'shield' : ($user['role'] == 'creator' ? 'star' : 'user') ?>"></i>
                        <?= ucfirst($user['role']) ?>
                    </div>
                </div>

                <!-- Phone -->
                <div class="form-group">
                    <label>
                        <i class="fa-solid fa-phone"></i> Phone Number
                    </label>
                    <input 
                        type="text" 
                        name="phone" 
                        value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                        placeholder="+1 (555) 000-0000"
                        autocomplete="tel"
                    >
                </div>

                <!-- City -->
                <div class="form-group">
                    <label>
                        <i class="fa-solid fa-location-dot"></i> City
                    </label>
                    <input 
                        type="text" 
                        name="city" 
                        value="<?= htmlspecialchars($user['city'] ?? '') ?>"
                        placeholder="Your city"
                        autocomplete="address-level2"
                    >
                </div>

                <!-- Bio -->
                <div class="form-group full-width">
                    <label>
                        <i class="fa-solid fa-align-left"></i> Bio
                    </label>
                    <textarea 
                        name="bio" 
                        placeholder="Tell us a bit about yourself..."
                    ><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                    <div class="input-hint">
                        <i class="fa-solid fa-info-circle"></i>
                        Share your story, interests, or what motivates you
                    </div>
                </div>

                <!-- FORM ACTIONS -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-save"></i>
                        Save Changes
                    </button>
                    <a href="/CroudSpark-X/dashboard/user-dashboard.php" class="btn btn-secondary">
                        <i class="fa-solid fa-times"></i>
                        Cancel
                    </a>
                </div>

            </form>

            <!-- QUICK LINKS -->
            <div class="quick-links">
                <a href="/CroudSpark-X/dashboard/change-password.php" class="quick-link">
                    <i class="fa-solid fa-lock"></i>
                    Change Password
                </a>
                <a href="/CroudSpark-X/dashboard/user-dashboard.php" class="quick-link">
                    <i class="fa-solid fa-gauge"></i>
                    My Dashboard
                </a>
            </div>

        </div>

    </div>
</div>

<script>
// Profile image preview and handling
const profileInput = document.getElementById('profileInput');
const hiddenProfileInput = document.getElementById('hiddenProfileInput');
const profilePreview = document.getElementById('profilePreview');
let selectedFile = null;

profileInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        selectedFile = file;
        
        // Create a new FileList and assign to hidden input
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        hiddenProfileInput.files = dataTransfer.files;
        
        // Preview the image
        const reader = new FileReader();
        reader.onload = function(ev) {
            profilePreview.innerHTML = `<img src="${ev.target.result}" alt="Profile">`;
            profilePreview.style.transform = "scale(1.1)";
            setTimeout(() => {
                profilePreview.style.transform = "scale(1)";
            }, 300);
        }
        reader.readAsDataURL(file);
    }
});

// Form submission loading state
document.getElementById('profileForm').addEventListener('submit', function(e) {
    const btn = this.querySelector('.btn-primary');
    btn.classList.add('loading');
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Updating...';
});

// Add focus animations
document.querySelectorAll('.form-group input, .form-group textarea').forEach(input => {
    input.addEventListener('focus', function() {
        this.parentElement.style.zIndex = '10';
    });
    
    input.addEventListener('blur', function() {
        this.parentElement.style.zIndex = '1';
    });
});

// Auto-save notification (optional)
let typingTimer;
const doneTypingInterval = 2000;

document.querySelectorAll('.form-group input:not([type="file"]), .form-group textarea').forEach(input => {
    input.addEventListener('input', function() {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => {
            // You can add auto-save functionality here
            console.log('Changes detected - ready to save');
        }, doneTypingInterval);
    });
});
</script>

<?php require_once __DIR__."/../includes/footer.php"; ?>