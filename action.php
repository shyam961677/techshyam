<?php
	require_once ('sendmail/emailHelper.php');

	$name 		= $_POST['name'];
	$email 		= $_POST['email'];
	$phone 		= $_POST['phone'];
	$subject 	= $_POST['subject'];
	$msg 	= $_POST['message'];
	$message  ='<table border="1" cellpadding="10" cellspacing="0">
					<tr>
						<th>Name</th>
						<td>'.$name.'</td>
					</tr>
					<tr>
						<th>Email</th>
						<td>'.$email.'</td>
					</tr>
					<tr>
						<th>Phone Number</th>
						<td>'.$phone.'</td>
					</tr>
					<tr>
						<th>Subject</th>
						<td>'.$subject.'</td>
					</tr>
					<tr>
						<th>Message</th>
						<td>'.$msg.'</td>
					</tr>
				</table>';
	$mail = sendMail($email,$message,$subject);
	if (!empty($mail)) {
		$res  = responseMail($email,$name);
		if (!empty($res)) {
			$data = [
				'status'=>'success',
				'message'=>'success',
			];
			echo json_encode($data);
		}
	}
?>