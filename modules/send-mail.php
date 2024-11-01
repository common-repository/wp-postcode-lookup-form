<?php
function wpplf23_sendMail($input, $email) {
	$options = get_option('wpplf23_plugin_options');
	
	if (!$email == "") {
		$to = $email;
		$subject = "Thank you for your enquirey";
	} else {
		if ((isset($options['notification_email_enable']))) {
			$to = $options['smtp_email_to_address'];
			$subject = $options['notification_email_subject'];
		} else {
			return;
		}
	}
	  
		//$to = get_option('admin_email');
		
		if ((isset($options['smtp_email_enable']))) {
			$mail = new PHPMailer;
			//Debug
			//$mail->SMTPDebug = false;
			//$mail->do_debug = 0;
			//End Debug
			$mail->isSMTP();                                      // Set mailer to use SMTP
			$mail->Host = $options['smtp_email_host'];                       // Specify main and backup server
			if ($options['smtp_email_auth_enabled'] == 1) {
				$mail->SMTPAuth = true;                               // Enable SMTP authentication
			} else {
				$mail->SMTPAuth = false;                               // Disable SMTP authentication
			}
			
			$mail->Username = $options['smtp_email_user'];                   // SMTP username
			$mail->Password = $options['smtp_email_pass'];               // SMTP password
			$mail->SMTPSecure = $options['smtp_email_auth_type'];                            // Enable encryption, 'ssl' also accepted
			$mail->Port = $options['smtp_email_port'];                                    //Set the SMTP port number - 587 for authenticated TLS
			$mail->setFrom($options['smtp_email_from_address'], $options['smtp_email_from_name']);     //Set who the message is to be sent from
			$mail->addReplyTo($options['smtp_email_reply_address'], $options['smtp_email_reply_name']);  //Set an alternative reply-to address
			$mail->addAddress($to, $options['smtp_email_to_name']);  // Add a recipient
			$mail->addCC($options['smtp_email_cc']);
			//$mail->addBCC('bcc@example.com');
			$mail->WordWrap = 50;                                 // Set word wrap to 50 characters
			//$mail->addAttachment('/usr/labnol/file.doc');         // Add attachments
			//$mail->addAttachment('/images/image.jpg', 'new.jpg'); // Optional name
			$mail->isHTML(true);                                  // Set email format to HTML
			$mail->Subject = $subject;
			$mail->Body    = $input;
			$mail->AltBody = $input;
			//Read an HTML message body from an external file, convert referenced images to embedded,
			//convert HTML into a basic plain-text alternative body
			if(!$mail->send()) {
			   echo 'Message could not be sent.';
			   echo 'Mailer Error: ' . $mail->ErrorInfo;
			   exit;
			}

			return;

		} else {
			wp_mail( $to, $subject, $input );
		}
}
?>