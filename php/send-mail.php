<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/PHPMailer.php';
require 'src/SMTP.php';
require 'src/Exception.php';

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	$name    = trim( $_POST['UserName'] );
	$email   = trim( $_POST['UserEmail'] );
	$subject = trim( $_POST['subject'] );
	$message = trim( $_POST['message'] );

	if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
		echo json_encode(
			array(
				'status'  => 'error',
				'message' => 'Invalid email address.',
			)
		);
		exit;
	}

	$mail = new PHPMailer( true );

	try {
		// SMTP Settings
		$mail->isSMTP();
		$mail->Host       = 'smtp.office365.com';
		$mail->SMTPAuth   = true;
		$mail->Username   = 'mostafa.amin@bastpet.co.uk';
		$mail->Password   = 'Bast2023&'; // use App Password if MFA is enabled
		$mail->SMTPSecure = 'tls';
		$mail->Port       = 587;

		// Email Content
		$mail->setFrom( 'mostafa.amin@bastpet.co.uk', 'BastPet Contact Form' );
		$mail->addAddress( 'mostafa.amin@bastpet.co.uk' );
		$mail->addReplyTo( $email, $name );

		$mail->isHTML( true );
		$mail->Subject = $subject;
		$mail->Body    = "
            <strong>Name:</strong> $name <br>
            <strong>Email:</strong> $email <br>
            <strong>Subject:</strong> $subject <br><br>
            <strong>Message:</strong> <br>$message
        ";

		$mail->send();
		echo json_encode(
			array(
				'status'  => 'success',
				'message' => 'Thank you! Your message has been sent.',
			)
		);
	} catch ( Exception $e ) {
		echo json_encode(
			array(
				'status'  => 'error',
				'message' => 'Mailer Error: ' . $mail->ErrorInfo,
			)
		);
	}
}
