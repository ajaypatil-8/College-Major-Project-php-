<?php
session_start();

/* Check login */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/* Protect page (only logged in users) */
function requireAuth() {
    if(!isLoggedIn()){
        header("Location: /CroudSpark-X/auth/login.php");
        exit;
    }
}

/* Optional: logout helper */
function logoutUser(){
    session_unset();
    session_destroy();
    header("Location: /CroudSpark-X/public/index.php");
    exit;
}
