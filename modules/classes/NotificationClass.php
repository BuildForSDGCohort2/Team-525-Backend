<?php

/**
 * 
 */
class NotificationClass
{


  function __construct()
  {
}

public function notifDetails($user){
$dbConn = new DbConn();
$qnf = $dbConn->getRows("SELECT id FROM notifications WHERE user = ? AND status = ?",["$user","0"]);
$notCount = count($qnf['data']);
return array('userNotif' => $notCount);
}

public function notifyUser($detail,$user){
$dbConn = new DbConn();
$genClass =new GeneralClass();
$now = time();
$det = htmlentities($detail);
$load = array(
'detail'=> $det,
'ndate'=>$now,
'user'=>$user);
$resp = $dbConn->insertDb($load,'notifications');
return $resp;
}

public function getUserNotifications(){
$dbConn = new DbConn();
$genClass =new GeneralClass();
$usr = $genClass->getUser();
$thisuser = $usr['email'];
$q3 = $dbConn->getRows("SELECT * FROM notifications WHERE user = ? ORDER BY id DESC",["$thisuser"]);
$arr = array(); 
if($q3['code'] == 200 && $q3['code']!==false){
foreach ($q3['data'] as $key => $rw) {
$rw['detail'] = stripslashes( html_entity_decode ($rw['detail']));
$arr[] = $rw;
}

}
return $arr;
}

public function feedNotification($load){
$dbConn = new DbConn();
$q3 = $dbConn->insertDb($load,'feed');
return $q3;
}//feedNotification





public function notifya2a($requester,$mentor,$qid){
$genClass = new GeneralClass();
$dbConn = new DbConn();
$qaClass = new QaClass();
$usr = $genClass->getUserFromEmail($requester);
$user_url = 'profile/'.$usr['username'];
$user_firstname = $usr['firstname'];
$user_surname = $usr['surname'];
$question = $qaClass->getQuestion($qid);
//
$usra = $genClass->getUserFromEmail($question['email']);
$author_url = '/profile/'.$usra['username'];
$author_firstname = $usra['firstname'];
$author_surname = $usra['surname'];
//
$asker_username = $question['username'];
$asker_firstname = $question['firstname'];
$asker_surname = $question['surname'];
$asked_title = $question['title'];
$asked_url = $question_url = 'topic/'.$question['url'];
$question_title = $question['title'];
$asker_url = 'profile/'.$asker_username;
$detail = htmlentities(addslashes(
  ' <a href="'.$user_url.'"> '.$user_firstname.' '.$user_surname.' </a>  asked you to answer a question -  '.' <a href="'.$question_url.'"> '.$question_title.' </a> asked by <a href="'.$author_url.'"> '.$author_firstname.' '.$author_surname.' </a>'));

$now = time();
$lda = array(
    'user'=>$mentor,
    'detail'=>$detail,
    'ndate'=>$now
);
$qt1 = $dbConn->insertDb($lda,'notifications');



if($qt1['code'] == 200){
    return true;
}else{
    return false;
}

}//notifya2a




/**/
public function notifySettlement($data){
$genClass = new GeneralClass();
$dbConn = new DbConn();
$user = $data['user'];
//
$usra = $genClass->getUserFromEmail($data['buyer']);
$buyer_url = 'profile/'.$usra['username'];
$buyer_firstname = $usra['firstname'];
$buyer_surname = $usra['surname'];
//
$article_title = $data['article_title'];
$article_url = 'article/'.$data['article_url'];

$detail = htmlentities(addslashes(
  'You have received the sum of 
  '.number_format($data['settlement_balance']).' naira being the settlement fee at the commission rate of 
  '.$data['commission_rate'].'% for your article - <a href="'.$article_url.'"> '.$article_title.' </a> -  purchased by <a href="'.$buyer_url.'"> '.$buyer_firstname.' '.$buyer_surname.' </a>'));

$now = time();
$lda = array(
    'user'=>$user,
    'detail'=>$detail,
    'ndate'=>$now
);
$qt1 = $dbConn->insertDb($lda,'notifications');

if($qt1['code'] == 200){
    return true;
}else{
    return false;
}

}//notifySettlement






public function sendNotify($type,$qId){
$genClass = new GeneralClass();
$dbConn = new DbConn();
$qaClass = new QaClass();
$socialClass = new SocialClass();
switch ($type) {
case "question":
$question = $qaClass->getQuestion($qId);
$asker_email = $question['email'];
$asker_username = $question['username'];
$asker_firstname = $question['firstname'];
$asker_surname = $question['surname'];
$asked_title = $question['title'];
$asked_url = 'topic/'.$question['url'];
$asker_url = 'profile/'.$asker_username;
$detail = htmlentities(addslashes(
  '<a href="'.$asker_url.'"> '.$asker_firstname.' '.$asker_surname.' </a>  asked a question: '. ' <a href="'.$asked_url.'"> '.$asked_title.' </a>'));

$arti = $socialClass->getDepartmentFollowers($question['department_id']);

$grand_users = array();
foreach ($arti as $ky => $rwi) { 
$grand_users[] = $rwi['user'];
}
$now =  time();
for ($i=0; $i < count($grand_users); $i++) {
$user = (!empty($grand_users[$i])) ? $grand_users[$i]:''; 
if(!empty($grand_users[$i])){
$nload = array('user'=>$user, 'detail'=>$detail, 'ndate'=>$now);
$qt1 = $dbConn->insertDb($nload,'notifications');
}
}
//if($qt1['code']==200){return true;}else{return false;}
break;//sendQuestion Notifications


case "answer":
$answer = $qaClass->getAnswer($qId);
$asker_email = $answer['asker_email'];
$asker_username = $answer['asker_username'];
$asker_firstname = $answer['asker_firstname'];
$asker_surname = $answer['asker_surname'];
$asker_url = 'profile/'.$asker_username;
//
$ans_email = $answer['ans_email'];
$ans_username = $answer['ans_username'];
$ans_firstname = $answer['ans_firstname'];
$ans_surname = $answer['ans_surname'];
$ans_url = 'profile/'.$ans_username;
//
$question_title = $answer['title'];
$question_did = $answer['department_id'];
$question_url = 'topic/'.$answer['url'];
//
$detail = htmlentities(addslashes(
  '<a href="'.$ans_url.'">'.$ans_firstname.' '.$ans_surname.'</a>  answered the question: '.'<a href="'.$question_url.'">'.$question_title.'</a> asked by <a href="'.$asker_url.'">'.$asker_firstname.' '.$asker_surname.'</a>'));

//users Following Answerer
$users_following_mentor = $socialClass->collateMentorsFollowers($ans_email);

//users folllowing department
//$users_following_category = collateUsersInCategory($question_did,$mysqli);

$arti  = $socialClass->getDepartmentFollowers($question_did);
$users_following_category = array();
foreach ($arti as $ky => $rwi) { 
$users_following_category[] = $rwi['user'];
}

$previous_answerers = $qaClass->collatePreviousAnswerers($answer['question_id']);

$grand_users = array_merge($users_following_category,$previous_answerers);
$final_users = array_filter(array_unique(array_merge($grand_users,[$asker_email])));
$now =  time();
foreach ($final_users as $val) {
$user = $val; 
$nload = array('user'=>$user, 'detail'=>$detail, 'ndate'=>$now);
$qt1 = $dbConn->insertDb($nload,'notifications');
}
if($qt1['code']==200){return true;}else{return false;}
break;//getFacultyName



case "comment":
$comment = $qaClass->getComment($qId);
$commenter_email = $comment['com_email'];
$commenter_username = $comment['com_username'];
$commenter_firstname = $comment['com_firstname'];
$commenter_surname = $comment['com_surname'];
$commenter_url = 'profile/'.$commenter_username;
//
$ans_email = $comment['parent_email'];
$ans_username = $comment['parent_username'];
$ans_firstname = $comment['parent_firstname'];
$ans_surname = $comment['parent_surname'];
$ans_url = 'profile/'.$ans_username;
//
$question_title = $comment['title'];
$question_did = $comment['department_id'];
$question_url = 'topic/'.$comment['url'];
//
$detail = htmlentities(addslashes(
  ' <a href="'.$commenter_url.'"> '.$commenter_firstname.' '.$commenter_surname.' </a>  also commented on the answer to  the question : '.' <a href="'.$question_url.'"> '.$question_title.' </a>'));

//users Following Answerer
$users_followers = ($comment['com_user_type'] == 'mentor') ? $socialClass->collateMentorsFollowers($commenter_email) : $socialClass->collateUsersMentors($commenter_email);

//users folllowing department
$users_following_category = $socialClass->collateUsersInCategory($question_did);

//users in previous comments
$previous_commenters = $qaClass->collatePreviousCommenters($comment['answer_id']);

$previous_answerers = $qaClass->collatePreviousAnswerers($comment['question_id']);

$grand_users = array_merge(
  $previous_answerers,
  $previous_commenters,
  [$commenter_email,$ans_email]
);
$final_users = array_filter(array_unique($grand_users));
$now =  time();
//print_r($final_users);
foreach ($final_users as $val) {
$user = $val; 
$nload = array('user'=>$user, 'detail'=>$detail, 'ndate'=>$now);
$qt1 = $dbConn->insertDb($nload,'notifications');
}

//if($qt1){return true;}else{return false;}
break;//getFacultyName




case "settlement":



break;//notifySettlement






case "sharing":



break;//notifysharing




}

}



}//NotificationClass

?>