<?php
session_start();

/* Check login */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/* Protect page (only logged in users) */
function requireAuth() {
    if(!isLoggedIn()){
        header("Location: /auth/login.php");
        exit;
    }
}

/* Optional: logout helper */
function logoutUser(){
    session_unset();
    session_destroy();
    header("Location: /index.php");
    exit;
}
