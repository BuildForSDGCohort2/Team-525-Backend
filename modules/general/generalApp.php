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
$feedClass = new FeedClass;
$payClass = new PaymentClass;
$dashClass = new DashClass;
$dbConn = new DbConn;
$notifyClass = new NotificationClass;
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



if($request['action']  ==   'gettransactions'){
$usr = $genClass->getUser();
$thisuser = $usr['email'];
$qt = $dbConn->getRows("SELECT * FROM transactions WHERE user = ? order  by   id  desc",["$thisuser"]);
if($qt['code'] == 200 && count($qt['data']) > 0){
$arr  =  $qt['data'];
}else{
$arr = array();
}
header('content-type: application/json');
echo '{"transactions":'.json_encode($arr).'}';
exit();
}


if($request['action']  ==   'gettransactions'){
$usr = $genClass->getUser();
$thisuser = $usr['email'];
$qt = $dbConn->getRows("SELECT * FROM transactions WHERE user = ? order  by   id  desc",["$thisuser"]);
if($qt['code'] == 200 && count($qt['data']) > 0){
$arr  =  $qt['data'];
}else{
$arr = array();
}
header('content-type: application/json');
echo '{"transactions":'.json_encode($arr).'}';
exit();
}



if($request['action']  ==   'get_payouts'){
$usr = $genClass->getUser();
$thisuser = $usr['email'];
$qt = $dbConn->getRows("SELECT * FROM payouts WHERE user = ? ORDER BY id DESC",["$thisuser"]);
if($qt['code'] == 200 && is_array($qt['data'])){
$rw = $qt['data'];
}else{
$rw = [];
}
header('content-type: application/json');
echo '{"payouts":'.json_encode($rw).'}';
exit();
}



if($action =='setNotification'){

$mode = $request['setmode'];
$data = $request['data'];
$tar = $request['data'];
$sq1 = $sq2 = $status = '';
if($mode == 'unread'){
$status = 0; $dur = 0;
}elseif($mode == 'read'){
$status = 1; $dur = 0;
}elseif($mode == 'trash'){
$status = -1; $dur = 0;
}elseif($mode == 'delete'){
$dur = 1;
}



if( $dur == 0){
$sql = "UPDATE notifications SET status = ? WHERE id IN (?)";
foreach ($tar as $key => $ids) {
$q1 = $dbConn->executeSql($sql,["$status","$ids"]);
}

}elseif( $mode == 'delete'){
$tir = "'".implode("','",$request['data'])."'";;

$sql = "DELETE FROM notifications WHERE id IN (".$tir.") ";
$q1 = $dbConn->executeSql($sql,[]);
}

//
if($q1['code'] == 200){
$state = '1';
$mess = 'Done!';
}else{
$state = '0';
$mess = 'Not Done!';  
}

header('content-type: application/json');
echo '
{"state":'.json_encode($state).',
"mess":'.json_encode($mess).'
}';
exit();
}


if($request['action']  ==   'list_friend_suggest'){
$socialClass = new SocialClass();
$usr = $genClass->getUser();
$thisuser = $usr['email'];
$idps  =  $socialClass->getUserDepartments($thisuser);
$arst = array();
if(count($idps) > 0){
foreach ($idps as $ky => $rwi) { 
  $arst[] = $rwi['department_id'];
}
$mydepts = implode(" , ", $arst);
}else{
$mydepts = "";  
}

$fll = $socialClass->usersFriendsArray($thisuser);
$friends = "'".implode("','", $fll)."'";

$sqax = "SELECT DISTINCT(f.user) AS other_email, u.firstname, u.surname, u.avatar, u.bio, u.username FROM followed_departments f 
JOIN users u ON f.user = u.email
WHERE f.department_id IN (".$mydepts.") AND f.user NOT IN (".$friends.")";
$tux = $dbConn->getRows($sqax,[]);

$response = array();
if($tux['code'] ==200 && $tux['data'] !==false){
$rws = $tux['data'];
foreach ($rws as $key => $rw) {
$rw['name'] = $rw['firstname'].' '.$rw['surname'] ;
$rw['bio'] = stripslashes(html_entity_decode($rw['bio'])) ;
$rw['is_followed'] = $socialClass->isUserFollowed($rw['other_email'],$thisuser);
$response[] = $rw;
}
}//code == 200


header('content-type: application/json');
echo json_encode($response);
exit();
}


if($request['action']  ==   'get_referral'){
$user = $genClass->getUser();
$me = $user['email'];

$spr = "SELECT  r.*, u.surname AS ref_surname, u.firstname AS ref_firstname, ur.surname AS r_surname, ur.firstname AS r_firstname 
FROM referrals r
JOIN users u ON r.referee = u.email
JOIN users ur ON r.referred = ur.email
 WHERE r.referee = ?
";
$pr = $dbConn->getRows($spr,["$me"]);
$rr  =  $pr['data'];
$arr1  =  array();
//
foreach ($rr as $key => $rts) {
$arr1[] = $rts;
}
//
$px = "SELECT count(r.id) AS powa_ref, u.surname, u.firstname FROM referrals r
JOIN users u ON r.referee = u.email
 WHERE r.ref_settled = ?
GROUP BY r.referee
ORDER BY count(r.id) DESC LIMIT ?
";
$pxr = $dbConn->getRows($px,["1","10"]);
$rrx  =  $pxr['data'];
$atr  =  array();
//
foreach ($rrx as $key => $rtsi) {
$atr[] = $rtsi;
}
//
$spr2 = "SELECT count(id) AS total_referred,
 SUM(case when ref_settled = 1 then `point` else 0 end) as approved_earn,
 SUM(`point_value`*`point`) as total_earn, 
 SUM(case when ref_settled = 1 AND redeemed = 0 then `point` else 0 end) as approved_not_redeemed, 
 SUM(case when ref_settled = 0 then `point` else 0 end) as not_approved
    FROM referrals WHERE referee = ?";
$pr2 = $dbConn->getRows($spr2,["$me"]);
$rwt = $pr2['data'][0]; 
//
$arx = array();
$arx['total_earn'] = (!empty($rwt['total_earn'])) ? $rwt['total_earn'] : 0;
$arx['ref_list'] = $arr1;
$arx['approved_earn'] = (!empty($rwt['approved_earn'])) ? $rwt['approved_earn'] : 0;
$arx['approved_not_redeemed'] = (!empty($rwt['approved_not_redeemed'])) ? $rwt['approved_not_redeemed'] : 0;
$arx['not_approved'] = (!empty($rwt['not_approved'])) ? $rwt['not_approved'] : 0;
$arx['total_referred'] = $rwt['total_referred'];
$arx['leaders'] = $atr;
//
$rsp =  '
{"referral":'.json_encode($arx).'}';
header('content-type: application/json');
echo $rsp;
exit();
}



