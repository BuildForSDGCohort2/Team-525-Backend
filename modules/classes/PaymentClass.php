<?php 
/**
 * 
 */
class PaymentClass
{
  function __construct()
  {
  }

public function isEnoughBalance($user,$cost){
$genClass = new GeneralClass();
$dbConn = new DbConn();
$usr = $genClass->getUser();
$user = $usr['email'];
$sql = "SELECT * FROM wallet WHERE user = ?";
$qr = $dbConn->getRow($sql,["$user"]);
$rw = $qr['data'];
//
if($qr['code'] == 200 && $rw['balance'] >= $cost){
return true;
}elseif($qr['code'] == 200 && $rw['balance'] < $cost){
return false;     
}else{
return false;           
}

}



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




public function getPayDetails($datax){
$genClass = new GeneralClass();
$dbConn = new DbConn();
$datax['service_cost'] = floatval($datax['service_cost']);
if(isset($_SESSION['senseiUser']) || isset($_SESSION['senseiMentor'])){
$usr = $genClass->getUser();
$thisuser = $usr['email'];
$q3 = $dbConn->getRow(
"SELECT b.balance, u.phone,  u.email, u.user_type, u.firstname, u.surname  FROM users u
JOIN wallet b ON u.email =  b.user WHERE b.user = ?",["$thisuser"]);
$row3 = $q3['data'];
$row3['balance'] = floatval($row3['balance']);
$row3['dir'] = $row3['user_type'];
//
$mt = array();
$mt['custom_fields'] = array();
$mt['custom_fields'][0]['display_name'] = $row3['firstname'].' '.$row3['surname'];
$mt['custom_fields'][0]['variable_name'] =  'phone';
$mt['custom_fields'][0]['value'] = $thisuser;
$row3['metadata'] = $mt;
$row3['isLogged'] = true;

$fru = strtoupper(substr($thisuser, 0, 3));
$ini  = "SEN".$genClass->crand(3).time().$fru;
$tr = time();
$trf = substr($tr, 0, 8);

$row3['ref'] = $row3['reference'] = $ini;
$row3['transferCode'] = $trf;

}else{
$row3 = array();
$row3['balance'] = -1;
$row3['email'] = null;
$row3['phone'] = $row3['userPhone'] =null;

$mt = array();
$mt['custom_fields'] = array();
$mt['custom_fields'][0]['display_name'] = "";
$mt['custom_fields'][0]['variable_name'] =  'phone';
$mt['custom_fields'][0]['value'] =  $thisuser;

$row3['metadata'] = $mt;
$row3['isLogged'] = false;

$fru = 'GUE';   
$ini  = "SEN".$genClass->crand(3).time().$fru;
$tr = time();
$trf = substr($tr, 0, 8);

$row3['ref'] = $row3['reference'] = $ini;
$row3['transferCode'] = $trf;

}
//
$settings = $genClass->getSettings();
$rws = $settings;
$datax['paystack_pk_key'] = $rws['paystack_pk_key'];
///
if(isset($datax['service_discount']) && $datax['service_discount'] > 0){
$datax['beforeDue'] = floatval($datax['service_cost'] + $datax['service_charge']);
$datax['dueAmount'] = floatval($datax['beforeDue'] - getPerc($datax['beforeDue'],$datax['service_discount']));
}else{
$datax['dueAmount'] = floatval($datax['service_cost'] + $datax['service_charge']);
$datax['beforeDue'] = floatval($datax['service_cost'] + $datax['service_charge']);

}
$datax['amount']  =  $datax['dueAmount']*100;

$firstArray = $datax;
$secondArray = $row3;
$joinArray = array_merge($firstArray,$secondArray);
//$thirdArray = array('transactionData' => $joinArray);
//$payLoad = array_merge($joinArray,$thirdArray);

//
return $joinArray;


}


public function initiateTransaction($request){
$genClass = new GeneralClass();
$dbConn = new DbConn();
$usr =  $genClass->getUser(); 
$customer = $usr['email']; 
$ref = $request['reference'];
$amt = $request['amount'];
$article_id = $request['article_id'];
$transactionName = $request['transactionName'];
$amount = $amt/100;
$time = time();

$check = $dbConn->getRows("SELECT ref FROM payments WHERE ref = ?",["$ref"]);
$isPayed = count($check['data']);

if($isPayed == 0){
$pload = array('amount'=>$amount, 'user'=>$customer,  'ref'=>$ref, 'pdate'=>$time, 'paymode'=>'card', 'payvendor'=>'paystack');
$qx = $dbConn->insertDb($pload,'payments');
$uid = $qx['lastInsertId'];
//
$trload = array('name'=>$transactionName, 'cost'=>$amount, 'article_id'=>$article_id, 'reference'=>$ref, 'tdate'=>$time);
$qz = $dbConn->insertDb($trload,'transactions');
$rsp = array("uid"=>$uid,"ref"=>$ref); 
}else{

$rsp = array("uid"=>'-1',"ref"=>'null');     
}


return $rsp;  

}



public function markTransaction($request){
$dbConn = new DbConn();
$ref = $request['reference'];
$status = $request['status'];
$qz = $dbConn->getRow("UPDATE transactions SET status = ? WHERE reference = ?",["$status","$ref"]);
if($qz['code'] ==200){$status = '1'; $mess = 'Success';}else{ $status = '0'; $mess = 'Failed';}
$sendr = $this->sendReceipt($ref);
return array("status" => $status,
"mess" => $mess
);

}



public function sendReceipt($ref){
$dbConn = new DbConn();
$emailClass = new EmailClass();
$sql = "SELECT t.name, t.cost, t.reference, t.tdate, t.status AS transaction_status, p.amount, p.ref, p.user, p.paymode, p.payvendor, p.status as pay_status, 
u.firstname, u.surname
FROM transactions t
JOIN payments p ON t.reference = p.ref
JOIN users u ON p.user = u.email
 WHERE t.reference = ?";
$qr = $dbConn->getRow($sql,["$ref"]);
$rw = $qr['data'];
$isBal = count($rw);
$rw['toName'] = $rw['firstname'].' '.$rw['surname'];
$rw['to'] = $rw['user'];
if($rw['pay_status'] == '1'){$type = 'blue';}
elseif($rw['pay_status'] == '-1'){
$type = 'red';
}

$senda = $emailClass->sendTxnReceipt($rw,$type);

return $senda;

}//sendReceipt







public function creditWallet($request,$username=false){
$dbConn = new DbConn();
$emailClass = new EmailClass();
$reference = $request['reference'];
if(isset($username) && $username != false){
$usr = $genClass->getUserFromUsername($username);
$user = $usr['email'];
}elseif(!isset($username) || $username == false){
$usr =  $genClass->getUser(); 
$user = $usr['email']; 
}
//
$rz = $dbConn->getRow("SELECT amount FROM payments WHERE ref = ?",["$reference"]);
$rw = $rz['data'];
$amount   = $rw['amount'];
//
$sql = "SELECT * FROM wallet WHERE user = ?";
$qr = $dbConn->getRow($sql,["$user"]);
$rw = $qr['data'];
$isBal = count($rw);
$curBalance = $rw['balance'];
$prevBalance = $rw['previous_balance'];
//
$newBalance = $curBalance+$amount;
$scred = "UPDATE wallet SET balance = ?, previous_balance = ? WHERE user = ?";
$cred = $dbConn->executeSql($scred,["$newBalance","$curBalance","$user"]);
if($cred['code'] == 200){
$mess = ' <i class="fas fa-check-circle good"></i> Account Credited successfully..';
$status = true;
$class = "good";
}else{
$mess = '<i class="fas fa-exclamation-triangle error"></i> Account Could not be Credited';
$status = false;
$class = "error";
}

return array(
"status" => $status,
"message" => $mess,
"reference" => $reference,
"class" => $class
); 

}//creditWallet




public function settleUser($request){
$dbConn = new DbConn();
$emailClass = new EmailClass();
$genClass = new GeneralClass();
$reference = $request['reference'];
$settings = $genClass->getSettings();
$settlement_rate = $settings['settlement_rate'];
$sqx = "SELECT t.*, p.user, a.mentor_id, a.title, a.url FROM transactions t
JOIN articles a ON t.article_id = a.id
JOIN payments p ON t.reference = p.ref
 WHERE t.reference = ?";
$ryu = $dbConn->getRows($sqx,["$reference"]);
$rw = $ryu["data"];
$isTr = count($rw);
//
$rxu = $dbConn->getRow($sqx,["$reference"]);
$rws = $rxu["data"];
//
$total_amount = $rws['cost'];
$user = $rws['mentor_id'];
//
$service_amount = ($total_amount/100)*$settlement_rate;
$amount_due = $total_amount-$service_amount;
$sdate = time();
//
$sloader = array('transaction_id'=>$rws['id'], 'user'=>$user, 'total_amount'=>$total_amount, 'service_amount'=>$service_amount, 'sdate'=>$sdate);
$sst = $dbConn->insertDb($sloader,'sales_settlement');
//
$sql = "SELECT * FROM wallet WHERE user = ?";
$qr = $dbConn->getRow($sql,["$user"]);
$rw = $qr['data'];
$curBalance = $rw['balance'];
$prevBalance = $rw['previous_balance'];
//
$newBalance = $curBalance+$amount_due;
$scred = "UPDATE wallet SET balance = ?, previous_balance = ? WHERE user = ?";
$cred = $dbConn->executeSql($scred,["$newBalance","$curBalance","$user"]);
if($cred['code'] == 200){
$mess = ' <i class="fas fa-check-circle good"></i> Author Settled successfully..';
$status = true;
$class = "good";
}else{
$mess = '<i class="fas fa-exclamation-triangle error"></i> Author Could not be Settled';
$status = false;
$class = "error";
}

 $rsps = array(
"status" => $status,
"message" => $mess,
"class" => $class
);


 
return $rsps;


}





public function dispatchArticle($request,$username=false){
$dbConn = new DbConn();
$emailClass = new EmailClass();
$genClass = new GeneralClass();
$notifyClass = new NotificationClass();
$articleClass = new ArticleSClass();
$reference = $request['reference'];
if(isset($username) && $username != false){
$usr = $genClass->getUserFromUsername($username);
$user = $usr['email'];
}else if(!isset($username) || $username == false){
$usr =  $genClass->getUser(); 
$user = $usr['email']; 
}
//
$rzq = $dbConn->getRow("SELECT * FROM transactions WHERE reference = ?",["$reference"]);
$rw = $rzq['data'];
$article_id   = $rw['article_id'];
//
$sql = "SELECT * FROM payments WHERE ref = ?";
$qr = $dbConn->getRow($sql,["$reference"]);
$rw1 = $qr['data'];
$payment_id = $rw1['id'];
//
$dsload = array('article_id'=>$article_id, 'payment_id'=>$payment_id, 'user'=>$user);
$cred = $dbConn->insertDb($dsload,'article_purchases');
if($cred['code']==200){
$mess = ' <i class="fas fa-check-circle good"></i> Article Saved successfully to your article library.';
$status = true;
$arts = $articleClass->getArticleById($article_id);
$author = $arts['mentor_id'];
$ust = $genClass->getUserFromEmail($rw1['user']);
$detail = '<p>There has been a successful purchase of your article - <u>'.$arts['title'].'</u> by <a href="profile/'.$ust['username'].'">'.$ust['firstname'].' '.$ust['surname'].'</a>.</p>
<p>Please check your wallet for  your transaction and settlement details.</p>
<p>Thank you for being a resourceful member.</p>
';
$ntf = $notifyClass->notifyUser($detail,$author);
$class = "good";
}else{
$mess = '<i class="fas fa-exclamation-triangle error"></i> Article Could not be Saved to your library';
$status = false;
$class = "error";
}

return array(
"status"=>$status,
"message"=>$mess,
"reference"=>$reference,
"class"=>$class
); 

}




public function nullifyTransaction($request){
$dbConn = new DbConn();
$genClass = new GeneralClass();
$ref = $request['ref'];
$ida = '{"status":"closed"}';
$qx = $dbConn->executeSql("UPDATE payments SET status = ?, data = ? WHERE ref =  ?",["-1","$ida","$ref"]);
if($qx){$status =  true;}else{ $status =  false;}

return array("status" => $status);
}



public function verifyPayStackTransaction($ref,$sk){
$dbConn = new DbConn();
$emailClass = new EmailClass();
$genClass = new GeneralClass();
$ref = $ref;
$url = 'https://api.paystack.co/transaction/verify/'.$ref;
$headers = array(
    'Authorization: Bearer '.$sk
);

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_HEADER, 0);
curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
$result = curl_exec($curl);
curl_close($curl);
$res = json_decode($result,true);

$qr = $dbConn->getRow("SELECT * FROM payments WHERE ref = ?",["$ref"]);
$irw = $qr['data'];
///
$message = $res['message'];
$gateway_response = $res['data']['gateway_response'];
$transaction_date = $res['data']['transaction_date'];
$amount = $res['data']['amount'];
$trueamount = $amount/100;
$data = json_encode($res['data']);
$user = $irw['user'];
$status = $res['data']['status'];
if($status=='success'){$istat='1'; $class="good";}
elseif ($status=='failed') {$istat='-1'; $class="error";}
elseif ($status=='abandoned') {$istat='-2'; $class="error";}
else{$istat='-1'; $class="error";}
//    

$qx = $dbConn->executeSql("UPDATE payments SET
user = ?, amount = ?, data = ?, status = ?
WHERE ref = ?",["$user","$trueamount","$data","$istat","$ref"]);
//$lid = $qx['lastInsertId'];



$rsps =  array(
      'message' => $message,
      'gateway_response' => $gateway_response,
      'amount' => $amount,
      'status' => $istat,
      'transaction_date' => $transaction_date,
      'data' => $data,
      'class' => $class 
);



return $rsps;


}




public function verifyPaystackDirectTransaction($request){
$dbConn = new DbConn();
$genClass = new GeneralClass();
$ref = $request['ref'];
$res = $this->verifyPayStackTransaction($ref,$paystack_key);
$qr = $dbConn->getRow("SELECT * FROM payments WHERE ref = ?",["$ref"]);
$irw = $qr['data'];
///
$message = $res['message'];
$gateway_response = $res['gateway_response'];
$transaction_date = $res['transaction_date'];
$amount = $res['amount'];
$trueamount = $amount/100;
$data = $res['data'];

$user = $irw['user'];
$status = $res['status'];
$istat = $status;
$class = $res['class'];


if(($irw['amount'] >= $trueamount) && ($status=='1')) {
$mess = ' <i class="fas fa-check-circle good"></i> Your payment was completed successfully';
$status = $istat;
}elseif($irw['amount'] < $trueamount && ($status=='1')){
$mess = '<i class="fas fa-exclamation-triangle error"></i> Your payment was received but the amount you paid (<s>N</s>'.number_format($trueamount).') did not tally with the amount you initiated for funding.';
$status = '0';
}elseif($status=='-1'){
$mess = ' <i class="fas fa-exclamation-triangle error"></i> Your payment was not successful -  <u>'.$gateway_response.'</u>';
$status = '0';
}elseif($status=='-2'){
$mess = '<i class="fas fa-exclamation-triangle error"></i> Your payment was not successful -  <u>'.$gateway_response.'</u>';
$status = '0';
}else{
$mess = '<i class="fas fa-exclamation-triangle error"></i> Your payment was not successful -  <u>'.$gateway_response.'</u>';
$status = '0';  
}

return array(
"message"=>$mess,
"amount"=>$amount,
"trueamount"=>$trueamount,
"class"=>$class,
"gateway_response"=>$gateway_response,
"status"=>$status
);

}



public function genRef(){
$genClass = new GeneralClass();
$usr =  $genClass->getUser(); 
$thisuser = $usr['email'];
$fru = strtoupper(substr($thisuser, 0, 4));
$ini  = "SEN".$genClass->crand(4).time().$fru;

return array("ref" => $ini);

}








}//PaymentClass

 ?>