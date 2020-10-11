<?php 
/**
 * 
 */

class AccountClass
{
/**/
function __construct(){

}



public function checkAndSave($username,$email){
$dbConn = new dbConn();

$uscheck =  $dbConn->getRows("SELECT username FROM users WHERE username = ?",["$username"]);
$isUs = count($uscheck['data']);
//
$emcheck =  $dbConn->getRow("SELECT username FROM users WHERE email = ?",["$email"]);
$rt = $emcheck["data"];
if(strlen($rt['username']) >  0){
return true;
}elseif($isUs == 0){
$qdr = $dbConn->executeSql("UPDATE users SET username = ? WHERE email = ?",["$username","$email"]);
return true;
}else{
return false;	
}
}

public function saveUsername($data){
$dbConn = new DbConn();
$genClass = new GeneralClass();
$username = $data['username'];
$usr = $genClass->getUser();
$thisuser = $usr['email'];
$rs = array();
$uscheck =  $dbConn->getRows("SELECT username FROM users WHERE username = ?",["$username"]);
if($uscheck['code']==200 && $uscheck['data']!==false){
$isUs = count($uscheck['data']);
if($isUs>0){
$rs['message'] = 'Username already exists';	
$rs['status'] = '0';	
}else{
$upds =  $dbConn->executeSql("UPDATE users SET username = ? WHERE email = ?",["$username","$thisuser"]);
if($upds['code']==200){
$rs['message'] = 'Username saved Successfully';	
$rs['status'] = '1';	
}else{
$rs['message'] = 'Error saving username';	
$rs['status'] = '0';	
}
}
return $rs;	
}

}//saveUsername

public function saveFollowedDepartments($user,$data){
$dbConn = new DbConn();
$rs = array();
foreach ($data as $key => $item) {
$this_data = array(
	'user'=>$user,
	'department_id'=>$item['id']
);
$doSave =  $dbConn->insertDb($this_data,'followed_departments');
$rs[] = $doSave['code'];
}

return $rs;

}//saveDepartments





public function userLogin($request){
	$dbConn = new DbConn();
	$genClass = new GeneralClass();
	$email = $request['email'];
	$password = $request['password'];
	$ip = $_SERVER['REMOTE_ADDR'];
	$rem = isset($request['remember']) ? $request['remember']:'';
	$prev = isset($request['prev']) ? $request['prev']:'';
	$_SESSION['PrevUrl'] = (isset($prev) && $prev!=='') ? $prev: NULL;
	$hashed = hash('sha512',strtolower($email).$password);
	//Lets search for this user
	$llg = "SELECT u.* FROM users u WHERE u.email = ? AND u.password = ?";
	$parms = ["$email","$hashed"];
	$log_query = $dbConn->getRows($llg,$parms);
	$logx = $dbConn->getRow($llg,$parms);
	///
	///
	$isUser = count($log_query['data']);
	if($isUser > 0){
	$row_user = $logx['data'];
	$level = $row_user['level'];
	$email = $row_user['email'];
	$user_type = $row_user['user_type'];
	$uid  = $row_user['id'];	
	}
	//


	if ($isUser == 1) { 
	//User is Found in our Database
	if($row_user['is_activated'] == '0'){


	$go = 'no';
	$state = '44';
	$mess = 'You Have not verified your account.  You can  verify your account by opening your email and clicking on the verification link we sent to you during registration';
	$ex = array(
		"state"=>$state,
	"email"=>$email,
	"mess"=>$mess,
	"uid"=>$uid,
	"isFound"=>'1', 
	"class"=>"error"
	);
	return $ex;
	//
	}elseif($row_user['status'] == -1){
	$go = 'no';
	$state = '41';
	$mess = 'Your Account is blocked please contact admin';
	$ex = array(
	"state"=>$state,
	"status"=>$state,
	"email"=>$email,
	"mess"=>$mess,
	"message"=>$mess,
	"uid"=>$uid,
	"isFound"=>'1', 
	"class"=>"error"
	);
	return $ex;
	}else{
	$go = 'yes';
	}
	//
	if($go == 'yes'){
	$genClass->redoSessionUser($user_type,$email,$level,$uid);
	//
	if (isset($_SESSION['PrevUrl'])) {
	$nextUrl = $_SESSION['PrevUrl']; 
	$_SESSION['PrevUrl'] = NULL;
	unset($_SESSION['PrevUrl']);
	}else{
	$nextUrl = './'.$user_type;	
	}
	/**/


	

	$state = 1;
	$mess = 'Login Success';
	$ex = array(
	"state"=>$state,
	"email"=>$email,
	"mess"=>$mess,
	"uid"=>$uid,
	"isFound"=>'1',
	"nextUrl"=>$nextUrl,
	"allSession"=>$_SESSION, 
	"class"=>"good center"
	);
	return $ex;
	}
	
	}//if user was found end
	else{
	
	$state = '0';
	$mess = 'Invalid login details';
	$nextUrl = '';
	
	$ex = array(
	"state"=>$state,
	"mess"=>$mess,
	"isFound"=>'0',
	"nextUrl"=>$nextUrl,
	"allSession"=>$_SESSION, 
	"class"=>"error center"
	);
	return $ex;
	}
	
	
	
	
	}//userlogin
	



public function adminLogin($request){
	$dbConn = new DbConn();
	$genClass = new GeneralClass();
	$email = $request['email'];
	$password = $request['password'];
	$ip = $_SERVER['REMOTE_ADDR'];
	$rem = isset($request['remember']) ? $request['remember']:'';
	$prev = isset($request['prev']) ? $request['prev']:'';
	$_SESSION['PrevUrl'] = (isset($prev) && $prev!=='') ? $prev: NULL;
	
	$hashed = hash('sha512',strtolower($email).$password);
	//Lets search for this user
	$llg = "SELECT u.* FROM users u WHERE u.email = ? AND u.password = ? AND is_admin = ?";
	$parms = ["$email","$hashed","1"];
	$log_query = $dbConn->getRows($llg,$parms);
	$logx = $dbConn->getRow($llg,$parms);
	///
	$isUser = count($log_query['data']);
	if($isUser > 0){
	$row_user = $logx['data'];
	$level = $row_user['level'];
	$email = $row_user['email'];
	$user_type = $row_user['user_type'];
	$uid  = $row_user['id'];	
	}
	//

	if ($isUser == 1) { 
	//User is Found in our Database
	if($row_user['is_activated'] == '0'){
	$go = 'no';
	$state = '44';
	$mess = 'You Have not verified your account.  You can  verify your account by opening your email and clicking on the verification link we sent to you during registration';
	$ex = array(
		"state"=>$state,
	"email"=>$email,
	"mess"=>$mess,
	"uid"=>$uid,
	"isFound"=>'1', 
	"class"=>"error"
	);
	return $ex;
	
	}elseif($row_user['status'] == -1){
	$go = 'no';
	$state = '41';
	$mess = 'Your Account is blocked please contact admin';
	$ex = array(
	"state"=>$state,
	"status"=>$state,
	"email"=>$email,
	"mess"=>$mess,
	"message"=>$mess,
	"uid"=>$uid,
	"isFound"=>'1', 
	"class"=>"error"
	);
	return $ex;
	
	}else{
	$go = 'yes';
	}
	
	
	
	if($go == 'yes'){
	
	$genClass->redoSessionUser('admin',$email,$level,$uid);
	
	if (isset($_SESSION['PrevUrl'])) {
	$nextUrl = $_SESSION['PrevUrl']; 
	$_SESSION['PrevUrl'] = NULL;
	unset($_SESSION['PrevUrl']);
	}else{
	$nextUrl = './admin';	
	}
	/**/
	
	$state = 1;
	$mess = 'Login Success';
	
	$ex = array(
	"state"=>$state,
	"email"=>$email,
	"mess"=>$mess,
	"uid"=>$uid,
	"isFound"=>'1',
	"nextUrl"=>$nextUrl,
	"allSession"=>$_SESSION, 
	"class"=>"good center"
	);
	return $ex;
	}
	
	}//if user was found end
	else{
	
	$state = '0';
	$mess = 'Details not found';
	$nextUrl = '';
	
	$ex = array(
	"state"=>$state,
	"mess"=>$mess,
	"isFound"=>'0',
	"nextUrl"=>$nextUrl,
	"allSession"=>$_SESSION, 
	"class"=>"error center"
	);
	return $ex;
	}
	
	
	
	
	}//adminlogin
	


public function regUser($request){
$dbConn = new DbConn();
$genClass = new GeneralClass();
$emailClass = new EmailClass();
$settings = $genClass->getSettings();
$password = $request['password'];
$password2 = $request['password2'];
$phone = $request['phone'];
$email = $request['email'];
$gender = (isset($request['gender']) && !empty($request['gender'])) ? $request['gender'] : '';
$firstname = $request['firstname'];
$surname = $request['surname'];
$user_type = $request['user_type'];
$rusex = (isset($request['ref']) && !empty($request['ref'])) ? $genClass->getUserFromText($request['ref']) : false;
$referee = ($rusex !== false) ? $rusex['email']:null;
$rand = mt_rand();
$brk = explode("@", $email);
$usname = $brk[0];
$strt = 0;
$ip = $_SERVER['REMOTE_ADDR'];
//Check Email
$emcheck =  $dbConn->getRows("SELECT email FROM users WHERE email = ?",["$email"]);
$isEmail = count($emcheck['data']);
//Check Phone
$phonecheck =  $dbConn->getRows("SELECT phone FROM users WHERE phone = ?",["$phone"]);
$isPhone = count($phonecheck['data']);
$hashed = hash('sha512',strtolower($email).$password);
$validchars = array('-', '_', '.', '@'); 
$code =  $genClass->crand(10);


if(!ctype_alnum(str_replace($validchars, '', $email))) { 
$go = 'no';
$state = '0';
$mess = ' Invalid characters in your email address';
$d_response = array(
	"state"=>$state,
	"mess"=>$mess,
	"class"=>"error center block"
);
return $d_response;
}
elseif ($emcheck['code'] == 200 && $isEmail > 0) {
$go = 'no';
$state = '0';
$mess = ' Email already in use.';
$d_response = array(
	"state"=>$state,
	"mess"=>$mess,
	"class"=>"error center block"
);
return $d_response;
}elseif (!$genClass->isValidEmail($email)) {
$go = 'no';
$state = '0';
$mess = ' Please supply a valid email address';
$d_response = array(
	"state"=>$state,
	"mess"=>$mess,
	"class"=>"error center block"
);
return $d_response;
}elseif (!$genClass->isValidPhone($phone)) {
$go = 'no';
$state = '0'; 
$mess = ' Please supply a valid phone number';
$d_response = array(
	"state"=>$state,
	"mess"=>$mess,
	"class"=>"error center block"
);
return $d_response;
}elseif ($password != $password2) {
$go = 'no';
$state = '0';
$mess = ' Passwords did not match.';
$d_response = array(
	"state"=>$state,
	"mess"=>$mess,
	"class"=>"error center block"
);
return $d_response;

}else{
	$go='yes';

}



if($go=='yes'){
$reg_time = time();

$reg_time = time();
$eri = explode("@", $email);
$rtei = strtoupper(substr($eri[0], 0, 4));
$kyi = $genClass->crand(8);
$hsi = $rtei.$kyi.$reg_time;
$match_key = $hsi;
$ky = $genClass->crand(16);
$hs = $email.$ky.$reg_time;
$match_key = hash('sha512',$hs);
$fnn = ucfirst($usname);
$uload = array(
	'phone'=>$phone, 
	'password'=>$hashed, 
	'email'=>$email, 
	'firstname'=>$firstname,
	'surname'=>$surname,
	'gender'=>$gender,
	'reg_time'=>$reg_time, 
	'user_type'=>$user_type, 
	'verification_code'=>$code, 
	'last_seen'=>$reg_time,
	'is_activated'=>'1',
	'referee'=>$referee,
	'match_key'=>$match_key
);
$query = $dbConn->insertDb($uload,'users');
if($query['code'] ==200){
$uid = $query['lastInsertId'];
//
do{
$nuserr = $usname.$genClass->crand(1);
$this->checkAndSave($nuserr,$email);
}while ($this->checkAndSave($usname,$email) == false);
#
$depsave = $this->saveFollowedDepartments($email,$request['followed_departments']);
#
$wload = array('user' => $email);
$qucdf = $dbConn->insertDb($wload,'wallet');;

if(!is_null($referee)){
$rfd = array(
	'reg_id'=>$uid,
	'referee'=>$referee, 
	'referred'=>$email,  
	'point'=>$settings['referral_point'],
	'point_value'=>$settings['referral_point_value'], 
	'rdate'=>$reg_time
);
$rqr = $dbConn->insertDb($rfd,'referrals');
}
////fire email
$to = $email;
$hisName = $firstname.' '.$surname;   
$senderemail = $settings['support_email'];
$sendername = $settings['site_short_name'];
$subject = $settings['signup_message_subject'];
$message = "
<p>Thank you <strong>$hisName</strong>.<br>
You are one step closer to becoming a verified user of ".$sendername.". </p>
<p>Kindly click on the 'Confirm Registration' Button below to complete your registration
</p>
<a class='btn-email' href='".$genClass->getbaseUrl()."/verify?code=".$code."'>
Confirm Registration
</a>

<p>
You may also copy and paste the link below in your browser if the button doesn't work. 
".$genClass->getbaseUrl()."/verify?code=".$code." </p>
<p>
Have a wonderful experience. 
</p>
";

$name = $email;
$messBody = $message;;
$senda = $emailClass->sendPlain($senderemail,$sendername,$to,$subject,$messBody,$type='blue');
$genClass->redoSessionUser('user',$email,0,$uid);


$state = '1';
$mess = '
<div><p> Thank You,  <br>Your registration has been received. <br>Kindly verify your registration by clicking on the verification link in the email we just sent to your email.</p>
<br>
<div class="py5 text-center">
<a class="btn-primary btn-bordered shadow-none btn btn-block" href="./account">Proceed to Dashboard<a>
</div>
</div>
';
}else{
$d_response = array(
	"state"=>"0",
	"mess"=>"Could not register user please contact admin",
	"nextUrl"=>"/register",
	"class"=>"error"
);    
}
$nextUrl = './account';
$d_response = array(
	"state"=>$state,
	"mess"=>$mess,
	"nextUrl"=>$nextUrl,
	"uid"=>$uid,
	"class"=>"good center block"
);


return $d_response;
}
}//regUser






public function getUserData(){
$genClass = new GeneralClass();
$dbConn = new DbConn();
$uda = $genClass->getUser();
return $uda;

}

public function editUser($request){
$genClass = new GeneralClass();
$dbConn = new DbConn();
$label = $request['label'];
$value = $request['value'];
$usr = $genClass->getUser();
$email = $usr['email'];

if($label === 'username'){
$sqz = "SELECT * FROM users WHERE username = ? AND email <> ?";
$qz = $dbConn->getRows($sqz,["$value","$email"]);
if(count($qz['data']) > 0){
return array('mess'=> ' Username already in use','status'=>'0');
}
}

if($label === 'bio'){
$value =  htmlentities(addslashes($genClass->purifyContent($value)));
}

$sql = "UPDATE users SET $label = ? WHERE email = ?";
$q1 = $dbConn->executeSql($sql,["$value","$email"]);
if($q1['code'] ==200){
$rsp = array('mess'=> ucfirst($label).' Updated!','status'=>'1');
}else{
$rsp = array('mess'=> ucfirst($label).' Not Updated!','status'=>'0');	
}
return $rsp;
}


public function updateUser($request){
$genClass = new GeneralClass();
$dbConn = new DbConn();
$usr =  $genClass->getUser(); 
$email = $usr['email']; 
$phone = $request['phone'];
$surname = $request['surname'];
$firstname = $request['firstname'];

if (!$genClass->isValidPhone($phone)) {
$go = 'no';
$state = '0'; 
$mess = ' Please supply a valid phone number';
//
return array(
"state" => $state,
"mess" => $mess,
"class" => "error center block"
);
//
}else{
$go='yes';
}
//
if($go=='yes'){
$reg_time = time();
$csql = "UPDATE users SET surname = ?, firstname = ?, phone = ? WHERE email = ?";
$q1 = $dbConn->executeSql($sql,["$surname","$firstname","$phone","$email"]);
$state = '1';
$mess = 'Profile Updated Successfully';
return array(
"state" => $state, 
"mess" => $mess,
"message" => $mess,
"nextUrl" => "/",
"class" => "good"
);

}
}


public function updatePassword($request){
$genClass = new GeneralClass();
$dbConn = new DbConn();
$usr =  $genClass->getUser(); 
$email = $usr['email']; 

$oldpassword = $request['oldpassword'];
$newpassword = $request['newpassword'];
$newpassword2 = $request['newpassword2'];

$hashed = hash('sha512',strtolower($email).$newpassword);
$hashedOld = hash('sha512',strtolower($email).$oldpassword);

/*Check Email
$emcheck =  mysqli_query($mysqli,"SELECT email, password FROM users WHERE 
    email = '$email'");
$rss = mysqli_fetch_assoc($emcheck);
$isEmail = mysqli_num_rows($emcheck);
*/


if ($newpassword != $newpassword2) {
$go = 'no';
$state = '0'; 
$mess = ' New Passwords do not match';


return array(
"state"=>$state,
	"mess"=>$mess,
	"class"=>"error center block"
);


}elseif ($rss['password'] != $hashedOld) {
$go = 'no';
$state = '0'; 
$mess = ' Wrong old Password entered';

return array(
"state"=>$state,
	"mess"=>$mess,
	"class"=>"error center block"
);

}else{$go='yes';}

if($go=='yes'){
$reg_time = time();

$csql = "UPDATE users SET 
password = ? 
WHERE email = ?";
$q1 = $dbConn->executeSql($csql,["$hashed","$email"]);
$state = '1';
$mess = 'Password Updated Successfully';

return array (
"state"=>$state, 
"mess"=>$mess,
"class"=>"good"
);

}


}//savePassword


public function adminCreateNewAccount($request){
$genClass = new GeneralClass();
$dbConn = new DbConn();
$settings = $genClass->getSettings();
$password = $request['password'];
$password2 = $request['password2'];
$firstname = $request['firstname'];
$surname = $request['surname'];
$phone = $request['phone'];
$email = $request['email'];
$ip = $_SERVER['REMOTE_ADDR'];
$usr = $genClass->getUser();
$base_user = $usr['email'];
//Check Email
$emcheck =  $dbConn->getRows("SELECT email FROM users WHERE email = ?",["$email"]);
$isEmail = count($emcheck['data']);
//Check Base Email
$basecheck =  $dbConn->getRows("SELECT email FROM users WHERE email = ?",["$base_user"]);
$isBase = count($basecheck['data']);
//Check Phone
$phonecheck =  $dbConn->getRows("SELECT phone FROM users WHERE phone = ?",["$phone"]);
$isPhone = count($phonecheck['data']);

$hashed = hash('sha512',strtolower($email).$password);

$validchars = array('-', '_', '.', '@'); 

$code =  $genClass->crand(10);
#
if(!ctype_alnum(str_replace($validchars, '', $email))) { 
$go = 'no';
$state = '0';
$mess = ' Invalid characters in your email address';
$rsp = array(
    "state"=>$state,
    "mess"=>$mess,
    "class"=>"error center block"
    );
return $rsp;

}elseif (!$genClass->isValidEmail($email)) {
$go = 'no';
$state = '0';
$mess = ' Please supply a valid email address';
$rsp = array(
    "state"=>$state,
    "mess"=>$mess,
    "class"=>"error center block"
    );
return $rsp;
}elseif (!$genClass->isValidEmail($base_user)) {
$go = 'no';
$state = '0';
$mess = ' Please supply a valid email address for base user email';
$rsp = array(
    "state"=>$state,
    "mess"=>$mess,
    "class"=>"error center block"
    );
return $rsp;
}elseif (!$genClass->isValidPhone($phone)) {
$go = 'no';
$state = '0'; 
$mess = ' Please supply a valid phone number';
$rsp = array(
    "state"=>$state,
    "mess"=>$mess,
    "class"=>"error center block"
    );
return $rsp;
}elseif ($isEmail > 0) {
$go = 'no';
$state = '0'; 
$mess = ' Email <u>'.$email.'</u> is already in use.';
$rsp = array(
    "state"=>$state,
    "mess"=>$mess,
    "class"=>"error center block"
    );
return $rsp;
}elseif ($isBase == 0) {
$go = 'no';
$state = '0'; 
$mess = ' Base user account does not exist.';
$rsp = array(
    "state"=>$state,
    "mess"=>$mess,
    "class"=>"error center block"
    );
return $rsp;
}
elseif ($isPhone >0) {
$go = 'no';
$state = '0';
$mess = ' Phone number already in use.';
$rsp = array(
    "state"=>$state,
    "mess"=>$mess,
    "class"=>"error center block"
    );
return $rsp;
}elseif ($password != $password2) {
$go = 'no';
$state = '0';
$mess = ' Passwords did not match.';
$rsp = array(
    "state"=>$state,
    "mess"=>$mess,
    "class"=>"error center block"
    );
return $rsp;
}else{$go='yes';}

if($go=='yes'){
$reg_time = time();

$ky = $genClass->crand(16);
$hs = $email.$ky.$reg_time;
$match_key = hash('sha512',$hs);

$reLoad = array(
    'phone'=>$phone, 
    'password'=>$hashed,
    'match_key'=>$match_key,
    'public_key'=>$ky, 
    'email'=>$email,
    'firstname'=>$firstname,
    'surname'=>$surname, 
    'user_type'=>$user_type, 
    'reg_time'=>$reg_time, 
    'verification_code'=>$code, 
    'last_seen'=>$reg_time
);
#
$qrs = $dbConn->insertDb($reLoad,'users');
#
$uid = $qrs['lastInsertId'];
#
$qucdf = $dbConn->insertDb(array('user'=>$email),'wallet');
#
$to = $email;   
$senderemail = $settings['support_email'];
$sendername = $settings['site_short_name'];
$subject = "Your New Account Created!";
$message = '<img src=https://vinrun.com/images/logo.png  height=50 /><br/><br/>
Thank you.<br><br/>
A new Sub Account has been created successfully

<b>Download your reports:</b><br/>
';


$emailClass = new EmailClass();
//$emailClass->sendEmail($senderemail,$sendername,$to,$subject,$message);
$sem = $emailClass->sendPlain($to, $senderemail, $subject,$message, array());

$state = '1';
$mess = '<p> Thank You,  <br> Your new Sub Account has been created. <br></p>';

$rspx = array(

"state" => $state, 
"uid" => $uid, 
"public_key" => $ky,
"email" => $email,
"phone" => $phone,
"account_data"=>$genClass->getUserFromEmail($session),
"base_user" => $base_user,
"firstname" => $firstname,
"surname" => $surname,
"mess" => $mess,
"class"=>"good center block"
);

return $rspx;

}//createSubAccount

}




public function getUserState($act,$limit=false,$offset=false){
$dbConn = new DbConn();
$genClass = new GeneralClass();
$feedClass = new FeedClass();
$qaClass  = new QaClass();
$socialClass  = new SocialClass();
$articlesClass = new ArticlesClass();
switch ($act) {
    case 'get_user_details':
$res = [];
return $res;
        break;//get_user_public_details
    case 'get_user_questions':
$res = $qaClass->getUserQuestions($usr['email'],$limit=false,$offset=false);
return $res;
        break;//get_user_public_details
    case 'get_user_answers':
$res = $qaClass->getUserAnswers($usr['email'],$limit=false,$offset=false);
return $res;
        break;//get_user_public_answers
    case 'get_user_articles':
$res = $articlesClass->getPublicArticles($username,$usr['user_type'],$offset=false,$limit=false);
return $res;
        break;//get_user_public_articles
    case 'get_user_education':
$res = $socialClass->getUserEducation($offset=false,$limit=false);
return $res;
        break;//get_user_public_articles
    case 'get_user_followers':
$res = $socialClass->getUserFollowers($offset=false,$limit=false);
return $res;
        break;//get_user_public_followers
    case 'get_user_followings':
$res = $socialClass->getUserFollowing($offset=false,$limit=false);
return $res;
    case 'get_user_departments':
$res = $socialClass->getUserDepartments($offset=false,$limit=false);
return $res;
        break;//get_user_public_followers
    case 'get_user_stats':




        break;//get_user_public_feed
}

}//getUserState






public function addEducation($data){
$dbConn = new DbConn();
$socialClass = new SocialClass();
$notifyClass = new NotificationClass();
$genClass = new GeneralClass();
$usr = $genClass->getUser();
$user = $usr['email'];
$eda = '';
$institution = $data['school_id'];
$faculty = ($data['faculty_id']=='0') ? $data['faculty_id_alt']:$data['faculty_id'];
$department = ($data['department_id']=='0') ? $data['department_id_alt']:$data['department_id'];
$current_level =  isset($data['current_level']) ? $data['current_level']:'';
$concentration = (isset($data['concentration'])) ? $data['concentration'] : '';
$start_year = $data['entry_year'];
$is_graduated =  $data['is_graduated'];
$end_year = ($is_graduated == 1) ? $data['graduation_year']:'Till Date';
$degree = $data['degree'];
$now = time();
$edload = array(
'user'=>$user, 
'school_id'=>$institution, 
'faculty_id'=>$faculty, 
'department_id'=>$department, 
'current_level'=>$current_level, 
'concentration'=>$concentration, 
'entry_year'=>$start_year, 
'is_graduated'=>$is_graduated, 
'graduation_year'=>$end_year, 
'degree'=>$degree
);
$sdd = $dbConn->insertDb($edload,'profile_education') ;
$uid = $sdd['lastInsertId'];
$eda = $socialClass->getOneEducation($uid);
if($sdd['code'] == 200){
$fload = array(
	'is_notification'=>1, 
	'cid'=>$uid, 
	'author'=>$user, 
	'create_date'=>$now
);
$feed = $notifyClass->feedNotification($fload);
$status = '1'; $message = 'Done! Education Added Successfully';
}else{
$status = '0'; $message = 'Error: Not Done! Try Again Later';  
}


return array(
	"status"=>$status, 
	"data"=>$eda, 
	"message"=>$message, 
	"mess"=>$message
);

}


public function editEducation($data){
$dbConn = new DbConn();
$notifyClass = new NotificationClass();
$genClass = new GeneralClass();
$usr = $genClass->getUser();
$user = $usr['email'];
$id = $data['id'];
$institution = $data['school_id'];
$faculty = ($data['faculty_id']=='0') ? $data['faculty_id_alt']:$data['faculty_id'];
$department = ($data['department_id']=='0') ? $data['department_id_alt']:$data['department_id'];
$current_level =  isset($data['current_level']) ? $data['current_level']:'';
$concentration = (isset($data['concentration'])) ? $data['concentration'] : '';
$start_year = $data['entry_year'];
$is_graduated =  $data['is_graduated'];
$end_year = ($is_graduated == 1) ? $data['graduation_year']:'Till Date';
$degree = $data['degree'];
$now = time();

$sql = "UPDATE profile_education 
SET 
user = ?, 
school_id = ?, 
faculty_id = ?, 
department_id = ?, 
current_level = ?, 
concentration = ?, 
entry_year = ?, 
is_graduated = ?, 
graduation_year = ?, 
degree = ?
WHERE id = ? AND user = ?";

$sdd = $dbConn->executeSql($sql,["$user","$institution","$faculty","$department","$current_level","$concentration","$start_year","$is_graduated","$end_year","$degree","$id","$user"]);

if($sdd['code'] == 200){
$fload = array(
	'is_notification'=>1, 
	'cid'=>$id, 
	'author'=>$user, 
	'create_date'=>$now
);
$feed = $notifyClass->feedNotification($fload);
$status = '1'; $message = 'Done! Education Edited Successfully';
}else{
$status = '0'; $message = 'Error: Not Done! Try Again Later';  
}


return array(
	"status"=>$status, 
	"message"=>$message, 
	"mess"=>$message
);

}


public function deleteEducation($request){
$dbConn = new DbConn();
$genClass = new GeneralClass();
$usr = $genClass->getUser();
$user = $usr['email'];
$id = $request['id'];
$sql = "DELETE FROM profile_education WHERE id = ?";

$del = $dbConn->executeSql($sql,["$id"]);

if($del['code'] == 200){
$status = '1'; $message = 'Done! Education Deleted Successfully';
}else{
$status = '0'; $message = 'Error: Not Done! Try Again Later';  
}

return array(
	"status"=>$status, 
	"message"=>$message, 
	"mess"=>$message
);

}




 



}//AccountClass