if($action=='getThisPage'){
$dbConn = new dbConn();
$uid = $request['id'];
$qx = $dbConn->getRow("SELECT * FROM pages WHERE id = ?",["$uid"]);
if($qx['code']==200 && $qx['data'] !==false){
$row = $qx['data'];
$dt  = array(
    'id' => $row['id'], 
    'title' => html_entity_decode($row['title']),
    'name' => html_entity_decode($row['name']),
    'intro' => html_entity_decode($row['intro']),
    'content' => stripslashes(html_entity_decode($row['message']))
    );
}else{
$dt = array();
}
$ds = json_encode($dt);

header('content-type: application/json');
echo '{
"page":'.$ds.'}';

exit();
}





if($action=='allFaq'){
	$dbConn = new dbConn();
$qx = $dbConn->getRows("SELECT id, question, answer FROM faq",[]);
if($qx['code']==200 && $qx['data'] !==false){
$rx = $qx['data'];
$arr = array();  
foreach ($rx as $key => $row) {
$row['id'] = $row['id']; 
$row['question'] = html_entity_decode($row['question']);
$row['answer'] = stripslashes(html_entity_decode($row['answer']));
$arr[] = $row;
}//foreache
}else{
$arr = array();
}

$ds = json_encode($arr);
header('content-type: application/json');
echo '{"faq":'.$ds.'}';

exit();
}




if($request['action']  ==   'get_transactions'){
$dbConn = new DbConn();
$usr = $genClass->getUser();
$user = $usr['email'];
$sql = " SELECT * FROM transactions WHERE user = ?";
$rs = $dbConn->getRows($sql,["$user"]);
if($rs['code'] == 200){
$rw = $rs['data'];
}else{
$rw = false;
}


header('content-type: application/json');
echo '{"transactions":'.json_encode($rw).'}';
exit();
}


if($request['action']  ==   'checkPublicUser'){
$dbConn = new DbConn();
$username = $request['username'];
$sql = " SELECT id FROM users WHERE username = ?";
$atr = [$username];
$rs = $dbConn->getRows($sql,$atr);
$rw = $rs['data'];
if(count($rw)>0){
$resp = true;
}else{
  $resp = false;
}
header('content-type: application/json');
echo '{"response":'.json_encode($resp).'}';
exit();
}


if($request['action']  ==   'searchReceipient'){
$dbConn = new DbConn();
$text = $request['text'];
$sql = " SELECT firstname,surname, username, email, user_type FROM 
users WHERE email LIKE ? OR 
firstname LIKE ? OR surname LIKE ? OR username LIKE ?
";
$txt =  "%".$text."%";
$atr = [$txt,$txt,$txt,$txt];
$rs = $dbConn->getRows($sql,$atr);
$rw = $rs['data'];


header('content-type: application/json');
echo '{"result":'.json_encode($rw).'}';
exit();
}


if($action=='userValidation'){
$dbConn = new DbConn();
$receiver = $request['receiver'];
$sql = "SELECT username, email FROM users WHERE username = ? OR email = ?";
$q1 = $dbConn->getRows($sql,["$receiver","$receiver"]);
$qs = $dbConn->getRow($sql,["$receiver","$receiver"]);
$row = $qs['data']; 
$isFound = count($q1['data']);
if($isFound > 0){
$found = true;
$email = $row['username'];
}else{
$found = false;
$email = '';    
}
//print_r($row);
//exit();
header('content-type: application/json');
echo '
{"isFound":'.json_encode($found).',
"email":'.json_encode($email).'
}';
exit();
}




if($action=='newMessage'){
$dbConn = new dbConn();
$genClass = new GeneralClass();
$notifyClass = new NotificationClass();
$data = $request;
$subject = $data['subject'];

$message = htmlentities(addslashes($data['message']));
$pid = $data['pid'];
$rs_data = array();
$usr = $genClass->getUser();
$sender = $usr['email'];

if($pid == 0){
$recx = $data['receiver'];
$usn = $genClass->getUserFromUsername($recx);
$receiver = $usn['email'];
}else{
$qr = $dbConn->getRow("SELECT sender, receiver FROM messages WHERE id = ?",["$pid"]);
$rse = $qr['data'];
$receiver = ($rse['sender'] == $sender) ? $rse['receiver'] : $rse['sender'];
}
$time = time();


$qts = $dbConn->getRows("SELECT id FROM messages 
  WHERE pid = ? AND message = ? AND subject = ?", 
  ["0","$message","$subject"]);
$rconf = count($qts['data']);
 if($rconf > 0){
$state = '0'; $mess = 'This message has already been delivered.';
$class = 'error';
header('content-type: application/json');
echo '{"state":'.json_encode($state).',
"mess":'.json_encode($mess).',
"class":'.json_encode($class).'}';

exit();

 }else{
$insload = array(
'subject'=>$subject, 
'pid'=>$pid, 
'message'=>$message, 
'sender'=>$sender, 
'receiver'=>$receiver, 
'sdate'=>$time, 
'last_sender'=>$sender, 
'updated'=>$time
);
$q1 = $dbConn->insertDb($insload,'messages');
$idx = $q1['lastInsertId'];
$usx = $genClass->getUserFromEmail($sender);
$sen_name = $usx['firstname']. ''. $usx['surname'];
$sen_url = 'profile/'.$usx['username'];
$detail = '<a href="'.$sen_url.'">'.$sen_name.'</a> sent a new message on <a href="account/messages/read/'.$pid.'">'.$subject.'</a>';
$qtnots = $notifyClass->notifyUser($detail,$receiver);

if($q1['code']==200){
if($pid !== 0){
  if(isset($data['socketId'])){
$channel_name = 'chat-push-channel-'.$pid;
$event_name = 'chat-push-event-'.$pid;
$trig_data = array('mid'=>$idx);
$socketId = $data['socketId'];
$pusher->trigger( $channel_name, $event_name, $trig_data, $socketId);
}//socketId exists, so trigger!
}
$rs_data = $genClass->getMessageById($idx);
$state = '1';
if($pid == '0'){ 
$mess = 'Message Sent!'; 
}else{
    $mess = 'Message Replied!';
//markMess($pid,0,$mysqli);
}
$class = 'good';
}else{
  
$state = '0'; 
if($pid == '0'){
$mess = 'Message Not Created!'; }else{$mess = 'Message Not Replied!';}
$class = 'error';
}


header('content-type: application/json');
echo '{"state":'.json_encode($state).',
"return_data":'.json_encode($rs_data).',
"mess":'.json_encode($mess).',
"class":'.json_encode($class).'}';

