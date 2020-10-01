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



if(isset($_REQUEST['data'])){
$formdata = json_decode($_REQUEST['data'],true);
}


if($request['action']  ==   'check_bal'){
    $usr = $genClass->getUser();
    $dbConn = new DbConn(); 
    $user = $usr['email'];
    $cart = $request['cart_data'];
$price_arr = array();
foreach ($cart as $key => $value) {
$price_arr[] = floatval ($value['price']);
}

$cost = array_sum($price_arr);

if($payClass->isEnoughBalance($user,$cost)){
$r = array('is_enough'=>true,'cost'=>$cost);
}else{
$r = array('is_enough'=>false,'cost'=>$cost);    
}
header('content-type: application/json');
echo json_encode($r);
}


    if($request['action']  ==   'get_dashboard'){ 
    $usr = $genClass->getUser();
    $dbConn = new DbConn(); 
    $rsp = array();
    $bal = $dbConn->getRow('SELECT balance FROM wallet ',[]);  
    $queries = $dbConn->getRow('SELECT count(id) AS num FROM vin_queries',[]);
    $reports = $dbConn->getRow('SELECT count(id) AS num FROM orders ',[]); 
    $payments = $dbConn->getRow('SELECT count(id) AS num FROM payments',[]); 
    $sub_accounts = $dbConn->getRow('SELECT count(id) AS num FROM users WHERE base_user <> ?',["0"]); 
    $users = $dbConn->getRow('SELECT count(id) AS num FROM users',[]); 
    $messages = $dbConn->getRow('SELECT count(id) AS num FROM messages ',[]);

        $rsp['balance'] = $bal['data']['balance'];
        
        $rsp['queries'] = $queries['data']['num'];
        $rsp['reports'] = $reports['data']['num'];
        $rsp['payments'] = $payments['data']['num'];
        $rsp['users'] = $users['data']['num'];
        $rsp['sub_accounts'] = $sub_accounts['data']['num'];
        $rsp['messages'] = $messages['data']['num'];
        $ars = array('dashboard'=>$rsp);

        header('content-type: application/json');
        echo json_encode($ars);
        exit();
        

} 


if($request['action']  ==   'genReqToken'){
$usr = $genClass->getUser();
$vin = $request['vin'];
$toks = 'tkn_'.$usr['email'].'_'.$vin.'_'.time();
$token = array('token'=>$toks);
header('content-type: application/json');
echo json_encode($token);
exit();   
}//generateReqToken


if($request['action']  ==   'getPrices'){
    $dbconn = new DbConn;
    $sql = 'SELECT carfax_price, autocheck_price, copart_price, manheim_price, iaai_price FROM settings LIMIT ?';
    $dbr = $dbconn->getRow($sql,["1"]);
    if($dbr['code']==200){
    $row = $dbr['data'];
    }else{
        $row = array();
    }
    header('content-type: application/json');
    echo json_encode($row);
    exit();   
    }//generateReqToken

if($request['action']  ==   'checkVin'){

    $usr = $genClass->getUser();
    $user = $usr['email'];
    $user_mode = $usr['user_mode'];
    $request['user'] = $user;
    $request['user_mode'] = $usr['user_mode'];
#

$rsp = $vinClass->checkVin($request);


    header('content-type: application/json');
    echo json_encode($rsp);
    exit();
    
    
    }

    
    
    if($request['action']  ==   'get_vin_queries'){
$offset = $request['offset'];
$limit = $request['limit'];    
        $rsp = $dashClass->getAdminVinQueries($offset,$limit);
        $ars = array('vin_queries'=>$rsp);
        header('content-type: application/json');
        echo json_encode($ars);
        exit();
        
        }   

