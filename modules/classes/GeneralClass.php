<?php 
/**
 * 
 */

class GeneralClass
{
public static $baseUrl;

public $users_cols_public = ["id","email","username","phone","firstname","surname","bio","gender","avatar","picture","user_type","reg_time"];
public $users_cols = ["id","email","username","phone","firstname","surname","bio","gender","avatar","picture","user_type","reg_time"];
public $valid_formats = array(
  "jpg", "JPG", "JPEG",  "PJPEG", "pdf", "xlsx", "xls",  "ppt", "pptx", "txt", "csv", "php", "ini", "html", "htacess",  
 "png", "gif", "bmp","jpeg","pjpeg","docx","doc");
public $image_formats = array("jpg", "JPG", "JPEG",  "PJPEG", "png", "gif", "bmp","jpeg","pjpeg");
/**/
public $ip_blacklist = ["200.003.98.78"];
public $maxVinTries = 3;

  function __construct()
  {
global $baseUrl;
self::$baseUrl = $baseUrl;
}

public function sqlPart($cols,$prefix=''){
$ar = (!empty($prefix)) ? $prefix.'.' : '';
$part = '';
$allcols = count($cols);
for ($i=0; $i < $allcols; $i++) { 
$part .= ($i < ($allcols - 1)) ? "$ar".$cols[$i].", " : "$ar".$cols[$i];
}
return $part;
}//sqlPart

public function getAdmin($user = null){
$dbConn = new DbConn();
if(!isset($user) ||  empty($user) ||  $user === null || $user === ''){
if(isset($_SESSION['vinrun_admin'])){
$session = $_SESSION['vinrun_admin'];
}else{
return false;
}
}else{
 $session = $user;   
} 

$sett = $this->getSettings();
$rl = $dbConn->getRow("SELECT * FROM users b 
WHERE email = ?",["$session"]);

$row = $rl['data'];
$rscom = $sett['resellerComm'];

if($rl['code'] == 200){
$rls = $dbConn->getRows("SELECT id AS notifNum FROM notifications WHERE user = ? AND status = ? ",["$session","0"]);

if($rls['code']==200){
$rw = count($rls['data']);
$row['notifNum'] = $rw;
}
$row['resellerComm'] = $rscom;
return $row;
}else{
return false;
}


}//getAdmin




public function getUser(){
$time = time();
$dbconn = new DbConn();
$ip = $_SERVER['REMOTE_ADDR'];
#
if(isset($_SESSION['senseiUser']) || isset($_SESSION['senseiMentor'])){

if(isset($_SESSION['senseiUser'])){$session = $_SESSION['senseiUser'];}
if(isset($_SESSION['senseiMentor'])){$session = $_SESSION['senseiMentor'];};
}elseif(isset($_SESSION['senseiAdmin'])){
$session = $_SESSION['senseiAdmin'];  
}else{
$usr = array('email'=>null,'user_mode'=>'guest','isLogged'=>false);
return $usr;
}



$ucols = $this->users_cols;
$sdy = $this->sqlPart($ucols,'b');  
$rl = $dbconn->getRow("SELECT w.balance, b.id AS uid, $sdy 
FROM users b 
JOIN wallet w ON b.email = w.user
WHERE b.email = ?",["$session"]);
if($rl['code'] == 200 && $rl['data']!==false){
$row = $rl['data'];
$row['user_mode'] = 'auth';
$row['isLogged'] = true;
$row['balance'] = floatval($row['balance']);
$row['bio'] = stripslashes(html_entity_decode($row['bio'])) ;
#
$rls = $dbconn->getRows("SELECT id AS notifNum FROM notifications WHERE user = ? AND status = ? ",["$session","0"]);
if($rls['code']==200){
$rw = count($rls['data']);
$row['notifNum'] = $rw;
}
#
$rlx = $dbconn->getRows("SELECT id AS messNum FROM messages WHERE user = ? AND status = ? ",["$session","0"]);
if($rlx['code']==200){
$rwx = count($rlx['data']);
$row['messNum'] = $rwx;
}
#


return $row;
}else{
return false;
}



}//getUser






public  function getUserFromEmail($session){
$dbConn = new DbConn(); 
$ucols = $this->users_cols;
$sdy = $this->sqlPart($ucols,'u');  
$rl = $dbConn->getRow("SELECT w.balance, u.id AS uid, $sdy 
  FROM users u 
  JOIN wallet w ON w.user = u.email
WHERE u.email = ?",["$session"]);


if($rl['code'] ==200 && $rl['data']!=false){
$row = $rl['data'];
$row['bio'] = stripslashes(html_entity_decode($row['bio'])) ;
return $row;
}else{
return false;
}

}//getUserFromEmail

public  function getUserFromUsername($session){ 
$dbConn = new DbConn();
$socialClass = new SocialClass();
if(isset($_SESSION['senseiMentor']) 
  || isset($_SESSION['senseiUser'])  
  || isset($_SESSION['senseiAdmin'])){
$ucols = $this->users_cols;
$isfl = true;
$uxr = $this->getUser();
}else{
$ucols = $this->users_cols_public;
$isfl = false;
}

$sdy = $this->sqlPart($ucols,'b');  
$rl = $dbConn->getRow("SELECT b.id, b.id AS uid, $sdy FROM users b 
WHERE b.username = ?",["$session"]);
$row = $rl['data'];
if(count($row) > 0){
$row['bio'] = stripslashes(html_entity_decode($row['bio'])) ;
$row['is_logged'] = $isfl;
$row['other_email'] = $row['email'];
$row['is_followed'] = ($isfl) ? $socialClass->isUserFollowed($row['email'],$uxr['email']) : false;
return $row;
}else{
return false;
}

}//getUserFromUsername


public  function getUserFromText($session){ 
$dbConn = new DbConn();
if(isset($_SESSION['senseiMentor']) 
  || isset($_SESSION['senseiUser'])  
  || isset($_SESSION['senseiAdmin'])){
$ucols = $this->users_cols;
}else{
$ucols = $this->users_cols_public;
}

$sdy = $this->sqlPart($ucols,'b');  

$sql = " SELECT b.id, b.id AS uid, $sdy FROM 
users b WHERE b.email LIKE ? OR  username LIKE ?";
$txt =  "%".$session."%";
$atr = [$txt,$txt];
$rl = $dbConn->getRows($sql,$atr);
$row = $rl['data'];
if(count($row) > 0){
$rx = $dbConn->getRow($sql,$atr);
$rw = $rx['data'];
$rw['bio'] = stripslashes(html_entity_decode($rw['bio'])) ;
return $rw;
}else{
return false;
}

}//getUserFromText



public function getUserPublic($act,$username,$limit=false,$offset=false){
$dbConn = new DbConn();
$genClass = new GeneralClass();
$usr = $this->getUserFromUsername($username);
$feedClass = new FeedClass();
$qaClass  = new QaClass();
$socialClass  = new SocialClass();
$articlesClass = new ArticlesClass();
switch ($act) {
    case 'get_user_public_details':
$res = $usr;
return $res;
        break;//get_user_public_details
    case 'get_user_public_questions':
$res = $qaClass->getUserQuestions($usr['email'],$limit=false,$offset=false);
return $res;
        break;//get_user_public_details
    case 'get_user_public_answers':
$res = $qaClass->getUserAnswers($usr['email'],$limit=false,$offset=false);
return $res;
        break;//get_user_public_answers
    case 'get_user_public_articles':
$res = $articlesClass->getPublicArticles($username,$usr['user_type'],$offset=false,$limit=false);
return $res;
        break;//get_user_public_articles
    case 'get_user_public_education':
$res = $socialClass->getUserPublicEducation($username,$offset=false,$limit=false);
return $res;
        break;//get_user_public_articles
    case 'get_user_public_followers':
$res = $socialClass->getUserPublicFollowers($username,$offset=false,$limit=false);
return $res;
        break;//get_user_public_followers
    case 'get_user_public_following':
$res = $socialClass->getUserPublicFollowing($username,$offset=false,$limit=false);
return $res;
        break;//get_user_public_followers
    case 'get_user_public_departments':
$res = $socialClass->listUserPublicDepartments($username,$offset=false,$limit=false);
return $res;
break;//get_user_public_feed
}

}//getUserPublic

public function crand($num) {     
    $uid = uniqid("", true);
    $namespace = $num; 
    $data = $namespace;
    $data .= $_SERVER['REQUEST_TIME'];
    $data .= $_SERVER['HTTP_USER_AGENT'];
    $data .= $_SERVER['REMOTE_ADDR'];
    $data .= $_SERVER['REMOTE_PORT'];
    $hash = strtoupper( hash('ripemd128', $uid . md5($data)));
    $grand = '' .substr($hash, 10, $num) .'';
    return $grand;
  }


public  function  redoSessionUser($mode,$email,$level,$uid)
{

 if($mode =='user'){
  $mdo = 'User';
 }elseif($mode =='admin'){
$mdo = 'Admin';
 }elseif($mode =='mentor'){
$mdo = 'Mentor';
 }elseif($mode =='account'){
$mdo = 'User';
 }
$mmo = 'sensei'.$mdo;
//clean old sessions
$_SESSION['senseiUser'] = NULL;
$_SESSION['senseiMentor'] = NULL;
$_SESSION['senseiAdmin'] = NULL;
$_SESSION['sessionid'] = NULL;
$_SESSION['senseiUser_level'] = NULL;
$_SESSION['senseiMentor_level'] = NULL;
$_SESSION['senseiAdmin_level'] = NULL;
unset($_SESSION['senseiUser']);
unset($_SESSION['senseiMentor']);
unset($_SESSION['senseiAdmin']);
unset($_SESSION['sessionid']);
unset($_SESSION['senseiUser_level']);
unset($_SESSION['senseiMentor_level']);
unset($_SESSION['senseiAdmin_level']);
//declare & assign fresh session variables

$_SESSION[$mmo] = $email;
$_SESSION[$mmo.'_level'] =  $level;
//session_regenerate_id(true); 
$_SESSION['sessionid'] = $uid.'-'.mt_rand().'-'.time();

$_SESSION['CREATED'] = time();

$sid = $_SESSION['sessionid'];   
$tm = time();

}//redoSession




private function getImg($str){
$texthtml = $str;
preg_match_all('/<img[^>]+>/i',$texthtml, $result);
return  $result;
}

private function getSrc($img){
$html = $img;
preg_match( '@src="([^"]+)"@' , $html, $match );
$src = array_pop($match);
// will return /images/image.jpg
return $src;
}

public  function enumImage($content){
$ogImage = $this->getImg($content);
//echo '<code>';
//var_dump($ogImage[0][0]);
//echo '</code>';
$goodPiks = array();
for ($i=0; $i < count($ogImage[0]); $i++) {
$src = $this->getSrc($ogImage[0][$i]); 
//echo "<br>";
//echo $src;
//print_r($ogImage[0][$i]);
$attr = @getimagesize($src);
//echo json_encode($attr);
if($attr[0] > 200){ 
  $goodPiks[] = $src;
}
//echo '<br>';
}

//echo "Good Piks Array:: ";
//print_r($goodPiks);
//exit();
$respo = array();
if(count($goodPiks) > 0){
$respo['pic'] = $goodPiks[0];
$respo['isPik'] =  true;
}else{
$respo['pic'] = null;
$respo['isPik'] =  false;
}


;
return $respo;
}//enumImage

public  function getUserFromId($id){
$dbConn = new DbConn(); 
$ucols = $this->users_cols;
$sdy = $this->sqlPart($ucols,'b');  
$rl = $dbConn->getRow("SELECT b.id, b.email,  b.firstname, b.username, b.avatar, b.surname, b.bio, b.id as uid, b.user_type FROM users b 
WHERE b.id = ?",["$id"]);
$row = $rl['data'];
if(count($row) > 0){
$row['bio'] = stripslashes(html_entity_decode($row['bio'])) ;
return $row;
}else{
return false;
}

}//getUserFromUsername


public function getWallet($user){
$dbConn = new DbConn();
$sql = "SELECT * FROM wallet WHERE user = ?";
$qr = $dbConn->getRow($sql,["$user"]);
if($qr['code']==200 && $qr['data']!==false){
return $qr['data'];  
}else{
return false;  
}
}

public function getSettings(){
$dbConn = new DbConn();
$rl = $dbConn->getRow("SELECT * FROM settings LIMIT ?",["1"]);
if($rl['code']==200 && $rl['data']!==false){
$row = $rl['data'];
return $row;
}else{
return false;
}
}//getBalance

public function getBalance($user){
  $dbConn = new DbConn();
$rl = $dbConn->getRow("SELECT balance FROM wallet 
WHERE user = ?",["$user"]);
$row = $rl['data'];
if(count($row) > 0){
return floatval($row['balance']) ;
}else{
return false;
}
}//getBalance


public function curSession(){
if(isset($_SESSION['senseiUser'])){
$thisuser = $_SESSION['senseiUser'];
}elseif(isset($_SESSION['senseiMentor'])){
$thisuser = $_SESSION['senseiMentor'];    
}elseif(isset($_SESSION['senseiAdmin'])){
$thisuser = $_SESSION['senseiAdmin'];    
}else{
$thisuser = null;
}
return $thisuser;
}//curSession



public function checkLogged($mode=false){
if($mode == false || !isset($mode)){
if(isset($_SESSION['senseiUser']) 
  || isset($_SESSION['senseiMentor']) 
  || isset($_SESSION['senseiAdmin'])){
$status = true;
}else{
$status = false;  
}

}elseif($mode == 'user'){
if(isset($_SESSION['senseiUser'])){
$status = true;    
}else{
$status = false;
}      
}elseif($mode == 'mentor'){
if(isset($_SESSION['senseiMentor'])){
$status = true;    
}else{
$status = false;
}      
}elseif($mode == 'admin'){
if(isset($_SESSION['SenseiAdmin'])){
$status = true;    
}else{
$status = false;
} 

}else{$status = false;}


return $status;
}//checkLogged



public function getbaseUrl(){
$server = $_SERVER['SERVER_NAME'];
$uhost = $_SERVER['HTTP_HOST'];
$params = explode('.', $uhost);
$pr = (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on') ? 
'http://':'https://';
return $pr.$server.'/';
}//getBaseUrl()


public function fullUserData($thisuser){
$dbConn = new dbConn();
$sqA = "SELECT u.id as uid, u.* FROM users u
WHERE u.email = ?";
$q = $dbConn->getRow($sqA,["$thisuser"]);

if($q['code'] == 200 && $q['data']!==false){
$row = $q['data'];
$row['bio'] = stripslashes(html_entity_decode($row['bio'])) ;
}else{
$row = array('email' => null, 'username' => null);
}


return $row;
exit();
}//fullUserData


public function getMessageById($id){
$dbConn = new dbConn();
$rt = $dbConn->getRow("SELECT m.*,  u.firstname, 
u.surname,  u.email  FROM messages m 
LEFT JOIN users u ON m.sender = u.email AND  m.receiver = u.email 
 WHERE m.id = ? ",["$id"]);
$rw = $rt['data'];
return $rw;
}

public function alterPhoneNumbers($array){
$arrayNum = array();
for($i=0; $i<count($array); $i++){
if(substr($array[$i], 0, 1)==0){ // returns 
$arrayNum[] = '234'.substr($array[$i], 1);
}elseif( strlen($array[$i]) > 13){
$arrayNum[] = $array[$i];
}
else{
$arrayNum[] = $array[$i];}
}

return $arrayNum;
}//alterNumbers





public function validate_phone_number($phone)
{
     // Allow +, - and . in phone number
     $filtered_phone_number = filter_var($phone, FILTER_SANITIZE_NUMBER_INT);
     // Remove "-" from number
     $phone_to_check = str_replace("-", "", $filtered_phone_number);
     // Check the lenght of number
     // This can be customized if you want phone number from a specific country
     if (strlen($phone_to_check) < 10 || strlen($phone_to_check) > 14) {
        return false;
     } else {
       return true;
     }
}//validate)






public function purifyContent($content){
$config = HTMLPurifier_Config::createDefault();
$config->set('HTML.Allowed', 'p,ul,ol,li,u,b,i,a[href],span,code,pre,hr,blockquote,img[src],h2,h3,h4,h5,h6,br,table,thead,tbody,tr,th,td');
$purifier = new HTMLPurifier($config);
return $purifier->purify($content);
}
/**/

public function isValidEmail( $str )
{
// This checks for "xxxxxx@yyyyyy.zzz"
    return ( ! preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? FALSE : TRUE;
}

public function isValidPhone( $string ) {

    if ( preg_match( '/^[+]?([\d]{0,3})?[\(\.\-\s]?([\d]{3})[\)\.\-\s]*([\d]{3})[\.\-\s]?([\d]{4})$/', $string ) ) {

        return TRUE;

    } else {

        return FALSE;

    }

}

public function SEO($input){ 

    //SEO - friendly URL String Converter    

    //ex) this is an example -> this-is-an-example
    $input = str_replace(array("'", '"'), "", $input); //remove single quote and dash

    $input = str_replace(
      array("&nbsp;", "&quot", '&amp', '&', 'quot'), 
      " ", $input);

    $input = str_replace(" ", "-", $input);

    $input = mb_convert_case($input, MB_CASE_LOWER, "UTF-8"); //convert to lowercase

    $input = preg_replace("#[^a-zA-Z0-9]+#", "-", $input); //replace everything non an with dashes

    $input = preg_replace("#(-){2,}#", "$1", $input); //replace multiple dashes with one
    $input = trim($input, "-"); //trim dashes from beginning and end of string if any

    return $input;


	} 



public function resizeImage($SrcImage,$DestImage,$Quality,$MaxWidth,$MaxHeight)
{
list($iWidth,$iHeight,$type)    = @getimagesize($SrcImage);
$ImageScale = @min($MaxWidth/$iWidth, $MaxHeight/$iHeight);
$NewWidth = @ceil($ImageScale*$iWidth);
$NewHeight = @ceil($ImageScale*$iHeight);
$NewCanves = @imagecreatetruecolor($NewWidth, $NewHeight);

switch(strtolower(@image_type_to_mime_type($type)))
{
case 'image/jpeg':
$NewImage = @imagecreatefromjpeg($SrcImage);
break;
case 'image/png':
$NewImage = @imagecreatefrompng($SrcImage);
break;
case 'image/gif':
$NewImage = @imagecreatefromgif($SrcImage);
break;

default:
return false;
}

$new_h = $MaxHeight;
$new_w = $MaxWidth;
$old_x = imageSX($NewImage);
$old_y=imageSY($NewImage);
$ratio1=$old_x/$new_w;
$ratio2=$old_y/$new_h;
if($ratio1>$ratio2)	{
$thumb_w=$new_w;
$thumb_h=$old_y/$ratio1;
}else{
$thumb_h=$new_h;
$thumb_w=$old_x/$ratio2;
}
// Resize Image
if(@imagecopyresampled($NewCanves, $NewImage,0, 0, 0, 0, $thumb_w, $thumb_h, $old_x, $old_y)){
// copy file
if(@imagejpeg($NewCanves,$DestImage,$Quality)){
 @imagedestroy($NewCanves);
return true;
}

}

}//resizeImage



public function detectProxies(){
  $test_HTTP_proxy_headers = array(
    'HTTP_VIA',
    'VIA',
    'Proxy-Connection',
    'HTTP_X_FORWARDED_FOR',  
    'HTTP_FORWARDED_FOR',
    'HTTP_X_FORWARDED',
    'HTTP_FORWARDED',
    'HTTP_CLIENT_IP',
    'HTTP_FORWARDED_FOR_IP',
    'X-PROXY-ID',
    'MT-PROXY-ID',
    'X-TINYPROXY',
    'X_FORWARDED_FOR',
    'FORWARDED_FOR',
    'X_FORWARDED',
    'FORWARDED',
    'CLIENT-IP',
    'CLIENT_IP',
    'PROXY-AGENT',
    'HTTP_X_CLUSTER_CLIENT_IP',
    'FORWARDED_FOR_IP',
    'HTTP_PROXY_CONNECTION');
    
    foreach($test_HTTP_proxy_headers as $header){
      if (isset($_SERVER[$header]) && !empty($_SERVER[$header])) {
        return true;
      }else{
        return false;
      }
    }
}


}







 ?>