<?php
session_start();
require_once __DIR__."/../config/db.php";
require_once __DIR__."/../includes/header.php";

// login required
if(!isset($_SESSION['user_id'])){
 header("Location: /CroudSpark-X/public/login.php");
 exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM campaigns WHERE user_id=? ORDER BY id DESC");
$stmt->execute([$user_id]);
$campaigns = $stmt->fetchAll();
?>

<section class="explore-hero">
    <h1>My Campaigns</h1>
    <p>Track and manage all your fundraising campaigns.</p>
</section>

<section class="my-campaigns-ui">

<?php if(count($campaigns) == 0): ?>
    <p style="text-align:center;">No campaigns created yet.</p>
<?php endif; ?>

<?php foreach($campaigns as $c): ?>

<div class="campaign-row">

    <div class="campaign-info">
        <h3><?= htmlspecialchars($c['title']) ?></h3>
        <p><?= htmlspecialchars($c['short_desc']) ?></p>
    </div>

    <div class="campaign-meta">
        <div class="meta-item">
            <span>Goal</span>
            <strong>₹<?= number_format($c['goal']) ?></strong>
        </div>

        <div class="meta-item">
            <span>Status</span>
            <?php if($c['status']=="approved"): ?>
                <span class="status approved">Approved</span>
            <?php elseif($c['status']=="rejected"): ?>
                <span class="status rejected">Rejected</span>
            <?php else: ?>
                <span class="status pending">Pending</span>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php endforeach; ?>

</section>

<?php require_once __DIR__."/../includes/footer.php"; ?>