if($request['action']  ==   'get_error_reports'){
$offset = $request['offset'];
$limit = $request['limit'];    
        $rsp = $dashClass->getAdminErrorReports($offset,$limit);
        $ars = array('error_reports'=>$rsp);
        header('content-type: application/json');
        echo json_encode($ars);
        exit();
  }      




    if($request['action']  ==   'get_faq'){   
        $rsp = $dashClass->getAdminFaq();
        $ars = array('faq'=>$rsp);
        header('content-type: application/json');
        echo json_encode($ars);
        exit();
        
        }   
    
    if($request['action']  ==   'get_pages'){ 
        $rsp = $dashClass->getAdminPages();
        $ars = array('pages'=>$rsp);
        header('content-type: application/json');
        echo json_encode($ars);
        exit();
        
        }    
    if($request['action']  ==   'get_notifications'){
      $offset = $request['offset'];
$limit = $request['limit'];     
        $rsp = $dashClass->getAdminNotifications($offset,$limit);
        $ars = array('notifications'=>$rsp);
        header('content-type: application/json');
        echo json_encode($ars);
        exit();
        
        }    
    

   if($request['action']  ==   'mark_notificaion'){ 
    $id = $request['id'];
    $index = $request['index'];
    $dbCox = new DbConn();
    $do = $dbCox->executeSql("UPDATE notifications SET status = ? WHERE id = ?",["1","$id"]);
    if($do['code'] == 200){
        $ars = array('mess'=>'Done', 'state'=>'1', 'index'=>$index);
    }else{
        $ars = array('mess'=>'Not Done', 'state'=>'0');
    }
        header('content-type: application/json');
        echo json_encode($ars);
        exit();
        
        } 


if($request['action']  ==   'get_vin_reports'){
$offset = $request['offset'];
$limit = $request['limit'];        
        $rsp = $dashClass->getAdminVinReports($offset,$limit);
        $ars = array('vin_reports'=>$rsp);
        header('content-type: application/json');
        echo json_encode($ars);
        exit();
}  
    

if($request['action']  ==   'get_messages'){ 
$offset = $request['offset'];
$limit = $request['limit'];          
$rsp = $dashClass->getAdminMessages($offset,$limit);
        $ars = array('messages'=>$rsp);
        header('content-type: application/json');
        echo json_encode($ars);
        exit();
}


if($request['action']  ==   'get_payments'){
$offset = $request['offset'];
$limit = $request['limit'];       
            $rsp = $payClass->getAdminPayments($offset,$limit);
            $ars = array('payments'=>$rsp);
            header('content-type: application/json');
            echo json_encode($ars);
            exit();
}

    
            if($request['action']  ==   'get_sub_accounts'){
$offset = $request['offset'];
$limit = $request['limit'];      
                $rsp = $payClass->getAdminSubAccounts($offset,$limit);
             $ars = array('sub_accounts'=>$rsp);
                header('content-type: application/json');
                echo json_encode($ars);
                exit();
                
                }


    
            if($request['action']  ==   'get_users'){
$offset = $request['offset'];
$limit = $request['limit'];      
                $rsp = $dashClass->getAdminUsers($offset,$limit);
             $ars = array('users'=>$rsp);
                header('content-type: application/json');
                echo json_encode($ars);
                exit();
                
                }
    
            if($request['action']  ==   'get_site_settings'){
            $ar = $genClass->getSettings(); 
             $ars = array('site_settings'=>$ar);
                header('content-type: application/json');
                echo json_encode($ars);
                exit();
                
                }

    

if($request['action']  ==   'createSubAccount'){     
$rsp = $accountClass->adminCreateNewAccount($request);
header('content-type: application/json');
echo json_encode($rsp);
exit();
}
    





if($request['action']  ==   'get_admin_questions'){
$dbConn = new DbConn();
$qaClass = new QaClass();
$offset = $request['offset'];
$limit = $request['limit'];
$response = $qaClass->getAdminQuestions($limit,$offset);
header('content-type: application/json');
echo '{"admin_questions":'.json_encode($response).'}';
exit();
}



if($request['action']  ==   'get_admin_answers'){
$dbConn = new DbConn();
$qaClass = new QaClass();
$offset = $request['offset'];
$limit = $request['limit'];
$response = $qaClass->getAdminAnswers($limit,$offset);
header('content-type: application/json');
echo '{"admin_answers":'.json_encode($response).'}';
exit();
}



if($request['action']  ==   'get_admin_comments'){
$dbConn = new DbConn();
$qaClass = new QaClass();
$offset = $request['offset'];
$limit = $request['limit'];
$response = $qaClass->getAdminComments($limit,$offset);
header('content-type: application/json');
echo '{"admin_answers":'.json_encode($response).'}';
exit();
}





