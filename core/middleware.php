<?php
session_start();

/* Check login */
function requireLogin(){
    if(!isset($_SESSION['user_id'])){
        header("Location: /CroudSpark-X/auth/login.php");
        exit;
    }
}

/* Admin only */
function requireAdmin(){
    requireLogin();
    if($_SESSION['role'] !== 'admin'){
        echo "Access denied";
        exit;
    }
}

/* Creator only */
function requireCreator(){
    requireLogin();
    if($_SESSION['role'] !== 'creator'){
        echo "Access denied";
        exit;
    }
}
