<?php

/* Redirect helper */
function redirect($path){
    header("Location: $path");
    exit;
}

/* Safe output */
function e($string){
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/* Format currency */
function formatINR($amount){
    return "₹" . number_format($amount);
}

/* Flash message */
function setFlash($msg){
    $_SESSION['flash'] = $msg;
}

function getFlash(){
    if(isset($_SESSION['flash'])){
        echo $_SESSION['flash'];
        unset($_SESSION['flash']);
    }
}