if($action=='debitUser' || $action=='creditUser'){
$dbConn = new DbConn();
$genClass = new GeneralClass();
if($action=='debitUser'){
    $type = 'debit';
    $dx = 'debited';
}elseif($action=='creditUser'){
    $type = 'credit';
    $dx = 'credited';
}
$id = $request['id'];
$amount = $request['amount'];
$csql = $dbConn->getRow("SELECT id, email FROM users WHERE id = ?", 
    ["$id"]);

if($csql['code'] == 200){
$rt = $csql['data'];
$uss = $rt['email'];
$time = time();
$rand =  mt_rand();
$ref = 'vrn-'.$rand.'-'.$time;
$ramount = $amount*-1;

$usr = $genClass->getUser();
$user = $usr['email']; 

$dox = $dashClass->affectWallet($uss,$amount,$type);
if($dox['done']){
$loads = array(
'user'=>$uss, 
'ref'=>$ref, 
'amount'=>$ramount, 
'date'=>$time, 
'paymode'=>'base_account', 
'status'=>1
);
$crp = $dbConn->insertDb($loads,'payments');
$mess = 'User '.$dx.' Successfully';
$state = '1';
$class = 'good';
}else{
$mess = 'User Not '.$dx;
$state = '0';
$class = 'error';    
}

}else{
$mess = 'Invalid user';
$state = '0';
$class = 'error';      
}



header('content-type: application/json');
echo '
    {
    "mess":'.json_encode($mess).',
    "state":'.json_encode($state).',
    "class":'.json_encode($class).'
    }';

 //print_r($request);
exit();


}






if($action=='getThisPage'){
$uid = $request['id'];
$qx = $dbConn->getRow("SELECT * FROM pages WHERE id = ?",["$uid"]);
$row = $qx["data"];
$dt  = array(
    'id' => $row['id'], 
    'title' => html_entity_decode($row['title']),
    'name' => html_entity_decode($row['name']),
    'intro' => html_entity_decode($row['intro']),
    'message' => stripslashes(html_entity_decode($row['message']))
    );
$ds = json_encode($dt);
header('content-type: application/json');
echo '{
"page":'.$ds.'}';

exit();
}


if($action=='getThisFaq'){
$uid = $request['id'];
$qx = $dbConn->getRow("SELECT * FROM faq WHERE id = ?",["$uid"]);
$row = $qx["data"];

$dt  = array(
    'id' => $row['id'], 
    'question' => html_entity_decode($row['question']),
    'answer' => stripslashes(html_entity_decode($row['answer']))
    );
$ds = json_encode($dt);
header('content-type: application/json');
echo '{
"faq":'.$ds.'}';

exit();
}



if($action == 'editPage'){
$dbConn = new DbConn();
$title = $request['title'];
$name = $request['name'];
$intro = $request['intro'];
$id = $request['id'];
$message = htmlentities(addslashes($request['message']));
#
$sw = $dbConn->getRows("SELECT * FROM pages  WHERE name = ? AND id <> ?",["$name","$id"]);
$rw = $sw["data"];
$isExist = count($rw);



if ($isExist >0) {
$go = 'no';
$state = '0';
$mess = 'Another page with same title already exist.';
header('content-type: application/json');
echo '{"state":'.json_encode($state).',"mess":'.json_encode($mess).',"class":"error center block"}';
exit();

}
else{$go='yes';}

if($go=='yes'){

$sql = "UPDATE pages
SET title = ?, name = ?, intro = ?,
message = ?
WHERE id = ?
"; 
$q1 = $dbConn->executeSql($sql,["$title","$name","$intro","$message","$id"]);
if($q1){
$state = '1'; $mess = 'Page Edited!'; $class = 'good';
}else{
$state = '0'; $mess = 'Page Not Edited!'; $class = 'error';
}
header('content-type: application/json');
echo '{"state":'.json_encode($state).',
"mess":'.json_encode($mess).',
"class":'.json_encode($class).'}';

exit();
}//

}




if($action=='editFAQ'){
$dbConn = new DbConn();
$id = $request['id'];
$question = $request['question'];
$answer = htmlentities(addslashes($request['answer']));
#
$iss = "UPDATE faq SET question = ?, answer = ? WHERE id = ? ";
$csql = $dbConn->executeSql($iss,["$question","$answer","$id"]);
if($csql['code']==200){
$mess = 'FAQ Updated Successfully';
$state = '1';
$class = 'good';
}else{
$mess = 'FAQ Not Updated';
$state = '0';
$class = 'error';    
}
#
header('content-type: application/json');
#
echo '
    {
    "mess":'.json_encode($mess).',
    "state":'.json_encode($state).',
    "class":'.json_encode($class).'
    }';

exit();


}


