<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors',1);

require_once __DIR__."/../config/db.php";
require_once __DIR__."/../config/env.php";
require_once __DIR__."/../vendor/autoload.php";


use Razorpay\Api\Api;

$keyId     = $_ENV['RAZORPAY_KEY_ID'];
$keySecret = $_ENV['RAZORPAY_KEY_SECRET'];

$api = new Api($keyId, $keySecret);

/* GET DATA FROM JS */
$razorpay_payment_id = $_POST['razorpay_payment_id'] ?? '';
$razorpay_order_id   = $_POST['razorpay_order_id'] ?? '';
$campaign_id         = $_POST['campaign_id'] ?? '';
$amount              = $_POST['amount'] ?? '';
$donor_name          = $_POST['donor_name'] ?? 'Anonymous';
$donor_email         = $_POST['donor_email'] ?? '';

$user_id = $_SESSION['user_id'] ?? null;

/* VALIDATION */
if(!$razorpay_payment_id || !$campaign_id){
    echo json_encode([
        "status"=>"error",
        "msg"=>"Missing payment or campaign id"
    ]);
    exit;
}

try{

    /* ================================
       FETCH PAYMENT FROM RAZORPAY
    =================================*/
    $payment = $api->payment->fetch($razorpay_payment_id);

    /* AUTO CAPTURE IF AUTHORIZED */
    if($payment->status == 'authorized'){
        $api->payment->fetch($razorpay_payment_id)->capture([
            'amount' => $payment->amount
        ]);
    }

    /* CHECK FINAL STATUS */
    if($payment->status != 'captured' && $payment->status != 'authorized'){
        echo json_encode([
            "status"=>"error",
            "msg"=>"Payment not captured"
        ]);
        exit;
    }

    /* ================================
       GET CAMPAIGN OWNER
    =================================*/
    $stmt = $pdo->prepare("SELECT user_id FROM campaigns WHERE id=?");
    $stmt->execute([$campaign_id]);
    $campaign = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$campaign){
        echo json_encode([
            "status"=>"error",
            "msg"=>"Campaign not found"
        ]);
        exit;
    }

    $owner_id = $campaign['user_id'];

    /* ================================
       INSERT DONATION
    =================================*/
    $insert = $pdo->prepare("
        INSERT INTO donations 
        (campaign_id,campaign_owner_id,user_id,donor_name,donor_email,amount,
        razorpay_order_id,razorpay_payment_id,payment_method,status,created_at)
        VALUES (?,?,?,?,?,?,?,?,?,'success',NOW())
    ");

    $insert->execute([
        $campaign_id,
        $owner_id,
        $user_id,
        $donor_name,
        $donor_email,
        $amount,
        $razorpay_order_id,
        $razorpay_payment_id,
        'razorpay'
    ]);

    echo json_encode([
        "status"=>"success",
        "msg"=>"Donation saved"
    ]);

}catch(Exception $e){
    echo json_encode([
        "status"=>"error",
        "msg"=>$e->getMessage()
    ]);
}
?>
