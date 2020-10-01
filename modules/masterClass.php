<?php 

require_once('purifier/HTMLPurifier.standalone.php');
require_once('classes/DbConn.php');
require_once('classes/AccountClass.php');
require_once('classes/GeneralClass.php');
require_once('classes/FeedClass.php');
require_once('classes/SocialClass.php');
require_once('classes/QaClass.php');
require_once('classes/ArticlesClass.php');
require_once('classes/DashClass.php');
require_once('classes/PaymentClass.php');
require_once('classes/EmailClass.php');
require_once('classes/NotificationClass.php');
/*
include 'classes/autol.php';
function __autoload($class_name) {
    if(file_exists('./classes/'.$class_name . '.php')) {
        require_once('./classes/'.$class_name . '.php');    
    } else {
        throw new Exception("Unable to load $class_name.");
    }
}*/



?>