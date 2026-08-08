<?php
require_once '../config/helpers.php';
loadEnv('../.env');

require_once '../config/database.php';
require_once '../config/auth.php';

try {
    $auth = Auth::getInstance();
    $auth->logout();
    
    // Redirect to home page
    redirect('/index.php?logged_out=true');
    
} catch (Exception $e) {
    // Still redirect even if logout fails
    redirect('/index.php');
}