exit();
}
}//

if($request['action']  ==   'list_user_departments'){
$socialClass = new SocialClass();
$rsp = $socialClass->listUserDepartments();
header('content-type: application/json');
echo '{"departments":'.json_encode($rsp).'}';
exit();
}



if($request['action']  ==   'list_user_mentors'){
$socialClass = new SocialClass();
if(isset($request['fetch_data'])){
$fmode = $request['fetch_data'];
}else{
$fmode = false;  
}
$rsp = $socialClass->listUserMentors(false,$fmode);
header('content-type: application/json');
echo '{"mentors":'.json_encode($rsp).'}';
exit();
} 

if($request['action']  ==   'fetch_education'){
$socialClass = new SocialClass();
$rsp = $socialClass->fetchEducation();
header('content-type: application/json');
echo '{
  "schools":'.json_encode($rsp['schools']).',
  "faculties":'.json_encode($rsp['faculties']).',
  "departments":'.json_encode($rsp['departments']).'
}';
exit();
} 


if($request['action']  ==   'get_this_article'){
$url = $request['url'];
$rsp = $articlesClass->getThisArticle($url);
header('content-type: application/json');
echo '{"article_details":'.json_encode($rsp).'}';
exit();
} 


if($request['action']  ==   'get_home_articles'){
$rsp = $articlesClass->getHomeArticle();
header('content-type: application/json');
echo '{"home_articles":'.json_encode($rsp).'}';
exit();
} 

if($request['action']  ==   'get_page_articles'){
$offset = $request['offset'];
$limit = $request['limit']; 
$rsp = $articlesClass->getPageArticles($offset,$limit);
header('content-type: application/json');
echo '{"page_articles":'.json_encode($rsp).'}';
exit();
} 


