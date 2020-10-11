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
$dbConn = new DbConn;
$payClass = new PaymentClass;
$dashClass = new DashClass;
#
$postdata = file_get_contents("php://input");
$request = json_decode($postdata,true);



if(is_array($request) && array_key_exists('action',$request)) { 
$action = $request['action'];
}else{
$action = false;
$request = array('action'=>false);
}


##
if(isset($_REQUEST['data'])){
$formdata = json_decode($_REQUEST['data'],true);
}



if($action == 'getPayDetails'){

$cart = $request['article_data'];

$total_cost = floatval($cart['price']);
$settings = $genClass->getSettings();
$row3 = $genClass->getUser();
$thisuser = $row3['email'];
$now = time();

$datax = array();
$datax['email'] = $thisuser;
$datax['service_cost'] = $total_cost;
$datax['service_charge'] = 0.00;
if(isset($_SESSION['senseiUser']) || isset($_SESSION['senseiMentor'])){
$mt = array();
$mt['custom_fields'] = array();
$mt['custom_fields'][0]['display_name'] = $row3['firstname'].' '.$row3['surname'];
$mt['custom_fields'][0]['variable_name'] =  'phone';
$mt['custom_fields'][0]['value'] = $thisuser;
$datax['metadata'] = $mt;
$datax['isLogged'] = true;

$fru = strtoupper(substr($thisuser, 0, 3));
$ini  = "SEN".$genClass->crand(3).time().$fru;
$tr = time();
$trf = substr($tr, 0, 8);
$datax['ref'] = $datax['reference'] = $ini;
}else{
$row3 = array();
$datax['balance'] = -1;
$datax['email'] = null;
$datax['phone'] = $datax['userPhone'] = null;
$mt = array();
$mt['custom_fields'] = array();
$mt['custom_fields'][0]['display_name'] = "";
$mt['custom_fields'][0]['variable_name'] =  'phone';
$mt['custom_fields'][0]['value'] =  $thisuser;

$datax['metadata'] = $mt;
$datax['isLogged'] = false;

$fru = 'gue';   
$ini  = "SEN".$genClass->crand(3).time().$fru;
$tr = time();
$trf = substr($tr, 0, 8);

$datax['ref'] = $datax['reference'] = $ini;

}
//
$datax['paystack_pk_key'] = $settings['paystack_pk_key'];
///
if(isset($datax['service_discount']) && $datax['service_discount'] > 0){
$datax['beforeDue'] = floatval($datax['service_cost'] + $datax['service_charge']);
$datax['dueAmount'] = floatval($datax['beforeDue'] - getPerc($datax['beforeDue'],$datax['service_discount']));
}else{
$datax['dueAmount'] = floatval($datax['service_cost'] + $datax['service_charge']);
$datax['beforeDue'] = floatval($datax['service_cost'] + $datax['service_charge']);
}
$datax['is_enough_balance']  =  $payClass->isEnoughBalance($thisuser,$datax['dueAmount']);
$datax['amount']  =  $datax['dueAmount']*100;
$datax['transactionName']  =  $cart['transactionName'];
$joinArray = $datax;


header('content-type: application/json');
echo json_encode($joinArray);
exit();


}


