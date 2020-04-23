<?php
function send_mail($recipient_email, $cc, $sender_name, $subject, $message, $rootpath = null, $filename = null){
	//Send E-Mail With Attachment	
	$from_email 	= 'noreply@dbtbharat.gov.in';
	$reply_to_email = 'noreply@dbtbharat.gov.in';
	$cc = 'feedback@dbtbharat.gov.in,bk.pujari@nic.in,'.$cc;
	if($filename){
		//Get uploaded file data
		$handle = fopen($rootpath.$filename, "r");
		$content = fread($handle, 2000000);
		fclose($handle);
		$encoded_content = chunk_split(base64_encode($content));
	}

	$boundary = md5("sanwebe");
	//header
	$headers = "MIME-Version: 1.0\r\n";
	$headers .= "From: ".$sender_name." <".$from_email."> \r\n";
	$headers .= "CC: ".$cc."\r\n";
	$headers .= "Reply-To: ".$reply_to_email."" . "\r\n";
	$headers .= "Content-Type: multipart/mixed; boundary = $boundary\r\n\r\n";
   
	//plain text
	$body = "--$boundary\r\n";
	$body .= "Content-Type: text/html; charset=utf-8\r\n";
	$body .= "Content-Transfer-Encoding: base64\r\n\r\n";
	$body .= chunk_split(base64_encode($message));
   
	if($filename){
		// attachment
		$body .= "--$boundary\r\n";
		$body .="Content-Type: txt; name=".$filename."\r\n";
		$body .="Content-Disposition: attachment; filename=".$filename."\r\n";
		$body .="Content-Transfer-Encoding: base64\r\n";
		$body .="X-Attachment-Id: ".rand(1000,99999)."\r\n\r\n";
		$body .= $encoded_content;
	}
	$sentMail = @mail($recipient_email,$subject, $body, $headers);
	return $sentMail;
}