if($request['action']  ==   'get_notifications'){
$notificationClass = new NotificationClass();
$rsp = $notificationClass->getUserNotifications();

header('content-type: application/json');
echo '{"notifications":'.json_encode($rsp).'}';
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


if(
$action == 'get_user_public_details' || 
$action == 'get_user_public_questions' || 
$action == 'get_user_public_answers' || 
$action == 'get_user_public_articles' || 
$action == 'get_user_public_followers' || 
$action == 'get_user_public_following' || 
$action == 'get_user_public_education' || 
$action == 'get_user_public_departments' || 
$action == 'get_user_public_feed'){
$act = $request['action'];
$username = $request['username'];
$rs_name = substr($action, 4);
$rsp = $genClass->getUserPublic($act,$username);
$r  = array($rs_name => $rsp);
header('content-type: application/json');
echo json_encode($r);
exit();

}//getPublicDetails


if($action=='get_messages'){
$usr = $genClass->getUser();

$thisuser = $user = $usr['email'];

$offset = $request['offset'];
$limit = $request['limit'];    
$rsp = $dashClass->getMessages($offset,$limit);


$refNums  =  count($rsp);
$arr  =  array();
if($refNums > 0){
foreach ($rsp as $key => $rw) {
//print_r($rw);
$rw['message'] = stripslashes(html_entity_decode($rw['message']));
$rw['chat_dir'] = ($rw['pid'] == 0) ? $rw['id'] : $rw['pid'];
if($rw['sender'] === $thisuser){
$usrx = $genClass->getUserFromEmail($rw['receiver']);
$rw['other_party'] = $usrx['username'];
}elseif($rw['receiver'] === $thisuser){
$uss = $genClass->getUserFromEmail($rw['receiver']);
$rw['other_party'] = $uss['username'];
}
$arr[] = $rw;
}//foreach
}



header('content-type: application/json');
echo '{"messages":'.json_encode($arr).'}';
exit();

}

if($action=='get_message_thread'){
  $dbConn = new dbConn();
$genClass = new GeneralClass();
$id = $request['pid'];
if(isset($_SESSION['senseiAdmin'])){
  $thisuser = 'admin';
}else{
$usr = $genClass->getUser();
$thisuser = $user = $usr['email'];}
$sql2  = "SELECT m.*,  u.firstname, 
u.surname,  u.email  FROM messages m 
LEFT JOIN users u ON m.sender = u.email AND  m.receiver = u.email 
 WHERE m.id = ? OR m.pid = ? AND (m.sender = ? OR m.receiver = ?)
ORDER BY m.id ASC";
///
$qt = $dbConn->getRows($sql2,["$id","$id","$thisuser","$thisuser"]);
//
if($qt['code'] == 200 && $qt['data'] !== false){
$rws  =  $qt['data'];
$refNums  =  count($rws);
}else{
$rws  =  [];
$refNums  =  0;
}

$arr  =  array();
if($refNums > 0){
foreach ($rws as $key => $rw) {
//$rw['message'] = stripslashes(html_entity_decode($rw['message']));
$rw['chat_dir'] = ($rw['pid'] == 0) ? $rw['id'] : $rw['pid'];

if($rw['sender'] === $thisuser){
$usrx = $genClass->getUserFromEmail($rw['receiver']);
$rw['other_party'] = $usrx['username'];
$rw['other_party_url'] = 'profile/'.$usrx['username'];
}elseif($rw['receiver'] === $thisuser){
$uss = $genClass->getUserFromEmail($rw['receiver']);
$rw['other_party'] = $uss['username'];
$rw['other_party_url'] = 'profile/'.$uss['username'];
}

//
$arr[] = $rw;
}//foreach
}



header('content-type: application/json');
echo '{"message_thread":'.json_encode($arr).'}';
exit();

}




if($action=='get_chat_push'){
$dbConn = new dbConn();
$genClass = new GeneralClass();
$id = $request['mid'];
$rw = $genClass->getMessageById($id);

header('content-type: application/json');
echo json_encode($rw);
exit();
}




if($action=='newMessage'){
$dbConn = new dbConn();
$genClass = new GeneralClass();
$notifyClass = new NotificationClass();
$data = $request;
$subject = $data['subject'];
$message = htmlentities(addslashes($data['message']));
$pid = $data['pid'];
$rs_data = array();
$usr = $genClass->getUser();

if($pid == 0){
  if(isset($_SESSION['vinrun_admin'])){
$receiver = $data['receiver'];
$sender = 'admin';    
}else{
$receiver = 'admin';
$sender = $usr['email'];
}
}else{
  if(isset($_SESSION['vinrun_admin'])){
$sender = 'admin';    
}else{
$sender = $usr['email'];
}
$qr = $dbConn->getRow("SELECT sender, receiver FROM messages WHERE id = ?",["$pid"]);
$rse = $qr['data'];
$receiver = ($rse['sender'] == $sender) ? $rse['receiver'] : $rse['sender'];
}
$time = time();


$qts = $dbConn->getRows("SELECT id FROM messages 
  WHERE pid = ? AND message = ? AND subject = ?", 
  ["0","$message","$subject"]);
$rconf = count($qts['data']);
 if($rconf > 0){
$state = '0'; $mess = 'This message has already been delivered.';
$class = 'error';
header('content-type: application/json');
echo '{"state":'.json_encode($state).',
"mess":'.json_encode($mess).',
"class":'.json_encode($class).'}';

exit();

 }else{
$insload = array(
'subject'=>$subject, 
'pid'=>$pid, 
'message'=>$message, 
'sender'=>$sender, 
'receiver'=>$receiver, 
'sdate'=>$time, 
'last_sender'=>$sender, 
'updated'=>$time
);
$q1 = $dbConn->insertDb($insload,'messages');
$idx = $q1['lastInsertId'];

//$updaT = mysqli_query($mysqli,"UPDATE messages SET 
//updated = '$time', message_num = message_num+1 WHERE id = '$pid'");


$detail = $sender.' sent a new message';
$qtnots = $notifyClass->notifyUser($detail,$receiver);
/*
$to = $receiver;  
$senderemail = "support@vinrun.com";
$sendername = $ust['firstname']. ' '.$ust['surname'];
$messBody = $data['message'];



$sEmail =  new EmailClass();
$senda = $sEmail->sendPlain($senderemail,$sendername,$to,$subject,$messBody,$type='plain');
*/
/*


$pid = (int)$pid;
$pusher->trigger( 'my-message-'.$pid.'channel', 'my-'.$pid.'event', 
  array('id' =>$idx, 'rmid' => $pid )
);
*/


if($q1['code']==200){
if($pid !== 0){
$channel_name = 'chat-push-channel-'.$pid;
$event_name = 'chat-push-event-'.$pid;
$trig_data = array('mid'=>$idx);
$socketId = $data['socketId'];
$pusher->trigger( $channel_name, $event_name, $trig_data, $socketId);
}
$rs_data = $genClass->getMessageById($idx);
$state = '1';
if($pid == '0'){ 
$mess = 'Message Sent!'; 
}else{
    $mess = 'Message Replied!';
//markMess($pid,0,$mysqli);
}
$class = 'good';
}else{
  
$state = '0'; 
if($pid == '0'){
$mess = 'Message Not Created!'; }else{$mess = 'Message Not Replied!';}
$class = 'error';
}



header('content-type: application/json');
echo '{"state":'.json_encode($state).',
"return_data":'.json_encode($rs_data).',
"mess":'.json_encode($mess).',
"class":'.json_encode($class).'}';

exit();
}
}//





if($action=='editUser'){
$dbConn = new DbConn();
$firstname = $request['firstname'];
$lastname = $request['surname'];
$phone = $request['phone'];
$company = $request['company'];
$id = $request['id'];
$sx = "SELECT * FROM  users WHERE  phone = ? AND id <> ?"; 
$rw = $dbConn->getRows($sx,["$phone","$id"]);
if($rw['code']==200 && $rw['data']!==false){
 $num = count($rw['data']); 
}


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
phone = ?,
company = ?
WHERE id = ?
"; 
$qr = $dbConn->executeSql($sql, ["$firstname","$lastname","$phone","$company","$id"]);

if($qr['code']==200){
$mess = ' User Edited Successfully!';$class = "good"; $state = "1";
}else{
$mess = 'User Cannot be Edited now! Try Again later';$class = "error"; $state = "0";
}

}

header('content-type: application/json');
echo '{
    "mess":'.json_encode($mess).',
    "class":'.json_encode($class).',
    "state":'.json_encode($state).'
    }';

exit();
}



if($request['action']  ==   'saveShare'){
$usr = $genClass->getUser();
$user = $usr['email'];
$load = $chk  = $ts = array();
$load['content_id'] = $chk['content_id'] = $request['id'];
$load['content_type']  = $request['content_type'];
$load['mode'] = $chk['mode'] = $request['mode'];
$load['user'] = $chk['user'] = $user;
$load['sdate'] = $now = time();
$load['status']  = 0;
#
$checkIf = $dbConn->isExists($chk, 'all_shares');
$isShared = $checkIf['response'];
if(!$isShared){
$doInsert = $dbConn->insertDb($load,'all_shares');
if($doInsert['code'] == 200){
$feedload = array('is_share'=>1, 'did'=>0, 'cid'=>$doInsert['lastInsertId'], 'author'=>$user, 'create_date'=>$now);
$feed = $notifyClass->feedNotification($feedload);


$mess = ' <i class="fa status-active fa-check"></i> Shared Successfully' .$doInsert['lastInsertId'];
$status = '1';
}else{
$mess = '<i class="fa fa-exclamation-triangle status-cancelled"></i> Share failed';
$status = '0';  
}
}else{
$mess = '<i class="fa fa-exclamation-triangle status-cancelled"></i>  Already Shared';
$status = '0';    
}




header('content-type: application/json');
echo '{"mess":'.json_encode($mess).',
"status":'.json_encode($status).',
"state":'.json_encode($status).'
}';
exit();
}



