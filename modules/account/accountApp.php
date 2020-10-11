<?php
if (!isset($_SESSION)) {session_start();}
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
error_reporting(E_ALL);
ini_set('display_errors', '1');
ob_start();

require_once('../masterClass.php');
require '../../vendor/autoload.php';
$app_id = '808189';
$app_key = '94b15ec0f11a47b7d711';
$app_secret = '6a44c8eb0be078fb1627';
$app_cluster = 'eu';
$pusher = new Pusher\Pusher( $app_key, $app_secret, $app_id, 
  array( 'cluster' => $app_cluster, 'useTLS' => true ) );
#
$genClass = new GeneralClass;
$articlesClass = new ArticlesClass;
$accountClass = new AccountClass;
$payClass = new PaymentClass;
$socialClass = new SocialClass;
$dashClass = new DashClass;
$dbConn = new DbConn;
#
$postdata = file_get_contents("php://input");
$request = json_decode($postdata,true);

if(is_array($request) && array_key_exists('action',$request)) { 
$action = $request['action'];
}else{
$action = false;
$request = array('action'=>false);
}



if(isset($_REQUEST['data'])){
$formdata = json_decode($_REQUEST['data'],true);
}


if($request['action']  ==   'getUser'){
    $rsp=$genClass->getUser();

    
    header('content-type: application/json');
    echo json_encode($rsp);
    exit();
    }

if($request['action']  ==   'edit_user'){
    $rsp=$accountClass->editUser($request);
    header('content-type: application/json');
    echo json_encode($rsp);
    exit();
    }

    
    
    if($request['action']  ==   'userLogin'){
    $rsp=$accountClass->userLogin($request);
    header('content-type: application/json');
    echo json_encode($rsp);
    exit();
    }    
    if($request['action']  ==   'adminLogin'){
    $rsp=$accountClass->adminLogin($request);
    header('content-type: application/json');
    echo json_encode($rsp);
    exit();
    }

if($request['action']  ==   'userRegister'){
$rsp=$accountClass->regUser($request);

header('content-type: application/json');
echo json_encode($rsp);
exit();
}


if($request['action']  ==   'list_departments'){
$rsp=$socialClass->listDepartments();
header('content-type: application/json');
echo json_encode($rsp);
exit();
}



if($request['action']  ==   'save_username'){
$rsp=$accountClass->saveUsername($request);
header('content-type: application/json');
echo json_encode($rsp);
exit();
}

if($request['action']  ==   'getUserData'){
$userData = $accountClass->getUserData();

header('content-type: application/json');
echo json_encode($userData);
exit();
}

if($request['action']  ==   'add_education'){
$rsp = $accountClass->addEducation($request);
header('content-type: application/json');
echo json_encode($rsp);
exit();
}//delete_education

if($request['action']  ==   'edit_education'){
$rsp = $accountClass->editEducation($request);
header('content-type: application/json');
echo json_encode($rsp);
exit();
}//delete_education

if($request['action']  ==   'delete_education'){
$rsp = $accountClass->deleteEducation($request);
header('content-type: application/json');
echo json_encode($rsp);
exit();
}//delete_education


if(
$action == 'get_user_details' || 
$action == 'get_user_questions' || 
$action == 'get_user_answers' || 
$action == 'get_user_articles' || 
$action == 'get_user_followers' || 
$action == 'get_user_followings' ||
$action == 'get_user_departments' || 
$action == 'get_user_education' || 
$action == 'get_user_stats'){
$act = $request['action'];
$rs_name = substr($action, 4);
$rsp = $accountClass->getUserState($act);
$r  = array($rs_name => $rsp);
header('content-type: application/json');
echo json_encode($r);
exit();

}//getPublicDetails



if($action == 'updateUser') {
$usr = $genClass->getUser();
$email = $usr['email'];
$phone = $request['phone'];
$surname = $request['surname'];
$firstname = $request['firstname'];

if (!$genClass->isValidPhone($phone)) {
$go = 'no';
$state = '0'; 
$mess = 'Please supply a valid phone number';
header('content-type: application/json');
echo '{"state":'.json_encode($state).',"mess":'.json_encode($mess).',"class":"error center block"}';
exit();
}else{$go='yes';}

if($go=='yes'){
$reg_time = time();

$csql = "UPDATE users SET
surname = ?, 
firstname = ?  
WHERE email = ?";
$q1 = $dbConn->executeSql($csql,
	["$surname","$firstname","$email"]);
$state = '1';
$mess = 'Profile Updated Successfully';
header('content-type: application/json');
echo '{"state":'.json_encode($state).', "mess":'.json_encode($mess).',"class":"good"}';
exit();
}
}


