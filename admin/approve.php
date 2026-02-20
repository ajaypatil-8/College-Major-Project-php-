<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT']."/config/db.php";

if($_SESSION['role']!="admin"){ exit; }

$id=$_GET['id'] ?? 0;

$pdo->prepare("UPDATE campaigns SET status='approved',approved_at=NOW() WHERE id=?")->execute([$id]);

echo "<script>alert('Campaign Approved');window.location='admin-dashboard.php';</script>";
