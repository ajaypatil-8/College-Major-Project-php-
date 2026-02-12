<?php
session_start();
require_once __DIR__."/../config/db.php";

if($_SESSION['role']!="admin"){ exit; }

$id=$_GET['id'] ?? 0;

$pdo->prepare("UPDATE campaigns SET status='rejected',reject_reason='Rejected by admin' WHERE id=?")->execute([$id]);

echo "<script>alert('Campaign Rejected');window.location='admin-dashboard.php';</script>";
