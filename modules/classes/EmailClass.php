<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';




class EmailClass{
//public static $mail; 
function __construct($sendgridApi=false) {
//global $mail;
$mail = new PHPMailer;
$genClass = new GeneralClass();
$settings = $genClass->getSettings();
$this->smtpUser = $settings['smtp_user'];
$this->smtpPassword = $settings['smtp_password'];
$this->sendgridApiKey = $settings['sendgrid_api_key'];
$this->mail = new PHPMailer;
}

public function sendGridAttachment($senderemail,$sendername,$to,$subject,$message,$type='blue', $files = array()){
$sendgridApi = $this->sendgridApiKey;
$data  = array();
$data['personalizations'][0]['to'][0] = array('email' => $destination_email, 'name' => '');
$data['personalizations'][0]['subject'] = $subject;
$data['content'][0] = array('type' => 'text/plain', 'value' => '__');
$data['content'][1] = array('type' => 'text/html', 'value' => $message);
$data['from'] = array('email' => $user_email, 'name' => '');
//$data['cc'] = array('email' => $user_email, 'name' => '');
$data['attachments'] = array();
for($i = 0; $i < count($files); $i++){
if(is_file($files[$i])){
$fp =    @fopen($files[$i],"rb");
$xdata =    @fread($fp,filesize($files[$i]));
@fclose($fp);
$xdata = chunk_split(base64_encode($xdata));
$pld = $xdata;
$flt = pathinfo($files[$i]);

$data['attachments'][$i]['content'] = $pld;
$data['attachments'][$i]['content_id'] = rand().time();
$data['attachments'][$i]['disposition'] = 'inline';
$data['attachments'][$i]['filename'] = $flt['basename'];
$data['attachments'][$i]['name'] = $flt['filename'];
$data['attachments'][$i]['type'] = $flt['extension'];
}//is_file
}

//
$curl = curl_init();

//////
curl_setopt($curl, CURLOPT_URL, "https://api.sendgrid.com/v3/mail/send");
curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($curl, CURLOPT_ENCODING, "");    
curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
curl_setopt($curl, CURLOPT_TIMEOUT, 30);
curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($curl, CURLOPT_POST, TRUE);
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($curl, CURLOPT_HTTPHEADER, array(
    "authorization: Bearer ".$sendgridApi."" ,
    "content-type: application/json"
  ));
    //
$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$responseBody = json_decode($response,true);
$err = curl_error($curl);

if(count($files) > 0){
if(is_file($files[0])) {
  


if ($err) {
return false;
} else {
return true;
}


}else{return false;   }   
}else{ return false;};

exit();
//
//curl_close($curl);
//echo 'Response:: ';
//var_dump($response);
//echo 'Error:: ';
//var_dump($err);
//echo 'httpCode:: ';
//var_dump($httpCode);

return $response;
exit();

//
if ($err) {
$rr = array(
    'httpCode' => $httpCode,
    'response' => $responseBody['requestError']['serviceException']['text'], 
    'message' => $err);
$final =  false; 
} else {
$rr = array(
    'httpCode' => $httpCode,
    'response' => $responseBody['requestError']['serviceException']['text']);
$final =  true;   
}


return $final;

//exit();
}//






public function sendPlain($senderemail,$sendername,$to,$subject,$message,$type='blue',$toName='you') {

$messageHtml = $this->dressHtml($message,$subject,$type);

$this->mail->IsSMTP();                           // telling the class to use SMTP
$this->mail->SMTPAuth   = true;                  // enable SMTP authentication
$this->mail->Host       = "mail.sln.ng"; // set the SMTP server
$this->mail->Port       = 25;                    // set the SMTP port
$this->mail->Username   = $this->smtpUser ; // SMTP account username
$this->mail->Password   = $this->smtpPassword ;        // SMTP account password
/**/
$this->mail->setFrom($senderemail, $sendername);
$this->mail->addReplyTo($senderemail, $sendername);
//To address and name
$this->mail->addAddress($to, $toName);

//Send HTML or Plain Text email
$this->mail->isHTML(true);

$this->mail->Subject = $subject;
$this->mail->Body = $messageHtml;

if(!$this->mail->send()) 
{
   $status = "Mailer Error: " . $this->mail->ErrorInfo;
} 
else 
{
    $status = "Message has been sent successfully to ".$to;
}
//$status =  mail($to, $subject, $messageHtml, $headers);

return $status;

}//sendPlain


public  function sendAttachment(
    $sendermail,
    $sendername,
    $to,
    $subject,
    $message,
    $files,
    $type='blue'){
    //Wrap message in table
//$mess = dressMessage($message,$subject);
$mess = $this->dressHtml($message,$subject,$type);
     // email fields: to, from, subject, and so on
     $from = " <".$sendermail.">"; 
     //$subject = date("d.M H:i")." F=".count($files); 
     //$message = date("Y.m.d H:i:s")."\n".count($files)." attachments";
     $headers = "From: $from";
     //$headers  = 'MIME-Version: 1.0' . "\r\n";
//$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
//$headers .= 'To: <'.$to.'>' . "\r\n";
//$headers = 'From: '.$sendername.' <'.$sendermail.'>' . "\r\n";
  
     // boundary 
     $semi_rand = md5(time()); 
     $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x"; 
  
     // headers for attachment 
     $headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\""; 
  
     // multipart boundary 
     $message = "--{$mime_boundary}\n" . "Content-Type: text/html; charset=\"iso-8859-1\"\n" .
     "Content-Transfer-Encoding: 7bit\n\n" . $mess . "\n\n"; 
  
     // preparing attachments
     for($i=0;$i<count($files);$i++){
         if(is_file($files[$i])){
             $message .= "--{$mime_boundary}\n";
             $fp =    @fopen($files[$i],"rb");
         $data =    @fread($fp,filesize($files[$i]));
                     @fclose($fp);
             $data = chunk_split(base64_encode($data));
             $message .= "Content-Type: application/octet-stream; name=\"".basename($files[$i])."\"\n" . 
             "Content-Description: ".basename($files[$i])."\n" .
             "Content-Disposition: attachment;\n" . " filename=\"".basename($files[$i])."\"; size=".filesize($files[$i]).";\n" . 
             "Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";
             }
         }
     $message .= "--{$mime_boundary}--";
     $returnpath = "-f" . $sendermail;
     $ok = @mail($to, $subject, $message, $headers, $returnpath); 
     //if($ok){ return $i; } else { return 0; }
     return $ok;
     }



public function sendReceipt($ref){
$dbConn = new DbConn();
$sql = "SELECT  p.*, p.status as pay_status, 
u.firstname, u.surname
FROM payments p
LEFT JOIN users u ON p.user = u.email
 WHERE t.reference = ?";
$qr = $dbConn->getRow($sql,["$ref"]);
if($qr['code']==200 && $qr['data']!==false){
$rw = $qr['data'];
$rw['toName'] = $rw['firstname'].' '.$rw['surname'];
$rw['to'] = $rw['user'];
if($rw['pay_status'] == '1'){$type = 'blue';}
elseif($rw['pay_status'] == '-1'){
$type = 'red';
}
$senda = $this->sendTxnReceipt($rw,$type);
}else{
$senda = array('status'=>'Email was not sent');  
}
return $senda;

}



    
public function sendTxnReceipt($data,$type='blue') {

$messageHtml = $this->dressReceipt($data,$type);
$to = $data['to'];

$this->mail->IsSMTP();                           // telling the class to use SMTP
$this->mail->SMTPAuth   = true;                  // enable SMTP authentication
$this->mail->Host       = "mail.sln.ng"; // set the SMTP server
$this->mail->Port       = 25;                    // set the SMTP port
$this->mail->Username   = $this->smtpUser ; // SMTP account username
$this->mail->Password   = $this->smtpPassword ;        // SMTP account password
/**/
$this->mail->setFrom('payments@sln.ng', 'SLN Payments');
$this->mail->addReplyTo('payments@sln.ng', 'SLN Payments');
//To address and name
$this->mail->addAddress($data['to'], $data['toName']);

//Send HTML or Plain Text email
$this->mail->isHTML(true);

$this->mail->Subject = 'Transaction Receipt';
$this->mail->Body = $messageHtml;

if(!$this->mail->send()) 
{
   $status = "Mailer Error: " . $this->mail->ErrorInfo;
} 
else 
{
    $status = "Message has been sent successfully to ".$to;
}
//$status =  mail($to, $subject, $messageHtml, $headers);

return $status;

}//sendPlain




