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
$feedClass = new FeedClass;
$accountClass = new AccountClass;
$payClass = new PaymentClass;
$dashClass = new DashClass;
#
$postdata = file_get_contents("php://input");
$request = json_decode($postdata,true);
$action = (is_array($request) && array_key_exists('action',$request))  ? $request['action'] : 'NULL';


##
if(isset($_REQUEST['data'])){
$formdata = json_decode($_REQUEST['data'],true);
}



if($request['action']  ==   'get_feed'){


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
  (f.is_question = 1  AND f.author = ? )  OR   
  (f.is_answer = 1  AND f.author = ? ) 
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
$frr = $dbConn->getRows($squser,["$thisuser","$thisuser","$limit","$offset"]);
$rws = $frr['data'];
$fnum  =  count($rws);
/*
if($fnum === 0){
$frr = $dbConn->getRows($sqquest,["$limit","$offset"]);
$rws = $frr['data'];
$fnum  =  count($rws);
}
*/

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
}

}//foreach


}


header('content-type: application/json');
echo '{"feed":'.json_encode($arr).'}';
exit();



} 


if($request['action']  ==   'get_department_details'){
$dbConn = new DbConn();
$qaClass = new QaClass();
$socialClass = new SocialClass();
$departmentUrl = $request['departmentUrl'];
$dpt  =  $socialClass->getDepartmentByUrl($departmentUrl);

header('content-type: application/json');
echo '{"department_details":'.json_encode($dpt).'}';
exit();

}


if($request['action']  ==   'get_department_feed'){


$dbConn = new DbConn();
$qaClass = new QaClass();
$socialClass = new SocialClass();
$departmentUrl = $request['departmentUrl'];
$offset = $request['offset'];
$limit = $request['limit'];
$dpt  =  $socialClass->getDepartmentByUrl($departmentUrl);
$did = $dpt['id'];
$squser = "SELECT f.* FROM feed f WHERE 
 (f.is_question = 1  AND f.did = ? ) OR 
  (f.is_answer = 1  AND f.did  = ?) OR 
  (f.is_comment = 1  AND f.did  = ? ) 
    ORDER BY  f.id desc  LIMIT ? OFFSET ?";

$frr = $dbConn->getRows($squser,["$did","$did","$did","$limit","$offset"]);
$rws = $frr['data'];
$fnum  =  count($rws);
$arr  =  $chko = array();

if($fnum > 0){
foreach ($rws as $key => $rw) {
$rw['is_category'] = true;
if ($rw['is_question'] == 1) {
$rtq =  $qaClass->getQuestion($rw['cid'],false,false);
if($rtq !== false){
$arr[] = $rtq;
}

}elseif ($rw['is_answer'] == 1) {
$rtq =  $qaClass->getAnswer($rw['cid'],false,false);

if($rtq !== false){
$arr[] = $rtq;
}
}

}//foreach


}




header('content-type: application/json');
echo '{"department_feed":'.json_encode($arr).'}';
exit();



} 




if($request['action']  ==   'get_topic_details'){
$dbConn = new DbConn();
$qaClass = new QaClass();
$url = $request['url'];
if(isset($_SESSION['senseiUser']) || isset($_SESSION['senseiMentor'])){
$isLoggedUser = true;
$usr = $genClass->getUser();
$thisuser = $usr['email'];
}else{
$isLoggedUser = false;  
}
$rtq =  ($isLoggedUser===true) ? $qaClass->getTopicDetails($url,$thisuser) : $qaClass->getTopicDetails($url);

header('content-type: application/json');
echo '{"topic_details":'.json_encode($rtq).'}';
exit();

} 


if($request['action']  ==   'get_topic'){
$dbConn = new DbConn();
$qaClass = new QaClass();
$offset = $request['offset'];
$limit = $request['limit'];
$url = $request['url'];
if(isset($_SESSION['senseiUser']) || isset($_SESSION['senseiMentor'])){
$isLoggedUser = true;
$usr = $genClass->getUser();
$thisuser = $usr['email'];
}else{
$isLoggedUser = false;  
}
$rtq =  ($isLoggedUser===true) ? $qaClass->getTopic($url,$thisuser,$limit,$offset) : $qaClass->getTopic($url,false,$limit,$offset);

header('content-type: application/json');
echo '{"topic":'.json_encode($rtq).'}';
exit();

} 


if($request['action']  ==   'list_more_question_answers'){
$dbConn = new DbConn();
$qaClass = new QaClass();
$qid = $request['id'];
$rsp = $qaClass->getMoreFeedQuestionAnswers($qid);
header('content-type: application/json');
echo '{"answers":'.json_encode($rsp).'}';
exit();

} 


if($request['action']  ==   'list_more_answer_comments'){
$dbConn = new DbConn();
$qaClass = new QaClass();
$qid = $request['parent_answer_id'];
$rsp = $qaClass->getMoreFeedAnswerComments($qid);

header('content-type: application/json');
echo '{"answers":'.json_encode($rsp).'}';
exit();
} 


if($request['action']  ==   'fetch_side_bar_departments'){
$dbConn = new DbConn();
$qaClass = new QaClass();
$rsp = $qaClass->getNavDepartments();
header('content-type: application/json');
echo '{"departments":'.json_encode($rsp).'}';
exit();
} 


if($request['action']  ==   'save_comment'){
  $qaClass = new QaClass();
$rsp = $qaClass->saveComment($request);

header('content-type: application/json');
echo json_encode($rsp);
exit();
}

if($request['action']  ==   'saveA2a'){
  $qaClass = new QaClass();
$rsp = $qaClass->saveA2a($request);
header('content-type: application/json');
echo json_encode($rsp);
exit();
}

if(
$request['action']  ==   'save_follow_user' ||
$request['action']  ==   'save_unfollow_user'
){
$socialClass = new SocialClass();
$rsp = $socialClass->saveFollowUser($request);
header('content-type: application/json');
echo json_encode($rsp);
exit();
}

if(
$request['action']  ==   'save_follow_department' ||
$request['action']  ==   'save_unfollow_department'
){
$socialClass = new SocialClass();
$rsp = $socialClass->saveFollowDepartment($request);
header('content-type: application/json');
echo json_encode($rsp);
exit();
}

if($request['action']  ==   'save_answer'){
$qaClass = new QaClass();
$rsp = $qaClass->saveAnswer($request);
header('content-type: application/json');
echo json_encode($rsp);
exit();
} 
if($request['action']  ==   'save_follow_topic'){
$qaClass = new QaClass();
$rsp = $qaClass->followTopic($request['question_id']);
header('content-type: application/json');
echo json_encode($rsp);
exit();
} 


if($request['action']  ==   'save_upvote'){
$comId = $request['id'];
$type = 'upvote';
$qaClass = new QaClass();
$rsp = $qaClass->saveVote($comId,$type);

header('content-type: application/json');
echo json_encode($rsp);
exit();
} 

if($request['action']  ==   'save_downvote'){
$comId = $request['id'];
$type = 'downvote';
$qaClass = new QaClass();
$rsp = $qaClass->saveVote($comId,$type);
header('content-type: application/json');
echo json_encode($rsp);
exit();
} 


if($request['action']  ==   'saveQuestion'){
$qaClass = new QaClass();
$rsp = $qaClass->saveQuestion($request);
header('content-type: application/json');
echo json_encode($rsp);
exit();
} 






?>