if($action=='reset_key'){
$dbConn = new DbConn();
$genClass = new GeneralClass();
$usr = $genClass->getUser();
$user = $usr['email'];
$match_key = $usr['match_key'];
$public_key = $usr['public_key'];
#
$reg_time = time();
$ky = $genClass->crand(10).$reg_time;
$hs = $user.$ky.$reg_time;
$match_key = hash('sha512',$hs);
#
$type = $request['type'];
#
if($type == 'secret'){
$col = 'match_key';$val = $match_key;
$ms = 'Secret Key ';
}elseif($type == 'public'){
$col = 'public_key';$val = $ky;
$ms = 'Public Key ';
}
#
$sql = " UPDATE users SET $col = ? WHERE email = ? "; 
#
$qr = $dbConn->executeSql($sql, ["$val","$user"]);
#
if($qr['code']==200){
$mess = $ms.' reset Successfully!';
$class = "good"; 
$state = "1";
}else{
$mess = $ms.'  Cannot be reset now! Try Again later';
$class = "error"; 
$state = "0";
}
$r = array();
$r['mess'] = $mess;
$r['class'] = $class;
$r[$col] = $val;
$r['state'] = $state;
header('content-type: application/json');
echo json_encode($r);

exit();
}



if($action=='editPassword'){
$dbConn = new DbConn();
$id = $request['id'];
$current = $request['oldpassword'];
$newpassword = $request['password'];
$newpassword2 = $request['password2'];
///
$ucheck =  $dbConn->getRow("SELECT email, password FROM users WHERE id = ?",["$id"]);
$rph = $ucheck['data'];
$email = $rph['email'];

$hashedCurrent = hash('sha512',strtolower($email).$current); 
$hashed = hash('sha512',strtolower($email).$newpassword);

if($hashedCurrent != $rph['password']){
$go = 'no';
header('content-type: application/json');
echo '{"state":'.json_encode('0').',
"mess":'.json_encode('Wrong Current Password Supplied.').',
"class":'.json_encode('error').'}';
exit();
}
elseif($newpassword != $newpassword2){
$go = 'no';
header('content-type: application/json');
echo '{"state":'.json_encode('0').',
"mess":'.json_encode('Your new password did not match.').',
"class":'.json_encode('error').'}';
exit();

}else{
$go = 'yes';
}
if($go == 'yes'){
$csql = "UPDATE users SET password = ? WHERE email = ?";
$q1 = $dbConn->executeSql($csql,["$hashed","$email"]);

if($q1['code']==200){
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





if($request['action']  ==   'get_activity_feed'){
$dbConn = new DbConn();
$qaClass = new QaClass();
$socialClass = new SocialClass();
$offset = $request['offset'];
$limit = $request['limit'];
if(isset($_SESSION['senseiUser']) || isset($_SESSION['senseiMentor'])){
$usr = $genClass->getUser();
$isLoggedUser = true;
$thisuser = $usr['email'];
$idps  =  $socialClass->getUserDepartments($thisuser);
$arst = array();
if(count($idps) > 0){
foreach ($idps as $ky => $rwi) { 
  $arst[] = $rwi['department_id'];
}
$mydepts = implode(" , ", $arst);
}else{
$mydepts = '0';  
}


$fll = $socialClass->usersFriendsArray($thisuser);
$friends = "'".implode("','", $fll)."'";

$squser = "SELECT f.* FROM feed f WHERE 
 (f.is_question = 1  AND f.did IN (".$mydepts.") ) OR 
  (f.is_answer = 1  AND f.did IN (".$mydepts.") ) OR 
  (f.is_comment = 1  AND f.did IN (".$mydepts.") ) OR
  (f.is_article = 1  AND f.author IN (".$friends.") ) OR
  (f.is_comment = 1  AND f.author IN (".$friends.") )  OR
  (f.is_blog = 1  AND f.author IN (".$friends.") ) OR   
  (f.is_question = 1  AND f.author = ? )  OR    
  (f.is_comment = 1  AND f.author = ? )  OR   
  (f.is_answer = 1  AND f.author = ? ) OR 
   (f.is_share = 1  AND f.author = ? ) OR 
   (f.is_blog = 1  AND f.author = ? ) OR 
   (f.is_article = 1  AND f.author = ? )   
    ORDER BY  f.id desc  LIMIT ? OFFSET ?";

}else{
$isLoggedUser = false;
}

$sqquest = "SELECT f.* FROM feed f WHERE 
 (f.is_question = 1 ) OR 
  (f.is_answer = 1) OR 
  (f.is_comment = 1 )  
    ORDER BY  f.id desc  LIMIT ? OFFSET ?";

if($isLoggedUser ===true){
$frr = $dbConn->getRows($squser,["$thisuser","$thisuser","$thisuser","$thisuser","$thisuser","$thisuser","$limit","$offset"]);
$rws = $frr['data'];
$fnum  =  count($rws);
//if($fnum === 0){
//$frr = $dbConn->getRows($sqquest,["$limit","$offset"]);
//$rws = $frr['data'];
//$fnum  =  count($rws);
//}

}elseif($isLoggedUser !==true){
$frr = $dbConn->getRows($sqquest,["$limit","$offset"]);
$rws = $frr['data'];
$fnum  =  count($rws);
}


$arr  =  $chko = array();


if($fnum > 0){

foreach ($rws as $key => $rw) {
    $rw['is_category'] = false;

if ($rw['is_question'] == 1) {
$rtq =  ($isLoggedUser===true) ? $qaClass->getQuestion($rw['cid'],false,false) : $qaClass->getQuestion($rw['cid'],false,false);

if($rtq !== false){
$arr[] = $rtq;
}

}elseif ($rw['is_answer'] == 1) {
$rtq =  ($isLoggedUser===true) ? $qaClass->getAnswer($rw['cid'],false,false) : $qaClass->getAnswer($rw['cid'],false,false);

if($rtq !== false){
$arr[] = $rtq;
}

}elseif ($rw['is_blog'] == 1) {
$rtq =  ($isLoggedUser===true) ? $feedClass->getArticle($rw['cid'],false) : $feedClass->getArticle($rw['cid'],false);
if($rtq !== false){
$arr[] = $rtq;
}

}elseif ($rw['is_share'] == 1) {
$rtq =  $feedClass->getShared($rw['cid']);
if($rtq !== false){
$arr[] = $rtq;
}
}elseif ($rw['is_comment'] == 1) {
$rtq =  ($isLoggedUser===true) ? $qaClass->getComment($rw['cid'],false) : $qaClass->getComment($rw['cid'],false);
if($rtq !== false){
$arr[] = $rtq;
}
}

}//foreach


}


header('content-type: application/json');
echo '{"activity_feed":'.json_encode($arr).'}';
exit();

}


if($request['action']  ==   'get_user_questions'){
$dbConn = new DbConn();
$qaClass = new QaClass();
$usr = $genClass->getUser();
$thisuser = $usr['email'];
$offset = $request['offset'];
$limit = $request['limit'];
$response = $qaClass->getUserQuestions($thisuser,$limit,$offset);
header('content-type: application/json');
echo '{"user_questions":'.json_encode($response).'}';
exit();
}



if($request['action']  ==   'get_user_answers'){
$dbConn = new DbConn();
$qaClass = new QaClass();
$usr = $genClass->getUser();
$thisuser = $usr['email'];
$offset = $request['offset'];
$limit = $request['limit'];
$response = $qaClass->getUserAnswers($thisuser,$limit,$offset);

header('content-type: application/json');
echo '{"user_answers":'.json_encode($response).'}';
exit();
}

if($request['action']  ==   'get_user_wallet_sales'){
$articlesClass = new ArticlesClass();
$offset = $request['offset'];
$limit = $request['limit'];
$rsp = $articlesClass->getUserArticlesSales($limit,$offset);
header('content-type: application/json');
echo '{"user_wallet_sales":'.json_encode($rsp).'}';
exit();
}

if($request['action']  ==   'get_user_wallet_purchases'){
$articlesClass = new ArticlesClass();
$offset = $request['offset'];
$limit = $request['limit'];
$rsp = $articlesClass->getUserArticlesPurchases($limit,$offset);
header('content-type: application/json');
echo '{"user_wallet_purchases":'.json_encode($rsp).'}';
exit();
}


if($request['action']  ==   'sendBlog'){
$dbConn = new DbConn();
$genClass = new GeneralClass();
$feedClass = new FeedClass();
$notifyClass = new NotificationClass();
$usr = $genClass->getUser();
$mentor_id = $usr['email'];
$title = $request['title'];
$url = $genClass->SEO($title);
$content = htmlentities(addslashes($genClass->purifyContent($request['content'])));
$is_published = $request['is_published'];
$create_date = time();
$mode = 'blog';

$rr = $dbConn->getRows("SELECT * FROM articles WHERE url = ? AND  title = ? AND content = ? AND mode = ?",["$url","$title","$content","$mode"]);

$isExist = count($rr['data']);

if($isExist == 0){
  $aload = array(
    'url' => $url,
    'mentor_id' => $mentor_id,
    'title' => $title,
    'content' => $content,
    'is_published' => $is_published,
    'create_date' => $create_date,
    'mode' => 'blog' 
  );
$sdd = $dbConn->insertDb($aload,'articles');
$uid = $sdd['lastInsertId'];
if($sdd['code']==200){
$feedload = array(
'is_blog'=>1, 
'cid'=>$uid, 
'author'=>$mentor_id, 
'create_date'=>$create_date
);
$feed = $notifyClass->feedNotification($feedload);
$fid = $feed['lastInsertId'];
#
$ft = $dbConn->getRow("SELECT * FROM feed WHERE id = ?",["$fid"]);
$ftr = $ft['data'];
$qs = $feedClass->getArticle($ftr['cid']);
$status = '1'; 
$mess = 'Blog Article Added Successfully';
}else{
$status = '0'; 
$mess = 'Error: Blog Article Not Added! Try Again Later';
$qs = array();   
}
}else{
$status = '0'; 
$mess = 'This Blog Article Already exists'; 
$qs = array();    
}
#
header('content-type: application/json');
echo '
{
"status":'.json_encode($status).',
"data":'.json_encode($qs).',  
"mess":'.json_encode($mess).'
}
';
exit();

}




if( isset($formdata) && $formdata['action']=='uploadArticle'){
$dbConn = new DbConn();
$genClass = new GeneralClass();
$usr = $genClass->getUser();
$user = $usr['email'];
$articleRef = isset($formdata['articleRef']) ? $formdata['articleRef']:'';
$fileRef = isset($formdata['articleRef']) ? $formdata['fileRef']:'';
$title = isset($formdata['articleRef']) ? $formdata['article_title']:'';
$url = $genClass->SEO($title);
$mode = isset($formdata['mode']) ? $formdata['mode']:'';
$index = isset($formdata['index']) ? $formdata['index']:'';
$for_sale = isset($formdata['for_sale']) ? $formdata['for_sale']:0;
$price = (isset($formdata['for_sale']) && isset($formdata['price'])) ? $formdata['price']:'0';
$content = htmlentities(addslashes($genClass->purifyContent($formdata['content'])));
$mentor_id = $usr['email'];
$file = $_FILES['banner'];
#
$pass =  $genClass->crand(10);
$code = mt_rand() . mt_rand() . mt_rand();
$valid_formats = array(
  "jpg", "JPG", "JPEG",  "PJPEG", "pdf", "xlsx", "xls",  "ppt", "pptx", "txt", "csv", "php", "ini", "html", "htacess",  
 "png", "gif", "bmp","jpeg","pjpeg","docx","doc");
$image_formats = array("jpg", "JPG", "JPEG",  "PJPEG", "png", "gif", "bmp","jpeg","pjpeg");
$pdate = time();
$rand = mt_rand().mt_rand();
#
if(!empty($_FILES['banner'])){
$banner_file = $file['name'];
$banner_size = $file['size'];
$flt = pathinfo($banner_file);
$banner_tmp = $file['tmp_name'];
$banner_name = !empty($formdata['fileTag']) ? $formdata['fileTag']:$flt['filename'];
$ext = $flt['extension'];
$banner_url = "files/articles/".$pdate.'-'.$rand.'.'.$ext; //a directory inside
$banner_actual = "../../files/articles/".$pdate.'-'.$rand.'.'.$ext;
$banner_thumb_url = "files/articles/".$pdate.'-'.$rand.'-thumb.'.$ext;
$banner_thumb_actual = "../../files/articles/thumbs/".$pdate.'-'.$rand.'-thumb.'.$ext;  
}
#
$sqls = "SELECT * FROM articles WHERE url = ? AND  title = ? AND reference = ? AND mentor_id = ?";
$avls = ["$url", "$title", "$articleRef", "$mentor_id"];
$hr = $dbConn->getRows($sqls, $avls);
$hrs = $dbConn->getRow($sqls, $avls);
$rwt = $hrs["data"];
$isDey = count($hr['data']);
#
$fr = $dbConn->getRows("SELECT * FROM article_files WHERE reference = ? AND name = ?",["$fileRef","$banner_file"]);
$isFileDey = count($fr['data']);
#
$can_create_article  = false;
$article_created  = false;
$file_created  = false;
$can_create_file  = false;
#
if($isDey == 0){
$can_create_article  = true;
$article_created  = false;
}else{
$article_created  = true;
$can_create_article  = false;
}

if($isFileDey == 0){
$can_create_file  = true;
$file_created  = false;
}else{
$can_create_file  = false;
$file_created  = true;
}


if ($can_create_article === false && $can_create_file === false) {
$go = "no";
$mess = '<div class="error center"><p>This article already exists.</p></div>';
header('content-type: application/json');
echo '{"state":'.json_encode(0).',
"mess":'.json_encode($mess).',
"index":'.json_encode($index).',
"name":'.json_encode($banner_name).',
"status":'.json_encode(false).',
"class":'.json_encode('error').'}';
exit();

}
elseif(!empty($_FILES['banner']) &&!in_array($ext,$valid_formats)){
$go='no';
$mess = '<div class="error center"><p>Invalid File ('.$ext.') Attached! Allowed formats include : '.implode(",", $valid_formats).'/p></div>' ;
header('content-type: application/json');
echo '{"state":'.json_encode(0).',
"mess":'.json_encode($mess).',
"index":'.json_encode($index).',
"status":'.json_encode(false).',
"name":'.json_encode($banner_name).',
"class":'.json_encode('error').'}';
exit();
}
elseif(!isset($formdata['article_title']) | !isset($formdata['content']) 
  | !isset($formdata['for_sale'])){
$go='no';$mess = '<div class="error center"><p>Please Supply all fields before you submit.</p> </div>' ;


header('content-type: application/json');
echo '{"state":'.json_encode(0).',
"mess":'.json_encode($mess).',
"index":'.json_encode($index).',
"status":'.json_encode(false).',
"name":'.json_encode($banner_name).',
"class":'.json_encode('error').'}';
exit();
}else{$go = "yes";}



