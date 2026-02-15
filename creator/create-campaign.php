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

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Campaign - CrowdSpark</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

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
    --border-hover: rgba(16, 185, 129, 0.3);
    
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
    --border-hover: rgba(16, 185, 129, 0.4);
    
    --orb-opacity: 0.25;
    
    --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.3);
    --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.4);
    --shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.5);
}

/* Green/Emerald accent colors - STAY CONSTANT */
:root,
[data-theme="dark"] {
    --accent-primary: #10b981;
    --accent-secondary: #34d399;
    --accent-gradient: linear-gradient(45deg, #10b981, #34d399);
    --orb-1: linear-gradient(45deg, #10b981, #34d399);
    --orb-2: linear-gradient(45deg, #059669, #10b981);
    --orb-3: linear-gradient(45deg, #047857, #059669);
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

/* Animated Background - Green/Emerald theme */
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
    background: var(--bg-card);
    backdrop-filter: blur(20px);
    border-bottom: 1px solid var(--border-color);
    padding: 24px 40px;
    display: flex;
    gap: 40px;
    font-weight: 700;
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: var(--shadow-sm);
    animation: slideInRight 0.6s ease-out;
    transition: all 0.3s ease;
}

.ks-header span {
    color: var(--text-secondary);
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
    background: var(--accent-gradient);
    transform: scaleX(0);
    transition: transform 0.3s ease;
    border-radius: 4px;
}

.ks-header .active {
    color: var(--accent-primary);
    background: rgba(16, 185, 129, 0.1);
}

.ks-header .active::before {
    transform: scaleX(1);
}

.ks-header span:hover {
    transform: translateY(-2px);
    color: var(--accent-primary);
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
    background: linear-gradient(45deg, var(--text-primary), var(--accent-primary));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    line-height: 1.2;
}

.ks-left p {
    color: var(--text-secondary);
    line-height: 1.8;
    margin-bottom: 20px;
    font-size: 1.05rem;
}

.tip-box {
    background: var(--bg-secondary);
    border-left: 4px solid var(--accent-primary);
    padding: 20px;
    border-radius: 12px;
    margin-top: 30px;
    animation: fadeInUp 1s ease-out;
    border: 1px solid var(--border-hover);
    transition: all 0.3s ease;
}

.tip-box h3 {
    color: var(--accent-primary);
    font-size: 1.1rem;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.tip-box ul {
    padding-left: 20px;
    color: var(--text-secondary);
}

.tip-box li {
    margin-bottom: 8px;
    font-size: 0.95rem;
}

/* ===== CARD ===== */
.ks-card {
    background: var(--bg-card);
    backdrop-filter: blur(20px);
    border: 1px solid var(--border-color);
    border-radius: 24px;
    padding: 50px;
    box-shadow: var(--shadow-md);
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
    background: var(--accent-gradient);
}

.ks-card:hover {
    box-shadow: var(--shadow-lg);
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
    color: var(--text-primary);
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
    border: 2px solid var(--border-color);
    background: var(--bg-secondary);
    font-family: 'DM Sans', sans-serif;
    font-size: 1rem;
    transition: all 0.3s ease;
    color: var(--text-primary);
}

input:focus, select:focus, textarea:focus {
    outline: none;
    border-color: var(--accent-primary);
    background: var(--bg-card-hover);
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
    transform: translateY(-2px);
}

input:hover, select:hover, textarea:hover {
    border-color: var(--border-hover);
}

textarea {
    min-height: 140px;
    resize: vertical;
}

input::placeholder, textarea::placeholder {
    color: var(--text-tertiary);
}

/* Character counter */
.char-counter {
    text-align: right;
    font-size: 0.85rem;
    color: var(--text-tertiary);
    margin-top: 6px;
}

/* ===== FILE UPLOAD ===== */
.upload-box {
    border: 3px dashed var(--border-hover);
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
    border-color: var(--accent-primary);
    background: rgba(16, 185, 129, 0.05);
    transform: scale(1.02);
}

.upload-box b {
    display: block;
    margin-bottom: 12px;
    font-size: 1.1rem;
    color: var(--text-primary);
}

.upload-box small {
    display: block;
    color: var(--text-secondary);
    margin-bottom: 20px;
    font-size: 0.9rem;
}

.upload-box input[type="file"] {
    display: none;
}

.custom-file-btn {
    display: inline-block;
    padding: 14px 28px;
    background: var(--accent-gradient);
    color: white;
    border-radius: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.custom-file-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
}

.file-count {
    margin-top: 12px;
    font-size: 14px;
    color: var(--text-secondary);
    font-weight: 600;
}

/* ===== MEDIA PREVIEW STYLES ===== */
.preview-container {
    margin-top: 20px;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
}

.preview-item {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    background: var(--bg-secondary);
    border: 2px solid var(--border-color);
    transition: all 0.3s ease;
}

.preview-item:hover {
    border-color: var(--accent-primary);
    transform: translateY(-4px);
    box-shadow: var(--shadow-md);
}

.preview-image, .preview-video {
    width: 100%;
    height: 200px;
    object-fit: cover;
    display: block;
}

.preview-controls {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
    padding: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.preview-item:hover .preview-controls {
    opacity: 1;
}

.preview-btn {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border: none;
    color: white;
    padding: 8px 12px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 700;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 6px;
}

.preview-btn:hover {
    background: var(--accent-primary);
    transform: scale(1.05);
}

.preview-btn.play {
    background: rgba(16, 185, 129, 0.8);
}

.preview-btn.play:hover {
    background: var(--accent-primary);
}

.preview-btn.delete {
    background: rgba(239, 68, 68, 0.8);
}

.preview-btn.delete:hover {
    background: #ef4444;
}

/* ===== CROP MODAL ===== */
.crop-modal {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.9);
    backdrop-filter: blur(10px);
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.crop-modal.active {
    display: flex;
}

.crop-container {
    background: var(--bg-card);
    border-radius: 24px;
    padding: 30px;
    max-width: 900px;
    width: 100%;
    max-height: 90vh;
    overflow: auto;
}

.crop-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.crop-header h3 {
    font-size: 1.5rem;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 10px;
}

.crop-header i {
    color: var(--accent-primary);
}

.crop-close {
    background: none;
    border: none;
    font-size: 2rem;
    color: var(--text-secondary);
    cursor: pointer;
    transition: all 0.3s ease;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.crop-close:hover {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    transform: rotate(90deg);
}

.crop-canvas-wrapper {
    position: relative;
    margin: 20px 0;
    border-radius: 16px;
    overflow: hidden;
    background: #000;
    max-height: 600px;
}

#cropCanvas {
    width: 100%;
    height: 600px;
    display: block;
    cursor: grab;
}

#cropCanvas:active {
    cursor: grabbing;
}

.crop-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
}

.crop-grid {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
}

.grid-line {
    position: absolute;
    background: rgba(255, 255, 255, 0.5);
}

.grid-line-v1, .grid-line-v2 {
    width: 1px;
    height: 100%;
    top: 0;
}

.grid-line-v1 { left: 33.33%; }
.grid-line-v2 { left: 66.66%; }

.grid-line-h1, .grid-line-h2 {
    height: 1px;
    width: 100%;
    left: 0;
}

.grid-line-h1 { top: 33.33%; }
.grid-line-h2 { top: 66.66%; }

.crop-frame {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border: 3px solid var(--accent-primary);
    box-shadow: 
        0 0 0 9999px rgba(0, 0, 0, 0.3),
        inset 0 0 0 2px rgba(255, 255, 255, 0.2);
}

.crop-instructions {
    text-align: center;
    padding: 14px 20px;
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(52, 211, 153, 0.05));
    border-radius: 12px;
    margin-bottom: 20px;
    color: var(--text-secondary);
    font-size: 14px;
    border: 1px solid rgba(16, 185, 129, 0.2);
}

.crop-instructions i {
    color: var(--accent-primary);
    margin-right: 6px;
}

.crop-instructions strong {
    color: var(--accent-primary);
    font-weight: 700;
}

.crop-controls {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin: 20px 0;
}

.control-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.control-group label {
    font-size: 12px;
    font-weight: 700;
    color: var(--text-secondary);
    text-transform: uppercase;
    display: flex;
    align-items: center;
    gap: 6px;
}

.control-group label i {
    color: var(--accent-primary);
}

.control-group input[type="range"] {
    width: 100%;
    height: 8px;
    border-radius: 4px;
    background: var(--border-color);
    outline: none;
    padding: 0;
    -webkit-appearance: none;
}

.control-group input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: var(--accent-gradient);
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
}

.control-group input[type="range"]::-moz-range-thumb {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: var(--accent-primary);
    cursor: pointer;
    border: none;
}

.crop-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 24px;
}

.crop-action-btn {
    padding: 14px 24px;
    border-radius: 12px;
    border: none;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: 'DM Sans', sans-serif;
    display: flex;
    align-items: center;
    gap: 8px;
}

.crop-action-btn i {
    font-size: 16px;
}

.crop-action-btn.cancel {
    background: var(--bg-secondary);
    color: var(--text-primary);
    border: 2px solid var(--border-color);
}

.crop-action-btn.cancel:hover {
    background: var(--border-color);
    border-color: #ef4444;
    color: #ef4444;
}

.crop-action-btn.reset {
    background: linear-gradient(135deg, #f59e0b, #fbbf24);
    color: white;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
}

.crop-action-btn.reset:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(245, 158, 11, 0.4);
}

.crop-action-btn.save {
    background: var(--accent-gradient);
    color: white;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.crop-action-btn.save:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
}

/* ===== VIDEO PREVIEW MODAL ===== */
.video-preview-modal {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.95);
    backdrop-filter: blur(10px);
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.video-preview-modal.active {
    display: flex;
}

.video-preview-container {
    background: var(--bg-card);
    border-radius: 24px;
    padding: 30px;
    max-width: 1000px;
    width: 100%;
    max-height: 90vh;
    overflow: auto;
}

.video-preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.video-preview-header h3 {
    font-size: 1.5rem;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 10px;
}

.video-preview-header i {
    color: var(--accent-primary);
}

.video-preview-close {
    background: none;
    border: none;
    font-size: 2rem;
    color: var(--text-secondary);
    cursor: pointer;
    transition: all 0.3s ease;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.video-preview-close:hover {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    transform: rotate(90deg);
}

.video-preview-wrapper {
    position: relative;
    border-radius: 16px;
    overflow: hidden;
    background: #000;
}

#previewVideo {
    width: 100%;
    max-height: 70vh;
    display: block;
}

/* ===== BUTTONS ===== */
.next-btn {
    background: var(--accent-gradient);
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
    background: var(--accent-gradient);
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
    border-color: var(--accent-primary);
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
    color: var(--accent-primary);
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
    background: var(--accent-gradient);
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
    color: var(--text-primary);
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
    
    .preview-container {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }
    
    .preview-controls {
        opacity: 1;
    }
}
</style>
</head>

<body>

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
                <label for="thumbnail" class="custom-file-btn">
                    <i class="fa fa-upload"></i> Choose Thumbnail
                </label>
                <input type="file" name="thumbnail" id="thumbnail" accept="image/*" required>
                <div class="file-count" id="thumbnailCount"></div>
                <div id="thumbnailPreview" class="preview-container"></div>
            </div>

            <div class="upload-box">
                <b>Additional Images</b>
                <small>Upload up to 10 images (JPG, PNG - Max 5MB each)</small>
                <label for="images" class="custom-file-btn">
                    <i class="fa fa-images"></i> Choose Images
                </label>
                <input type="file" name="images[]" id="images" accept="image/*" multiple>
                <div class="file-count" id="imagesCount"></div>
                <div id="imagesPreview" class="preview-container"></div>
            </div>

            <div class="upload-box">
                <b>Videos</b>
                <small>Upload up to 4 videos (MP4, MOV - Max 50MB each)</small>
                <label for="videos" class="custom-file-btn">
                    <i class="fa fa-video"></i> Choose Videos
                </label>
                <input type="file" name="videos[]" id="videos" accept="video/*" multiple>
                <div class="file-count" id="videosCount"></div>
                <div id="videosPreview" class="preview-container"></div>
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
                <label for="document" class="custom-file-btn">
                    <i class="fa fa-id-card"></i> Choose Document
                </label>
                <input type="file" name="document" id="document" accept="image/*,application/pdf" required>
                <div class="file-count" id="documentCount"></div>
                <div id="documentPreview" class="preview-container"></div>
            </div>

            <button class="next-btn" name="submit_campaign" type="submit">🚀 Submit Campaign</button>
        </form>
        <?php endif; ?>

    </div>
</div>

<script>
// Theme System
function getTheme() {
    return localStorage.getItem('crowdspark-theme') || 'light';
}

function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('crowdspark-theme', theme);
}

