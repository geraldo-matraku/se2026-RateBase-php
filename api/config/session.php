<?php

if (session_status() === PHP_SESSION_NONE) {
    //  Konfigurimet e Cookie para se të nisim sesionin
    session_set_cookie_params([
        'lifetime' => 0,            
        'path' => '/',              
        'domain' => 'localhost',   
        'secure' => false,          
        'httponly' => true,         
        'samesite' => 'Lax'         
    ]);

    session_start();
}