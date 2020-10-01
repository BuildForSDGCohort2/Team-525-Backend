<?php
//if (!isset($_SESSION)) {session_start();}
class ArticlesClass {

public function getArticles($owntype=false,$offset=false,$limit=false){
$dbConn = new dbConn();
$genClass = new GeneralClass();
$usr = $genClass->getUser();
$thisuser = $usr['email'];

if(isset($_SESSION['senseiUser'])){
$utype = 'user';
}elseif (isset($_SESSION['senseiMentor'])) {
$utype = 'mentor';
}




$lim = (isset($limit) && $limit !== false) ? $limit : 12; 

if($owntype == 'purchased'){
$rsp = (isset($offset) && $offset !== false) ? 
$dbConn->getRows("SELECT  py.status, py.id, py.ref, a.*, a.id as aid,  u.firstname, 
    u.surname, u.username, u.user_type, u.avatar, u.bio,
count(DISTINCT  ap.id) AS total_sales,
count(DISTINCT  asv.id) AS total_saves,
count(DISTINCT  aslk.id) AS total_likes,
count(DISTINCT  ac.id) AS total_comments,
count(DISTINCT  asl.id) AS total_shares,
CEILING((sum(DISTINCT ar.rate)/count(DISTINCT  ar.id))) AS total_ratings
FROM article_purchases ap 
JOIN articles a ON a.id = ap.article_id
LEFT JOIN article_ratings ar ON a.id = ar.article_id  
LEFT JOIN article_saves asv ON a.id = asv.article_id 
LEFT JOIN article_likes aslk ON a.id = aslk.article_id 
LEFT JOIN article_comments ac ON a.id = ac.article_id 
LEFT JOIN all_shares asl ON a.id = asl.content_id  AND asl.content_type LIKE ? 
LEFT JOIN payments py ON py.id = ap.payment_id
JOIN users u ON u.email = ap.user
WHERE ap.user = ?  
GROUP BY a.id
ORDER BY a.id desc  LIMIT ? OFFSET ?",["","$thisuser","$lim","$offset"])
:
$dbConn->getRows("SELECT  py.status, py.id, py.ref, a.*, a.id as aid,  u.firstname, 
    u.surname, u.username, u.user_type, u.avatar, u.bio,
count(DISTINCT  ap.id) AS total_sales,
count(DISTINCT  asv.id) AS total_saves,
count(DISTINCT  aslk.id) AS total_likes,
count(DISTINCT  ac.id) AS total_comments,
count(DISTINCT  asl.id) AS total_shares,
CEILING((sum(DISTINCT ar.rate)/count(DISTINCT  ar.id))) AS total_ratings
FROM article_purchases ap 
JOIN articles a ON a.id = ap.article_id
LEFT JOIN article_ratings ar ON a.id = ar.article_id  
LEFT JOIN article_saves asv ON a.id = asv.article_id 
LEFT JOIN article_likes aslk ON a.id = aslk.article_id 
LEFT JOIN article_comments ac ON a.id = ac.article_id 
LEFT JOIN all_shares asl ON a.id = asl.content_id  AND asl.content_type LIKE ? 
LEFT JOIN payments py ON py.id = ap.payment_id
JOIN users u ON u.email = ap.user
WHERE ap.user = ?  
GROUP BY a.id
ORDER BY a.id desc LIMIT ? ",["","$thisuser","$lim"])
;


}elseif($owntype == 'created' OR $owntype == false){
$rsp = (isset($offset) && $offset !== false) ? 
$dbConn->getRows("SELECT  a.*, a.id as aid,  u.firstname, 
    u.surname, u.username, u.avatar, u.bio,
count(DISTINCT  ap.id) AS total_sales,
count(DISTINCT  asv.id) AS total_saves,
count(DISTINCT  aslk.id) AS total_likes,
count(DISTINCT  ac.id) AS total_comments,
count(DISTINCT  asl.id) AS total_shares,
CEILING((sum(DISTINCT ar.rate)/count(DISTINCT  ar.id))) AS total_ratings
FROM articles a 
LEFT JOIN article_ratings ar ON a.id = ar.article_id 
LEFT JOIN article_purchases ap ON a.id = ap.article_id 
LEFT JOIN article_saves asv ON a.id = asv.article_id 
LEFT JOIN article_likes aslk ON a.id = aslk.article_id
LEFT JOIN article_comments ac ON a.id = ac.article_id 
LEFT JOIN all_shares asl ON a.id = asl.content_id  AND asl.content_type LIKE ? 
JOIN users u ON u.email = a.mentor_id
WHERE a.mentor_id = ?  
GROUP BY a.id
ORDER BY a.id desc  LIMIT ? OFFSET ?",["","$thisuser","$lim","$offset"])
:
$dbConn->getRows("SELECT  a.*, a.id as aid,  u.firstname, 
    u.surname, u.username, u.avatar,u.bio,
count(DISTINCT  ap.id) AS total_sales,
count(DISTINCT  asv.id) AS total_saves,
count(DISTINCT  ac.id) AS total_comments,
count(DISTINCT  aslk.id) AS total_likes,
count(DISTINCT  asl.id) AS total_shares,
CEILING((sum(DISTINCT ar.rate)/count(DISTINCT  ar.id))) AS total_ratings
FROM articles a 
LEFT JOIN article_ratings ar ON a.id = ar.article_id 
LEFT JOIN article_purchases ap ON a.id = ap.article_id 
LEFT JOIN article_saves asv ON a.id = asv.article_id 
LEFT JOIN article_likes aslk ON a.id = aslk.article_id 
LEFT JOIN article_comments ac ON a.id = ac.article_id 
LEFT JOIN all_shares asl ON a.id = asl.content_id  AND asl.content_type LIKE ? 
JOIN users u ON u.email = a.mentor_id
WHERE a.mentor_id = ?  
GROUP BY a.id
ORDER BY a.id desc LIMIT ? ",["","$thisuser","$lim"])
;
}


$rwv  =  $rsp['data'];
$isArt = count($rwv);
//WHERE a.mentor_id  = '$user'
$arr  =  array();
if($isArt > 0){
//do{
foreach ($rwv as $key => $rw) {
$rw['content'] = $rw['description'] = stripslashes(html_entity_decode($rw['content']));
$rw['total_sales'] = (int)$rw['total_sales'];
$rw['total_saves'] = (int)$rw['total_saves'];
$rw['total_shares'] = (int)$rw['total_shares'];
$rw['total_comments'] = (int)$rw['total_comments'];
$rw['total_likes'] = (int)$rw['total_likes'];
$rw['total_ratings'] = (int)$rw['total_ratings'];
$rw['bio'] = stripslashes(html_entity_decode($rw['bio'])) ;
$rw['author_name'] = $rw['firstname'].' '.$rw['surname'];
$rw['author_url'] = 'profile/'.$rw['username'];
$rw['has_bought'] = $this->hasBought($rw['aid']);
$rw['is_'.$rw['mode']] = true;

$ty = $dbConn->getRows("SELECT  af.url, af.name as filename, af.size, af.mime, af.adate AS create_date FROM  article_files af WHERE  af.article_id = ? order by af.id desc",["$rw[aid]"]);
$rx  =  $ty['data'];
$file_count = count($rx);
$rw['file_ccount'] = $file_count; 
$art = array();
foreach ($rx as $ky => $rxd) {
$art[] = $rxd; 
}//foreach
$rw['files'] = $art;
$arr[] = $rw;
}//foreach
}


return $arr;

}//getArticles


public function hasBought($aid){
$dbConn = new dbConn();
$genClass = new GeneralClass();

if(isset($_SESSION['senseiUser']) || isset($_SESSION['senseiMentor'])){
$usr = $genClass->getUser();
$thisuser = $usr['email'];
$rsp = $dbConn->getRows("SELECT * FROM article_purchases WHERE id =? AND user = ?",["$aid","$thisuser"]);
$rws = count($rsp['data']);
$rets = ($rws > 0) ? true:false;
}else{
$rets = false;
}
return $rets;

}//hasBought




public function getHomeArticle(){
$dbConn = new dbConn();
$genClass = new GeneralClass();
$tcr = $dbConn->getRows("SELECT  a.title, a.mode, a.create_date, a.url, a.content, u.firstname, u.surname, u.username,u.avatar
    FROM  articles a 
JOIN users u ON a.mentor_id = u.email
order by rand() LIMIT ? ",["6"]);
$rcr  =  $tcr['data'];
#
$arrr = array();
foreach ($rcr as $kx => $rxr) {
$rxr['comment'] = stripslashes(html_entity_decode($rxr['content']));
$rxr['author_url'] = 'profile/'.$rxr['username'];
$rxr['author_name'] = $rxr['firstname'].' '.$rxr['surname'];
$arrr[] = $rxr; 
}//foreach


return $arrr;

}//getHomeArticles

public function getPageArticles($offset=false,$limit=false){

$dbConn = new dbConn();
$genClass = new GeneralClass();
$lim = (isset($limit) && $limit !== false) ? $limit : 12; 

$tcr = (isset($offset) && $offset !== false) ? 
 $dbConn->getRows("SELECT  a.title, a.mode, a.create_date, a.url, a.content, u.firstname, u.surname, u.username,u.avatar
    FROM  articles a 
JOIN users u ON a.mentor_id = u.email
ORDER BY a.id DESC LIMIT ? OFFSET ?",["$lim","$offset"])
 :

 $dbConn->getRows("SELECT  a.title, a.mode, a.create_date, a.url, a.content, u.firstname, u.surname, u.username,u.avatar
    FROM  articles a 
JOIN users u ON a.mentor_id = u.email
ORDER BY a.id DESC LIMIT ?",["$lim"]);
$rcr  =  $tcr['data'];
#
$arrr = array();
foreach ($rcr as $kx => $rxr) {
$rxr['comment'] = stripslashes(html_entity_decode($rxr['content']));
$rxr['author_url'] = 'profile/'.$rxr['username'];
$rxr['author_name'] = $rxr['firstname'].' '.$rxr['surname'];
$arrr[] = $rxr; 
}//foreach


return $arrr;

}//getPageArticls

public function getThisArticle($url){

$dbConn = new dbConn();
$genClass = new GeneralClass();
$usr = $genClass->getUser();
$thisuser = $usr['email'];

if(isset($_SESSION['senseiUser'])){
$utype = 'user';
}elseif (isset($_SESSION['senseiMentor'])) {
$utype = 'mentor';
}


$rsp = $dbConn->getRow("SELECT  a.*, a.id as aid,  u.firstname, 
    u.surname, u.bio, u.username, u.avatar, u.bio,
count(DISTINCT  ap.id) AS total_sales,
count(DISTINCT  asv.id) AS total_saves,
count(DISTINCT  ac.id) AS total_comments,
count(DISTINCT  aslk.id) AS total_likes,
count(DISTINCT  asl.id) AS total_shares,
CEILING((sum(DISTINCT ar.rate)/count(DISTINCT  ar.id))) AS total_ratings
FROM articles a 
LEFT JOIN article_ratings ar ON a.id = ar.article_id 
LEFT JOIN article_purchases ap ON a.id = ap.article_id 
LEFT JOIN article_saves asv ON a.id = asv.article_id 
LEFT JOIN article_likes aslk ON a.id = aslk.article_id 
LEFT JOIN article_comments ac ON a.id = ac.article_id 
LEFT JOIN all_shares asl ON a.id = asl.content_id  AND asl.content_type LIKE ? 
JOIN users u ON u.email = a.mentor_id
WHERE a.url = ?  
GROUP BY a.id
ORDER BY a.id",["","$url"])
;


$rw  =  $rsp['data'];

$arr  =  array();
if($rw !== false){

$rw['content'] = $rw['description'] = stripslashes(html_entity_decode($rw['content']));
$rw['total_sales'] = (int)$rw['total_sales'];
$rw['total_saves'] = (int)$rw['total_saves'];
$rw['total_shares'] = (int)$rw['total_shares'];
$rw['total_comments'] = (int)$rw['total_comments'];
$rw['total_likes'] = (int)$rw['total_likes'];
$rw['total_ratings'] = (int)$rw['total_ratings'];
$rw['author_bio'] = stripslashes(html_entity_decode($rw['bio'])) ;
$rw['author_avatar'] = $rw['avatar'];
$rw['author_name'] = $rw['firstname'].' '.$rw['surname'];
$rw['author_url'] = 'profile/'.$rw['username'];
$rw['has_bought'] = $this->hasBought($rw['aid']);
$rw['is_'.$rw['mode']] = true;

#FILES
$ty = $dbConn->getRows("SELECT  af.url, af.name as filename, af.size, af.mime, af.adate AS create_date FROM  article_files af WHERE  af.article_id = ? order by af.id desc",["$rw[aid]"]);
$rx  =  $ty['data'];
#
$file_count = count($rx);
$rw['file_ccount'] = $file_count; 
$art = array();
foreach ($rx as $ky => $rxd) {
$art[] = $rxd; 
}//foreach
$rw['files'] = $art;
#========

#comments
$tcx = $dbConn->getRows("SELECT  ac.*, u.firstname, u.surname, u.username,u.avatar
    FROM  article_comments ac 
JOIN users u ON ac.user = u.email
WHERE  ac.article_id = ? order by ac.id desc",["$rw[aid]"]);
$rcx  =  $tcx['data'];
#
$arx = array();
foreach ($rcx as $kx => $rxc) {
$rxc['comment'] = stripslashes(html_entity_decode($rxc['comment']));
$rxc['author_url'] = 'profile/'.$rxc['username'];
$rxc['author_name'] = $rxc['firstname'].' '.$rxc['surname'];
$arx[] = $rxc; 
}//foreach
$rw['all_comments'] = $arx;
#========

$sfc = " ( SELECT Count(*) FROM article_files WHERE article_id = ? ) 
    as article_files ";

#related
$tcr = $dbConn->getRows("SELECT  a.title, a.url, a.content, u.firstname, u.surname, u.username,u.avatar
    FROM  articles a 
JOIN users u ON a.mentor_id = u.email
WHERE a.mode = ? order by rand() LIMIT ? ",["$rw[mode]","5"]);
$rcr  =  $tcr['data'];
#
$arrr = array();
foreach ($rcr as $kx => $rxr) {
$rxr['comment'] = stripslashes(html_entity_decode($rxr['content']));
$rxr['author_url'] = 'profile/'.$rxr['username'];
$rxr['author_name'] = $rxr['firstname'].' '.$rxr['surname'];
$arrr[] = $rxr; 
}//foreach
$rw['related_articles'] = $arrr;
#========

#more_from_author
$tcm = $dbConn->getRows("SELECT  a.title, a.url, a.content, u.firstname, u.surname, u.username,u.avatar
    FROM  articles a 
JOIN users u ON a.mentor_id = u.email
WHERE a.mentor_id = ? order by rand() LIMIT ? ",["$rw[mentor_id]","7"]);
$rcm  =  $tcm['data'];

#
$arm = array();
foreach ($rcm as $kx => $rxm) {
$rxm['comment'] = stripslashes(html_entity_decode($rxm['content']));
$rxm['author_url'] = 'profile/'.$rxm['username'];
$rxm['author_name'] = $rxm['firstname'].' '.$rxm['surname'];
$arm[] = $rxm; 
}//foreach
$rw['more_from_author'] = $arm;
#========
}else{
	$rw = false;
}



return $rw;

}//getThisArticle


public function getUserArticlesSales($limit=false, $offset=false){
$dbConn = new dbConn();
$genClass = new GeneralClass();
$usr = $genClass->getUser();
$thisuser = $usr['email'];
#
$lim = (isset($limit) && $limit !== false) ? $limit : 12; 
#
$rsp = $dbConn->getRows("SELECT  py.user AS paying_user, py.status, py.id, py.ref, py.amount AS sale_cost, py.pdate AS sale_date, a.*, a.id as aid,  u.firstname, 
    u.surname, u.username, u.user_type, u.avatar,
count(DISTINCT  ap.id) AS total_sales,
count(DISTINCT  asv.id) AS total_saves,
count(DISTINCT  aslk.id) AS total_likes,
count(DISTINCT  ac.id) AS total_comments,
count(DISTINCT  asl.id) AS total_shares,
CEILING((sum(DISTINCT ar.rate)/count(DISTINCT  ar.id))) AS total_ratings
FROM article_purchases ap 
JOIN articles a ON a.id = ap.article_id
LEFT JOIN article_ratings ar ON a.id = ar.article_id  
LEFT JOIN article_saves asv ON a.id = asv.article_id 
LEFT JOIN article_likes aslk ON a.id = aslk.article_id 
LEFT JOIN article_comments ac ON a.id = ac.article_id 
LEFT JOIN all_shares asl ON a.id = asl.content_id  AND asl.content_type LIKE ? 
LEFT JOIN payments py ON py.id = ap.payment_id
JOIN users u ON u.email = ap.user
WHERE ap.user = ?  
GROUP BY a.id
ORDER BY a.id desc LIMIT ? ",["","$thisuser","$lim"])
;

$rwv  =  $rsp['data'];
$isArt = count($rwv);
//WHERE a.mentor_id  = '$user'
$arr  =  array();
if($isArt > 0){
//do{
foreach ($rwv as $key => $rw) {
$rw['content'] = $rw['description'] = stripslashes(html_entity_decode($rw['content']));
$rw['sale_cost'] = floatval($rw['sale_cost']) ;
$rw['total_sales'] = (int)$rw['total_sales'];
$rw['total_saves'] = (int)$rw['total_saves'];
$rw['total_shares'] = (int)$rw['total_shares'];
$rw['total_comments'] = (int)$rw['total_comments'];
$rw['total_likes'] = (int)$rw['total_likes'];
$rw['total_ratings'] = (int)$rw['total_ratings'];
$rw['author_name'] = $rw['firstname'].' '.$rw['surname'];
$rw['author_url'] = 'profile/'.$rw['username'];
$rw['has_bought'] = $this->hasBought($rw['aid']);
$rw['is_'.$rw['mode']] = true;

$ty = $dbConn->getRows("SELECT  af.url, af.name as filename, af.size, af.mime, af.adate AS create_date FROM  article_files af WHERE  af.article_id = ? order by af.id desc",["$rw[aid]"]);
$rx  =  $ty['data'];
$file_count = count($rx);
$rw['file_ccount'] = $file_count; 
$art = array();
foreach ($rx as $ky => $rxd) {
$art[] = $rxd; 
}//foreach
$rw['files'] = $art;
$arr[] = $rw;
}//foreach
}



return $arr;

}//




public function getUserArticlesPurchases($limit=false, $offset=false){
$dbConn = new dbConn();
$genClass = new GeneralClass();
$usr = $genClass->getUser();
$thisuser = $usr['email'];
#
$lim = (isset($limit) && $limit !== false) ? $limit : 12; 
#
$rsp = $dbConn->getRows("SELECT  py.user AS paying_user, py.status, py.id, py.ref, py.amount AS purchase_cost, py.pdate AS purchase_date, a.*, a.id as aid,  u.firstname, 
    u.surname, u.username, u.user_type, u.avatar,
count(DISTINCT  ap.id) AS total_sales,
count(DISTINCT  asv.id) AS total_saves,
count(DISTINCT  aslk.id) AS total_likes,
count(DISTINCT  ac.id) AS total_comments,
count(DISTINCT  asl.id) AS total_shares,
CEILING((sum(DISTINCT ar.rate)/count(DISTINCT  ar.id))) AS total_ratings
FROM article_purchases ap 
JOIN articles a ON a.id = ap.article_id
LEFT JOIN article_ratings ar ON a.id = ar.article_id  
LEFT JOIN article_saves asv ON a.id = asv.article_id 
LEFT JOIN article_likes aslk ON a.id = aslk.article_id 
LEFT JOIN article_comments ac ON a.id = ac.article_id 
LEFT JOIN all_shares asl ON a.id = asl.content_id  AND asl.content_type LIKE ? 
LEFT JOIN payments py ON py.id = ap.payment_id
JOIN users u ON u.email = py.user
WHERE py.user = ?  
GROUP BY a.id
ORDER BY a.id desc LIMIT ? ",["","$thisuser","$lim"])
;

$rwv  =  $rsp['data'];
$isArt = count($rwv);
//WHERE a.mentor_id  = '$user'
$arr  =  array();
if($isArt > 0){
//do{
foreach ($rwv as $key => $rw) {
$rw['content'] = $rw['description'] = stripslashes(html_entity_decode($rw['content']));
$rw['purchase_cost'] = floatval( $rw['purchase_cost']);
$rw['total_sales'] = (int)$rw['total_sales'];
$rw['total_saves'] = (int)$rw['total_saves'];
$rw['total_shares'] = (int)$rw['total_shares'];
$rw['total_comments'] = (int)$rw['total_comments'];
$rw['total_likes'] = (int)$rw['total_likes'];
$rw['total_ratings'] = (int)$rw['total_ratings'];
$rw['author_name'] = $rw['firstname'].' '.$rw['surname'];
$rw['author_url'] = 'profile/'.$rw['username'];
$rw['has_bought'] = $this->hasBought($rw['aid']);
$rw['is_'.$rw['mode']] = true;

$ty = $dbConn->getRows("SELECT  af.url, af.name as filename, af.size, af.mime, af.adate AS create_date FROM  article_files af WHERE  af.article_id = ? order by af.id desc",["$rw[aid]"]);
$rx  =  $ty['data'];
$file_count = count($rx);
$rw['file_ccount'] = $file_count; 
$art = array();
foreach ($rx as $ky => $rxd) {
$art[] = $rxd; 
}//foreach
$rw['files'] = $art;
$arr[] = $rw;
}//foreach
}



return $arr;

}//


public function getPublicArticles($username,$userType,$offset=false,$limit=false){
$dbConn = new dbConn();
$genClass = new GeneralClass();
$usr = $genClass->getUserFromUsername($username);
$thisuser = $usr['email'];
$utype = $userType;

$lim = (isset($limit) && $limit !== false) ? $limit : 12; 

$rsp = (isset($offset) && $offset !== false) ? 
$dbConn->getRows("SELECT  a.*, a.id as aid,  u.firstname, 
    u.surname, u.username, u.avatar, u.bio,
count(DISTINCT  ap.id) AS total_sales,
count(DISTINCT  asv.id) AS total_saves,
count(DISTINCT  aslk.id) AS total_likes,
count(DISTINCT  ac.id) AS total_comments,
count(DISTINCT  asl.id) AS total_shares,
CEILING((sum(DISTINCT ar.rate)/count(DISTINCT  ar.id))) AS total_ratings
FROM articles a 
LEFT JOIN article_ratings ar ON a.id = ar.article_id 
LEFT JOIN article_purchases ap ON a.id = ap.article_id 
LEFT JOIN article_saves asv ON a.id = asv.article_id 
LEFT JOIN article_likes aslk ON a.id = aslk.article_id 
LEFT JOIN article_comments ac ON a.id = ac.article_id 
LEFT JOIN all_shares asl ON a.id = asl.content_id  AND asl.content_type = ? 
JOIN users u ON u.email = a.mentor_id
WHERE a.mentor_id = ?  
GROUP BY a.id
ORDER BY a.id desc  LIMIT ? OFFSET ?",["article","$thisuser","$lim","$offset"])
:
$dbConn->getRows("SELECT  a.*, a.id as aid,  u.firstname, 
    u.surname, u.username, u.avatar, u.bio,
count(DISTINCT  ap.id) AS total_sales,
count(DISTINCT  asv.id) AS total_saves,
count(DISTINCT  aslk.id) AS total_likes,
count(DISTINCT  ac.id) AS total_comments,
count(DISTINCT  asl.id) AS total_shares,
CEILING((sum(DISTINCT ar.rate)/count(DISTINCT  ar.id))) AS total_ratings
FROM articles a 
LEFT JOIN article_ratings ar ON a.id = ar.article_id 
LEFT JOIN article_purchases ap ON a.id = ap.article_id 
LEFT JOIN article_saves asv ON a.id = asv.article_id 
LEFT JOIN article_likes aslk ON a.id = aslk.article_id
LEFT JOIN article_comments ac ON a.id = ac.article_id 
LEFT JOIN all_shares asl ON a.id = asl.content_id  AND asl.content_type = ? 
JOIN users u ON u.email = a.mentor_id
WHERE a.mentor_id = ?  
GROUP BY a.id
ORDER BY a.id desc LIMIT ? ",["article","$thisuser","$lim"])
;



$rwv  =  $rsp['data'];
$isArt = count($rwv);
//WHERE a.mentor_id  = '$user'
$arr  =  array();
if($isArt > 0){
//do{
foreach ($rwv as $key => $rw) {
$rw['content'] = $rw['description'] = stripslashes(html_entity_decode($rw['content']));
$rw['total_sales'] = (int)$rw['total_sales'];
$rw['total_saves'] = (int)$rw['total_saves'];
$rw['total_likes'] = (int)$rw['total_likes'];
$rw['total_shares'] = (int)$rw['total_shares'];
$rw['total_comments'] = (int)$rw['total_comments'];
$rw['total_ratings'] = (int)$rw['total_ratings'];
$rw['author_name'] = $rw['firstname'].' '.$rw['surname'];
$rw['bio'] = stripslashes(html_entity_decode($rw['bio'])) ;
$rw['author_url'] = 'profile/'.$rw['username'];
$rw['has_bought'] = $this->hasBought($rw['aid']);
$rw['is_'.$rw['mode']] = true;
$ty = $dbConn->getRows("SELECT  af.url, af.name as filename, af.size, af.mime, af.adate AS create_date FROM  article_files af WHERE  af.article_id = ? order by af.id desc",["$rw[aid]"]);
$rx  =  $ty['data'];
$file_count = count($rx);
$rw['file_ccount'] = $file_count; 
$art = array();
foreach ($rx as $ky => $rxd) {
$art[] = $rxd; 
}//foreach
$rw['files'] = $art;
$arr[] = $rw;
}//foreach
}


return $arr;

}//getPublicArticles



}//vinClass

?>