function toggleTheme() {
    const currentTheme = getTheme();
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    setTheme(newTheme);
}

(function() {
    const savedTheme = getTheme();
    setTheme(savedTheme);
})();

window.CrowdSparkTheme = {
    toggle: toggleTheme,
    set: setTheme,
    get: getTheme
};

// Media Uploader Class
class MediaUploader {
    constructor(inputId, previewId, countId, maxFiles = 10) {
        this.input = document.getElementById(inputId);
        this.previewContainer = document.getElementById(previewId);
        this.countElement = document.getElementById(countId);
        this.maxFiles = maxFiles;
        this.files = [];
        this.currentEditIndex = null;
        this.offsetX = 0;
        this.offsetY = 0;
        this.isDragging = false;
        
        if (this.input && this.previewContainer) {
            this.init();
        }
    }
    
    init() {
        this.input.addEventListener('change', (e) => this.handleFileSelect(e));
        if (!document.getElementById('cropModal')) {
            this.createCropModal();
        }
    }
    
    handleFileSelect(event) {
        const newFiles = Array.from(event.target.files);
        
        if (this.files.length + newFiles.length > this.maxFiles) {
            alert(`Maximum ${this.maxFiles} files allowed`);
            return;
        }
        
        newFiles.forEach(file => {
            const isVideo = file.type.startsWith('video/');
            const maxSize = isVideo ? 50 * 1024 * 1024 : 5 * 1024 * 1024;
            const maxSizeLabel = isVideo ? '50MB' : '5MB';
            
            if (file.size > maxSize) {
                alert(`${file.name} is too large. Maximum size is ${maxSizeLabel}`);
                return;
            }
            
            this.files.push({
                file: file,
                preview: URL.createObjectURL(file),
                cropped: null
            });
        });
        
        this.renderPreviews();
        event.target.value = '';
    }
    