if($action=='addFAQ'){
$dbConn = new DbConn();
$question = $request['question'];
$answer = htmlentities(addslashes($request['answer']));
$dsr = array(
'question'=>$question, 'answer'=>$answer
);
$csql = $dbConn->insertDb($dsr,"faq");
if($csql['code']==200){
$mess = 'FAQ Added Successfully';
$state = '1';
$class = 'good';
}else{
$mess = 'FAQ Not Added';
$state = '0';
$class = 'error';    
}

header('content-type: application/json');

echo '
    {
    "mess":'.json_encode($mess).',
    "state":'.json_encode($state).',
    "class":'.json_encode($class).'
    }';

 //print_r($request);
exit();


}





if($action=='editSite'){
$dbConn = new DbConn();
$field = $request['field'];
$value = $request['label'];

$csql = $dbConn->executeSql("UPDATE settings SET $field = ?",["$value"]);
if($csql['code']){
$mess = 'Updated Successfully';
$state = '1';
$class = 'good';
}else{
$mess = 'Not Updated';
$state = '0';
$class = 'error';    
}

header('content-type: application/json');

echo '
    {
    "mess":'.json_encode($mess).',
    "state":'.json_encode($state).',
    "class":'.json_encode($class).'
    }
    ';

 //print_r($request);
exit();


}




if($action == 'saveContact'){
$dbConn = new DbConn();
$user = $usr['email'];
$subject = $request['subject'];
$message = $request['message'];
$comments = '<h2>'.$subject.'</h2> '.$message;
$ip = $_SERVER['REMOTE_ADDR'];
$stime = time();

if(empty($subject) || empty($message)){ 
$go = 'no';
$state = '0';
$mess = 'Please supply all fields';

$ex = '{"state":'.json_encode($state).',
"mess":'.json_encode($mess).',
"class":"error"}';

echo $ex;
}
else{
$go = 'yes';


}
if($go == 'yes'){
$srt = array(
'user'=>$user, 'message'=>$comments, 'stime'=>$stime
);
$qr = $dbConn->insertDb($srt,'contacts');
$state = '1';
$mess = 'Your message has been saved successfully';
header('content-type: application/json');
echo  '{
"state":'.json_encode($state).',
"mess":'.json_encode($mess).',
"class":"good"
}';

exit();
//echo header("location: ./dashboard");
}



}




//addYear
if($action=='editUser'){
$dbConn = new DbConn();
$firstname =$request['firstname'];
$lastname = $request['surname'];
$phone = $request['phone'];
$id = $request['id'];
$sx = "SELECT * FROM  users WHERE  phone = ? AND id <> ?"; 
$rw = $dbConn->getRows($sx,["$phone","$id"]);
$num = count($rw['data']);
#
if(empty($firstname) || empty($lastname) || empty($phone)){
$mess = 'Some required fields are missing'; 
$state = "0";
$class = "error";
$go = 'no';
}

elseif($num > 0){  
$mess = 'A User with similar credentials already exist.';
$state = "0";
$class = "error";
$go = 'no';
}else{
$go = 'yes';
}

if($go == 'yes'){
$sql = "UPDATE users SET
firstname = ?, 
surname = ?,
phone = ?
WHERE id = ?
"; 
$qr = $dbConn->executeSql($sql,["$firstname","$lastname","$phone","$id"]);
if($qr['code'] == 200){
$mess = ' User Edited Successfully!';
$class = "good"; 
$state = "1";
}else{
$mess = 'User Cannot be Edited now! Try Again later';
$class = "error"; 
$state = "0";
}

}
header('content-type: application/json');
echo '
    {
    "mess":'.json_encode($mess).',
    "class":'.json_encode($class).',
    "state":'.json_encode($state).'
    }
    ';

 //print_r($request);
exit();

}


if($action=='delUser'){
$dbConn = new DbConn();
$id = $request['id'];
$sql = $dbConn->getRows("SELECT email, id FROM users WHERE id = ?",["$id"]);
$rs = $sql['data'];
$nm = count($rs);
$ius = $rs['email'];
if($nm > 0){
$csql = $dbConn->executeSql("DELETE FROM users WHERE id = ?",["$id"]);
if($csql['code']==200){
$csql2 = $dbConn->executeSql("DELETE FROM wallet WHERE user = ?",["$ius"]);
$mess = 'User Deleted Successfully';
$state = '1';
$class = 'good';
}else{
$mess = 'User Not Deleted';
$state = '0';
$class = 'error';    
}

}else{

$mess = 'User Not found to be Deleted : '.$nm;
$state = '0';
$class = 'error';      
}

header('content-type: application/json');

echo '
    
    {
    "mess":'.json_encode($mess).',
    "state":'.json_encode($state).',
    "class":'.json_encode($class).'
    }
    ';

 //print_r($request);
exit();


}


