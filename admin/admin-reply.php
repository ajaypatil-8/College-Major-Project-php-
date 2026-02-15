<?php
session_start();
require_once __DIR__."/../config/db.php";
require_once __DIR__."/../config/env.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__."/../vendor/phpmailer/src/PHPMailer.php";
require __DIR__."/../vendor/phpmailer/src/SMTP.php";
require __DIR__."/../vendor/phpmailer/src/Exception.php";

/* ===== ADMIN LOGIN CHECK ===== */
if(!isset($_SESSION['user_id']) || $_SESSION['role']!='admin'){
    header("Location: login.php");
    exit;
}

$msg="";
$success="";

/* ===== SEND REPLY ===== */
if(isset($_POST['send_reply'])){

    $id = $_POST['msg_id'];
    $reply = trim($_POST['reply']);

    if(empty($reply)){
        $msg="Reply cannot be empty";
    }else{

        $stmt=$pdo->prepare("SELECT * FROM contact_messages WHERE id=?");
        $stmt->execute([$id]);
        $data=$stmt->fetch();

        if($data){

            $user_email=$data['email'];
            $user_name=$data['name'];

            $mail = new PHPMailer(true);

            try{
                $mail->isSMTP();
                $mail->Host       = $_ENV['MAIL_HOST'];
                $mail->SMTPAuth   = true;
                $mail->Username   = $_ENV['MAIL_USER'];
                $mail->Password   = $_ENV['MAIL_PASS'];
                $mail->SMTPSecure = 'tls';
                $mail->Port       = $_ENV['MAIL_PORT'];

                $mail->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME']);
                $mail->addAddress($user_email);

                $mail->isHTML(true);
                $mail->Subject = "Reply from CrowdSpark Support";
                $mail->Body    = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 40px 20px;'>
                    <div style='background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); padding: 40px; border-radius: 20px; text-align: center;'>
                        <h1 style='color: white; margin: 0 0 20px 0; font-size: 32px;'>💬 Support Reply</h1>
                        <div style='background: white; padding: 30px; border-radius: 15px; margin: 20px 0; text-align: left;'>
                            <p style='color: #334155; margin: 0 0 20px 0; font-size: 16px;'><strong>Hello $user_name,</strong></p>
                            <p style='color: #64748b; margin: 0; font-size: 15px; line-height: 1.6;'>$reply</p>
                        </div>
                        <p style='color: rgba(255,255,255,0.9); margin: 20px 0 0 0; font-size: 14px;'>Best regards,<br>CrowdSpark Support Team</p>
                    </div>
                </div>
                ";

                $mail->send();

                // update DB
                $up=$pdo->prepare("UPDATE contact_messages 
                SET admin_reply=?, status='replied', replied_at=NOW() 
                WHERE id=?");
                $up->execute([$reply,$id]);

                $success="Reply sent successfully!";

            }catch(Exception $e){
                $msg="Mail failed: ".$mail->ErrorInfo;
            }
        }
    }
}

/* ===== FETCH ALL MESSAGES ===== */
$stmt=$pdo->query("SELECT * FROM contact_messages ORDER BY id DESC");
$messages=$stmt->fetchAll();

$pendingCount = 0;
$repliedCount = 0;
foreach($messages as $m) {
    if($m['status'] == 'pending') $pendingCount++;
    else $repliedCount++;
}
?>

<?php require_once __DIR__."/../includes/header.php"; ?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@400;500;600;700;800;900&display=swap');

/* ===== THEME VARIABLES ===== */
:root {
    /* Light Theme */
    --bg-primary: #ffffff;
    --bg-secondary: #f8fafc;
    --bg-card: rgba(255, 255, 255, 0.9);
    --bg-card-hover: rgba(255, 255, 255, 0.95);
    
    --text-primary: #0f172a;
    --text-secondary: #475569;
    --text-tertiary: #64748b;
    
    --border-color: rgba(15, 23, 42, 0.1);
    --border-hover: rgba(239, 68, 68, 0.3);
    
    --orb-opacity: 0.25;
    
    --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
    --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.12);
}

[data-theme="dark"] {
    /* Dark Theme */
    --bg-primary: #0f0f0f;
    --bg-secondary: #1a1a1a;
    --bg-card: rgba(20, 20, 30, 0.85);
    --bg-card-hover: rgba(30, 30, 40, 0.9);
    
    --text-primary: #ffffff;
    --text-secondary: #cbd5e1;
    --text-tertiary: #94a3b8;
    
    --border-color: rgba(255, 255, 255, 0.15);
    --border-hover: rgba(239, 68, 68, 0.4);
    
    --orb-opacity: 0.25;
    
    --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.3);
    --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.4);
    --shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.5);
}

