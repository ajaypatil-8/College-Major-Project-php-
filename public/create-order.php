<?php
require_once $_SERVER['DOCUMENT_ROOT']."/config/env.php";
require_once $_SERVER['DOCUMENT_ROOT']."/vendor/autoload.php";

use Razorpay\Api\Api;

$keyId     = $_ENV['RAZORPAY_KEY_ID'];
$keySecret = $_ENV['RAZORPAY_KEY_SECRET'];

$api = new Api($keyId, $keySecret);

$amount = $_POST['amount'] ?? 0;

if($amount <= 0){
    echo json_encode(["status"=>"error"]);
    exit;
}

try{

$order = $api->order->create([
    'receipt' => 'donation_'.time(),
    'amount' => $amount * 100, // paisa
    'currency' => 'INR'
]);

echo json_encode([
    "status"=>"success",
    "order_id"=>$order['id']
]);

}catch(Exception $e){
    echo json_encode([
        "status"=>"error",
        "msg"=>$e->getMessage()
    ]);
}