if($action=='blockUser'){
$dbConn = new DbConn();
$id = $request['id'];
$type = $request['type'];
if($type=='1'){
$tp = 'Unblocked';    
}else{
$tp = 'Blocked';    
}
#
$csql = $dbConn->executeSql("UPDATE users SET status = ? WHERE id = ?",["$type","$id"]);
if($csql['code']==200){
$mess = 'User '.$tp.' Successfully';
$state = '1';
$class = 'good';
}else{
$mess = 'User Not '.$tp.'';
$state = '0';
$class = 'error';    
}

header('content-type: application/json');

echo '
    
    {
    "mess":'.json_encode($mess).',
    "state":'.json_encode($state).',
    "class":'.json_encode($class).'
    }
    ';

 //print_r($request);
exit();


}



if($action=='getThisQuery'){
$dbConn = new DbConn();
$id = $request['id'];
$q1 = $dbConn->getRow("SELECT  q.* FROM vin_queries q WHERE q.id = ?",["$id"]);
$row = $q1["data"]; 
header('content-type: application/json');
echo '{
"query":'.json_encode(array($row)).'
}';

exit();

}



if($action=='makeAdmin'){
$dbConn = new DbConn();
$id = $request['id'];
$type = $request['type'];
if($type=='admin'){
$level = '2';
}else{
$level = '1';    
}
$crs = $dbConn->getRow("SELECT * FROM users WHERE id = ?",["$id"]);
$rs = $crs["data"];
///
if($rs['type'] !== $type){
$mms = $dbConn->executeSql("UPDATE users SET type = ?, level = ? WHERE id = ?",["$type","2","$id"]);
if($mms['code']){
$mess = 'User Successfully made '.$type;
$state = '1';
$class = 'good';
}else{
$mess = 'User Not made '.$type;
$state = '0';
$class = 'error';    
}

}else{
$mess = 'User Already '.$type;
$state = '0';
$class = 'error';     
}

header('content-type: application/json');

echo '
    
    {
    "mess":'.json_encode($mess).',
    "state":'.json_encode($state).',
    "class":'.json_encode($class).'
    }
    ';

 //print_r($request);
exit();


}



if($action=='loginAs'){
$dbConn = new DbConn();
$id = $request['id'];
$crs = $dbConn->getRows("SELECT * FROM users WHERE id = ?",["$id"]);
$rs = $crs['data'];
$crsx = $dbConn->getRow("SELECT * FROM users WHERE id = ?",["$id"]);
$rsx = $crsx['data'];
$isAdd = count($rs);
///
if($isAdd > 0){

$genClass->redoSessionUser('user',$rsx['email'],$rsx['level'],$rsx['id']);


$mess = 'Successfully logged in as <u>'.$rsx['email'].'</u><br>
You are now being redirected to the user\'s dashboard.
';
$state = '1';
$class = 'good';
$nextUrl = './account/dashboard';
}else{
$mess = 'User does not exist';
$state = '0';
$class = 'error';
$nextUrl = '';     
}


header('content-type: application/json');

echo '
    {
    "mess":'.json_encode($mess).',
    "state":'.json_encode($state).',
    "class":'.json_encode($class).',
    "nextUrl":'.json_encode($nextUrl).'
    }
    ';

exit();


}





if($action=='creditUser'){
$dbConn = new DbConn();
$settings = new GeneralClass();
$isComm = $request['applyComm'];
$id = $request['id'];
$amount = $request['amount'];
if($isComm){
$dueAmount = (($amount/100)*$settings['resellerComm'])+$amount;    
}else{
$dueAmount = $amount;    
}
$csql = $dbConn->getRow("SELECT id, email FROM users WHERE id = ?",["$id"]);
$rt = $csql['data'];
$uss = $rt['email'];
$time = time();
$rand =  mt_rand();
$ref = 'vrn-'.$rand.'-'.$time;
#
$crt = affectWallet($uss,$dueAmount,'credit');
#
if($crt['done']){ 
$dload = array(
'user'=>$uss, 
'ref'=>$ref, 
'amount'=>$dueAmount, 
'date'=>$time, 
'paymode'=>'admin', 
'status'=>'1'
);
#
$crp = $dbConn->insertDb($dload,'payments');
$mess = 'User Credited Successfully';
$state = '1';
$class = 'good';
}else{
$mess = 'User Not Credited';
$state = '0';
$class = 'error';    
}
#
header('content-type: application/json');
#
echo '
    {
    "mess":'.json_encode($mess).',
    "state":'.json_encode($state).',
    "class":'.json_encode($class).'
    }';

exit();

}



