<?php
session_start();
require_once __DIR__."/../config/db.php";
require_once __DIR__."/../uploads/upload.php";

/* LOGIN CHECK */
if(!isset($_SESSION['user_id'])){
    header("Location: /CroudSpark-X/user/login.php");
    exit;
}

if($_SESSION['role']!="creator"){
    header("Location: /CroudSpark-X/user/becomecreator.php");
    exit;
}

$step = $_GET['step'] ?? 1;
$campaign_id = $_GET['id'] ?? null;
$msg="";

/* ================= STEP 1 ================= */
if($step==1 && isset($_POST['step1'])){
    $title = trim($_POST['title']);
    $category = $_POST['category'];
    $goal = $_POST['goal'];
    $location = $_POST['location'];
    $end_date = $_POST['end_date'];
    $user_id = $_SESSION['user_id'];

    if($title=="" || $goal==""){
        $msg="Fill required fields";
    }else{
        $stmt=$pdo->prepare("INSERT INTO campaigns 
        (user_id,title,category,goal,location,end_date,status,created_at)
        VALUES (?,?,?,?,?,?,'draft',NOW())");
        $stmt->execute([$user_id,$title,$category,$goal,$location,$end_date]);
        $cid = $pdo->lastInsertId();
        header("Location: create-campaign.php?step=2&id=".$cid);
        exit;
    }
}

/* ================= STEP 2 ================= */
if($step==2 && isset($_POST['step2'])){
    $campaign_id = $_POST['campaign_id'];

    if(!empty($_FILES['images']['name'][0]) && count($_FILES['images']['name']) > 10){
        $msg="Max 10 images allowed";
    }
    elseif(!empty($_FILES['videos']['name'][0]) && count($_FILES['videos']['name']) > 4){
        $msg="Max 4 videos allowed";
    }
    elseif(empty($_FILES['thumbnail']['name'])){
        $msg="Thumbnail required";
    }
    else{
        $thumb = uploadToCloudinary($_FILES['thumbnail']['tmp_name'],"campaigns","image");
        if($thumb){
            $stmt=$pdo->prepare("INSERT INTO campaign_media (campaign_id,media_url,media_type) VALUES (?,?,?)");
            $stmt->execute([$campaign_id,$thumb,'thumbnail']);
        }

        if(!empty($_FILES['images']['name'][0])){
            foreach($_FILES['images']['tmp_name'] as $tmp){
                if($tmp){
                    $img = uploadToCloudinary($tmp,"campaigns","image");
                    if($img){
                        $stmt=$pdo->prepare("INSERT INTO campaign_media (campaign_id,media_url,media_type) VALUES (?,?,?)");
                        $stmt->execute([$campaign_id,$img,'image']);
                    }
                }
            }
        }

        if(!empty($_FILES['videos']['name'][0])){
            foreach($_FILES['videos']['tmp_name'] as $tmp){
                if($tmp){
                    $vid = uploadToCloudinary($tmp,"campaigns","video");
                    if($vid){
                        $stmt=$pdo->prepare("INSERT INTO campaign_media (campaign_id,media_url,media_type) VALUES (?,?,?)");
                        $stmt->execute([$campaign_id,$vid,'video']);
                    }
                }
            }
        }

        header("Location: create-campaign.php?step=3&id=".$campaign_id);
        exit;
    }
}

/* ================= STEP 3 ================= */
if($step==3 && isset($_POST['step3'])){
    $campaign_id = $_POST['campaign_id'];
    $short = $_POST['short_desc'];
    $story = $_POST['story'];

    if($short=="" || $story==""){
        $msg="Fill all fields";
    }else{
        $stmt=$pdo->prepare("UPDATE campaigns SET short_desc=?, story=? WHERE id=?");
        $stmt->execute([$short,$story,$campaign_id]);
        header("Location: create-campaign.php?step=4&id=".$campaign_id);
        exit;
    }
}

/* ================= STEP 4 ================= */
if($step==4 && isset($_POST['submit_campaign'])){
    $campaign_id = $_POST['campaign_id'];
    $upi   = trim($_POST['upi']);
    $acc   = trim($_POST['account_no']);
    $ifsc  = trim($_POST['ifsc']);
    $holder= trim($_POST['holder']);
    $doc_type = $_POST['doc_type'];

    if(empty($_FILES['document']['name'])){
        $msg="Upload ID proof document";
    }
    else{
        $docUrl = uploadToCloudinary($_FILES['document']['tmp_name'],"documents","image");

        $stmt=$pdo->prepare("INSERT INTO campaign_bank
        (campaign_id,upi_id,account_no,ifsc,holder_name)
        VALUES (?,?,?,?,?)");
        $stmt->execute([$campaign_id,$upi ?: null,$acc ?: null,$ifsc ?: null,$holder ?: null]);

        $stmt=$pdo->prepare("INSERT INTO campaign_documents
        (campaign_id,doc_url,doc_type)
        VALUES (?,?,?)");
        $stmt->execute([$campaign_id,$docUrl,$doc_type]);

        $stmt=$pdo->prepare("UPDATE campaigns SET status='pending' WHERE id=?");
        $stmt->execute([$campaign_id]);

        echo "<script>alert('Campaign submitted for admin approval 🚀');window.location='/CroudSpark-X/creator/creator-dashboard.php';</script>";
        exit;
    }
}

require_once __DIR__."/../includes/header.php";
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

/* Animated Background - Green/Emerald theme */
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
    background: linear-gradient(45deg, #10b981, #34d399);
    top: -10%;
    left: -10%;
    animation-delay: 0s;
}

.orb-2 {
    width: 400px;
    height: 400px;
    background: linear-gradient(45deg, #059669, #10b981);
    bottom: -10%;
    right: -10%;
    animation-delay: 5s;
}

.orb-3 {
    width: 350px;
    height: 350px;
    background: linear-gradient(45deg, #047857, #059669);
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
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes shimmer {
    0% { background-position: -1000px 0; }
    100% { background-position: 1000px 0; }
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

/* ===== PROGRESS HEADER ===== */
.ks-header {
    position: relative;
    z-index: 1;
    background: rgba(20, 20, 30, 0.85);
    backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.15);
    padding: 24px 40px;
    display: flex;
    gap: 40px;
    font-weight: 700;
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: 0 4px 20px rgba(16, 185, 129, 0.08);
    animation: slideInRight 0.6s ease-out;
}

.ks-header span {
    color: #cbd5e1;
    padding: 12px 24px;
    border-radius: 50px;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    cursor: pointer;
}

.ks-header span::before {
    content: '';
    position: absolute;
    left: 0;
    bottom: -24px;
    width: 100%;
    height: 4px;
    background: linear-gradient(45deg, #10b981, #34d399);
    transform: scaleX(0);
    transition: transform 0.3s ease;
    border-radius: 4px;
}

.ks-header .active {
    color: #10b981;
    background: rgba(16, 185, 129, 0.1);
}

.ks-header .active::before {
    transform: scaleX(1);
}

.ks-header span:hover {
    transform: translateY(-2px);
    color: #10b981;
}

/* ===== CONTAINER ===== */
.ks-container {
    position: relative;
    z-index: 1;
    max-width: 1200px;
    margin: 60px auto;
    display: grid;
    grid-template-columns: 380px 1fr;
    gap: 60px;
    padding: 0 20px;
    animation: fadeInUp 0.7s ease-out;
}

/* ===== LEFT SIDEBAR ===== */
.ks-left {
    animation: slideInRight 0.8s ease-out;
}

.ks-left h2 {
    font-family: 'Playfair Display', serif;
    font-size: 2.5rem;
    font-weight: 900;
    margin-bottom: 16px;
    background: linear-gradient(45deg, #10b981, #34d399);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    line-height: 1.2;
}

.ks-left p {
    color: #cbd5e1;
    line-height: 1.8;
    margin-bottom: 20px;
    font-size: 1.05rem;
}

.tip-box {
    background: rgba(20, 20, 30, 0.7);
    border-left: 4px solid #10b981;
    padding: 20px;
    border-radius: 12px;
    margin-top: 30px;
    animation: fadeInUp 1s ease-out;
    border: 1px solid rgba(16, 185, 129, 0.2);
}

.tip-box h3 {
    color: #10b981;
    font-size: 1.1rem;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.tip-box ul {
    padding-left: 20px;
    color: #cbd5e1;
}

.tip-box li {
    margin-bottom: 8px;
    font-size: 0.95rem;
}

/* ===== CARD ===== */
.ks-card {
    background: rgba(20, 20, 30, 0.85);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 24px;
    padding: 50px;
    box-shadow: 0 10px 40px rgba(16, 185, 129, 0.08);
    animation: fadeInUp 0.9s ease-out;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.ks-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #10b981, #34d399);
}

.ks-card:hover {
    box-shadow: 0 20px 60px rgba(16, 185, 129, 0.12);
    transform: translateY(-4px);
}

/* ===== FORM ELEMENTS ===== */
.form-group {
    margin-bottom: 28px;
    animation: fadeInUp 0.5s ease-out backwards;
}

.form-group:nth-child(1) { animation-delay: 0.1s; }
.form-group:nth-child(2) { animation-delay: 0.2s; }
.form-group:nth-child(3) { animation-delay: 0.3s; }
.form-group:nth-child(4) { animation-delay: 0.4s; }
.form-group:nth-child(5) { animation-delay: 0.5s; }

label {
    font-weight: 700;
    margin-bottom: 10px;
    display: block;
    color: #fff;
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

label .required {
    color: #ef4444;
    margin-left: 4px;
}

input, select, textarea {
    width: 100%;
    padding: 16px 18px;
    border-radius: 12px;
    border: 2px solid rgba(255, 255, 255, 0.15);
    background: rgba(10, 10, 20, 0.6);
    font-family: 'DM Sans', sans-serif;
    font-size: 1rem;
    transition: all 0.3s ease;
    color: #fff;
}

input:focus, select:focus, textarea:focus {
    outline: none;
    border-color: #10b981;
    background: rgba(20, 20, 30, 0.7);
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
    transform: translateY(-2px);
}

input:hover, select:hover, textarea:hover {
    border-color: #34d399;
}

textarea {
    min-height: 140px;
    resize: vertical;
}

input::placeholder, textarea::placeholder {
    color: #94a3b8;
}

/* Character counter */
.char-counter {
    text-align: right;
    font-size: 0.85rem;
    color: #94a3b8;
    margin-top: 6px;
}

/* ===== FILE UPLOAD ===== */
.upload-box {
    border: 3px dashed rgba(16, 185, 129, 0.3);
    padding: 50px 40px;
    border-radius: 16px;
    text-align: center;
    background: rgba(16, 185, 129, 0.02);
    margin-bottom: 24px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.upload-box::before {
    content: '📁';
    font-size: 3rem;
    display: block;
    margin-bottom: 16px;
    animation: pulse 2s infinite;
}

.upload-box:hover {
    border-color: #10b981;
    background: rgba(16, 185, 129, 0.05);
    transform: scale(1.02);
}

.upload-box b {
    display: block;
    margin-bottom: 12px;
    font-size: 1.1rem;
    color: #fff;
}

.upload-box small {
    display: block;
    color: #cbd5e1;
    margin-bottom: 20px;
    font-size: 0.9rem;
}

.upload-box input[type="file"] {
    cursor: pointer;
    padding: 12px;
    background: rgba(20, 20, 30, 0.6);
    border: 2px solid rgba(255, 255, 255, 0.15);
}

.upload-box input[type="file"]:hover {
    border-color: #10b981;
}

/* ===== BUTTONS ===== */
.next-btn {
    background: linear-gradient(45deg, #10b981, #34d399);
    color: white;
    border: none;
    padding: 18px 32px;
    border-radius: 50px;
    font-weight: 800;
    font-size: 1.05rem;
    width: 100%;
    cursor: pointer;
    margin-top: 30px;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
    position: relative;
    overflow: hidden;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.next-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s;
}

.next-btn:hover::before {
    left: 100%;
}

.next-btn:hover {
    transform: translateY(-4px) scale(1.02);
    box-shadow: 0 20px 50px rgba(16, 185, 129, 0.4);
}

.next-btn:active {
    transform: translateY(-2px) scale(1);
}

.next-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* ===== ERROR & SUCCESS ===== */
.error {
    color: white;
    background: linear-gradient(135deg, #dc2626, #ef4444);
    margin-bottom: 24px;
    font-weight: 600;
    padding: 16px 20px;
    border-radius: 12px;
    animation: fadeInUp 0.4s ease-out;
    box-shadow: 0 8px 24px rgba(220, 38, 38, 0.25);
    display: flex;
    align-items: center;
    gap: 12px;
}

.error::before {
    content: '⚠️';
    font-size: 1.5rem;
}

.success {
    color: white;
    background: linear-gradient(135deg, #10b981, #34d399);
    margin-bottom: 24px;
    font-weight: 600;
    padding: 16px 20px;
    border-radius: 12px;
    animation: fadeInUp 0.4s ease-out;
    box-shadow: 0 8px 24px rgba(16, 185, 129, 0.25);
    display: flex;
    align-items: center;
    gap: 12px;
}

.success::before {
    content: '✅';
    font-size: 1.5rem;
}

/* ===== VALIDATION STYLES ===== */
input.invalid, textarea.invalid, select.invalid {
    border-color: #ef4444;
    background: rgba(239, 68, 68, 0.05);
}

input.valid, textarea.valid, select.valid {
    border-color: #10b981;
    background: rgba(16, 185, 129, 0.05);
}

.validation-message {
    font-size: 0.85rem;
    margin-top: 6px;
    display: none;
}

.validation-message.error {
    color: #ef4444;
    display: block;
    background: none;
    padding: 0;
    box-shadow: none;
    animation: none;
}

.validation-message.error::before {
    content: '⚠ ';
}

.validation-message.success {
    color: #10b981;
    display: block;
    background: none;
    padding: 0;
    box-shadow: none;
    animation: none;
}

.validation-message.success::before {
    content: '✓ ';
}

/* ===== STEP INDICATOR ===== */
.step-indicator {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 30px;
}

.step-number {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(45deg, #10b981, #34d399);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    font-size: 1.3rem;
    box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
    animation: pulse 2s infinite;
}

.step-title {
    font-size: 1.5rem;
    font-weight: 800;
    color: #fff;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 968px) {
    .ks-container {
        grid-template-columns: 1fr;
        gap: 40px;
        margin: 40px auto;
    }
    
    .ks-header {
        padding: 20px;
        gap: 20px;
        flex-wrap: wrap;
    }
    
    .ks-card {
        padding: 30px;
    }
    
    .ks-left h2 {
        font-size: 2rem;
    }
}

@media (max-width: 640px) {
    .ks-header span {
        font-size: 0.9rem;
        padding: 8px 16px;
    }
    
    .ks-card {
        padding: 24px;
    }
    
    .upload-box {
        padding: 30px 20px;
    }
}
</style>

<!-- Background Animation -->
<div class="bg-animation">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
</div>

<div class="ks-header">
    <span class="<?= $step==1?'active':'' ?>">📝 Basics</span>
    <span class="<?= $step==2?'active':'' ?>">📸 Media</span>
    <span class="<?= $step==3?'active':'' ?>">📖 Story</span>
    <span class="<?= $step==4?'active':'' ?>">💳 Payment</span>
</div>

<div class="ks-container">

    <div class="ks-left">
        <h2>Create Your Project</h2>
        <p>Make it easy for people to understand and fund your campaign. A clear title, authentic media, and compelling story dramatically increase your success rate.</p>
        
        <div class="tip-box">
            <h3>💡 Pro Tips</h3>
            <ul>
                <li>Use a clear, specific title</li>
                <li>Set realistic funding goals</li>
                <li>Add high-quality images</li>
                <li>Tell your authentic story</li>
                <li>Update supporters regularly</li>
            </ul>
        </div>
    </div>

    <div class="ks-card">

        <?php if($msg): ?><div class="error"><?= $msg ?></div><?php endif; ?>

        <?php if($step==1): ?>
        <div class="step-indicator">
            <div class="step-number">1</div>
            <div class="step-title">Campaign Basics</div>
        </div>

        <form method="POST" id="step1Form">
            <div class="form-group">
                <label>Campaign Title <span class="required">*</span></label>
                <input type="text" name="title" id="title" required maxlength="100" 
                       placeholder="e.g., Help Save My Mother's Life">
                <div class="char-counter"><span id="titleCount">0</span>/100</div>
                <div class="validation-message" id="titleError"></div>
            </div>

            <div class="form-group">
                <label>Category <span class="required">*</span></label>
                <select name="category" id="category" required>
                    <option value="">Select a category</option>
                    <option value="Medical">🏥 Medical</option>
                    <option value="Education">📚 Education</option>
                    <option value="Startup">🚀 Startup</option>
                    <option value="Community">🤝 Community</option>
                    <option value="Emergency">🆘 Emergency</option>
                    <option value="Animal">🐾 Animal Welfare</option>
                </select>
            </div>

            <div class="form-group">
                <label>Funding Goal (₹) <span class="required">*</span></label>
                <input type="number" name="goal" id="goal" required min="1000" 
                       placeholder="e.g., 100000">
                <div class="validation-message" id="goalError"></div>
            </div>

            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" id="location" 
                       placeholder="e.g., Mumbai, Maharashtra">
            </div>

            <div class="form-group">
                <label>Campaign End Date</label>
                <input type="date" name="end_date" id="end_date">
                <div class="validation-message" id="dateError"></div>
            </div>

            <button class="next-btn" name="step1" type="submit">Save & Continue →</button>
        </form>
        <?php endif; ?>

        <?php if($step==2): ?>
        <div class="step-indicator">
            <div class="step-number">2</div>
            <div class="step-title">Upload Media</div>
        </div>

        <form method="POST" enctype="multipart/form-data" id="step2Form">
            <input type="hidden" name="campaign_id" value="<?= $campaign_id ?>">

            <div class="upload-box">
                <b>Campaign Thumbnail <span class="required" style="color: #ef4444;">*</span></b>
                <small>Main image that appears on your campaign (JPG, PNG - Max 5MB)</small>
                <input type="file" name="thumbnail" id="thumbnail" accept="image/*" required>
            </div>

            <div class="upload-box">
                <b>Additional Images</b>
                <small>Upload up to 10 images (JPG, PNG - Max 5MB each)</small>
                <input type="file" name="images[]" id="images" accept="image/*" multiple>
            </div>

            <div class="upload-box">
                <b>Videos</b>
                <small>Upload up to 4 videos (MP4, MOV - Max 50MB each)</small>
                <input type="file" name="videos[]" id="videos" accept="video/*" multiple>
            </div>

            <button class="next-btn" name="step2" type="submit">Save & Continue →</button>
        </form>
        <?php endif; ?>

        <?php if($step==3): ?>
        <div class="step-indicator">
            <div class="step-number">3</div>
            <div class="step-title">Tell Your Story</div>
        </div>

        <form method="POST" id="step3Form">
            <input type="hidden" name="campaign_id" value="<?= $campaign_id ?>">

            <div class="form-group">
                <label>Short Description <span class="required">*</span></label>
                <textarea name="short_desc" id="short_desc" required maxlength="300" 
                          placeholder="A brief summary of your campaign (max 300 characters)"></textarea>
                <div class="char-counter"><span id="shortCount">0</span>/300</div>
                <div class="validation-message" id="shortError"></div>
            </div>

            <div class="form-group">
                <label>Full Campaign Story <span class="required">*</span></label>
                <textarea name="story" id="story" required minlength="100"
                          placeholder="Tell your complete story. Be honest, personal, and specific. Explain why you need help and how the funds will be used." 
                          style="min-height: 250px;"></textarea>
                <div class="char-counter"><span id="storyCount">0</span> characters</div>
                <div class="validation-message" id="storyError"></div>
            </div>

            <button class="next-btn" name="step3" type="submit">Save & Continue →</button>
        </form>
        <?php endif; ?>

        <?php if($step==4): ?>
        <div class="step-indicator">
            <div class="step-number">4</div>
            <div class="step-title">Payment & Verification</div>
        </div>

        <form method="POST" enctype="multipart/form-data" id="step4Form">
            <input type="hidden" name="campaign_id" value="<?= $campaign_id ?>">

            <div class="form-group">
                <label>UPI ID</label>
                <input type="text" name="upi" id="upi" placeholder="yourname@upi">
                <div class="validation-message" id="upiError"></div>
            </div>

            <div class="form-group">
                <label>Bank Account Number</label>
                <input type="text" name="account_no" id="account_no" placeholder="1234567890">
            </div>

            <div class="form-group">
                <label>IFSC Code</label>
                <input type="text" name="ifsc" id="ifsc" placeholder="SBIN0001234" maxlength="11">
                <div class="validation-message" id="ifscError"></div>
            </div>

            <div class="form-group">
                <label>Account Holder Name</label>
                <input type="text" name="holder" id="holder" placeholder="Full name as per bank">
            </div>

            <div class="form-group">
                <label>ID Proof Type <span class="required">*</span></label>
                <select name="doc_type" required>
                    <option value="aadhaar">📇 Aadhaar Card</option>
                    <option value="pan">💳 PAN Card</option>
                    <option value="voter">🗳️ Voter ID</option>
                    <option value="passport">✈️ Passport</option>
                </select>
            </div>

            <div class="upload-box">
                <b>Upload ID Proof <span class="required" style="color: #ef4444;">*</span></b>
                <small>Clear photo or scan of your ID document (JPG, PNG, PDF - Max 5MB)</small>
                <input type="file" name="document" id="document" accept="image/*,application/pdf" required>
            </div>

            <button class="next-btn" name="submit_campaign" type="submit">🚀 Submit Campaign</button>
        </form>
        <?php endif; ?>

    </div>
</div>

<script>
// Character counters
document.addEventListener('DOMContentLoaded', function() {
    
    // Title counter
    const titleInput = document.getElementById('title');
    if(titleInput) {
        titleInput.addEventListener('input', function() {
            document.getElementById('titleCount').textContent = this.value.length;
            validateTitle();
        });
    }
    
    // Short description counter
    const shortInput = document.getElementById('short_desc');
    if(shortInput) {
        shortInput.addEventListener('input', function() {
            document.getElementById('shortCount').textContent = this.value.length;
            validateShortDesc();
        });
    }
    
    // Story counter
    const storyInput = document.getElementById('story');
    if(storyInput) {
        storyInput.addEventListener('input', function() {
            document.getElementById('storyCount').textContent = this.value.length;
            validateStory();
        });
    }
    
    // Goal validation
    const goalInput = document.getElementById('goal');
    if(goalInput) {
        goalInput.addEventListener('blur', validateGoal);
    }
    
    // Date validation
    const dateInput = document.getElementById('end_date');
    if(dateInput) {
        dateInput.addEventListener('change', validateDate);
    }
    
    // UPI validation
    const upiInput = document.getElementById('upi');
    if(upiInput) {
        upiInput.addEventListener('blur', validateUPI);
    }
    
    // IFSC validation
    const ifscInput = document.getElementById('ifsc');
    if(ifscInput) {
        ifscInput.addEventListener('blur', validateIFSC);
    }
    
    // File upload validation
    const thumbnailInput = document.getElementById('thumbnail');
    if(thumbnailInput) {
        thumbnailInput.addEventListener('change', function() {
            validateFile(this, 5 * 1024 * 1024, 'image');
        });
    }
    
    const imagesInput = document.getElementById('images');
    if(imagesInput) {
        imagesInput.addEventListener('change', function() {
            if(this.files.length > 10) {
                alert('Maximum 10 images allowed');
                this.value = '';
            }
        });
    }
    
    const videosInput = document.getElementById('videos');
    if(videosInput) {
        videosInput.addEventListener('change', function() {
            if(this.files.length > 4) {
                alert('Maximum 4 videos allowed');
                this.value = '';
            }
        });
    }
});

// Validation functions
function validateTitle() {
    const input = document.getElementById('title');
    const error = document.getElementById('titleError');
    
    if(input.value.trim().length < 10) {
        input.classList.add('invalid');
        input.classList.remove('valid');
        error.textContent = 'Title should be at least 10 characters';
        error.classList.add('error');
        return false;
    } else {
        input.classList.add('valid');
        input.classList.remove('invalid');
        error.textContent = 'Looks good!';
        error.classList.remove('error');
        error.classList.add('success');
        return true;
    }
}

function validateGoal() {
    const input = document.getElementById('goal');
    const error = document.getElementById('goalError');
    const value = parseInt(input.value);
    
    if(value < 1000) {
        input.classList.add('invalid');
        input.classList.remove('valid');
        error.textContent = 'Minimum goal is ₹1,000';
        error.classList.add('error');
        return false;
    } else if(value > 10000000) {
        input.classList.add('invalid');
        input.classList.remove('valid');
        error.textContent = 'Maximum goal is ₹1,00,00,000';
        error.classList.add('error');
        return false;
    } else {
        input.classList.add('valid');
        input.classList.remove('invalid');
        error.textContent = 'Valid amount';
        error.classList.remove('error');
        error.classList.add('success');
        return true;
    }
}

function validateDate() {
    const input = document.getElementById('end_date');
    const error = document.getElementById('dateError');
    const selectedDate = new Date(input.value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if(selectedDate < today) {
        input.classList.add('invalid');
        input.classList.remove('valid');
        error.textContent = 'End date cannot be in the past';
        error.classList.add('error');
        return false;
    } else {
        input.classList.add('valid');
        input.classList.remove('invalid');
        error.textContent = '';
        error.classList.remove('error');
        return true;
    }
}

function validateShortDesc() {
    const input = document.getElementById('short_desc');
    const error = document.getElementById('shortError');
    
    if(input.value.trim().length < 20) {
        input.classList.add('invalid');
        input.classList.remove('valid');
        error.textContent = 'Description should be at least 20 characters';
        error.classList.add('error');
        return false;
    } else {
        input.classList.add('valid');
        input.classList.remove('invalid');
        error.textContent = 'Perfect!';
        error.classList.remove('error');
        error.classList.add('success');
        return true;
    }
}

function validateStory() {
    const input = document.getElementById('story');
    const error = document.getElementById('storyError');
    
    if(input.value.trim().length < 100) {
        input.classList.add('invalid');
        input.classList.remove('valid');
        error.textContent = 'Story should be at least 100 characters for better engagement';
        error.classList.add('error');
        return false;
    } else {
        input.classList.add('valid');
        input.classList.remove('invalid');
        error.textContent = 'Great story!';
        error.classList.remove('error');
        error.classList.add('success');
        return true;
    }
}

function validateUPI() {
    const input = document.getElementById('upi');
    const error = document.getElementById('upiError');
    const upiPattern = /^[\w.-]+@[\w.-]+$/;
    
    if(input.value && !upiPattern.test(input.value)) {
        input.classList.add('invalid');
        input.classList.remove('valid');
        error.textContent = 'Invalid UPI format (e.g., name@upi)';
        error.classList.add('error');
        return false;
    } else if(input.value) {
        input.classList.add('valid');
        input.classList.remove('invalid');
        error.textContent = 'Valid UPI';
        error.classList.remove('error');
        error.classList.add('success');
        return true;
    }
    return true;
}

function validateIFSC() {
    const input = document.getElementById('ifsc');
    const error = document.getElementById('ifscError');
    const ifscPattern = /^[A-Z]{4}0[A-Z0-9]{6}$/;
    
    if(input.value && !ifscPattern.test(input.value)) {
        input.classList.add('invalid');
        input.classList.remove('valid');
        error.textContent = 'Invalid IFSC code format';
        error.classList.add('error');
        return false;
    } else if(input.value) {
        input.classList.add('valid');
        input.classList.remove('invalid');
        error.textContent = 'Valid IFSC';
        error.classList.remove('error');
        error.classList.add('success');
        return true;
    }
    return true;
}

function validateFile(input, maxSize, type) {
    const file = input.files[0];
    if(file) {
        if(file.size > maxSize) {
            alert(`File size should not exceed ${maxSize / (1024 * 1024)}MB`);
            input.value = '';
            return false;
        }
    }
    return true;
}
</script>

<?php require_once __DIR__."/../includes/footer.php"; ?>