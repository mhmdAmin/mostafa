<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Adjust if needed


// Handle form POST
if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	$name    = trim( $_POST['UserName'] ?? '' );
	$email   = trim( $_POST['UserEmail'] ?? '' );
	$subject = trim( $_POST['subject'] ?? '' );
	$message = trim( $_POST['message'] ?? '' );
}
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
	// Server settings
	$mail->isSMTP();
	$mail->Host       = 'smtp.gmail.com';
	$mail->SMTPAuth   = true;
	$mail->Username   = 'bastpet.uk@gmail.com';        // Your Gmail
	$mail->Password   = 'bqqzzthczuaijqht';          // 16-char App Password for Gmail
	$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
	$mail->Port       = 587;

	// Recipients
	$mail->setFrom( 'bastpet.uk@gmail.com', 'bastpet.co.uk[ Contact Form ]' );
	// $mail->addAddress( 'bastpet.uk@gmail.com' );         // Send to yourself
	$mail->addAddress( 'mostafa.amin@bastpet.co.uk' );         // Send to yourself
	// Optional: Forward copy to business email (can also be done via Gmail settings)

	// Content
	$mail->isHTML( true );
	$mail->CharSet  = 'UTF-8';
	$mail->Encoding = 'base64';
	$mail->Subject  = $subject;
	$mail->Body     = $message;
	$mail->AltBody  = $message;

	$mail->send();
	// echo json_encode(['success' => true, 'message' => 'Message has been sent']);
} catch ( Exception $e ) {
	echo json_encode(
		array(
			'success' => false,
			'message' => "Message could not be sent. Error: {$mail->ErrorInfo}",
		)
	);
}
