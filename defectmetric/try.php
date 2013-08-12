<?php
ini_set("SMTP","xchcasha.cisco.com" ); 
ini_set("sendmail_from","venvemur@cisco.com" ); 
$to      = 'venvemur@cisco.com';
$subject = 'HI';
$message = 'hello';
$headers = 'From: venvemur@cisco.com' . "\r\n" .
   'Reply-To: venvemur@cisco.com' . "\r\n" .
   'X-Mailer: PHP/' . phpversion();


if(mail($to, $subject, $message, $headers))
	echo "YAY";
else
	echo "NO";
?>