if($action == 'getFundDetails'){
$row3 = $genClass->getUser();
$thisuser = $row3['email'];
$now = time();
$data = $request['data'];
if($data){
//
$datax = array();
$datax['email'] = $thisuser;
$datax['service_cost'] = floatval($data['service_cost']);
$datax['service_charge'] = 0.00;
if(isset($_SESSION['senseiMentor'])
	|| isset($_SESSION['senseiUser'])){
$mt = array();
$mt['custom_fields'] = array();
$mt['custom_fields'][0]['display_name'] = $row3['firstname'].' '.$row3['surname'];
$mt['custom_fields'][0]['variable_name'] =  'phone';
$mt['custom_fields'][0]['value'] = $thisuser;
$datax['metadata'] = $mt;
$datax['isLogged'] = true;

$fru = strtoupper(substr($thisuser, 0, 3));
$ini  = "SEN".$genClass->crand(3).time().$fru;
$tr = time();
$trf = substr($tr, 0, 8);
$datax['ref'] = $datax['reference'] = $ini;
}else{
$row3 = array();
$datax['balance'] = -1;
$datax['email'] = null;
$datax['phone'] = $datax['userPhone'] = null;
$mt = array();
$mt['custom_fields'] = array();
$mt['custom_fields'][0]['display_name'] = "";
$mt['custom_fields'][0]['variable_name'] =  'phone';
$mt['custom_fields'][0]['value'] =  $thisuser;

$datax['metadata'] = $mt;
$datax['isLogged'] = false;

$fru = 'GUE';   
$ini  = "SEN".$genClass->crand(3).time().$fru;
$tr = time();
$trf = substr($tr, 0, 8);

$datax['ref'] = $datax['reference'] = $ini;

}
//
$rty = $dbConn->getRow("SELECT paystack_pk_key FROM settings",[]);
$rws = $rty['data'];
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
$datax['transactionName']  =  'Wallet Funding';
$datax['pay_mode']  =  'wallet';
$datax['pay_vendor']  =  'site';
$datax['is_parsed']  =  true;
$joinArray = $datax;
}else{
$joinArray = $request['data'];
}



header('content-type: application/json');
echo json_encode($joinArray);
exit();


}





if($action=='initiateTransaction'){
$res = $payClass->initiateTransaction($request);
header('content-type: application/json');
echo json_encode($res);
exit();	

}

if($action=='submitPayment'){


$res = $payClass->initiateTransaction($request);
header('content-type: application/json');
echo json_encode($res);
exit();	

}


if($action=='fundWallet'){
$res = $payClass->fundWallet($request);
header('content-type: application/json');
echo json_encode($res);
exit();	

}








if($action=='dispatchArticle'){
$rsp = $payClass->dispatchArticle($request);
header('content-type: application/json');
echo json_encode($rsp);
exit(); 
}




if($action=='settleUser'){
$rsp = $payClass->settleUser($request);
header('content-type: application/json');
echo json_encode($rsp);
exit();
}




if($action=='nullifyTransaction'){
$ref = $request['ref'];
$ida = '{"status":"closed"}';
$srt = "UPDATE payments SET status = ?, data = ? WHERE ref =  ?";
$qx = $dbConn->executeSql($srt,["-1","$ida","$ref"]);
if($qx['code'] == 200){$status =  true;}else{ $status =  false;}

header('content-type: application/json');
echo '{"status":'.json_encode($status).'}';
exit();	


}

if($action == 'verifyPaystackDirectTransaction'){
$ref = $request['ref'];
$ukey = $genClass->getSettings();
$paystack_key = $ukey['paystack_key'];
$res = $payClass->verifyPayStackTransaction($ref,$paystack_key);

header('content-type: application/json');
echo json_encode($res);
exit();
}

if($action == 'verifyWalletPay'){
$res = $payClass->verifyWalletPayment($request);

header('content-type: application/json');
echo json_encode($res);
exit();
}

if($action=='nullifyTransaction'){
$dbConn = new dbConn();
$ref = $request['ref'];
$ida = '{"status":"closed"}';
$qx = $dbConn->executeSql("UPDATE payments SET status = ?, data = ? WHERE ref =  ?",["-1","$ida","$ref"]);
if($qx['code'] == 200){$status =  true;}else{ $status =  false;}
#
header('content-type: application/json');
echo '{"status":'.json_encode($status).'}';
exit();	


}


if($action=='markTransaction'){
$res = $payClass->markTransaction($request);
header('content-type: application/json');
echo json_encode($res);
exit(); 
}



if($request['action']  ==   'genRef'){
$fru = strtoupper(substr($genClass->getUser(), 0, 4));
$ini  = "SEN".$genClass->crand(6).time().$fru;
header('content-type: application/json');
echo '{"ref":'.json_encode($ini).'}';
exit();
}




?>