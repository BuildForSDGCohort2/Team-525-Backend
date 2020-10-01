<?php

class DashClass{


 public function getMessages($offset=false,$limit=false){
 $dbConn = new dbConn();
$genClass = new GeneralClass();

$usr = $genClass->getUser();
$thisuser = $user = $usr['email'];


$lim = (isset($limit) && $limit !== false) ? $limit : 12; 
$rsp = (isset($offset) && $offset !== false) ? 
$dbConn->getRows("SELECT m.*,  u.firstname, 
u.surname,  u.email  FROM messages m 
LEFT JOIN users u ON m.sender = u.email AND m.receiver = u.email 
 WHERE m.pid = ? AND (m.receiver = ? OR m.sender = ?)
 order by m.id desc LIMIT ? OFFSET ?",["0","$thisuser","$thisuser","$lim","$offset"])
:
$dbConn->getRows("SELECT m.*,  u.firstname, 
u.surname,  u.email  FROM messages m 
LEFT JOIN users u ON m.sender = u.email AND m.receiver = u.email 
 WHERE m.pid = ? AND (m.receiver = ? OR m.sender = ?)
 order by m.id desc LIMIT ? ",["0","$thisuser","$thisuser","$lim"]);
$rts = array();
if($rsp['code'] == 200){
$rws = $rsp['data'];
foreach ($rws as $key => $rw) {
$rts[] = $rw;
}
}

return $rts;


 }//getMessages


 public function getVinQueries($offset=false,$limit=false){
$dbConn = new DbConn();
$genClass = new GeneralClass();
$usr = $genClass->getUser();
$thisuser = $usr['email'];
$lim = (isset($limit) && $limit !== false) ? $limit : 12; 
$rsp = (isset($offset) && $offset !== false) ? 
$dbConn->getRows("SELECT v.* FROM vin_queries v WHERE v.user = ? order by v.id desc LIMIT ? OFFSET ?",["$thisuser","$lim","$offset"])
:
$dbConn->getRows("SELECT v.* FROM vin_queries v WHERE v.user = ? order by v.id desc LIMIT ? ",["$thisuser","$lim"]);
$rts = array();
if($rsp['code'] == 200){
$rws = $rsp['data'];
foreach ($rws as $key => $rw) {
$rw['response'] = json_decode($rw['response'],true);
$rts[] = $rw;
}
}

return $rts;

 }//getVinQueries 


 public function getAdminVinQueries($offset=false,$limit=false){
$dbConn = new DbConn();
$genClass = new GeneralClass();
$lim = (isset($limit) && $limit !== false) ? $limit : 12; 
$rsp = (isset($offset) && $offset !== false) ? 
$dbConn->getRows("SELECT v.* FROM vin_queries v order by v.id desc LIMIT ? OFFSET ?",["$lim","$offset"])
:
$dbConn->getRows("SELECT v.* FROM vin_queries v order by v.id desc LIMIT ? ",["$lim"]);
$rts = array();
if($rsp['code'] == 200){
$rws = $rsp['data'];
foreach ($rws as $key => $rw) {
$rw['response'] = json_decode($rw['response'],true);
$rts[] = $rw;
}
}

return $rts;

 }//getAdminVinQueries 




public function getVinReports($offset=false,$limit=false){
    $dbConn = new DbConn();
    $genClass = new GeneralClass();
    $usr = $genClass->getUser();
    $thisuser = $usr['email'];
    
//
$lim = (isset($limit) && $limit !== false) ? $limit : 12; 
$rsp = (isset($offset) && $offset !== false) ? 
$dbConn->getRows("SELECT o.*, v.vin, v.response FROM orders o 
JOIN vin_queries v ON o.queryid = v.id WHERE o.user = ? order by o.id desc LIMIT ? OFFSET ?",["$thisuser","$lim","$offset"])
:
$dbConn->getRows("SELECT o.*, v.vin, v.response FROM orders o 
JOIN vin_queries v ON o.queryid = v.id WHERE o.user = ? order by o.id desc LIMIT ? ",["$thisuser","$lim"]);
//
$rts = array();
if($rsp['code'] == 200){
$rws = $rsp['data'];
foreach ($rws as $key => $rw) {
$rw['response'] = json_decode($rw['response'],true);
$rw['items'] = json_decode($rw['items'],true);
$rts[] = $rw;
}
}

return $rts;

 }//getVinReports


public function getAdminVinReports($offset=false,$limit=false){
    $dbConn = new DbConn();
    $genClass = new GeneralClass();
    
//
$lim = (isset($limit) && $limit !== false) ? $limit : 12; 
$rsp = (isset($offset) && $offset !== false) ? 
$dbConn->getRows("SELECT o.*, v.vin, v.response FROM orders o 
JOIN vin_queries v ON o.queryid = v.id order by o.id desc LIMIT ? OFFSET ?",["$lim","$offset"])
:
$dbConn->getRows("SELECT o.*, v.vin, v.response FROM orders o 
JOIN vin_queries v ON o.queryid = v.id  order by o.id desc LIMIT ? ",["$lim"]);
//
$rts = array();
if($rsp['code'] == 200){
$rws = $rsp['data'];
foreach ($rws as $key => $rw) {
$rw['response'] = json_decode($rw['response'],true);
$rw['items'] = json_decode($rw['items'],true);
$rts[] = $rw;
}
}

return $rts;

 }//getAdminVinReports


public function getAdminErrorReports($offset=false,$limit=false){
    $dbConn = new DbConn();
    $genClass = new GeneralClass();
    
//
$lim = (isset($limit) && $limit !== false) ? $limit : 12; 
$rsp = (isset($offset) && $offset !== false) ? 
$dbConn->getRows("SELECT m.*, o.url FROM missing_reports m 
JOIN orders o ON m.order_id = o.id
order by m.id desc LIMIT ? OFFSET ?",["$lim","$offset"])
:
$dbConn->getRows("SELECT m.*, o.url FROM missing_reports m 
JOIN orders o ON m.order_id = o.id
order by m.id desc LIMIT ? ",["$lim"]);
//
$rts = array();
if($rsp['code'] == 200){
$rws = $rsp['data'];
foreach ($rws as $key => $rw) {
//$rw['response'] = json_decode($rw['response'],true);
//$rw['items'] = json_decode($rw['items'],true);
$rts[] = $rw;
}
}

return $rts;

 }//getAdminErrorReports

 public function getPayments($offset=false,$limit=false){
    $dbConn = new DbConn();
    $genClass = new GeneralClass();
    $usr = $genClass->getUser();
    $thisuser = $usr['email'];
 
//
$lim = (isset($limit) && $limit !== false) ? $limit : 12; 
$rsp = (isset($offset) && $offset !== false) ? 
$dbConn->getRows("SELECT p.* FROM payments p WHERE p.user = ? order by p.id desc LIMIT ? OFFSET ?",["$thisuser","$lim","$offset"])
:
$dbConn->getRows("SELECT p.* FROM payments p WHERE p.user = ? order by p.id desc LIMIT ? ",["$thisuser","$lim"]);
//
$rts = array();
if($rsp['code'] == 200){
$rws = $rsp['data'];
foreach ($rws as $key => $rw) {
//$rw['response'] = json_decode($rw['response'],true);
$rts[] = $rw;
}
}

return $rts;

 }//getPayments


 public function getAdminPayments($offset=false,$limit=false){
    $dbConn = new DbConn();
    $genClass = new GeneralClass();
 
//
$lim = (isset($limit) && $limit !== false) ? $limit : 12; 
$rsp = (isset($offset) && $offset !== false) ? 
$dbConn->getRows("SELECT p.* FROM payments p order by p.id desc LIMIT ? OFFSET ?",["$lim","$offset"])
:
$dbConn->getRows("SELECT p.* FROM payments p  order by p.id desc LIMIT ? ",["$lim"]);
//
$rts = array();
if($rsp['code'] == 200){
$rws = $rsp['data'];
foreach ($rws as $key => $rw) {
//$rw['response'] = json_decode($rw['response'],true);
$rts[] = $rw;
}
}

return $rts;

 }//getAdminPayments


 public function getAdminPages(){
    $dbConn = new DbConn();
    $genClass = new GeneralClass();
$rsp = 
$dbConn->getRows("SELECT p.* FROM pages p order by p.id desc",[]);
//
$rts = array();
if($rsp['code'] == 200){
$rws = $rsp['data'];
foreach ($rws as $key => $rw) {
$rw['message'] = html_entity_decode($rw['message']);
$rts[] = $rw;
}
}

return $rts;

 }//getAdminPages


 public function getAdminFaq(){
    $dbConn = new DbConn();
    $genClass = new GeneralClass();
$rsp = 
$dbConn->getRows("SELECT p.* FROM faq p order by p.id desc",[]);
//
$rts = array();
if($rsp['code'] == 200){
$rws = $rsp['data'];
foreach ($rws as $key => $rw) {
$rw['answer'] = html_entity_decode($rw['answer']);
$rts[] = $rw;
}
}

return $rts;

 }//getAdminFaq



 public function getNotifications($offset=false,$limit=false){
    $dbConn = new DbConn();
    $genClass = new GeneralClass();
    $usr = $genClass->getUser();
    $thisuser = $usr['email'];
//
$lim = (isset($limit) && $limit !== false) ? $limit : 12; 
$rsp = (isset($offset) && $offset !== false) ? 
$dbConn->getRows("SELECT * FROM notifications WHERE user = ? order by  id desc LIMIT ? OFFSET ?",["$thisuser","$lim","$offset"])
:
$dbConn->getRows("SELECT * FROM notifications WHERE user = ? order by  id desc LIMIT ? ",["$thisuser","$lim"]);
//
$rts = array();
if($rsp['code'] == 200){
$rws = $rsp['data'];
foreach ($rws as $key => $rw) {
//$rw['response'] = json_decode($rw['response'],true);
$rts[] = $rw;
}
}

return $rts;

 }//getNotifications



 public function getAdminNotifications($offset=false,$limit=false){
    $dbConn = new DbConn();
    $genClass = new GeneralClass();
    $usr = $genClass->getUser();
    $thisuser = $usr['email'];
//
$lim = (isset($limit) && $limit !== false) ? $limit : 12; 
$rsp = (isset($offset) && $offset !== false) ? 
$dbConn->getRows("SELECT * FROM notifications order by  id desc LIMIT ? OFFSET ?",["$lim","$offset"])
:
$dbConn->getRows("SELECT * FROM notifications order by id desc LIMIT ? ",["$lim"]);
//

$rts = array();
if($rsp['code'] == 200){
$rws = $rsp['data'];
foreach ($rws as $key => $rw) {
//$rw['response'] = json_decode($rw['response'],true);
$rts[] = $rw;
}
}



return $rts;

 }//getAdminNotifications



 public function getAdminMessages($offset=false,$limit=false){
    $dbConn = new DbConn();
    $genClass = new GeneralClass();
//
$lim = (isset($limit) && $limit !== false) ? $limit : 12; 
$rsp = (isset($offset) && $offset !== false) ? 
$dbConn->getRows("SELECT m.* FROM messages m WHERE pid = 0  order by m.updated desc, m.id desc LIMIT ? OFFSET ?",["$lim","$offset"])
:
$dbConn->getRows("SELECT m.* FROM messages m WHERE pid = 0 order by m.updated desc, m.id desc LIMIT ? ",["$lim"]);
//
$rts = array();
if($rsp['code'] == 200){
$rws = $rsp['data'];
foreach ($rws as $key => $rw) {
//$rw['response'] = json_decode($rw['response'],true);
$rts[] = $rw;
}
}

return $rts;

 }//getAdminMessages

 public function getSubAccounts($offset=false,$limit=false){
    $dbConn = new DbConn();
    $genClass = new GeneralClass();
    $usr = $genClass->getUser();
    $thisuser = $usr['email'];
    $sql  = "SELECT m.*, w.balance FROM users m
    JOIN wallet w ON m.email = w.user
    WHERE m.base_user = ?
     order by m.id desc";

    //
$lim = (isset($limit) && $limit !== false) ? $limit : 12; 
$rsp = (isset($offset) && $offset !== false) ? 
$dbConn->getRows("SELECT m.*, w.balance FROM users m
    JOIN wallet w ON m.email = w.user
    WHERE m.base_user = ?
     order by m.id desc LIMIT ? OFFSET ?",["$thisuser","$lim","$offset"])
:
$dbConn->getRows("SELECT m.*, w.balance FROM users m
    JOIN wallet w ON m.email = w.user
    WHERE m.base_user = ?
     order by m.id desc LIMIT ? ",["$thisuser","$lim"]);
//

$rts = array();
if($rsp['code'] == 200){
$rws = $rsp['data'];
foreach ($rws as $key => $rw) {
//$rw['response'] = json_decode($rw['response'],true);
$rts[] = $rw;
}
}

return $rts;

 }//getSubAccounts


 public function getAdminSubAccounts($offset=false,$limit=false){
    $dbConn = new DbConn();
    $genClass = new GeneralClass();
    $sql  = "SELECT m.*, w.balance FROM users m
    JOIN wallet w ON m.email = w.user
    WHERE m.base_user = ?
     order by m.id desc";

    //
$lim = (isset($limit) && $limit !== false) ? $limit : 12; 
$rsp = (isset($offset) && $offset !== false) ? 
$dbConn->getRows("SELECT m.*, w.balance FROM users m
    JOIN wallet w ON m.email = w.user
    WHERE m.base_user <> ?
     order by m.id desc LIMIT ? OFFSET ?",["0","$lim","$offset"])
:
$dbConn->getRows("SELECT m.*, w.balance FROM users m
    JOIN wallet w ON m.email = w.user
    WHERE m.base_user <> ?
     order by m.id desc LIMIT ? ",["0","$lim"]);
//

$rts = array();
if($rsp['code'] == 200){
$rws = $rsp['data'];
foreach ($rws as $key => $rw) {
//$rw['response'] = json_decode($rw['response'],true);
$rts[] = $rw;
}
}

return $rts;

 }//getAdminSubAccounts



 public function getAdminUsers($offset=false,$limit=false){
    $dbConn = new DbConn();
    $genClass = new GeneralClass();
    //
$lim = (isset($limit) && $limit !== false) ? $limit : 12; 
$rsp = (isset($offset) && $offset !== false) ? 
$dbConn->getRows("SELECT m.*, w.balance FROM users m
    JOIN wallet w ON m.email = w.user
   
     order by m.id desc LIMIT ? OFFSET ?",["$lim","$offset"])
:
$dbConn->getRows("SELECT m.*, w.balance FROM users m
    JOIN wallet w ON m.email = w.user
     order by m.id desc LIMIT ? ",["$lim"]);
//

$rts = array();
if($rsp['code'] == 200){
$rws = $rsp['data'];
foreach ($rws as $key => $rw) {
//$rw['response'] = json_decode($rw['response'],true);
$rts[] = $rw;
}
}

return $rts;

 }//getUsers





public function createSubAccount($request){
$genClass = new GeneralClass();
$dbConn = new DbConn();
$settings = $genClass->getSettings();
$sendgridApi = $settings['sendgridApi'];
$password = $request['password'];
$password2 = $request['password2'];
$thisuser = $_SESSION['vinUser'];
$company = $request['company'];
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
    'company'=>$company,
    'base_user'=>$base_user, 
    'reg_time'=>$reg_time, 
    'verification_code'=>$code, 
    'last_seen'=>$reg_time
);

$qrs = $dbConn->insertDb($reLoad,'users');

$uid = $qrs['lastInsertId'];


$qucdf = $dbConn->insertDb(array('user'=>$email),'wallet');

////fire email

$list_prices = 
'<br>NMVTIS Report = N'.$settings['nmvtis_price'].'<br/>
AutoCheck Report = N'.$settings['autocheck_price'].'<br/>
CarFax Report = N'.$settings['carfax_price'].'<br/>
Copart Report = N'.$settings['copart_price'].'<br/>
Manheim Report = N'.$settings['manheim_price'].'<br/>
iaai Report = N'.$settings['iaai_price'].'<br/>
<br/>'
;
$to = $email;   
$senderemail ="support@vinrun.com";
$sendername ="VinRun Team";
$subject = "Your New Sub Account Created!";
$message = '<img src=https://vinrun.com/images/logo.png  height=50 /><br/><br/>
Thank you.<br><br/>
A new Sub Account has been created successfully for <u>$base_user</u>
<br/>
You can make payment online or directly to the account below<br>

<b>Account Name: iTech Partners<br>

GTBank Number: 0019640272</b><br/>

Call Charles on +2348032009053 for your account to be credited<br/><br/>

<h4>Pricing:</h4>
 '.$list_prices.'

<b>Download your reports:</b><br/>
Once you purchase a reports, they will be available for download on the VIN Reports page on your dashBoard.<br/>
You can also click Send to your email to get your report on your Registered email.<br/><br/>
 

Have a wonderful experience. <br><br/><br/>

For support, please contact Charles:<br/>
Call/SMS/Whatsapp: +2347053163837<br/>
Email: support@vinrun.com<br/>
';


$emailClass = new EmailClass($sendgridApi);
//$emailClass->sendEmail($senderemail,$sendername,$to,$subject,$message);
$sem = $emailClass->sendGrid($to, $senderemail, $subject,$message, array());

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
"company" => $company,
"mess" => $mess,
"class"=>"good center block"
);

return $rspx;

}

}


public function isEnoughBalance($user,$amount){
$dbConn = new DbConn();
$rq = $dbConn->getRow('SELECT balance FROM wallet WHERE user = ?',["user"]);
if($rq['code']==200){
$rsp = $rq['data'];
$isOk = ($rsp['balance']>=$amount) ? true:false;
}else{
$isOk = false;
}
return $isOk;
}//isEnoughBalance

public function affectWallet($user,$amount,$type){
$dbConn = new DbConn();
$rq = $dbConn->getRow('SELECT user,balance FROM wallet WHERE user = ?',["$user"]);
if($rq['code']==200){
$rsp = $rq['data'];

if($type == 'debit'){

$isEnough = ($rsp['balance']>=$amount) ? true:false;
if($isEnough){
$rws = $dbConn->executeSql("UPDATE wallet SET balance = balance-? WHERE user = ?",["$amount","$user"]);
if($rws['code'] == 200){
$response = array('mess'=>'Wallet Debited','state'=>'1','done'=>true);
}else{
$response = array('mess'=>'Unable to debit Wallet','state'=>'0','done'=>false);    
}
return $response;
}else{
return array('mess'=>'Insufficient Wallet Balance','state'=>'0','done'=>false);    
}

}elseif($type == 'credit'){
$rws = $dbConn->executeSql("UPDATE wallet SET balance = balance+? WHERE user = ?",["$amount","$user"]);
if($rws['code'] == 200){
$response = array('mess'=>'Wallet Credited','state'=>'1','done'=>true);
}else{
$response = array('mess'=>'Unable to credit Wallet','state'=>'0','done'=>false);    
}
return $response;
}//typeisCredit

}else{
return  array('mess'=>'Invalid User','state'=>'0','done'=>false);    

}


}//isEnoughBalance



public function dispatchOrder($orderID){
$dbConn = new DbConn();
$genClass = new GeneralClass();
$providerClass = new ProvidersClass();
$usr = $genClass->getUser();
$user = $usr['email'];
$uid = $usr['id'];

$qri = $dbConn->getRow("SELECT * FROM orders WHERE id = ?",["$orderID"]);
if($qri['code'] ==200 && $qri['data'] !== false) {
$rpts = $ro = $qri['data'];
$isOr = count($rpts);
}else{
$rpts = [];
$status = false;
return array(
    "mess"=>'Order search was empty',
    "status"=>$status,
    "done"=>$status,
    "class"=>"error",
    "url"=>""
    );
}



$queryID = $rpts['queryid'];
//

$checklist = json_decode($rpts['items'],true);  




if(count($checklist) > 0) {
    $mess  = 'At least one report found'; 
    $rx =  explode("_", $ro['url']);
    $vin = '';
    $roo = 'files/reports/';

$rds = $added = array();
foreach ($checklist as $key => $rw) {
$rname = $rw['name'];
$vin = $rw['vin'];
$file_name = $vin.'_'.$queryID.'_'.$rname.'_file.pdf';
/*$reqe = $providerClass->autoVinGetReportsQuery($vin,$rname,$file_name);

if($reqe){
$rw['file_url'] = $roo.$file_name;    
}else{
$rw['file_url'] = '';    
}

if(!empty($rw['file_url'])){}
$rds[] = $rw;

*/
$rw['file_url'] = $roo.$file_name; 
$rds[] = $rw;   
$added[] = true;
//foreach
}


if(count($added) > 0){
$link = '<u><a href="account/report-details/'.$ro['url'].'">Order Link</a></u> ';
$mess = "Your Report is ready please visit ".$link." to download your report(s)";
$mess .= '';
$class = "good";
$status = $done =  true; 
$url = 'account/report-details/'.$ro['url'];
for ($x=0; $x < count($rds); $x++) { 
$dname = $rds[$x]['name'].'_file'; 
$file = $rds[$x]['file_url'];
$qrex = $dbConn->executeSql("UPDATE orders SET $dname = ?, status = ? WHERE id = ?",["$file","1","$orderID"]);
}//for loop
}else{
$mess = "No report order found. Please contact admin.";
$class = "error";
$status = $done = false; 
$url = '';
}
return array(
    "mess"=>$mess,
    "status"=>$status,
    "done"=>$status,
    "class"=>$class,
    "url"=>$url
    );

}else{//checklist empty
return array(
    "mess"=>"No report saved",
    "status"=>false,
    "done"=>false,
    "class"=>"error",
    "url"=>""
    );    
}//checklist empty ends

}//dispatchOrder




}//DashClass


?>