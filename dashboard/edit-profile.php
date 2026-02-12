<?php
session_start();
require_once __DIR__."/../config/db.php";
require_once __DIR__."/../includes/header.php";

if(!isset($_SESSION['user_id'])){
    header("Location: /CroudSpark-X/public/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$msg = "";

/* ===== UPDATE PROFILE ===== */
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);

    $stmt = $pdo->prepare("UPDATE users SET name=?, email=? WHERE id=?");
    $stmt->execute([$name,$email,$user_id]);

    $_SESSION['name'] = $name;
    $msg = "Profile updated successfully";
}

/* ===== GET USER DATA ===== */
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<section class="explore-hero">
    <h1>Edit Profile</h1>
    <p>Update your personal information.</p>
</section>

<section class="form-wrap">
    <form method="post" class="simple-form">

```
    <?php if($msg): ?>
        <p style="color:green;"><?= $msg ?></p>
    <?php endif; ?>

    <label>Full Name</label>
    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>

    <label>Email</label>
    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

    <button class="btn-primary">Save Changes</button>

</form>
```

</section>

<?php require_once __DIR__."/../includes/footer.php"; ?>