if ($go == "yes"){



if ( !empty($_FILES['banner'])) {
if(move_uploaded_file($banner_tmp, $banner_actual)){
$uploaded = $banner_url;
}else{
  $uploaded = false;
};
}else{
$rs = array(
  'index'=>$index,
  'name'=>$banner_name,
  'status'=> false,
  'mess'=>'No file attached',
  'class'=>'error'
);
header('content-type: application/json');
echo json_encode($rs);
exit();
}
//Writes the information to the database 
$now = time();
if ($can_create_article === true) {
$arload = array(
'mode'=>$mode, 
'url'=>$url, 
'price'=>$price, 
'for_sale'=>$for_sale,
'reference'=>$articleRef,
'title'=>$title,
'content'=>$content,
'create_date'=>$now,
'mentor_id'=>$mentor_id
);
$sar = $dbConn->insertDb($arload,'articles');
$uid = $sar['lastInsertId'];
$feedload = array(
'is_article'=>1, 
'cid'=>$uid, 
'author'=>$mentor_id, 
'create_date'=>$pdate
);
$feed = $notifyClass->feedNotification($feedload);
$fid = $feed['lastInsertId'];

}elseif($article_created === true &&  $can_create_file === true){
$uid = $rwt['id'];
$sar = array('code'=>200);
}

if($uploaded != false && $can_create_file === true){

$afload = array(
'name'=>$banner_name, 
'url'=>$banner_url, 
'article_id'=>$uid, 
'reference'=>$fileRef,
'mime'=>$ext,
'size'=>$banner_size,
'adate'=>$now
);
$ear = $dbConn->insertDb($afload,'article_files');
if($ear['code'] == 200){$doner = 1;}
}else{
$doner = 0;  
}

if($sar['code']==200 && $doner == 1){
$mess = 'Saved Successfully';  $status = true;  $class = 'good';
}else{
$mess = 'File not saved';  $status = false;  $class = 'error';  
}

$dars = '
{
"state":'.json_encode($status).',
"status":'.json_encode($status).',
"index":'.json_encode($index).',
"name":'.json_encode($banner_name).',
"mess":'.json_encode($mess).',
"class":'.json_encode($class).'}';


header('content-type: application/json');
echo $dars;
exit();
}



}




