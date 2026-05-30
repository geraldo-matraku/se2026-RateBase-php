<?php

if (session_status() === PHP_SESSION_NONE) {
    
    $isLocalhost = (
        isset($_SERVER['HTTP_HOST']) && 
        (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false)
    );

    if ($isLocalhost) {

        session_set_cookie_params([
            'lifetime' => 0,            
            'path' => '/',              
            'domain' => 'localhost',   
            'secure' => false,          
            'httponly' => true,         
            'samesite' => 'Lax'         
        ]);
    } else {
 
        session_set_cookie_params([
            'lifetime' => 86400, 
            'path' => '/',              
           
            'secure' => true,    
            'httponly' => true,  
            'samesite' => 'None' 
        ]);
    }

    session_start();
}