if($action=='debitUser'){
$dbConn = new DbConn();
$id = $request['id'];
$amount = $request['amount'];
$csql = $dbConn->getRow("SELECT id, email FROM users WHERE id = ?",["$id"]);
$rt = $csql['data'];
$uss = $rt['email'];
$time = time();
$rand =  mt_rand();
$ref = 'vrn-'.$rand.'-'.$time;
$ramount = $amount*-1;

$crt = $dashClass->affectWallet($uss,$amount,'debit');

if($crt['done']){
$dld = array(
'user'=>$uss, 
'ref'=>$ref, 
'amount'=>$ramount, 
'date'=>$time, 
'paymode'=>'admin', 
'status'=>1
);

$crp = $dbConn->insertDb($dld,'payments');
$mess = 'User Debited Successfully';
$state = '1';
$class = 'good';
}else{
$mess = 'User Not Debited';
$state = '0';
$class = 'error';    
}




header('content-type: application/json');

echo '
    {
    "mess":'.json_encode($mess).',
    "state":'.json_encode($state).',
    "class":'.json_encode($class).'
    }';

 //print_r($request);
exit();


}



if($action=='editPassword'){
$dbConn = new DbConn();
$id = $request['id'];
$current = $request['oldpassword'];
$newpassword = $request['password'];
$newpassword2 = $request['password2'];
///
$sx = "SELECT email, password FROM users WHERE id = ?";
$ucheck = $dbConn->getRow($sx,["$id"]);
$rph = $ucheck["data"];
$email = $rph['email'];

$hashedCurrent = hash('sha512',strtolower($email).$current); 
$hashed = hash('sha512',strtolower($email).$newpassword);


if($newpassword != $newpassword2){
$go = 'no';
header('content-type: application/json');
echo '{"state":'.json_encode('0').',
"mess":'.json_encode('Your new password did not match.').',
"class":'.json_encode('error').'}';
exit();
}
elseif($hashedCurrent != $rph['password']){
$go = 'no';
header('content-type: application/json');
echo '{"state":'.json_encode('0').',
"mess":'.json_encode('Wrong Current Password Supplied.').',
"class":'.json_encode('error').'}';
exit();
}else{
$go = 'yes';
}
if($go == 'yes'){

$csql = "UPDATE users SET password = ? WHERE email = ?";
$q1 = $dbConn->executeSql($csql,["$hashed","$email"]);

if($q1){
$state = '1'; $mess = 'Password Updated!'; $class = 'good';
}else{
$state = '0'; $mess = 'Unable to Update Password!'; $class = 'error';
}
header('content-type: application/json');
echo '{"state":'.json_encode($state).',
"mess":'.json_encode($mess).',
"class":'.json_encode($class).'}';

exit();
}

}//




if($action=='sendMessage'){
$dbConn = new DbConn();
$subject = $request['subject'];
$message = htmlentities(addslashes($request['message']));
$pid = $request['pid'];
$time = time();
$sender = 'admin';
$receiver = $request['receiver'];
if($pid == 0){
$type = 'new';
}else{
$type = 'reply';
}
$mload = array(
'pid'=>$pid, 
'sender'=>$sender, 
'receiver'=>$receiver, 
'subject'=>$subject, 
'message'=>$message, 
'sdate'=>$time, 
'updated'=>$time
);
$csql = $dbConn->insertDb($mload,'messages');
if($csql['code']==200){
if($type == 'reply'){ 
$casql = $dbConn->executeSql("UPDATE messages SET status = ? WHERE id = ?",["0","$pid"]);
}

$mess = 'Message Sent Successfully';
$state = '1';
$class = 'good';
}else{
$mess = 'Message Not Sent';
$state = '0';
$class = 'error';    
}

header('content-type: application/json');

echo '
    {
    "mess":'.json_encode($mess).',
    "state":'.json_encode($state).',
    "class":'.json_encode($class).'
    }';

 //print_r($request);
exit();


}