if($action=='getThisPage'){
$dbConn = new dbConn();
$uid = $request['id'];
$qx = $dbConn->getRow("SELECT * FROM pages WHERE id = ?",["$uid"]);
if($qx['code']==200 && $qx['data'] !==false){
$row = $qx['data'];
$dt  = array(
    'id' => $row['id'], 
    'title' => html_entity_decode($row['title']),
    'name' => html_entity_decode($row['name']),
    'intro' => html_entity_decode($row['intro']),
    'content' => stripslashes(html_entity_decode($row['message']))
    );
}else{
$dt = array();
}
$ds = json_encode($dt);

header('content-type: application/json');
echo '{
"page":'.$ds.'}';

exit();
}





if($action=='allFaq'){
  $dbConn = new dbConn();
$qx = $dbConn->getRows("SELECT id, question, answer FROM faq",[]);
if($qx['code']==200 && $qx['data'] !==false){
$rx = $qx['data'];
$arr = array();  
foreach ($rx as $key => $row) {
$row['id'] = $row['id']; 
$row['question'] = html_entity_decode($row['question']);
$row['answer'] = stripslashes(html_entity_decode($row['answer']));
$arr[] = $row;
}//foreache
}else{
$arr = array();
}

$ds = json_encode($arr);
header('content-type: application/json');
echo '{"faq":'.$ds.'}';

exit();
}