if($action == 'updatePassword') {
$usr = $genClass->getUser();
$email = $usr['email'];
$oldpassword = $request['oldpassword'];
$newpassword = $request['newpassword'];
$newpassword2 = $request['newpassword2'];

$hashed = hash('sha512',strtolower($email).$newpassword);
$hashedOld = hash('sha512',strtolower($email).$oldpassword);

//Check Email
$emcheck =  $dbConn->getRows("SELECT email, password FROM users WHERE 
    email = ?",["$email"]);
$rss = $emcheck['data'];
$isEmail = count($rss);
//
if ($newpassword != $newpassword2) {
$go = 'no';
$state = '0'; 
$mess = ' New Passwords do not match';
header('content-type: application/json');
echo '{"state":'.json_encode($state).',"mess":'.json_encode($mess).',"class":"error center block"}';
exit();
}elseif ($rss['password'] != $hashedOld) {
$go = 'no';
$state = '0'; 
$mess = ' Wrong old Password entered';
header('content-type: application/json');
echo '{"state":'.json_encode($state).',"mess":'.json_encode($mess).',"class":"error center block"}';
exit();
}else{$go='yes';}

if($go=='yes'){
$reg_time = time();
$csql = "UPDATE users SET 
password = ? 
WHERE email = ?";
$q1 = $dbConn->executeSql($csql,["$hashed","$email"]);

$state = '1';
$mess = 'Password Updated Successfully';
header('content-type: application/json');
echo '{"state":'.json_encode($state).', "mess":'.json_encode($mess).',"class":"good"}';
exit();
}
}





if($action == 'userPasswordReset'){
$genClass = new GeneralClass();
$dbConn = new DbConn();
$settings = $genClass->getSettings();
$semail = $request['email'];
$sq = "SELECT * FROM users  WHERE  email = ? ";
$qup = $dbConn->getRows($sq,["$semail"]);
if($qup['code']==200 && $qup['data']!==false){
$row_us = $qup['data'];
$trt = count($row_us);

//exit();
if (empty($semail)) {
$go = 'no';
$state = '0';
$mess = 'Please Enter your Email adddress';
header('content-type: application/json');
echo '{"state":'.json_encode($state).',"mess":'.json_encode($mess).',"class":"error block"}';
exit(); 


}elseif($trt > 0){
$qr = $dbConn->getRow("SELECT * FROM users WHERE email = ?",["$semail"]);

$row = $qr['data'];
$ui = $row['id'];
$useremail = $row['email'];
$active = $row['is_activated'];
$validation_code  = $row['verification_code'];
$firstname = $row['firstname'];
$surname = $row['surname'];
}//nums > 0
else{
$go = 'no';
$state = '0';
$mess = 'Your registration details not found .';
header('content-type: application/json');
echo '{"state":'.json_encode($state).',"mess":'.json_encode($mess).',"class":"error  block"}';
exit(); 
}
}//query ok
else{
$go = 'no';
$state = '0';
$mess = 'System Error. Please contact Site Admin';
header('content-type: application/json');
echo '{"state":'.json_encode($state).',"mess":'.json_encode($mess).',"class":"error  block"}';
exit(); 
}    



$code =  $genClass->crand(8);

////fire email
$baseUrl = $settings['base_url'];
$to = $useremail;
$hisName = $firstname.' '.$surname;   
$senderemail = "support@vinrun.com";
$sendername = $settings['site_short_name'];
$subject = 'You requested to reset your password ';
$message = "
Thank you <strong>$hisName</strong>.<br>
There was a request to reset password from your account at ".$sendername.".
If you confirm that you actually made that request please click on the 'RESET PASSWORD ' button below.
<br>
<a class='btn-email' href='".$baseUrl."/set-password?code=".$code."&si=".$ui."'>
RESET PASSWORD
</a>

<br>
You may also copy and paste the link below in your browser if the button doesn't work. 
<br>
".$baseUrl."/set-password?code=".$code."&si=".$ui."
 <br>
Please disregard this email if the request did not come from you.
 <br>
See you back soon.";

$name = $useremail;
$messBody = $message;


$sEmail =  new EmailClass();
$senda = $sEmail->sendPlain($senderemail,$sendername,$to,$subject,$messBody,$type='blue');



$update = $dbConn->executeSql("UPDATE users set verification_code='$code' WHERE email = ?",["$useremail"]);

$state = '1';
$mess = 'We sent a verification link to your email address. <br>Please open your email and click on this link to complete your password recovery process.';




header('content-type: application/json');
echo  '{
"state":'.json_encode($state).',
"mess":'.json_encode($mess).',
"uid":'.json_encode($iduser).',
"class":"good "
}';

exit();



}



    if($request['action']  ==   'get_articles'){
$offset = $request['offset'];
$limit = $request['limit'];  
$type = $request['type'];    
$rsp = $articlesClass->getArticles($type,$offset,$limit);
        
        
        $ars = array($type.'_articles'=>$rsp);
        header('content-type: application/json');
        echo json_encode($ars);
        exit();
        
        } 



