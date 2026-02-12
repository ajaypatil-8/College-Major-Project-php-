<?php
session_start();
require_once __DIR__."/../config/db.php";
require_once __DIR__."/../includes/header.php";

if(!isset($_SESSION['user_id'])){
    header("Location: /CroudSpark-X/public/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* ===== FETCH DONATIONS ===== */
$stmt = $pdo->prepare("
    SELECT d.*, c.title 
    FROM donations d
    JOIN campaigns c ON d.campaign_id = c.id
    WHERE d.user_id = ?
    ORDER BY d.created_at DESC
");
$stmt->execute([$user_id]);
$donations = $stmt->fetchAll();
?>

<section class="explore-hero">
    <h1>My Donations</h1>
    <p>Complete history of your contributions.</p>
</section>

<section class="donation-history">

```
<table>
    <thead>
        <tr>
            <th>Campaign</th>
            <th>Amount</th>
            <th>Date</th>
            <th>Status</th>
        </tr>
    </thead>

    <tbody>
    <?php if(count($donations) > 0): ?>
        <?php foreach($donations as $d): ?>
        <tr>
            <td><?= htmlspecialchars($d['title']) ?></td>
            <td>₹<?= number_format($d['amount']) ?></td>
            <td><?= date("d M Y", strtotime($d['created_at'])) ?></td>
            <td>Success</td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="4" style="text-align:center">No donations yet</td>
        </tr>
    <?php endif; ?>
    </tbody>

</table>
```

</section>

<?php require_once __DIR__."/../includes/footer.php"; ?>