if($action == 'save_artilce_comment'){
$usr = $genClass->getUser();
$thisuser = $usr['email'];
$id = $request['aid'];
$comment = htmlentities(addslashes($genClass->purifyContent($request['newComment'])));
$now = time();
$aload = array(
'article_id'=>$id, 
'user'=>$thisuser, 
'cdate'=>$now, 
'comment'=>$comment
);
$q3 = $dbConn->insertDb($aload,'article_comments');
if($q3['code'] === 200){
$uids = $q3['lastInsertId'];
$status = '1'; $mess = 'comment Saved !';
$rc = $dbConn->getRow(" SELECT c.*, u.firstname, u.surname, u.username, u.user_type, u.avatar FROM article_comments c 
JOIN users u ON c.user = u.email
WHERE c.article_id = ? AND c.id = ?",["$id","$uids"]);
$rcw = $rc['data'];
$rcw['comment'] = stripslashes(html_entity_decode($rcw['comment']));
$lastcomm = $rcw;
}else{
$status = '0'; $mess = 'comment Not Saved !'; 
$lastcomm  = array(); 
}

header('content-type: application/json');
echo '{
"status":'.json_encode($status).',
"mess":'.json_encode($mess).',
"last_comment":'.json_encode($lastcomm).'
}';
exit();

}





if($action == 'getRefData'){
$usr = $genClass->getUser();
$me = $usr['email'];
$sql = "SELECT * FROM referrals WHERE ref_settled = ? AND redeemed = ? AND referee = ?";
$rs = array();
$pr = $dbConn->getRows($sql,["1","0","$me"]);

if($pr['code']==200 && $pr['data']!==false){
$rr  =  $pr['data'];
$arr1  =  array();
$init_sum = $init_point =0;
foreach ($rr as $key => $rts) {
$this_gain = $rts['point']*$rts['point_value'];
$init_sum = $init_sum+$this_gain;
$this_point = $rts['point'];
$init_point = $init_point+$this_point;
}
$final_sum = $init_sum;
$final_point = $init_point;
$rs['total_earn'] = $final_sum;
$rs['total_points'] = $final_point;
}else{
$rs['total_earn'] = 0;
$rs['total_points'] = 0;  
}


header('content-type: application/json');
echo json_encode($rs);
exit();

}


if($action == 'convert_ref_point'){
$usr = $genClass->getUser();
$me = $usr['email'];
$sql = "SELECT * FROM referrals WHERE ref_settled = ? AND redeemed = ? AND referee = ?";
$rs = array();
$pr = $dbConn->getRows($sql,["1","0","$me"]);
if($pr['code']==200 && $pr['data']!==false){
$rr  =  $pr['data'];
$arr1  =  array();
$init_sum = 0;
foreach ($rr as $key => $rts) {
$this_gain = $rts['point']*$rts['point_value'];
$init_sum = $init_sum+$this_gain;
}
$final_sum = $init_sum;

$sxc = "UPDATE referrals SET redeemed = ? WHERE ref_settled = ? AND redeemed = ? AND referee = ?";
$pas = $dbConn->getRows($sxc,["1","1","0","$me"]);

if($pas['code']==200){

if($payClass->creditWallet($me, $final_sum)){
$rs['mess'] = $rs['message'] = 'Awarded Referral Points Successfully';
$rs['status'] = '1';
$rs['class']='good';
$rs['wallet']= $payClass->getWallet($me);
}else{
$rs['mess'] = $rs['message'] = 'Unable to  award Referral Points';
$rs['status'] = '0';
$rs['class']='error'; 
}
}else{
$rs['mess'] = $rs['message'] = 'Unable to save referral earnings award. Please try again.';
$rs['status'] = '0';
$rs['class']='error';   
}
}else{
$rs['mess'] = $rs['message'] = 'Unable to calculate points. Please try again.';
$rs['status'] = '0';
$rs['class']='error';
}


header('content-type: application/json');
echo json_encode($rs);
exit();

}






if($request['action']  ==   'withdraw_earn'){
$usr = $genClass->getUser();
$user = $usr['email'];
$data = $request['data'];
$amount = $data['amount'];
$rdate = time();
//
$bank_name = $data['bank_name'];
$account_number = $data['account_number'];
$account_name = $data['account_name'];
$account_type = $data['account_type'];
//
$checker = $dbConn->getRows("SELECT * FROM wallet  WHERE user = ?",["$user"]); //
$rwe = $checker['data'];
$isChecked = count($rwe);
if($isChecked > 0){
if($rwe[0]['withdrawable'] >= $amount){
$deber = $dbConn->executeSql("UPDATE wallet SET withdrawable = withdrawable-? WHERE user = ?",["$amount","$user"]); 
$ldr = array(
  'user'=>$user,
  'bank_name'=>$bank_name,
  'account_name'=>$account_name,
  'account_number'=>$account_number,
  'account_type'=>$account_type,
  'amount'=>$amount,
  'wdate'=>$rdate
);
$sdd = $dbConn->insertDb($ldr,'payouts');  
if($deber['code'] == 200 && $sdd['code']==200){
$status = '1'; 
$class = 'good'; 
$message = 'Withdrawal request Sent Successfully';
}else{
$status = '0'; 
$class = 'error'; 
$message = 'Withdrawal request Cannot be sent at this time. Try again later.';  
}

} else{
 $status = '0'; 
 $class = 'error'; 
 $message = 'Insufficient Withdrawable Wallet balance.';   
}


}else{
 $status = '0'; 
 $message = 'User not found.'; 
 $class = 'error';  
}


header('content-type: application/json');
echo '{
  "status":'.json_encode($status).', 
  "class":'.json_encode($class).', 
  "message":'.json_encode($message).'
}';
exit();




}



?>