if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'updateProfilePic') {
$usr = $genClass->getUser();
$old_avatar = $genClass->getbaseUrl().$usr['avatar'];
$old_picture = $genClass->getbaseUrl().$usr['picture'];

$thisuser = $usr['email'];
$file = $_FILES['file'];
$pass =  $genClass->crand(10);
$code = mt_rand() . mt_rand() . mt_rand();
$valid_formats = array(
  "jpg", "JPG", "JPEG",  "PJPEG",  "png", "gif","jpeg","pjpeg");
$pdate = time();
$rand = mt_rand().mt_rand();
//
if(!empty($_FILES['file'])){
$banner_file = $file['name'];
$banner_size = $file['size'];
$flt = pathinfo($banner_file);
$banner_tmp = $file['tmp_name'];
$banner_name = $flt['filename'];
$ext = $flt['extension'];
$banner_url = "files/profile/".$pdate.'-'.$rand.'.'.$ext; //a directory inside
$banner_actual = "../../files/profile/".$pdate.'-'.$rand.'.'.$ext;
$banner_thumb_url = "files/profile/thumbs/".$pdate.'-'.$rand.'-thumb.'.$ext;
$banner_thumb_actual = "../../files/profile/thumbs/".$pdate.'-'.$rand.'-thumb.'.$ext;  
}
//
if( !empty($_FILES['file']) &&!in_array($ext,$valid_formats)){
$go='no';
$mess = '<div class="error center"><p>Invalid File ('.$ext.') Attached! Allowed formats include : '.implode(",", $valid_formats).'/p></div>' ;
header('content-type: application/json');
echo '{"state":'.json_encode(0).',
"mess":'.json_encode($mess).',
"class":'.json_encode('error').'}';
exit();
}

else{$go= "yes";}



if ($go == "yes"){

if ( !empty($_FILES['file'])) {
if(move_uploaded_file($banner_tmp, $banner_actual)){
$res = $genClass->resizeImage($banner_actual, $banner_thumb_actual,100,150,150);
if(file_exists($old_avatar)){
unlink($old_avatar);    
}
if (file_exists($old_picture)) {
unlink($old_picture);
}


};
}else{$banner_thumb_url = $banner_actual = '';}

/*
if ( !empty($_FILES['file'])) {
if(move_uploaded_file($banner_tmp, $banner_actual)){
$fileUrl = $banner_url;
}
*/
$sar = $dbConn->executeSql("UPDATE users SET avatar = ?, picture = ? WHERE email = ?", ["$banner_thumb_url", "$banner_url", "$thisuser"]);
}else{$sar = array('code'=>100);}
if($sar['code']==200){
$mess = 'Saved Successfully'; $status = '1';  $class = 'good';
}else{
 $mess = 'Cannot Upload Picture'; $status = '0';  $class = 'error'; 
}



header('content-type: application/json');
echo '{"state":'.json_encode($status).',
"mess":'.json_encode($mess).',
"avatar":'.json_encode($banner_thumb_url).',
"class":'.json_encode($class).'}';
exit();




}


?>