if($action=='approvePay'){
$dbConn = new DbConn();
$dashClass = new DashClass();
$id = $request["id"];
//
$sdr = "SELECT * FROM payments WHERE id = ?";
$rr = $dbConn->getRow($sdr,["$id"]);
$rpa = $rr['data'];
$amount = $rpa['amount']; 
$bank = $rpa['payvendor'];
$ref = $rpa['ref'];
$user =  $rpa['user'];
//
if($rpa['status'] == 1){
$mess = 'Payment already Approved';
$state = '0';
$class = 'error'; 

}else{
if($dashClass->affectWallet($user,$amount,'credit')){
$sql = $dbConn->executeSql(
"UPDATE payments SET status = ? WHERE id = ?",["1","$id"]);
//
//$sql2 = $dbConn->executeSql("UPDATE transactions SET status = ? WHERE reference = ?",["1","$ref"]);
if($sql['code']==200){
$reason = 'Deposit at '.$bank;
$type = 'credit';
$date = time();
$detail = 'Your Payment at <u>'.$bank.'</u> has been verified and you have been subscribed to the current campaign';
//$notifyClass->sendNotify($detail,$user);
$mess = 'Payment Approved Successfully ';
$state = '1';
$class = 'good';
}else{

$mess = 'Payment Not Approved';
$state = '0';
$class = 'error'; 
}

}else{
$mess = 'Error Performing Wallet Crediting';
$state = '0';
$class = 'error';     
}

}


header('content-type: application/json');

echo '
  
    {
    "mess":'.json_encode($mess).',
    "status":'.json_encode($state).',
    "class":'.json_encode($class).'
    }
    ';

 //print_r($request);
exit();
 

}


if($action=='cancelPay'){
    $dbConn = new DbConn();
$id = $request["id"];
//
$sdr = "SELECT * FROM payments WHERE id = ?";
$rr = $dbConn->getRow($sdr,["$id"]);
$rpa = $rr['data'];
$amount = $rpa['amount']; 
$bank = $rpa['payvendor'];
$ref = $rpa['ref'];
$user =  $rpa['user'];
//
if($rpa['status'] == -1){
$mess = 'Payment already cancelled';
$state = '0';
$class = 'error'; 

}else{
$sql = $dbConn->executeSql(
"UPDATE payments SET status = ? WHERE id = ?",["-1","$id"]);
//
//$sql2 = $dbConn->executeSql("UPDATE transactions SET status = ? WHERE reference = ?",["-1","$ref"]);
if($sql['code']==200){
$reason = 'Deposit at '.$bank;
$type = 'credit';
$date = time();
$detail = 'Your Payment notification for payment at <u>'.$bank.'</u> has been cancelled for lack of merit';
$notifyClass->notifyUser($detail,$user);
$mess = 'Payment cancelled Successfully ';
$state = '1';
$class = 'good';
}else{

$mess = 'Payment Not cancelled';
$state = '0';
$class = 'error'; 

}

}



header('content-type: application/json');

echo '
  
    {
    "mess":'.json_encode($mess).',
    "status":'.json_encode($state).',
    "class":'.json_encode($class).'
    }
    ';

 //print_r($request);
exit();
 
}




if($action == 'user_search'){
$dbConn = new DbConn();
$term = $request["term"];
$xterm  = "%$term%";
$sqr = 'SELECT u.*, b.balance FROM users u  
JOIN wallet b ON u.email = b.user
WHERE u.firstname LIKE ? OR  u.phone LIKE ? OR u.surname LIKE ? OR u.email LIKE ?  OR u.username LIKE ?  
ORDER BY u.id DESC';
$params = [$xterm ,$xterm , $xterm , $xterm , $xterm];
$sqz = $dbConn->getRows($sqr,$params);

if($sqz['code']==200 && $sqz['data']!==false){
$arr = $sqz['data'];
$mess = 'Search Returned..';
$state = '1';
}else{
$arr = array();
$mess = 'Search not returned..';
$state = '0';
}


header('content-type: application/json');
echo '
    {
    "searched_users":'.json_encode($arr).',
    "mess":'.json_encode($mess).',
    "status":'.json_encode($state).'
    }
    ';
exit();
 
}




?>