 public function dressHtml($message, $subject, $type){
$genClass = new GeneralClass();
$settings = $genClass->getSettings();
$tbl_attr = array();
$tbl_attr['defaults'] = '  border="0" cellpadding="0" cellspacing="0" ';
$tbl_attr['style'] = ' border-spacing:0;border-collapse:collapse;vertical-align:top;margin:0 auto;text-align:inherit; ';
$tbl_attr['tr_inner_tr'] = ' border-bottom: 1px solid #dddddd; padding:10px 10px 10px 10px; ';
$tbl_attr['tr_inner_td'] = ' padding:18px 8px; ';
if($type == 'red'){
$status_color = ' #db0a5b ';
}elseif ($type == 'blue') {
$status_color =  ' #2574a9 ';
}elseif ($type == 'plain') {
 $status_color =  ' #bdc3c7 ';
}else{
 $status_color =  ' #2574a9 ';//defaults to blue   
}
$logoLink =$settings['logo_url'];
$serverLink = $settings['base_url'];
 // 
$mBody = '<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:100,200,300,400,500,600,700,800,900" type="text/css">
<title></title>
 <style type="text/css">
    body, html{
  color: #535b61;
  font-family: "Poppins", sans-serif;
      background: #e7e9ed;
  font-size: 0.98rem;
  line-height: 22px;
    }
    .txt-h4{
      font-size: 25px;
      font-weight: 700;
      line-height: 1.4;
    }
    .txt-white{
      color: #ffffff;
    }
    .message-text{
        font-weight:500;
        font-size:1.1rem;
        color:#454545;
    }

    a.btn-email{
        display:inline-block !important;
        padding-top:12px;
        padding-bottom:12px;
        padding-left: 35px;
        padding-right: 35px;
        margin-top:16px;
        margin-bottom:16px;
        border-radius:27px;
        -webkit-border-radius:27px;
        -moz-border-radius:27px;
        color:#ffffff;
        text-decoration:none;
        font-weight:600;
        background-color: '.$status_color.';
    }
    .py10{ padding-top: 10px; padding-bottom: 10px; }
    .bold{ font-weight: 700; }
  </style>
</head>
<body>

<div style="height: 100%; padding: 1px 0 40px 0;">
<div style=" text-align: center; padding:2px; margin-top: 40px;">
  <span><a href="'.$serverLink.'"><img src="'.$serverLink.$logoLink.'" height="50"></a></span>
</div>
<table '.$tbl_attr['defaults'].' width="100%" style="'.$tbl_attr['style'].' width: 100%; text-align: inherit;">
<tr>
  <td>
 <div style="padding: 20px 0;"> 
<table '.$tbl_attr['defaults'].' width="504px" style="'.$tbl_attr['style'].' width: auto; text-align: inherit; border:1px solid '.$status_color.';">
<tr><td>   
<table '.$tbl_attr['defaults'].' width="100%"  style="border-spacing:0;border-collapse:collapse;vertical-align:top;max-width:500px;min-width:100%;margin:0 auto;text-align:inherit">
<tr>
  <td> 
<div style="background:'.$status_color.'; text-align: center; text-transform: uppercase; padding: 40px 30px; color: white;">
<!--<div class="py10 txt-white ">-</div> -->
<span class="txt-h4 txt-white bold">'.$subject.'</span>  
<!--<div>-</div>-->
</div> 
</td>
</tr>

<tr>
<td>
<div style="padding: 20px 30px; background: #ffffff;"> 
 <p class="message-text">

'.$message.'

</p>
<div style="padding: 10px 0; text-align: ; line-height: 1.7;">
 
  Thanks & Regards,<br>
<strong>'.$settings['sitename'].'</strong>
</div><!--summary-->


</div> <!--innner-->
</td>
</tr>

<tr>
  <td>
<div style="padding: 10px 10px 10px 10px; text-align: center; line-height: 1.6; background: #e7e9eb; font-size: 12px; color: #666666;"> 
Please do not reply to this email. <br>Emails sent to this address will not be answered. 
<br>
Copyright &copy; '.date("Y").'. '.$settings['copyright_info'].'
</div>
  </td>
</tr>

</table>
</td>
</tr>
</table>

</div>
  </td>
</tr>
</table>

</div>

</body>
</html>';



return $mBody;
exit();


/**/
}