/* Red/Pink orbs for Admin - visible on both themes */
[data-theme="dark"] {
    --orb-1: linear-gradient(45deg, #ef4444, #f87171);
    --orb-2: linear-gradient(45deg, #ec4899, #f472b6);
    --orb-3: linear-gradient(45deg, #dc2626, #ef4444);
}

[data-theme="light"] {
    --orb-1: linear-gradient(45deg, #ef4444, #f87171);
    --orb-2: linear-gradient(45deg, #ec4899, #f472b6);
    --orb-3: linear-gradient(45deg, #dc2626, #ef4444);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg-primary);
    color: var(--text-primary);
    overflow-x: hidden;
    position: relative;
    transition: background-color 0.3s ease, color 0.3s ease;
}

/* Animated Background - Red/Pink theme */
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
    animation-delay: 0s;
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

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-10px); }
    75% { transform: translateX(10px); }
}

@keyframes shimmer {
    0% { background-position: -1000px 0; }
    100% { background-position: 1000px 0; }
}

/* Container */
.admin-container {
    position: relative;
    z-index: 1;
    max-width: 1200px;
    margin: 0 auto;
    padding: 120px 40px 80px;
}

/* Page Header */
.page-header {
    margin-bottom: 40px;
    animation: fadeInUp 0.8s ease;
}

.page-title {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2.5rem, 6vw, 3.5rem);
    font-weight: 900;
    background: linear-gradient(135deg, var(--text-primary), #ef4444);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin: 0 0 10px;
}

.page-subtitle {
    color: var(--text-secondary);
    font-size: 1.1rem;
    font-weight: 500;
}

/* Stats Row */
.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    background: var(--bg-card);
    backdrop-filter: blur(20px);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    padding: 24px;
    text-align: center;
    position: relative;
    overflow: hidden;
    transition: all 0.4s ease;
    animation: fadeInUp 0.6s ease both;
}

.stat-card:nth-child(1) { animation-delay: 0.1s; }
.stat-card:nth-child(2) { animation-delay: 0.2s; }

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #ef4444, #dc2626);
}

.stat-card:hover {
    transform: translateY(-5px);
    border-color: var(--border-hover);
}

.stat-number {
    font-size: 36px;
    font-weight: 900;
    color: var(--text-primary);
    margin-bottom: 8px;
}

.stat-label {
    color: var(--text-secondary);
    font-size: 14px;
    font-weight: 600;
}

/* Alert Messages */
.alert {
    padding: 16px 24px;
    border-radius: 16px;
    font-size: 14px;
    font-weight: 600;
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
    color: #ef4444;
    border-left: 4px solid #ef4444;
}

[data-theme="light"] .alert-error {
    background: rgba(239, 68, 68, 0.15);
    color: #dc2626;
}

.alert-success {
    background: rgba(16, 185, 129, 0.2);
    color: #10b981;
    border-left: 4px solid #10b981;
}

[data-theme="light"] .alert-success {
    background: rgba(16, 185, 129, 0.15);
    color: #059669;
}

/* Messages Grid */
.messages-grid {
    display: grid;
    gap: 24px;
}

.message-card {
    background: var(--bg-card);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    padding: 32px;
    border: 1px solid var(--border-color);
    transition: all 0.4s ease;
    position: relative;
    overflow: hidden;
    animation: fadeInUp 0.6s ease both;
}

.message-card::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: linear-gradient(135deg, #ef4444, #dc2626);
}

.message-card:hover {
    transform: translateY(-4px);
    border-color: var(--border-hover);
}

.message-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 12px;
}

.message-user {
    flex: 1;
}

.user-name {
    font-size: 18px;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: 4px;
}

.user-email {
    color: var(--text-tertiary);
    font-size: 14px;
}

.status-badge {
    padding: 8px 16px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
}

.status-pending {
    background: rgba(245, 158, 11, 0.2);
    color: #f59e0b;
}

.status-replied {
    background: rgba(16, 185, 129, 0.2);
    color: #10b981;
}

.message-subject {
    font-size: 16px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 12px;
}

.message-content {
    color: var(--text-secondary);
    line-height: 1.7;
    margin-bottom: 24px;
    padding: 20px;
    background: var(--bg-secondary);
    border-radius: 12px;
    border-left: 3px solid rgba(239, 68, 68, 0.3);
}

.reply-section {
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid var(--border-color);
}

