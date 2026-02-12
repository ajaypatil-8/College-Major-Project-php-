<?php
session_start();
require_once __DIR__."/../config/db.php";
require_once __DIR__."/../includes/header.php";

if(!isset($_SESSION['user_id'])){
    header("Location: /CroudSpark-X/public/login.php");
    exit;
}

$msg = "";

if($_SERVER["REQUEST_METHOD"]=="POST"){

    $current = $_POST['current'];
    $new = $_POST['new'];
    $confirm = $_POST['confirm'];

    if($new !== $confirm){
        $msg = "<p style='color:red'>New passwords do not match</p>";
    }else{

        $stmt = $pdo->prepare("SELECT password FROM users WHERE id=?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if(!$user || !password_verify($current,$user['password'])){
            $msg = "<p style='color:red'>Current password wrong</p>";
        }else{

            $hash = password_hash($new,PASSWORD_DEFAULT);
            $up = $pdo->prepare("UPDATE users SET password=? WHERE id=?");
            $up->execute([$hash,$_SESSION['user_id']]);

            $msg = "<p style='color:green'>Password updated successfully</p>";
        }
    }
}
?>

<section class="explore-hero">
    <h1>Change Password</h1>
    <p>Keep your account secure.</p>
</section>

<section class="form-wrap">
    <form method="post" class="simple-form">

```
    <?= $msg ?>

    <label>Current Password</label>
    <input type="password" name="current" required>

    <label>New Password</label>
    <input type="password" name="new" required>

    <label>Confirm New Password</label>
    <input type="password" name="confirm" required>

    <button type="submit" class="btn-primary">Update Password</button>

</form>
```

</section>

<?php require_once __DIR__."/../includes/footer.php"; ?>
