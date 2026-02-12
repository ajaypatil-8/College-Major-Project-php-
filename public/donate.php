<?php
session_start();
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/header.php";

if(!isset($_GET['id'])){
    echo "<h2 style='text-align:center;margin:50px;'>Invalid campaign</h2>";
    exit;
}

$campaign_id = intval($_GET['id']);

// fetch campaign
$q = "SELECT * FROM campaigns WHERE id=$campaign_id AND status='approved'";
$res = mysqli_query($conn,$q);

if(mysqli_num_rows($res)==0){
    echo "<h2 style='text-align:center;margin:50px;'>Campaign not found</h2>";
    exit;
}

$campaign = mysqli_fetch_assoc($res);

$msg="";
if($_SERVER["REQUEST_METHOD"]=="POST"){

    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $amount = intval($_POST['amount']);

    if($amount < 1){
        $msg = "Invalid amount";
    }else{

        // insert donation
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;

        $stmt = $conn->prepare("INSERT INTO donations (user_id,campaign_id,amount) VALUES (?,?,?)");
        $stmt->bind_param("iii",$user_id,$campaign_id,$amount);
        $stmt->execute();

        // update raised amount
        $conn->query("UPDATE campaigns SET raised_amount = raised_amount + $amount WHERE id=$campaign_id");

        $msg = "Thank you! Donation successful.";
    }
}

$percent = 0;
if($campaign['goal_amount']>0){
    $percent = ($campaign['raised_amount']/$campaign['goal_amount'])*100;
}
?>

<section class="donation-hero">
    <h1>Make a Donation</h1>
    <p>Your support brings hope and real change to those in need.</p>
</section>

<section class="donation-layout">

```
<!-- LEFT -->
<div class="donation-card">
    <h2>Donation Details</h2>

    <?php if($msg): ?>
        <p style="color:green;font-weight:600;"><?php echo $msg; ?></p>
    <?php endif; ?>

    <form method="post">

        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="fullname" required>
        </div>

        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Enter Amount (₹)</label>
            <input type="number" name="amount" min="10" required>
        </div>

        <button type="submit" class="donate-main-btn">
            Donate Securely
        </button>

        <p class="secure-note">🔒 Demo donation (no real payment)</p>

    </form>
</div>

<!-- RIGHT -->
<div class="donation-summary">

    <div class="summary-image">
        <img src="/CroudSpark-X/uploads/<?php echo $campaign['image']; ?>" 
        style="width:100%;height:200px;object-fit:cover;border-radius:14px;">
    </div>

    <h3><?php echo $campaign['title']; ?></h3>

    <p class="summary-desc">
        <?php echo substr($campaign['description'],0,120); ?>
    </p>

    <div class="summary-progress">
        <span><strong>₹<?php echo number_format($campaign['raised_amount']); ?></strong> raised</span>
        <span>of ₹<?php echo number_format($campaign['goal_amount']); ?> goal</span>
    </div>

    <div class="progress-bar">
        <div class="progress-fill" style="width:<?php echo $percent; ?>%;"></div>
    </div>

    <div class="summary-trust">
        ✔ Verified Campaign<br>
        💙 Support this cause
    </div>

</div>
```

</section>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