.reply-label {
    font-size: 14px;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.reply-label i {
    color: #10b981;
}

.reply-text {
    color: var(--text-secondary);
    line-height: 1.7;
    padding: 16px;
    background: rgba(16, 185, 129, 0.1);
    border-radius: 12px;
    margin-bottom: 8px;
}

.reply-timestamp {
    font-size: 12px;
    color: var(--text-tertiary);
}

/* Reply Form */
.reply-form {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.reply-textarea {
    width: 100%;
    min-height: 140px;
    padding: 16px;
    border-radius: 12px;
    border: 2px solid var(--border-color);
    background: var(--bg-secondary);
    color: var(--text-primary);
    font-size: 15px;
    font-weight: 500;
    font-family: 'DM Sans', sans-serif;
    resize: vertical;
    transition: all 0.3s ease;
}

.reply-textarea::placeholder {
    color: var(--text-tertiary);
}

.reply-textarea:focus {
    outline: none;
    border-color: #ef4444;
    box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15);
}

.btn-send {
    padding: 16px 32px;
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #fff;
    font-weight: 800;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    align-self: flex-start;
}

.btn-send:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 80px 20px;
    color: var(--text-tertiary);
}

.empty-state i {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.5;
    color: #ef4444;
}

.empty-state h3 {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 8px;
    color: var(--text-secondary);
}

/* Responsive */
@media (max-width: 768px) {
    .admin-container {
        padding: 100px 20px 60px;
    }
    
    .page-title {
        font-size: 2.5rem;
    }
    
    .message-card {
        padding: 24px 20px;
    }
}
</style>

<!-- Background Animation -->
<div class="bg-animation">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
</div>

<div class="admin-container">

    <!-- PAGE HEADER -->
    <div class="page-header">
        <h1 class="page-title">📩 Support Inbox</h1>
        <p class="page-subtitle">Manage and respond to user messages</p>
    </div>

    <!-- STATS -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-number"><?= $pendingCount ?></div>
            <div class="stat-label">Pending Messages</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $repliedCount ?></div>
            <div class="stat-label">Replied Messages</div>
        </div>
    </div>

    <!-- ALERT MESSAGES -->
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

    <!-- MESSAGES -->
    <?php if(empty($messages)): ?>
    <div class="empty-state">
        <i class="fa-solid fa-inbox"></i>
        <h3>No messages yet</h3>
        <p>User messages will appear here</p>
    </div>
    <?php else: ?>
    
    <div class="messages-grid">
        <?php foreach($messages as $m): ?>
        <div class="message-card">
            
            <div class="message-header">
                <div class="message-user">
                    <div class="user-name">
                        <i class="fa-solid fa-user"></i>
                        <?= htmlspecialchars($m['name']) ?>
                    </div>
                    <div class="user-email">
                        <i class="fa-solid fa-envelope"></i>
                        <?= htmlspecialchars($m['email']) ?>
                    </div>
                </div>
                <span class="status-badge status-<?= $m['status'] ?>">
                    <?= strtoupper($m['status']) ?>
                </span>
            </div>

            <div class="message-subject">
                <i class="fa-solid fa-comments"></i>
                <?= htmlspecialchars($m['subject']) ?>
            </div>

            <div class="message-content">
                <?= nl2br(htmlspecialchars($m['message'])) ?>
            </div>

            <?php if($m['admin_reply']): ?>
            
            <div class="reply-section">
                <div class="reply-label">
                    <i class="fa-solid fa-check-circle"></i>
                    Your Reply
                </div>
                <div class="reply-text">
                    <?= nl2br(htmlspecialchars($m['admin_reply'])) ?>
                </div>
                <div class="reply-timestamp">
                    <i class="fa-solid fa-clock"></i>
                    Sent on <?= date('d M Y, h:i A', strtotime($m['replied_at'])) ?>
                </div>
            </div>

            <?php else: ?>

            <div class="reply-section">
                <form method="POST" class="reply-form">
                    <input type="hidden" name="msg_id" value="<?= $m['id'] ?>">
                    
                    <div class="reply-label">
                        <i class="fa-solid fa-reply"></i>
                        Write Reply
                    </div>
                    
                    <textarea 
                        name="reply" 
                        class="reply-textarea"
                        placeholder="Type your reply here..." 
                        required
                    ></textarea>
                    
                    <button name="send_reply" class="btn-send">
                        <i class="fa-solid fa-paper-plane"></i>
                        Send Reply
                    </button>
                </form>
            </div>

            <?php endif; ?>

        </div>
        <?php endforeach; ?>
    </div>
    
    <?php endif; ?>

</div>

<?php require_once __DIR__."/../includes/footer.php"; ?>