    renderPreviews() {
        this.previewContainer.innerHTML = '';
        
        this.files.forEach((fileObj, index) => {
            const previewItem = document.createElement('div');
            previewItem.className = 'preview-item';
            
            const isVideo = fileObj.file.type.startsWith('video/');
            
            if (isVideo) {
                const video = document.createElement('video');
                video.src = fileObj.preview;
                video.className = 'preview-video';
                video.controls = false;
                video.muted = true;
                previewItem.appendChild(video);
            } else {
                const img = document.createElement('img');
                img.src = fileObj.cropped || fileObj.preview;
                img.className = 'preview-image';
                previewItem.appendChild(img);
            }
            
            const controls = document.createElement('div');
            controls.className = 'preview-controls';
            controls.innerHTML = `
                ${isVideo ? `<button type="button" class="preview-btn play" data-index="${index}">
                    <i class="fa fa-play"></i> Play
                </button>` : `<button type="button" class="preview-btn edit" data-index="${index}">
                    <i class="fa fa-crop"></i> Edit
                </button>`}
                <button type="button" class="preview-btn delete" data-index="${index}">
                    <i class="fa fa-trash"></i> Delete
                </button>
            `;
            const editBtn = controls.querySelector('.preview-btn.edit');
            if (editBtn) {
                editBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.openCropModal(index);
                });
            }
            
            const playBtn = controls.querySelector('.preview-btn.play');
            if (playBtn) {
                playBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.openVideoPreview(index);
                });
            }
            
            const deleteBtn = controls.querySelector('.preview-btn.delete');
            if (deleteBtn) {
                deleteBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.deleteFile(index);
                });
            }
            
            previewItem.appendChild(controls);
            this.previewContainer.appendChild(previewItem);
        });
        
        if (this.countElement) {
            this.countElement.textContent = `${this.files.length} file(s) selected`;
        }
    }
    
    deleteFile(index) {
        URL.revokeObjectURL(this.files[index].preview);
        if (this.files[index].cropped) {
            URL.revokeObjectURL(this.files[index].cropped);
        }
        this.files.splice(index, 1);
        this.renderPreviews();
    }
    
    createCropModal() {
        if (document.getElementById('cropModal')) return;
        
        const modal = document.createElement('div');
        modal.className = 'crop-modal';
        modal.id = 'cropModal';
        modal.innerHTML = `
            <div class="crop-container">
                <div class="crop-header">
                    <h3><i class="fa fa-crop-alt"></i> Crop & Adjust Image</h3>
                    <button type="button" class="crop-close">&times;</button>
                </div>
                
                <div class="crop-instructions">
                    <i class="fa fa-info-circle"></i> 
                    <strong>Drag</strong> to reposition • 
                    <strong>Zoom</strong> to scale • 
                    <strong>Rotate</strong> to adjust angle
                </div>
                
                <div class="crop-canvas-wrapper">
                    <canvas id="cropCanvas"></canvas>
                    <div class="crop-overlay">
                        <div class="crop-grid">
                            <div class="grid-line grid-line-v1"></div>
                            <div class="grid-line grid-line-v2"></div>
                            <div class="grid-line grid-line-h1"></div>
                            <div class="grid-line grid-line-h2"></div>
                        </div>
                        <div class="crop-frame"></div>
                    </div>
                </div>
                
                <div class="crop-controls">
                    <div class="control-group">
                        <label><i class="fa fa-search-plus"></i> Zoom: <span id="zoomValue">1.0</span>x</label>
                        <input type="range" id="zoomSlider" min="0.5" max="3" step="0.05" value="1">
                    </div>
                    <div class="control-group">
                        <label><i class="fa fa-rotate"></i> Rotate: <span id="rotateValue">0</span>°</label>
                        <input type="range" id="rotateSlider" min="0" max="360" step="1" value="0">
                    </div>
                    <div class="control-group">
                        <label><i class="fa fa-sun"></i> Brightness: <span id="brightnessValue">100</span>%</label>
                        <input type="range" id="brightnessSlider" min="50" max="150" step="1" value="100">
                    </div>
                </div>
                
                <div class="crop-actions">
                    <button type="button" class="crop-action-btn cancel"><i class="fa fa-times"></i> Cancel</button>
                    <button type="button" class="crop-action-btn reset"><i class="fa fa-undo"></i> Reset</button>
                    <button type="button" class="crop-action-btn save"><i class="fa fa-check"></i> Save Changes</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        modal.querySelector('.crop-close').addEventListener('click', () => this.closeCropModal());
        modal.querySelector('.cancel').addEventListener('click', () => this.closeCropModal());
        modal.querySelector('.reset').addEventListener('click', () => this.resetCrop());
        modal.querySelector('.save').addEventListener('click', () => this.saveCrop());
        
        const zoomSlider = document.getElementById('zoomSlider');
        const rotateSlider = document.getElementById('rotateSlider');
        const brightnessSlider = document.getElementById('brightnessSlider');
        
        zoomSlider.addEventListener('input', (e) => {
            document.getElementById('zoomValue').textContent = parseFloat(e.target.value).toFixed(1);
            this.updateCanvas();
        });
        
        rotateSlider.addEventListener('input', (e) => {
            document.getElementById('rotateValue').textContent = e.target.value;
            this.updateCanvas();
        });
        
        brightnessSlider.addEventListener('input', (e) => {
            document.getElementById('brightnessValue').textContent = e.target.value;
            this.updateCanvas();
        });
        
        // Create video preview modal
        const videoModal = document.createElement('div');
        videoModal.className = 'video-preview-modal';
        videoModal.id = 'videoPreviewModal';
        videoModal.innerHTML = `
            <div class="video-preview-container">
                <div class="video-preview-header">
                    <h3><i class="fa fa-play-circle"></i> Video Preview</h3>
                    <button type="button" class="video-preview-close">&times;</button>
                </div>
                <div class="video-preview-wrapper">
                    <video id="previewVideo" controls></video>
                </div>
            </div>
        `;
        document.body.appendChild(videoModal);
        
        videoModal.querySelector('.video-preview-close').addEventListener('click', () => this.closeVideoPreview());
        videoModal.addEventListener('click', (e) => {
            if (e.target === videoModal) this.closeVideoPreview();
        });
    }
    
    openCropModal(index) {
        this.currentEditIndex = index;
        const modal = document.getElementById('cropModal');
        const canvas = document.getElementById('cropCanvas');
        
        this.offsetX = 0;
        this.offsetY = 0;
        this.isDragging = false;
        
        const img = new Image();
        img.onload = () => {
            this.currentImage = img;
            canvas.width = 800;
            canvas.height = 600;
            this.updateCanvas();
            this.setupDragging(canvas);
        };
        img.src = this.files[index].preview;
        
        document.getElementById('zoomSlider').value = 1;
        document.getElementById('rotateSlider').value = 0;
        document.getElementById('brightnessSlider').value = 100;
        document.getElementById('zoomValue').textContent = '1.0';
        document.getElementById('rotateValue').textContent = '0';
        document.getElementById('brightnessValue').textContent = '100';
        
        modal.classList.add('active');
    }
    
    setupDragging(canvas) {
        let startX, startY;
        
        canvas.addEventListener('mousedown', (e) => {
            this.isDragging = true;
            startX = e.offsetX - this.offsetX;
            startY = e.offsetY - this.offsetY;
        });
        
        canvas.addEventListener('mousemove', (e) => {
            if (!this.isDragging) return;
            this.offsetX = e.offsetX - startX;
            this.offsetY = e.offsetY - startY;
            this.updateCanvas();
        });
        
        canvas.addEventListener('mouseup', () => {
            this.isDragging = false;
        });
        
        canvas.addEventListener('mouseleave', () => {
            this.isDragging = false;
        });
    }
    
    closeCropModal() {
        document.getElementById('cropModal').classList.remove('active');
        this.currentEditIndex = null;
        this.currentImage = null;
        this.offsetX = 0;
        this.offsetY = 0;
    }
    
    updateCanvas() {
        const canvas = document.getElementById('cropCanvas');
        const ctx = canvas.getContext('2d');
        const zoom = parseFloat(document.getElementById('zoomSlider').value);
        const rotate = parseInt(document.getElementById('rotateSlider').value);
        const brightness = parseInt(document.getElementById('brightnessSlider').value);
        
        ctx.fillStyle = '#000';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        
        ctx.save();
        ctx.translate(canvas.width / 2 + this.offsetX, canvas.height / 2 + this.offsetY);
        ctx.rotate((rotate * Math.PI) / 180);
        ctx.filter = `brightness(${brightness}%)`;
        
        const scaledWidth = this.currentImage.width * zoom;
        const scaledHeight = this.currentImage.height * zoom;
        
        ctx.drawImage(
            this.currentImage,
            -scaledWidth / 2,
            -scaledHeight / 2,
            scaledWidth,
            scaledHeight
        );
        
        ctx.restore();
    }
    
    resetCrop() {
        this.offsetX = 0;
        this.offsetY = 0;
        document.getElementById('zoomSlider').value = 1;
        document.getElementById('rotateSlider').value = 0;
        document.getElementById('brightnessSlider').value = 100;
        document.getElementById('zoomValue').textContent = '1.0';
        document.getElementById('rotateValue').textContent = '0';
        document.getElementById('brightnessValue').textContent = '100';
        this.updateCanvas();
    }
    
    saveCrop() {
        if (this.currentEditIndex === null) return;
        
        const canvas = document.getElementById('cropCanvas');
        canvas.toBlob((blob) => {
            const croppedUrl = URL.createObjectURL(blob);
            
            if (this.files[this.currentEditIndex].cropped) {
                URL.revokeObjectURL(this.files[this.currentEditIndex].cropped);
            }
            
            this.files[this.currentEditIndex].cropped = croppedUrl;
            this.files[this.currentEditIndex].croppedBlob = blob;
            
            this.renderPreviews();
            this.closeCropModal();
        }, 'image/jpeg', 0.9);
    }
    
    openVideoPreview(index) {
        const modal = document.getElementById('videoPreviewModal');
        const video = document.getElementById('previewVideo');
        video.src = this.files[index].preview;
        modal.classList.add('active');
        video.play();
    }
    
    closeVideoPreview() {
        const modal = document.getElementById('videoPreviewModal');
        const video = document.getElementById('previewVideo');
        video.pause();
        video.src = '';
        modal.classList.remove('active');
    }
}

// Initialize uploaders
document.addEventListener('DOMContentLoaded', function() {
    window.thumbnailUploader = new MediaUploader('thumbnail', 'thumbnailPreview', 'thumbnailCount', 1);
    window.imagesUploader = new MediaUploader('images', 'imagesPreview', 'imagesCount', 10);
    window.videosUploader = new MediaUploader('videos', 'videosPreview', 'videosCount', 4);
    window.documentUploader = new MediaUploader('document', 'documentPreview', 'documentCount', 1);
    
    // Handle Step 2 form submission
    const step2Form = document.getElementById('step2Form');
    if(step2Form) {
        step2Form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Check if thumbnail exists
            if(window.thumbnailUploader.files.length === 0) {
                alert('Please upload a campaign thumbnail');
                return false;
            }
            
            // Create FormData
            const formData = new FormData(this);
            formData.set('step2', '1');
            
            // Remove old file inputs from FormData
            formData.delete('thumbnail');
            formData.delete('images[]');
            formData.delete('videos[]');
            
            // Add thumbnail (cropped or original)
            if(window.thumbnailUploader.files[0]) {
                const thumbFile = window.thumbnailUploader.files[0].croppedBlob || window.thumbnailUploader.files[0].file;
                formData.append('thumbnail', thumbFile, 'thumbnail.jpg');
            }
            
            // Add additional images (cropped or original)
            window.imagesUploader.files.forEach((fileObj, index) => {
                const imgFile = fileObj.croppedBlob || fileObj.file;
                formData.append('images[]', imgFile, `image_${index}.jpg`);
            });
            
            // Add videos
            window.videosUploader.files.forEach((fileObj, index) => {
                formData.append('videos[]', fileObj.file, fileObj.file.name);
            });
            
            // Show loading state
            const submitBtn = this.querySelector('.next-btn');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Uploading...';
            
            // Submit via AJAX
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(async response => {
                if (response.redirected || (response.url && response.url.includes('step=3'))) {
                    window.location.href = response.url || ('create-campaign.php?step=3&id=' + formData.get('campaign_id'));
                    return;
                }
                
                const html = await response.text();
                document.body.innerHTML = html;
            })
            .catch(error => {
                alert('Upload failed. Please try again.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }
    
    // Character counters
    const titleInput = document.getElementById('title');
    if(titleInput) {
        titleInput.addEventListener('input', function() {
            document.getElementById('titleCount').textContent = this.value.length;
            validateTitle();
        });
    }
    
    const shortInput = document.getElementById('short_desc');
    if(shortInput) {
        shortInput.addEventListener('input', function() {
            document.getElementById('shortCount').textContent = this.value.length;
            validateShortDesc();
        });
    }
    
    const storyInput = document.getElementById('story');
    if(storyInput) {
        storyInput.addEventListener('input', function() {
            document.getElementById('storyCount').textContent = this.value.length;
            validateStory();
        });
    }
    
    const goalInput = document.getElementById('goal');
    if(goalInput) {
        goalInput.addEventListener('blur', validateGoal);
    }
    
    const dateInput = document.getElementById('end_date');
    if(dateInput) {
        dateInput.addEventListener('change', validateDate);
    }
    
    const upiInput = document.getElementById('upi');
    if(upiInput) {
        upiInput.addEventListener('blur', validateUPI);
    }
    
    const ifscInput = document.getElementById('ifsc');
    if(ifscInput) {
        ifscInput.addEventListener('blur', validateIFSC);
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
</script>

</body>
</html>

<?php require_once __DIR__."/../includes/footer.php"; ?>
