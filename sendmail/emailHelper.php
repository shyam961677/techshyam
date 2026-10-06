<?php

{
require 'PHPMailer-master/PHPMailerAutoload.php';

function sendMail($to,$message, $type)
{
	$mail = new PHPMailer;
	//Enable SMTP debugging. 
	// $mail->SMTPDebug = 2;                               
	//Set PHPMailer to use SMTP.
        // $mail->IsMail();
	$mail->IsSMTP();            
	//Set SMTP host name                          
	$mail->Host = "smtp.gmail.com";
	 //$mail->Host = "secure.emailsrvr.com";

	//Set this to true if SMTP host requires authentication to send email
	$mail->SMTPAuth = true;                       
	//Provide username and password
	$mail->Username =  "shyammilan002@gmail.com";
	$mail->Password =  "udrmdtnszjdbqpvy";
	//If SMTP requires TLS encryption then set it
	$mail->SMTPSecure = "SSL";                     
	// $mail->SMTPSecure = "TLS";                     
	//Set TCP port to connect to 
	$mail->Port = 587;
	// $mail->Port = 465;
	$mail->From = $to;
	$mail->FromName = "Welcome To My PortFolio";

	$mail->addAddress("shyammilan002@gmail.com", "Welcome To My PortFolio");

	$mail->isHTML(true);

	$mail->Subject = $type;
	$mail->Body = $message;
	$mail->AltBody = "This is the plain text version of the email content";

	if(!$mail->send()){
		return false;
	} 
	else{
	    return true;
    
	}
} 



function responseMail($to,$name)
{
	$mail = new PHPMailer;
	
	$mail->IsSMTP();            
	//Set SMTP host name                          
	$mail->Host = "smtp.gmail.com";
	 //$mail->Host = "secure.emailsrvr.com";

	//Set this to true if SMTP host requires authentication to send email
	$mail->SMTPAuth = true;                       
	//Provide username and password
	$mail->Username =  "shyammilan002@gmail.com";
	$mail->Password =  "udrmdtnszjdbqpvy";
	//If SMTP requires TLS encryption then set it
	$mail->SMTPSecure = "SSL";                     
	// $mail->SMTPSecure = "TLS";                     
	//Set TCP port to connect to 
	$mail->Port = 587;
	// $mail->Port = 465;
	$mail->From = "shyammilan002@gmail.com";
	$mail->FromName = "Welcome To My PortFolio";

	$mail->addAddress($to, "Welcome To My PortFolio");
	// $mail->addAddress($to, "Welcome To My PortFolio");

	$mail->isHTML(true);

	$mail->Subject = 'Thanks for visiting my PortFolio ';
	$mail->Body = 'Hii ' . $name . ',<br> &nbsp;&nbsp;&nbsp; Thanks for visiting my Portfolio. I am very grateful for your time. Thank you for such a wonderful contribution. Thank you for taking the time.<br> Admin will be connect soon';
	$mail->AltBody = "This is the plain text version of the email content";

	if(!$mail->send()){
		return false;
	} 
	else{
	    return true;
    
	}
} 


}



?>