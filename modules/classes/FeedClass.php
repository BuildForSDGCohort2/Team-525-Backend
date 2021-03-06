<?php

class FeedClass {

/*

public function saveComment($data){
$dbConn = new DbConn();
$genClass = new GeneralClass();
$usr = $genClass->getUser();
$thisuser = $usr['email'];
$now = time();
$cload = array(
'comment' => $data['new_comment'],
'question_id' => $data['question_id'],
'author_id' => $usr['uid'],
'create_date' => $now,
'answer_id' => $data['id']
);
$rt = $dbConn->insertDb($cload,'comments');
$arr = array();
if($rt['code']==200){
$arr['data'] = $this->getComment($rt['lastInsertId'],false);
$arr['message'] = $rt['message'];
$arr['state'] = '1';
}else{ 
$arr['message'] = 'failed'; 
$arr['state'] = '0';
}
return $arr;
}//saveComment



public function saveAnswer($data){
$dbConn = new DbConn();
$genClass = new GeneralClass();
$usr = $genClass->getUser();
$thisuser = $usr['email'];
$now = time();
$cload = array(
'comment' => $data['new_answer'],
'question_id' => $data['question_id'],
'author_id' => $usr['uid'],
'create_date' => $now,
'answer_id' => 0
);
$rt = $dbConn->insertDb($cload,'comments');
$arr = array();
if($rt['code']==200){
$arr['data'] = $this->getAnswer($rt['lastInsertId'],false);
$arr['message'] = $rt['message'];
$arr['state'] = '0';
}else{ 
$arr['message'] = 'failed'; 
$arr['state'] = '0';
}
return $arr;
}//saveAnswer

*/




public function getArticle($qId,$author=false){

$dbConn = new DbConn();
$genClass = new GeneralClass();
if($author !== false){
$usr = $genClass->getUser();
$thisuser = $usr['email'];
}
$ucols = $genClass->users_cols;
$sdc = $genClass->sqlPart($ucols,'u'); 
$a_comm = " ( SELECT Count(*) FROM article_comments WHERE article_id = ? ) 
    as com_num ";
$a_likes = " ( SELECT Count(*) FROM article_likes WHERE article_id = ? ) 
    as total_likes ";
$a_saves = " ( SELECT Count(*) FROM article_saves WHERE article_id = ? ) 
    as total_savings";
$a_file = " ( SELECT Count(*) FROM article_files WHERE article_id = ? ) 
    as total_files ";
$a_pur = " ( SELECT Count(*) FROM article_purchases WHERE article_id = ? ) 
    as total_purchases ";
$a_rate_sum = " ( SELECT sum(rate) FROM article_ratings WHERE article_id = ? ) 
    as rate_total_sum ";
$a_rate_count = " ( SELECT Count(*) FROM article_ratings WHERE article_id = ? ) 
    as rate_total_num ";
$sql =   "SELECT a.*, $a_likes, $a_saves, $sdc, $a_comm, $a_file, $a_pur, $a_rate_sum, $a_rate_count
     FROM articles a
LEFT JOIN users u ON u.email = a.mentor_id
WHERE a.id = ? AND a.mode = ? ";
$cols =["$qId","$qId","$qId","$qId","$qId","$qId","$qId","$qId","blog"];

$qt = $dbConn->getRow($sql,$cols);


if($qt['code'] ==200 && $qt['data'] !==false){
$rw = $qt['data'];
$rw['content'] = html_entity_decode($rw['content']);
$rw['author_name'] = $rw['firstname'].' '.$rw['surname'];
$rw['average_rating'] = ($rw['rate_total_sum'] > 0) ? $rw['rate_total_sum']/$rw['rate_total_num'] : 0;
$rw['author_url'] = (!empty($rw['username'])) ? 'profile/'.$rw['username'] : '';
$rw['comment_list'] = array();
$rw['fn_name'] = 'getArticle';
$rw['is_'.$rw['mode']] = true;
}else{
$rw = false;
}



return $rw;
}//getArticle



public function getShared($fid){

$dbConn = new DbConn();
$genClass = new GeneralClass();
$articleClass = new ArticlesClass();
$qaClass = new QaClass();
$usr = $genClass->getUser();
$thisuser = $usr['email'];

$sqlxi = "SELECT * FROM all_shares WHERE id = ?";
$gsh = $dbConn->getRow($sqlxi,["$fid"]);
if($gsh['code'] ===200 && $gsh['data'] !== false){
$rda = $gsh['data'];
$usx = $genClass->getUserFromEmail($rda['user']);
$sharer_name = $usx['firstname'].' '.$usx['surname'];
$sharer_url = 'profile/'.$usx['username'];
$share_date = $rda['sdate'];
if($rda['content_type'] === 'article'){
$sqa = "SELECT * FROM articles  WHERE id = ?";
$aid = $rda['content_id'];
$qt = $dbConn->getRow($sqa,["$aid"]);
$rd = $qt['data'];
$rw = $articleClass->getArticleById($rd['id']);
if($rw !==false){
$mda = 'is_shared_'.$rd['mode'];
$rw[$mda] = true;
//$rw['is_article'] = true;
}
}elseif($rda['content_type'] === 'topic'){
$sqa = "SELECT * FROM questions  WHERE id = ?";
$aid = $rda['content_id'];
$qt = $dbConn->getRow($sqa,["$aid"]);
$rd = $qt['data'];
$rw = $qaClass->getQuestion($rd['id']);
if($rw !==false){
$rw['is_shared_topic'] = true;
}
}elseif($rda['content_type'] === 'answer'){
$sqa = "SELECT * FROM comments  WHERE id = ?";
$aid = $rda['content_id'];
$qt = $dbConn->getRow($sqa,["$aid"]);
$rd = $qt['data'];
$rw = $qaClass->getAnswer($rd['id']);
if($rw !==false){
$rw['is_shared_answer'] = true;
}
}else{
$rw = false;
}

$rw['sharer_name'] = $sharer_name;
$rw['sharer_url'] = $sharer_url;
$rw['is_shared'] = true;
	
}else{
$rw = [];
}




return $rw;

}//getShared


}//FeedClass


?>