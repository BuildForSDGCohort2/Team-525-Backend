
<?php 
/**
 
 */
class ReferralClass
{/**/

function __construct(){}

public function saveReferee($data){
$fr=array(
  "user" => $status, 
"referee" => $message, 
"mess" => $message, 
"sent" => $sent
);
}
//saveReferee

public function getReferral(){
$genClass = new GeneralClass();
$dbConn = new DbConn();
$usr = $genClass->getUser();
$user = $usr['email'];
$qt = $dbConn->getRow($mysqli,"SELECT count(id) As refNums FROM referrals WHERE referee = ? order  by id  desc",["$user"]);
$rw  =  $qt["data"];
//
$pt = $dbConn->getRows("SELECT rs.*, rs.status AS redeem_status, u.firstname, u.surname FROM referral_settlements rs
 LEFT JOIN users u ON rs.referred_user = u.email
WHERE rs.referee = ? ORDER BY   rs.id  DESC",["$user"]);
$ro  =  $pt['data'];
$arr2  =  array();
foreach ($ro as $key => $rwx) {
$arr2[] = $rwx;
}
//
$pr = $dbConn->getRows("SELECT  sum(rs.amount_paid) AS total_revenue, c.firstname, c.surname, rs.referee FROM referral_settlements rs
JOIN users c ON rs.referee = c.email WHERE  rs.status <> ? GROUP BY rs.referee LIMIT 10",["2"]);
$rr  =  $pr['data'];
$arr3  =  array();
foreach ($rr as $key => $rwr) {
$rwr['total_revenue'] = (float)$rwr['total_revenue'];
$arr3[] = $rwr;
}
//
$rvx = $dbConn->getRow("SELECT  sum(amount_paid) AS total_revenue FROM referral_settlements
WHERE referee = ? AND status <> ?",["$user","2"]);
$rv = $rvx['data'];
$total_revenue = ($rv['total_revenue'] > 0) ? (float)$rv['total_revenue']:0;


return array(
"ref" => $rw['refNums'],
"ref_revenue" => $arr2,
"leaders" => $arr3,
"total_revenue" => $total_revenue
);

}//getRerral




public function sendReferralInvite($data){
$genClass = new GeneralClass();
$dbConn = new DbConn();
$emailClass new EmailClass();
$smsClass = new SmsCLass();
$usr = $genClass->getUser();
$user = $usr['email'];
/*
$ar = array("{referralLink}", "{sitename}");
$br = array($my_referral_link, $site_short_name);
$mBody = str_replace($ar, $br, $site_referral_whatsapp_message);
$messBody = html_entity_decode($mBody);
*/ 
$mode = $data['mode'];
$res  = array();
//
$name = $usr['firstname'].' '.$usr['surname'];
$usname = $usr['username'];
if($usr['user_type'] == 'user'){
$utp = 'user';  
}elseif($usr['user_type'] == 'mentor'){
$utp = 'mentor';  
}
$my_referral_link = $genClass->getbaseUrl().'/r/'.$usname;

if($mode == 'phone'){
$inum = array();
for($x = 0; $x < count($data['data']); $x++){
$num = $data['data'][$x]['number'];
if(!empty($num)){
$inum[] = $num;    
}
}
$numbers = $genClass->alterNumbers($inum);
for ($i=0; $i < count($numbers) ; $i++) { 
$messBody = "
$name requested we ask you to join $site_short_name.
If you are interested please click the link below to join.
".$my_referral_link."
Hoping to see you soon.
";
//$link = 'https://api.whatsapp.com/send?phone='.$numbers[$i].'&text='.urlencode($messBody).'';

$res[] = $smsClass->sendSms($messBody, 'Sensei.ng', $numbers[$i]);
////$res[] =file_get_contents($link);
}

}elseif ($mode == 'email') {
$list = explode(",", $request['data']['invite_mail']);
$imail = array();
for($x = 0; $x < count($list); $x++){
$to = $list[$x];
$subject = 'Your Friend - '.$name.' Sent an Invitation';
$sendername = $name;
$senderemail = $settings['support_email'];
$messBody = "
Hello!<br>
<p>Your friend - $name asked us to send you an invitation to join <strong>$site_short_name</strong>.<br>
<strong>$site_short_name</strong> is an online learning community for tertiary students.<br>
Here students can share and learn from the insights of other students and academics.
</p>
<p>
If you are interested please click the button below to join.<br>
<a class='btn-email' href='".$my_referral_link."'>
Confirm Registration
</a>
</p>
<p>
If the button doesn't work, please copy the link below and paste on your browser to access the link.</p>
".$my_referral_link."
<p>Hoping to see you soon.</p>
";
/*
echo 'Sender email : '.$senderemail.
'<br> Sender name : '.
$sendername.
'<br> To : '.$to.
'<br> Subjects'.$subject.
'Body :'.$messBody;
exit();
*/
$res[] = $emailClass->sendPlain($senderemail,$sendername,$to,$subject,$messBody,$type='blue');


if(!empty($num)){}



}//loop


}

$goodMess = ' <i class="fas fa-check-circle"></i> Invitation Dispatched';

return array(
  "response" => $res,
  "message" => $goodMess,
  "status" => 1
);



}



public function redeemReferral($idata){

$genClass = new GeneralClass();
$dbConn = new DbConn();
$usr = $genClass->getUser();
$user = $usr['email'];
$data = $idata['data'];
$mode = $data['mode'];
$amount = $data['amount'];
$rdate = time();
$riu = $dbConn->getRow("SELECT  sum(amount_paid) AS total_revenue FROM referral_settlements
WHERE referee = ? AND status <> ?".["$user","2"]);
$rv = $riu['data'];
$total_revenue = ($rv['total_revenue'] > 0) ? (int)$rv['total_revenue']:0;

if($amount <= $total_revenue){

if($mode == 'bank'){
$bnk = array();
$bnk['bank_name'] = $data['bank_name'];
$bnk['bank_account_number'] = $data['bank_account_number'];
$bnk['bank_account_name'] = $data['bank_account_name'];
$bank_details = json_encode($bnk);
//
$eload = array('user'=>$user, 'mode'=>$mode, 'bank_details'=>$bank_details, 'amount'=>$amount, 'rdate'=>$rdate);
}elseif($mode == 'wallet'){
$eload = array('user'=>$user, 'mode'=>$mode,  'amount'=>$amount, 'rdate'=>$rdate);
}


if($mode == 'wallet'){
if($genClass->creditUser($user,$amount)){
$sdc = $dbConn->executeSql("UPDATE referral_settlements SET status = ? WHERE status <= ? AND referee = ? ",["2","1","$user"]);
$sdd = $dbConn->insertDb($eload,'referral_redemptions');    
}
}elseif($mode == 'bank'){
$sdc = $dbConn->executeSql("UPDATE referral_settlements SET status = ? WHERE status <= ? AND referee = ?",["2","1","$user"]);
$sdd = $dbConn->insertDb($eload,'referral_redemptions');    
}

 
if($sdd['code'] == 200 && $sdc['code'] ==200){
$status = '1'; $message = 'Done! Referral Earnings Redeemed';
}else{
$status = '0'; $message = 'Not Done! Referral Earnings Cannot be redeemed at this time'. $act.' now. Try again later.';  
}


  
}else{
$status = '0'; $message = 'Not Done! You have not earned up to the amount you tried to redeem. Reduce to correct amount and try again.';    
}


return array (
	"status" => $status, 
	"message" => $message
);

}//redeemReferral





public function withdraw_earn($request){
$genClass = new GeneralClass();
$dbConn = new DbConn();
$usr = $genClass->getUser();
$user = $usr['email'];
$data = $request;
$amount = $data['amount'];
$rdate = time();
//
$bank_name = $data['bank_name'];
$account_number = $data['account_number'];
$account_name = $data['account_name'];
$account_type = $data['account_type'];
//
if($amount <= $usr['balance']){
if($genClass->debitUser($user,$amount)){
$aeload = array('user'=>$user,  'bank_name'=>$bank_name, 'account_name'=>$account_name, 'account_number'=>$account_number, 'account_type'=>$account_type,  'amount'=>$amount, 'wdate'=>$rdate);
$sdd = $dbConn->insertDb($aeload,'earning_withdrawals'); 
if($sdd){
$status = '1'; $message = 'Done!  Withdrawal request Sent';
$sent = true;
}else{
$status = '0'; $message = 'Not Done! Withdrawal request Cannot be sent at this time. Try again later.'; 
$sent = false; 
}

} else{
 $status = '0'; $message = 'Not Done! Wallet Debit Error!.';
 $sent = false;   
}
}else{
 $status = '0'; $message = 'Not Done! Insufficient Wallet balance.';
 $sent = false;   
}


return array(
  "status" => $status, 
"message" => $message, 
"mess" => $message, 
"sent" => $sent
);


}//withdrawEarn







}//ReferralClass

 ?>

