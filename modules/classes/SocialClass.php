<?php

/**
 * 
 */
class SocialClass
{
	
	function __construct()
	{
		# code...
	}

public function saveFollowUser($data){
$dbConn = new DbConn();
$genClass = new GeneralClass();
$usr = $genClass->getUser();
$notifyClass = new NotificationClass();
$thisuser = $usr['email'];
$mentor = $data['other_email'];
$now = time();
$r = array();
$qt = $dbConn->getRows("SELECT * FROM mentor_follow WHERE user = ? AND mentor = ?",["$thisuser","$mentor"]);
$qd = $dbConn->getRow("SELECT * FROM mentor_follow WHERE user = ? AND mentor = ?",["$thisuser","$mentor"]);
$folCount  =  count($qt['data']);
$isFollowed = ($folCount == 0 ) ? false : true;
if($isFollowed === true){
$qx = $dbConn->executeSql("DELETE FROM mentor_follow WHERE user = ? AND mentor = ?",["$thisuser","$mentor"]);
if($qx['code']==200){
$r['status'] = '1';
$r['mode'] = 'unfollow';	
$r['mess'] = 'Unfollowed';	
}else{
$r['status'] = '0';	
$r['mess'] = 'Unfollow Failed';
$r['mode'] = 'unfollow';	
}
}elseif($isFollowed === false){
$frx = $qd['data'];

if($mentor === $thisuser){
$r['status'] = '100';	
$r['mess'] = 'Cant follow self';
$r['mode'] = 'follow';
}else{//start follow process
$fload = array(
'user'=>$thisuser,
'mentor'=>$mentor,
'fdate'=>$now
);
$qx = $dbConn->insertDb($fload,"mentor_follow");
if($qx['code']==200){
$r['status'] = '1';	
$r['mess'] = 'Followed';
$r['mode'] = 'follow';
$mentor_url = 'profile/'.$usr['username'];
$detail = '<a href="'.$mentor_url.'">'.$usr['firstname'].''.$usr['surname'].'</a> followed you';	
$notifyClass->notifyUser($detail,$mentor);
}else{
$r['status'] = '0';	
$r['mess'] = 'Unfollow Failed';
$r['mode'] = 'follow';	
}
}//
}


return $r;
}



public function saveFollowDepartment($data){
$dbConn = new DbConn();
$genClass = new GeneralClass();
$usr = $genClass->getUser();
$notifyClass = new NotificationClass();
$thisuser = $usr['email'];
$did = $data['department_id'];
$now = time();
$r = array();
$qt = $dbConn->getRows("SELECT * FROM followed_departments WHERE user = ? AND department_id = ?",["$thisuser","$did"]);
$qd = $dbConn->getRow("SELECT * FROM followed_departments WHERE user = ? AND department_id = ?",["$thisuser","$did"]);
$folCount  =  count($qt['data']);
$isFollowed = ($folCount == 0 ) ? false : true;
if($isFollowed === true){
$qx = $dbConn->executeSql("DELETE FROM followed_departments WHERE user = ? AND department_id = ?",["$thisuser","$did"]);
if($qx['code']==200){
$r['status'] = '1';
$r['mode'] = 'unfollow';	
$r['mess'] = 'Unfollowed';	
}else{
$r['status'] = '0';	
$r['mess'] = 'Unfollow Failed';
$r['mode'] = 'unfollow';	
}
}elseif($isFollowed === false){
$frx = $qd['data'];
$fload = array(
'user'=>$thisuser,
'department_id'=>$did
);
$qx = $dbConn->insertDb($fload,"followed_departments");
if($qx['code']==200){
$r['status'] = '1';	
$r['mess'] = 'Followed';
$r['mode'] = 'follow';
}else{
$r['status'] = '0';	
$r['mess'] = 'Unfollow Failed';
$r['mode'] = 'follow';	
}
}



return $r;
}//followDepartment


public function isUserFollowed($mentor,$user){
$dbConn = new DbConn();
$qt = $dbConn->getRows("SELECT * FROM mentor_follow WHERE user = ? AND mentor = ?",["$user","$mentor"]);
$isFol  =  count($qt['data']);
$status = ($isFol == 0 ) ? false : true;
return $status;
}

public function isDepartmentFollowed($did,$user){
$dbConn = new DbConn();
$qt = $dbConn->getRows("SELECT * FROM followed_departments WHERE user = ? AND department_id = ?",["$user","$did"]);
$isFol  =  count($qt['data']);
$status = ($isFol == 0 ) ? false : true;
return $status;
}


public function getUserPublicEducation($username,$offset=false,$limit=false){
$dbConn = new dbConn();
$genClass = new GeneralClass();
$usr = $genClass->getUserFromUsername($username);
$thisuser = $usr['email'];

$lim = (isset($limit) && $limit !== false) ? $limit : 12; 
$rsp = (isset($offset) && $offset !== false) ? 
$dbConn->getRows("SELECT  pe.*, i.name AS school_name, d.name AS department_name
FROM profile_education pe
JOIN institutions i ON pe.school_id = i.id
JOIN departments d  ON pe.department_id = d.id
WHERE pe.user = ?
ORDER BY pe.id asc  LIMIT ? OFFSET ?",["$thisuser","$lim","$offset"])
:
$dbConn->getRows("SELECT  pe.*, i.name AS school_name, d.name AS department_name
FROM profile_education pe
JOIN institutions i ON pe.school_id = i.id
JOIN departments d  ON pe.department_id = d.id
WHERE pe.user = ?
ORDER BY pe.id asc LIMIT ? ",["$thisuser","$lim"])
;


$rws = $rsp['data'];
return $rws;
}//getUserPublicEducation

public function getOneEducation($eid){
$dbConn = new dbConn();
$rsp =  
$dbConn->getRow("SELECT  pe.*, i.name AS school_name, d.name AS department_name
FROM profile_education pe
JOIN institutions i ON pe.school_id = i.id
JOIN departments d  ON pe.department_id = d.id
WHERE pe.id = ?",["$eid"]);

$rws = $rsp['data'];
return $rws;
}//getOneEducation


public function getUserEducation($offset=false,$limit=false){
$dbConn = new dbConn();
$genClass = new GeneralClass();
$usr = $genClass->getUser();
$thisuser = $usr['email'];

$lim = (isset($limit) && $limit !== false) ? $limit : 12; 
$rsp = (isset($offset) && $offset !== false) ? 
$dbConn->getRows("SELECT  pe.*, i.name AS school_name, d.name AS department_name
FROM profile_education pe
JOIN institutions i ON pe.school_id = i.id
JOIN departments d  ON pe.department_id = d.id
WHERE pe.user = ?
ORDER BY pe.id asc  LIMIT ? OFFSET ?",["$thisuser","$lim","$offset"])
:
$dbConn->getRows("SELECT  pe.*, i.name AS school_name, d.name AS department_name
FROM profile_education pe
JOIN institutions i ON pe.school_id = i.id
JOIN departments d  ON pe.department_id = d.id
WHERE pe.user = ?
ORDER BY pe.id asc LIMIT ? ",["$thisuser","$lim"])
;


$rws = $rsp['data'];
return $rws;
}//getUSerEducation




public function getUserFollowing($offset=false,$limit=false){

$dbConn = new dbConn();
$genClass = new GeneralClass();
$usr = $genClass->getUser();
$thisuser = $usr['email'];

if(isset($_SESSION['senseiMentor']) 
  || isset($_SESSION['senseiUser'])  
  || isset($_SESSION['senseiAdmin'])){
$isfl = true;
$uxr = $genClass->getUser();
}else{
$isfl = false;
}


$lim = (isset($limit) && $limit !== false) ? $limit : 12; 
$rsp = (isset($offset) && $offset !== false) ? 
$dbConn->getRows("SELECT  m.fdate, m.mentor AS followed_user, mu.firstname AS followed_firstname, mu.surname AS followed_surname, mu.avatar AS followed_avatar, mu.username AS followed_username
FROM mentor_follow m
JOIN users mu ON m.mentor = mu.email
WHERE m.user = ?
ORDER BY m.id desc  LIMIT ? OFFSET ?",["$thisuser","$lim","$offset"])
:
$dbConn->getRows("SELECT  m.fdate, m.mentor AS followed_user, mu.firstname AS followed_firstname, mu.surname AS followed_surname, mu.avatar AS followed_avatar, mu.username AS followed_username
FROM mentor_follow m
JOIN users mu ON m.mentor = mu.email
WHERE m.user = ?
ORDER BY m.id desc LIMIT ? ",["$thisuser","$lim"])
;

$curs = $genClass->getUser();
$curUser = $curs['email'];
$rws = $rsp['data'];
$ars = array();
foreach ($rws as $key => $rw) {
$rw['is_followed'] = $this->isUserFollowed($rw['followed_user'],$curUser);
$rw['other_avatar'] = $rw['followed_avatar'];
$rw['is_logged'] = $isfl;
$rw['other_username'] = $rw['followed_username'];
$rw['other_email'] = $rw['followed_user'];
$rw['other_url'] = 'profile/'.$rw['followed_username'];
$rw['other_name'] = $rw['followed_firstname'].' '.$rw['followed_surname'];
$ars[] = $rw;
}
return $ars;	

}//getUserFollowing


public function getUserPublicFollowing($username,$offset=false,$limit=false){

$dbConn = new dbConn();
$genClass = new GeneralClass();
$usr = $genClass->getUserFromUsername($username);
$thisuser = $usr['email'];
if(isset($_SESSION['senseiMentor']) 
  || isset($_SESSION['senseiUser'])  
  || isset($_SESSION['senseiAdmin'])){
$isfl = true;
$uxr = $genClass->getUser();
}else{
$isfl = false;
}

$lim = (isset($limit) && $limit !== false) ? $limit : 12; 
$rsp = (isset($offset) && $offset !== false) ? 
$dbConn->getRows("SELECT  m.fdate, m.mentor AS followed_user, mu.firstname AS followed_firstname, mu.surname AS followed_surname, mu.avatar AS followed_avatar, mu.username AS followed_username
FROM mentor_follow m
JOIN users mu ON m.mentor = mu.email
WHERE m.user = ?
ORDER BY m.id desc  LIMIT ? OFFSET ?",["$thisuser","$lim","$offset"])
:
$dbConn->getRows("SELECT  m.fdate, m.mentor AS followed_user, mu.firstname AS followed_firstname, mu.surname AS followed_surname, mu.avatar AS followed_avatar, mu.username AS followed_username
FROM mentor_follow m
JOIN users mu ON m.mentor = mu.email
WHERE m.user = ?
ORDER BY m.id desc LIMIT ? ",["$thisuser","$lim"])
;

$curs = $genClass->getUser();
$curUser = $curs['email'];
$rws = $rsp['data'];
$ars = array();
foreach ($rws as $key => $rw) {
$rw['is_followed'] = $this->isUserFollowed($rw['followed_user'],$curUser);
$rw['other_avatar'] = $rw['followed_avatar'];
$row['is_logged'] = $isfl;
$rw['other_url'] = 'profile/'.$rw['followed_username'];
$rw['other_username'] = $rw['followed_username'];
$rw['other_email'] = $rw['followed_user'];
$rw['other_name'] = $rw['followed_firstname'].' '.$rw['followed_surname'];
$ars[] = $rw;
}
return $ars;	

}//getUserPublicFollowing



public function getUserFollowers($offset=false,$limit=false){

$dbConn = new dbConn();
$genClass = new GeneralClass();
$usr = $genClass->getUser();
$thisuser = $usr['email'];

$lim = (isset($limit) && $limit !== false) ? $limit : 12; 
$rsp = (isset($offset) && $offset !== false) ? 
$dbConn->getRows("SELECT  m.fdate, m.user AS follower_user, mu.firstname AS follower_firstname, mu.surname AS follower_surname, mu.avatar AS follower_avatar, mu.username AS follower_username
FROM mentor_follow m
JOIN users mu ON m.user = mu.email
WHERE m.mentor = ?
ORDER BY m.id desc  LIMIT ? OFFSET ?",["$thisuser","$lim","$offset"])
:
$dbConn->getRows("SELECT  m.fdate, m.user AS follower_user, mu.firstname AS follower_firstname, mu.surname AS follower_surname, mu.avatar AS follower_avatar, mu.username AS follower_username
FROM mentor_follow m
JOIN users mu ON m.user = mu.email
WHERE m.mentor = ?
ORDER BY m.id desc LIMIT ? ",["$thisuser","$lim"])
;

$curs = $genClass->getUser();
$curUser = $curs['email'];
$rws = $rsp['data'];
$ars = array();
foreach ($rws as $key => $rw) {
$rw['is_followed'] = $this->isUserFollowed($rw['follower_user'],$curUser);
$rw['other_avatar'] = $rw['follower_avatar'];
$rw['other_url'] = 'profile/'.$rw['follower_username'];
$rw['other_username'] = $rw['follower_username'];
$rw['other_email'] = $rw['follower_user'];
$rw['other_name'] = $rw['follower_firstname'].' '.$rw['follower_surname'];
$ars[] = $rw;
}
return $ars;	
	
}//getUserFollowers





public function getUserPublicFollowers($username,$offset=false,$limit=false){

$dbConn = new dbConn();
$genClass = new GeneralClass();
$usr = $genClass->getUserFromUsername($username);
$thisuser = $usr['email'];

$lim = (isset($limit) && $limit !== false) ? $limit : 12; 
$rsp = (isset($offset) && $offset !== false) ? 
$dbConn->getRows("SELECT  m.fdate, m.user AS follower_user, mu.firstname AS follower_firstname, mu.surname AS follower_surname, mu.avatar AS follower_avatar, mu.username AS follower_username
FROM mentor_follow m
JOIN users mu ON m.user = mu.email
WHERE m.mentor = ?
ORDER BY m.id desc  LIMIT ? OFFSET ?",["$thisuser","$lim","$offset"])
:
$dbConn->getRows("SELECT  m.fdate, m.user AS follower_user, mu.firstname AS follower_firstname, mu.surname AS follower_surname, mu.avatar AS follower_avatar, mu.username AS follower_username
FROM mentor_follow m
JOIN users mu ON m.user = mu.email
WHERE m.mentor = ?
ORDER BY m.id desc LIMIT ? ",["$thisuser","$lim"])
;

$curs = $genClass->getUser();
$curUser = $curs['email'];
$rws = $rsp['data'];
$ars = array();
foreach ($rws as $key => $rw) {
$rw['is_followed'] = $this->isUserFollowed($rw['follower_user'],$curUser);
$rw['other_avatar'] = $rw['follower_avatar'];
$rw['other_url'] = 'profile/'.$rw['follower_username'];
$rw['other_username'] = $rw['follower_username'];
$rw['other_email'] = $rw['follower_user'];
$rw['other_name'] = $rw['follower_firstname'].' '.$rw['follower_surname'];
$ars[] = $rw;
}
return $ars;	
	
}//getUserPublicFollowers



public function getUserDepartments($user=false){
$genClass = new GeneralClass();
$dbConn = new DbConn();
if(isset($user) && $user != false){
$userx = $user;
}else if(!isset($user) || $user == false){
$usr =  $genClass->getUser(); 
$userx = $usr['email']; 
} 
$sq = "SELECT f.id as fid, f.department_id, d.name FROM followed_departments f 
JOIN departments d ON f.department_id = d.id
 WHERE f.user = ?";
$qr2 = $dbConn->getRows($sq,["$userx"]);
$rws = $qr2['data'];
$ayt = array();
foreach ($rws as $key => $rt) {
$rt['is_followed'] = $this->isDepartmentFollowed($rt['department_id'],$userx);
$ayt[] = $rt;
}

return $ayt;
}//getDepartmentFollowers



public function listUserMentors($username=false,$fmode=false){
$dbConn = new DbConn();
$genClass = new GeneralClass();
$usr = $genClass->getUser();
$thisuser = $usr['email'];
$rl = $dbConn->getRows("SELECT m.*, u.username, u.firstname, u.surname, u.avatar FROM mentor_follow m
JOIN users u ON m.mentor = u.email
WHERE m.user = ?
",["$thisuser"]);
$rows = $rl['data'];
$ars = array();
foreach ($rows as $key => $rwt) {
if($fmode !== false && is_array($fmode)){
$rwt['has_asked'] = $this->hasAsked($fmode['question_id'],$rwt['mentor']);
}
$ars[] = $rwt;
}

return $ars;
}//listUserMentors

public function hasAsked($qid,$mentor){
$dbConn = new DbConn();
$qt = $dbConn->getRows("SELECT * FROM a2a WHERE question_id = ? AND mentor = ?",["$qid","$mentor"]);
$isFol  =  count($qt['data']);
$status = ($isFol == 0 ) ? false : true;
return $status;
}//

public function getDepartmentFollowers($did){
$dbConn = new DbConn();	
$sq = "SELECT f.id, f.department_id, f.user FROM followed_departments f 
 WHERE f.department_id = ?";
$qr2 = $dbConn->getRows($sq,["$did"]);

$rws = $qr2['data'];
return $rws;
}//getDepartmentFollowers

public function fetchEducation(){
	$genClass = new GeneralClass();
	$usr = $genClass->getUser();
	$thisuser = $usr['email'];
$dbConn = new DbConn();	
$sq = "SELECT i.* FROM institutions i ";
$qr2 = $dbConn->getRows($sq,[]);
$rws = $qr2['data'];
//		
$sqf = "SELECT f.* FROM faculties f ";
$qrf = $dbConn->getRows($sqf,[]);
$rwf = $qrf['data'];
//	
$sqx = "SELECT d.*, d.id AS department_id FROM departments d";
$qrx = $dbConn->getRows($sqx,[]);
$rwa = $qrx['data'];
$ard = array();
foreach ($rwa as $key => $rwd) {
$rwd['is_followed'] = $this->isDepartmentFollowed($rwd['id'],$thisuser);
$ard[] = $rwd;
}
return array(
	'schools'=> $rws, 
	'faculties'=>$rwf, 
	'departments'=>$ard);
}/// fetchEducation



public function usersFriendsArray($username = false){
$genClass = new GeneralClass();
$dbConn = new DbConn();
if(!isset($username) ||  empty($username) ||  $username === null || $username === ''){
if(isset($_SESSION['senseiMentor'])){
$user = $_SESSION['senseiMentor'];
}else if(isset($_SESSION['senseiUser'])){
$user = $_SESSION['senseiUser'];
}else if(isset($_SESSION['senseiAdmin'])){
$user = $_SESSION['senseiAdmin'];
}else{
return false;
}
}else{
$user = $username;   
}

$folers = $this->usersFollowersEmailArray($user);
$folis = $this->usersFollowingEmailArray($user);
$ipals = array_merge($folers,$folis);

$friends = array_unique($ipals);

return $friends;


}//usersFriedsArray



public function usersFollowersEmailArray($username=false){
$dbConn = new DbConn();
if(!isset($username) ||  empty($username) ||  $username === null || $username === ''){
if(isset($_SESSION['senseiMentor'])){
$user = $_SESSION['senseiMentor'];
}else if(isset($_SESSION['senseiUser'])){
$user = $_SESSION['senseiUser'];
}else if(isset($_SESSION['senseiAdmin'])){
$user = $_SESSION['senseiAdmin'];
}else{
return false;
}
}else{
$user = $username;   
}

$rl = $dbConn->getRows("SELECT  u.email FROM mentor_follow m 
JOIN users u ON m.user = u.email
  WHERE m.mentor = ?  AND m.user <> ? ORDER BY m.id desc",["$user","$user"]);
$rows = $rl['data'];
$isArt  =  count($rows);
$arr  =  array();
if($isArt > 0){
foreach ($rows as $key => $rw) {
$arr[] = $rw['email'];
}
}else{
$arr = array();    
}
return $arr;

}//usersFollowerEmailArray



public function usersFollowingEmailArray($username = false){
$dbConn = new DbConn();
if(!isset($username) ||  empty($username) ||  $username === null || $username === ''){
if(isset($_SESSION['senseiMentor'])){
$user = $_SESSION['senseiMentor'];
}else if(isset($_SESSION['senseiUser'])){
$user = $_SESSION['senseiUser'];
}else if(isset($_SESSION['senseiAdmin'])){
$user = $_SESSION['senseiAdmin'];
}else{
return false;
}
}else{
$user = $username;   
}
$rl = $dbConn->getRows("SELECT   u.email FROM mentor_follow m 
JOIN users u ON m.mentor = u.email
  WHERE m.user = ? AND m.mentor <> ?  ORDER BY m.id desc",["$user","$user"]);
$rows = $rl['data'];
$isArt  =  count($rows);
$arr  =  array();
if($isArt > 0){
foreach ($rows as $key => $rw) {
$arr[] = $rw['email'];
}    
}else{
$arr = array();    
}
return $arr;

}//usersFollowingEmailArray


public function getDepartmentByUrl($durl){
$dbConn = new DbConn();
$cmc = " ( SELECT Count(*) FROM followed_departments WHERE department_id = d.id ) 
    as department_follows ";
##
$tpc = " ( SELECT Count(*) FROM feed WHERE did = d.id ) 
    as topics_num ";
##
$asn = " ( SELECT Count(*) FROM comments c 
LEFT JOIN questions q ON q.id = c.question_id
WHERE q.department_id = d.id) as answer_num ";
$rl = $dbConn->getRow("SELECT d.id, d.id AS department_id, d.url, d.name, $cmc, $tpc, $asn FROM departments d 
WHERE d.url = ?
",["$durl"]);
$row = $rl['data'];
$row['is_department_feed'] = true;
$row['department_id'] = $row['id'] = (int)$row['department_id'];
return $row;
}//getDepartmentByUrl

public function listDepartments(){
$dbConn = new DbConn();
$rl = $dbConn->getRows("SELECT id, name FROM departments",[]);
$rows = $rl['data'];
return $rows;
}//listDepartments

public function listUserDepartments($username=false){
$dbConn = new DbConn();
$genClass = new GeneralClass();
$usr = $genClass->getUser();
$thisuser = $usr['email'];
$rl = $dbConn->getRows("SELECT fd.department_id AS id, d.name FROM followed_departments fd
JOIN departments d ON d.id = fd.department_id
GROUP BY fd.department_id
",[]);
$rows = $rl['data'];
return $rows;
}//listUserDepartments



public function listUserPublicDepartments($username=false,$offset=false,$limit=false){


$dbConn = new dbConn();
$genClass = new GeneralClass();
$usr = $genClass->getUserFromUsername($username);
$thisuser = $usr['email'];
$rl = $dbConn->getRows("SELECT fd.department_id, fd.department_id AS id, d.name FROM followed_departments fd
JOIN departments d ON d.id = fd.department_id
WHERE fd.user = ?
",["$thisuser"]);
$rws = $rl['data'];
$ayt = array();
foreach ($rws as $key => $rt) {
$usr = $genClass->getUser();
$userx = $usr['email'];
if(isset($_SESSION['senseiMentor']) || isset($_SESSION['senseiUser'])){
$rt['is_followed'] = $this->isDepartmentFollowed($rt['id'],$userx);
}
$ayt[] = $rt;
}


return $ayt;
}//listUserPublicDepartments

public function collateUsersMentors($asker_email){
$dbConn = new DbConn();
$sqq = $dbConn->getRows("SELECT mentor FROM mentor_follow WHERE user = ?",["$asker_email"]);
$rew = $sqq['data'];
$art = array();
foreach ($rew as $ky => $rt) {
$art[] = $rt['mentor'];
}
return $art;
}



public function collateMentorsFollowers($ans_email){
$dbConn = new DbConn();
$sqq = $dbConn->getRows("SELECT user FROM mentor_follow WHERE mentor = ?",["$ans_email"]);
$rew = $sqq['data'];
$art = array();
foreach ($rew as $ky => $rt) {
$art[] = $rt['user'];
}
return $art;  
}

public function collateUsersInCategory($department_id){
$dbConn = new DbConn();
$sqz = $dbConn->getRows("SELECT user FROM followed_departments WHERE department_id = ?",["$department_id"]);
$rez = $sqz['data'];
$arz = array();
foreach ($rez as $ky => $rt) {
$arz[] = $rt['user'];
}
return $arz;
}




public function getDepartmentDetails($url){
$dbConn = new DbConn();
$sql = "SELECT d.id, d.id AS did, d.description, d.name, d.url, i.name AS school, 
i.id AS school_id FROM departments d 
JOIN institutions i ON  i.id = d.school_id
WHERE d.url = ?";
$qre = $dbConn->getRow($sql,["$url"]);
$rw  =  $qre['data'];
$pcount = count($rw);
///
$did = $rw['did'];
$fdr = $dbConn->getRows("SELECT id FROM followed_departments WHERE department_id = ?",["$did"]);
$rwd = $fdr['data'];

$rw['dept_follows'] = count($rwd);
//
$fdt = $dbConn->getRow("SELECT
  COUNT(DISTINCT c.id) AS answer_num,
  COUNT(DISTINCT q.id) AS question_num
FROM questions q
LEFT JOIN comments c ON q.id = c.question_id 
WHERE q.department_id = ? AND c.answer_id = 0
",["$did"]);
$rwt = $fdt['data'];

$rw['question_num'] = $rwt['question_num'];
$rw['answer_num'] = $rwt['answer_num'];


if(count($pcount)>0){
return $rw;
}else{
return  array();
}
}//getDepartmentDetails





}///SocialClass

?>