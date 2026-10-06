<?php

{
	require 'PHPMailer-master/PHPMailerAutoload.php';
	function sendMail($to,$message)
	{
		$mail = new PHPMailer;



//Enable SMTP debugging. 
		$mail->SMTPDebug = 2;                               
//Set PHPMailer to use SMTP.
		$mail->isSMTP();            
//Set SMTP host name                          
		$mail->Host = "smtp.gmail.com";
		/*$mail->Host = "secure.emailsrvr.com";*/

//Set this to true if SMTP host requires authentication to send email
		$mail->SMTPAuth = true;                          
//Provide username and password
		$mail->Username = "agarwalmayank30@gmail.com";                 
		$mail->Password = "280892@crush";
//If SMTP requires TLS encryption then set it
		$mail->SMTPSecure = "tls";                           
//Set TCP port to connect to 
		$mail->Port = 587;
		/*$mail->Port = 465; */
		$mail->From = "agarwalmayank30@gmail.com";
		$mail->FromName = "Mayank Agarwal";

		$mail->addAddress($to, "Arjit Agrawal");

		$mail->isHTML(true);

		$mail->Subject = "Subject Text";
		$mail->Body = "<i>".$message."</i>";
		$mail->AltBody = "This is the plain text version of the email content";

		if(!$mail->send()) 
		{
			echo "Mailer Error: " . $mail->ErrorInfo;
		} 
		else 
		{
			echo "Message has been sent successfully";
		}
	}

	sendMail("agarwalmayank555@gmail.com","Check mail");

}
?>