 public  function dressReceipt($data, $type){

$genClass = new GeneralClass();
$settings = $genClass->getSettings();
$tbl_attr = array();
$tbl_attr['defaults'] = '  border="0" cellpadding="0" cellspacing="0" ';
$tbl_attr['style'] = ' border-spacing:0;border-collapse:collapse;vertical-align:top;margin:0 auto;text-align:inherit; ';
$tbl_attr['tr_inner_tr'] = ' border-bottom: 1px solid #dddddd; padding:10px 10px 10px 10px; ';
$tbl_attr['tr_inner_td'] = ' padding:18px 8px; ';
if($type == 'red'){
$status_color = ' #db0a5b ';
}elseif ($type == 'blue') {
$status_color =  ' #2574a9 ';
}elseif ($type == 'plain') {
 $status_color =  ' #bdc3c7 ';
}else{
 $status_color =  ' #2574a9 ';//defaults to blue   
}
$logoLink =$settings['logo_url'];
$serverLink = $settings['base_url'];
$footer_message = 'If you have any issues with this transaction, kindly  send an email to payments@sensei.com';

$mBody = '<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:100,200,300,400,500,600,700,800,900" type="text/css">
<title></title>
  <style type="text/css">
    body, html{
  color: #535b61;
  font-family: "Poppins", sans-serif;
      background: #fefefe;
  font-size: 0.98rem;
  line-height: 22px;
    }
    .txt-h4{
      font-size: 25px;
      font-weight: 700;
      line-height: 1.4;
    }
    .txt-h5{
      font-size: 22px;
      font-weight: 700;
      line-height: 1.2;
    }
    .txt-h4{
      font-size: 19px;
      font-weight: 700;
      line-height: 1.05;
    }
    .txt-white{
      color: #ffffff;
    }
    .message-text{
        font-weight:400;
        font-size:1.1rem;
        color:#454545;
    }
    .footer-text{
    font-weight:500;
        font-size:0.85rem;
        color:#000;  
    }
    a.btn-email{
        display:inline-block !important;
        padding-top:12px;
        padding-bottom:12px;
        padding-left: 35px;
        padding-right: 35px;
        margin-top:16px;
        margin-bottom:16px;
        border-radius:27px;
        -webkit-border-radius:27px;
        -moz-border-radius:27px;
        color:#ffffff;
        text-decoration:none;
        font-weight:600;
        background-color: '.$status_color.';
    }
    .py10{ padding-top: 10px; padding-bottom: 10px; }
    .bold{ font-weight: 700; }
    .pd20{
      padding: 10px 20px;
    }
    .pd10{
      padding: 10px;
    }
    .txt-right{
      text-align:right
    }
    .txt-left{
      text-align:left
    }
    .txt-center{
      text-align: center;
    }
  </style>
</head>
<body>

<div style="height: 100%; padding: 1px 0 40px 0;">
<div style=" text-align: center; padding:2px; margin-top: 40px;">
  <span><a href="'.$serverLink.'"><img src="'.$serverLink.$logoLink.'" height="50"></a></span>
</div>
<table '.$tbl_attr['defaults'].' width="100%" style="'.$tbl_attr['style'].' width: 100%; text-align: inherit;">
<tr>
  <td>
 <div style="padding: 20px 0;"> 
<table '.$tbl_attr['defaults'].' width="504px" style="'.$tbl_attr['style'].' width: auto; text-align: inherit; border:1px solid '.$status_color.';">
<tr><td>   
<table '.$tbl_attr['defaults'].' width="100%"  style="border-spacing:0;border-collapse:collapse;vertical-align:top;max-width:500px;min-width:100%;margin:0 auto;text-align:inherit">
<tr>
  <td colspan="2"> 
<div style="background:'.$status_color.'; text-align: center; text-transform: uppercase; padding: 40px 30px; color: white;">
<div class="py10 txt-white "> RECEIPT</div> <!---->
<span class="txt-h5 txt-white bold">'.$data['name'].'</span>  
<!--<div>-</div>-->
</div> 
</td>
</tr>

<tr>
<td class="pd20 txt-h5 txt-center bold" colspan="2">Transaction Details</td>
</tr>


<tr>
<td class="pd20 bold">Amount</td>
<td class="pd20 txt-right">NGN '.floatval($data['amount']).'</td>
</tr>



<tr>
<td class="pd20 bold">Date</td>
<td class="pd20 txt-right">'.date("d/m/Y",$data['tdate']).'</td>
</tr>



<tr>
<td class="pd20 bold">Mode of Payment</td>
<td class="pd20 txt-right">'.ucfirst($data['paymode']).'</td>
</tr>



<tr>
<td class="pd20 bold">Vendor</td>
<td class="pd20 txt-right">'.ucfirst($data['payvendor']).'</td>
</tr>




<tr>
<td class="pd20 bold">Reference</td>
<td class="pd20 txt-right">'.$data['ref'].'</td>
</tr>





<tr>
<td class="pd20 bold">Payment Status</td>
<td class="pd20 txt-right">'.$this->doStatus($data['pay_status'],'pay').'</td>
</tr>



<tr>
<td colspan="2" class="txt-center">
<div style="padding: 5px 20px; text-align:center; background: #fafafa;"> 
 <p class="footer-text">

'.$footer_message.'

</p>
<div style="padding: 10px 0; text-align: ; line-height: 1.7;">
 
  Thanks & Regards,<br>
<strong>'.$settings['sitename'].'</strong>
</div><!--summary-->


</div> <!--innner-->
</td>
</tr>

<tr>
  <td class="txt-center" colspan="2">
<div style="padding: 10px 10px 10px 10px; text-align: center; line-height: 1.6; background: #e7e9eb; font-size: 12px; color: #666666;"> 
Please do not reply to this email. <br>Emails sent to this address will not be answered. 
<br>
Copyright &copy; '.date("Y").'. '.$settings['copyright_info'].'
</div><!---->
  </td>
</tr>

</table>
</td>
</tr>
</table>

</div>
  </td>
</tr>
</table>

</div>

</body>
</html>';



return $mBody;
exit();


/**/
}//dressReceipt

    

public function doStatus($status,$mode){
if($status == '1' && $mode == 'pay'){
$state = 'Successful';
}elseif ($status == '-1' && $mode == 'pay') {
$state = 'Failed';
}elseif ($status == '-2' && $mode == 'pay') {
$state = 'Abandoned';
}elseif ($status == '1' && $mode == 'txn') {
$state = 'Completed';
}elseif ($status == '0' && $mode == 'txn') {
$state = 'Uncompleted';
}
return $state;
exit();

}

}//EmailClass

?>