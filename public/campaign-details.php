<?php
session_start();
require_once __DIR__."/../config/db.php";

$id = $_GET['id'] ?? null;
if(!$id){
die("Campaign not found");
}

/* ===== GET CAMPAIGN ===== */
$stmt=$pdo->prepare("SELECT * FROM campaigns WHERE id=? AND status='approved'");
$stmt->execute([$id]);
$c=$stmt->fetch(PDO::FETCH_ASSOC);

if(!$c){
die("Campaign not found or not approved");
}

/* ===== GET MEDIA ===== */
$m=$pdo->prepare("SELECT * FROM campaign_media WHERE campaign_id=?");
$m->execute([$id]);
$media=$m->fetchAll(PDO::FETCH_ASSOC);

$thumb=$c['thumbnail']; // default from campaign table
$images=[];
$videos=[];

foreach($media as $mm){
if($mm['media_type']=="thumbnail") $thumb=$mm['media_url'];
if($mm['media_type']=="image") $images[]=$mm['media_url'];
if($mm['media_type']=="video") $videos[]=$mm['media_url'];
}
?>

<?php require_once __DIR__."/../includes/header.php"; ?>

<style>
.wrap{max-width:1100px;margin:120px auto;padding:20px;}
.grid{display:grid;grid-template-columns:1.2fr .8fr;gap:40px}
.title{font-size:34px;font-weight:800;margin:10px 0}
.card{background:#fff;padding:25px;border-radius:18px;box-shadow:0 10px 40px rgba(0,0,0,.08)}
.media{width:100%;border-radius:16px;margin-bottom:15px}
.donate-btn{background:#f59e0b;color:#fff;border:none;padding:15px;width:100%;border-radius:12px;font-size:18px;font-weight:700;cursor:pointer}
</style>

<div class="wrap">
<div class="grid">

<!-- LEFT -->
<div>

<?php if($thumb): ?>
<img src="<?= $thumb ?>" class="media">
<?php endif; ?>

<h1 class="title"><?= htmlspecialchars($c['title']) ?></h1>

<p><b>Category:</b> <?= $c['category'] ?></p>
<p><b>Location:</b> <?= $c['location'] ?></p>
<p><b>Goal:</b> ₹<?= number_format($c['goal']) ?></p>

<p style="margin-top:15px"><?= nl2br(htmlspecialchars($c['short_desc'])) ?></p>

<h3 style="margin-top:25px">Story</h3>
<p><?= nl2br(htmlspecialchars($c['story'])) ?></p>

<!-- IMAGES -->
<?php if($images): ?>
<h3 style="margin-top:30px">Gallery</h3>
<?php foreach($images as $img): ?>
<img src="<?= $img ?>" class="media">
<?php endforeach; ?>
<?php endif; ?>

<!-- VIDEOS -->
<?php if($videos): ?>
<h3 style="margin-top:30px">Videos</h3>
<?php foreach($videos as $v): ?>
<video src="<?= $v ?>" controls class="media"></video>
<?php endforeach; ?>
<?php endif; ?>

</div>

<!-- RIGHT DONATE -->
<div>
<div class="card">

<h3>Support this campaign ❤️</h3>

<?php if(!isset($_SESSION['user_id'])): ?>
<p>Please login to donate</p>
<a href="/CroudSpark-X/user/login.php" class="donate-btn" style="display:block;text-align:center;text-decoration:none">Login to Donate</a>

<?php else: ?>

<form action="/CroudSpark-X/public/fake-payment.php" method="POST">
<input type="hidden" name="campaign_id" value="<?= $c['id'] ?>">

<label>Enter amount (₹)</label>
<input type="number" name="amount" required
style="width:100%;padding:12px;border:1px solid #ddd;border-radius:10px;margin:10px 0">

<button class="donate-btn">Donate Now 🚀</button>
</form>

<?php endif; ?>

</div>
</div>

</div>
</div>

<?php require_once __DIR__."/../includes/footer.php"; ?>
